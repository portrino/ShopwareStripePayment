<?php
// Define the CSRFWhitelistAware interface for Shopware versions < 5.2
if (!interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    interface CSRFWhitelistAware {}
}

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Order;
use Shopware\Plugins\StripePayment\Util;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
abstract class Shopware_Controllers_Frontend_StripePayment extends Shopware_Controllers_Frontend_Payment implements CSRFWhitelistAware
{
    /**
     * The ID of the order payment status 'completely paid'
     */
    const PAYMENT_STATUS_COMPLETELY_PAID = 12;

    /**
     * The ID of the order payment status 'open'
     */
    const PAYMENT_STATUS_OPEN = 17;

    /**
     * The ID of the order payment status 'review necessary'
     */
    const PAYMENT_STATUS_REVIEW_NECESSARY = 21;

    /**
     * @inheritdoc
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'stripeWebhook'
        );
    }

    /**
     * @inheritdoc
     */
    public function getOrderNumber()
    {
        return (parent::getOrderNumber()) ?: $this->get('modules')->Order()->sGetOrderNumber();
    }

    /**
     * Creates a source using the selected Stripe payment method class and completes its payment
     * flow. That is, if the source is already chargeable, the charge is created and the order is
     * saved. If however the source requires a flow like 'redirect', the flow is executed without
     * charing the source or creating an order (these steps will be peformed by the flow).
     */
    public function indexAction()
    {
        $stripeSession = Util::getStripeSession();

        // Create a source using the selected Stripe payment method
        try {
            $source = $this->getStripePaymentMethod()->createStripeSource(
                ($this->getAmount() * 100), // Amount has to be in cents!
                $this->getCurrencyShortName(),
                $this->getStatementDescriptor()
            );
        } catch (Exception $e) {
            $this->get('pluginlogger')->error('StripePayment: Failed to create source', array('exception' => $e, 'trace' => $e->getTrace()));
            $message = $this->getStripePaymentMethod()->getErrorMessage($e);
            $this->cancelCheckout($message);
            return;
        }

        // Trigger the payment flow if required
        if ($source->flow === 'redirect') {
            if ($source->redirect->status === 'failed') {
                $message = $this->getStripePaymentMethod()->getSnippet('payment_error/message/redirect/failed');
                $this->cancelCheckout($message);
                return;
            }

            // Perform a redirect to complete the payment flow
            $stripeSession->redirectClientSecret = $source->client_secret;
            $this->redirect($source->redirect->url);
        } elseif ($source->status === 'chargeable') {
            // No special flow required, hence use the source to create the charge and save the order
            try {
                $charge = $this->createCharge($source);
                $order = $this->saveOrderWithCharge($charge);
            } catch (Exception $e) {
                $this->get('pluginlogger')->error('StripePayment: Failed to create charge', array('exception' => $e, 'trace' => $e->getTrace(), 'sourceId' => $source->id));
                $message = $this->getStripePaymentMethod()->getErrorMessage($e);
                $this->cancelCheckout($message);
                return;
            }

            $this->finishCheckout($order);
        } else {
            // Unable to process payment
            $message = $this->getStripePaymentMethod()->getSnippet('payment_error/message/source_declined');
            $this->cancelCheckout($message);
        }
    }

    /**
     * Note: Only use this action for creating the return URL of a Stripe redirect flow.
     *
     * Compares the 'client_secret' contained in the redirect request with the session and,
     * if valid, fetches the respective source and charges it with the order amount. Finally
     * the order is saved and the checkout is finished.
     */
    public function completeRedirectFlowAction()
    {
        Util::initStripeAPI();
        // Compare the client secrets
        $clientSecret = $this->Request()->getParam('client_secret');
        if (!$clientSecret || $clientSecret !== Util::getStripeSession()->redirectClientSecret) {
            $message = $this->getStripePaymentMethod()->getSnippet('payment_error/message/redirect/internal_error');
            $this->cancelCheckout($message);
            return;
        }

        // Try to get the Stripe source
        $sourceId = $this->Request()->getParam('source');
        $source = Stripe\Source::retrieve($sourceId);
        if (!$source) {
            $message = $this->getStripePaymentMethod()->getSnippet('payment_error/message/redirect/internal_error');
            $this->cancelCheckout($message);
            return;
        } elseif ($source->status !== 'chargeable') {
            $message = $this->getStripePaymentMethod()->getSnippet('payment_error/message/redirect/source_not_chargeable');
            $this->cancelCheckout($message);
            return;
        }

        // Use the source to create the charge and save the order
        try {
            $charge = $this->createCharge($source);
            $order = $this->saveOrderWithCharge($charge);
        } catch (Exception $e) {
            $message = $this->getStripePaymentMethod()->getErrorMessage($e);
            $this->cancelCheckout($message);
            return;
        }

        $this->finishCheckout($order);
    }

