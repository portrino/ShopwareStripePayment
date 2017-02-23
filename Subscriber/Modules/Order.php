<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Modules;

use Enlight\Event\SubscriberInterface;

/**
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Order implements SubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber' => ['onFilterOrderNumber', 10000]
        );
    }

    /**
     * Note: In case that a payment method is selected that is not a Stripe payment method,
     *       this method does nothing.
     *
     * Checks the session's 'sOrderVariables' for a 'sOrderNumber' and, if it is not set,
     * saves the order number contained in the event $args in the session and returns it.
     * If however 'sOrderNumber' is already set in the session, the cached value is returned.
     * This is a workaround that allows calling 'sOrder::sGetOrderNumber()' more than once
     * for the same session and still receiving the same order number.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFilterOrderNumber(\Enlight_Event_EventArgs $args)
    {
        $session = Shopware()->Container()->get('session');
        // Check the selected payment method
        $paymentMethod = $session->sOrderVariables->sPayment['name'];
        if (strpos($paymentMethod, 'stripe_payment_') !== 0) {
            return;
        }

        if (!isset($session->sOrderVariables->sOrderNumber)) {
            // Save the create order number in the session
            $session->sOrderVariables->sOrderNumber = $args->getReturn();
        }

        // Use the cached order number
        return $session->sOrderVariables->sOrderNumber;
    }
}