    /**
     * Parses the request body and tries to retrieve both the POSTed webhook event as well as the
     * order that references the source contained in the event for verification. If the webhook
     * call is valid, the is updated based on the event type:
     *
     *  - charge.succeeded: Set the orders payment status to 'completely paid'
     *  - charge.failed: Set the orders payment status to 'review necessary'
     */
    public function stripeWebhookAction()
    {
        Util::initStripeAPI();
        // Disable the default renderer to supress errors caused by the template engine
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        // Try to parse the request payload
        try {
            $rawBody = $this->Request()->getRawBody();
            $eventJson = Zend_Json::decode($rawBody);
        } catch (\Exception $e) {
            echo 'Failed to decode request JSON';
            return;
        }

        // Verify the event by fetching it from Stripe and finding the corresponding order
        $event = Stripe\Event::retrieve($eventJson['id']);
        $order = $this->get('models')->getRepository('Shopware\Models\Order\Order')->findOneBy(array(
            'temporaryId' => $event->data->object->source->id
        ));
        if (!$order) {
            echo 'Could not find order for event';
            return;
        }

        switch ($event->type) {
            case 'charge.succeeded':
                // Update the order's payment status to 'completely paid'
                $paymentStatus = $this->get('models')->find('Shopware\Models\Order\Status', self::PAYMENT_STATUS_COMPLETELY_PAID);
                $order->setPaymentStatus($paymentStatus);
                $this->get('models')->flush($order);
                break;
            case 'charge.failed':
                // Update the order's payment status to 'review necessary'
                $paymentStatus = $this->get('models')->find('Shopware\Models\Order\Status', self::PAYMENT_STATUS_REVIEW_NECESSARY);
                $order->setPaymentStatus($paymentStatus);
                $this->get('models')->flush($order);
                break;
        }

        // Just respond with 'OK' to make debugging easier
        echo 'OK';
    }

    /**
     * Creates and returns a Stripe charge for the order, whose checkout is handled by this
     * controller, using the provided Stripe $source.
     *
     * @param Stripe\Source $source
     * @return Stripe\Charge
     * @throws Exception If creating the charge failed.
     */
    protected function createCharge(Stripe\Source $source)
    {
        // Get the necessary user info and shop info
        $user = $this->getUser();
        $userEmail = $user['additional']['user']['email'];
        if ($this->get('plugins')->get('Frontend')->get('StripePayment')->assertMinimumVersion('5.2.0')) {
            $customerNumber = $user['additional']['user']['customernumber'];
        } else {
            $customerNumber = $user['billingaddress']['customernumber'];
        }
        $orderNumber = $this->getOrderNumber();

        // Prepare the charge data
        $chargeData = array(
            'source' => $source->id,
            'amount' => ($this->getAmount() * 100), // Amount has to be in cents!
            'currency' => $this->getCurrencyShortName(),
            'description' => sprintf('%s / Kunden-Nr.: %s / Bestell-Nr. %s', $userEmail, $customerNumber, $orderNumber),
            'metadata' => array(
                'platform_name' => Util::STRIPE_PLATFORM_NAME
            )
        );
        // Try to add a customer reference to the charge
        $stripeCustomer = Util::getStripeCustomer();
        if ($source->customer && $stripeCustomer) {
            $chargeData['customer'] = $stripeCustomer->id;
        }

        return Stripe\Charge::create($chargeData);
    }

    /**
     * Saves the order in the database adding both the ID of the given $charge (as 'transactionId')
     * and the charge's 'balance_transaction' (as 'paymentUniqueId' aka 'temporaryID'). We use the
     * 'balance_transaction' as 'paymentUniqueId', because altough the column in the backend order
     * list is named 'Transaktion' or 'tranaction', it DOES NOT display the transactionId, but the
     * field 'temporaryID', to which the 'paymentUniqueId' is written. Additionally the
     * 'balance_transaction' is displayed in the shop owner's Stripe account, so it can be used to
     * easily identify an order. Finally the cleared date of the order is set to the current date
     * and the order is returned.
     *
     * @param Stripe\Charge $charge
     * @return Order
     */
    protected function saveOrderWithCharge(Stripe\Charge $charge)
    {
        // Save the payment details in the order. Use the balance_transaction as the paymentUniqueId,
        // because altough the column in the backend order list is named 'Transaktion' or 'tranaction',
        // it displays NOT the transactionId, but the field 'temporaryID', to which the paymentUniqueId
        // is written. Additionally the balance_transaction is displayed in the shop owner's Stripe
        // account, so it can be used to easily identify an order.
        $orderNumber = $this->saveOrder(
            $charge->id, // transactionId
            $charge->source->id, // paymentUniqueId
            ($charge->status === 'succeeded') ? self::PAYMENT_STATUS_COMPLETELY_PAID : self::PAYMENT_STATUS_OPEN // paymentStatusId
        );
        if (!$orderNumber) {
            // Order creation failed
            return null;
        }

        // Update the cleared date
        $order = $this->get('models')->getRepository('Shopware\Models\Order\Order')->findOneBy(array(
            'number' => $orderNumber
        ));
        $order->setClearedDate(new \DateTime());
        $this->get('models')->flush($order);

        return $order;
    }

    /**
     * Finishes the checkout process by redirecting to the checkout's finish page. By passing the
     * 'paymentUniqueId' (aka 'temporaryID') to 'sUniqueID', we allow an early return of the 'Checkout'
     * controller's 'finishAction()'. The order is created by calling 'saveOrder()' on this controller
     * earlier, so it definitely exists after the redirect. However, 'finishAction()' can only find
     * the order, if we pass the 'sUniqueID' here. If we don't pass the 'paymentUniqueId', there are
     * apparently some shops that fail to display the order summary, although a vanilla Shopware 5 or 5.1
     * installation works correctly. That is, because the basket is empty after creating the order,
     * the session's sOrderVariables are assigned to the view and NO redirect to the confirm action
     * is performed (see https://github.com/shopware/shopware/blob/6e8b58477c1a9aa873328c258139fa6085238b4b/engine/Shopware/Controllers/Frontend/Checkout.php#L272-L275).
     * Anyway, setting 'sUniqueID' seems to be the safe way to display the order summary.
     *
     * @param Order $order
     */
    protected function finishCheckout(Order $order)
    {
        Util::resetStripeSession();
        $this->redirect(array(
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $order->getTemporaryId()
        ));
    }

    /**
     * Cancles the checkout process by redirecting (back) to the checkout's confirm page. If the optional
     * parameter $errorMessage is set, it is prefixed added to the session so that it will be displayed on the
     * confirm page after the redirect.
     *
     * @param string|null $errorMessage
     */
    protected function cancelCheckout($errorMessage = null)
    {
        if ($errorMessage) {
            $prefix = $this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/base')->get('payment_error/message/charge_failed');
            Util::getStripeSession()->paymentError = $prefix . ' ' . $errorMessage;
        }
        $this->redirect(array(
            'controller' => 'checkout',
            'action' => ($this->get('shop')->getTemplate()->getVersion() < 3) ? 'confirm' : 'index'
        ));
    }

    /**
     * Returns an instance of a Stripe payment method, which is used e.g. to create
     * stripe sources.
     *
     * @return Shopware\Plugins\StripePayment\Components\PaymentMethods\Base
     */
    protected function getStripePaymentMethod()
    {
        $paymentMethod = $this->get('session')->sOrderVariables->sPayment;
        $adminModule = $this->get('modules')->Admin();

        return $adminModule->sInitiatePaymentClass($paymentMethod);
    }

    /**
     * Checks the Stripe session for a 'statementDescriptor' and, if not found, creates a new one and
     * saves it in the session, before returning it. The statement descriptor has the format
     * '<shop_name> (<shop_url>), Nr. <order_number>' and is truncated to at most 22 characters, which
     * is the hard limit enforced by the Stripe API.
     *
     * @return string
     */
    protected function getStatementDescriptor()
    {
        $stripeSession = Util::getStripeSession();
        if (!$stripeSession->statementDescriptor) {
            // Determine the statement descriptor and save it in the session
            $shopName = $this->get('shop')->getName();
            $shopUrl = $this->Request()->getHttpHost() . $this->Request()->getBaseUrl();
            $orderNumber = $this->getOrderNumber();
            $statementDescriptor = sprintf('%s (%s), Nr. %s', $shopName, $shopUrl, $orderNumber);
            $stripeSession->statementDescriptor = substr($statementDescriptor, 0, 22);
        }

        return $stripeSession->statementDescriptor;
    }
}
