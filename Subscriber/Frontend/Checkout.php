<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Frontend;

use Enlight\Event\SubscriberInterface;
use Shopware\Plugins\StripePayment\Util;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for frontend controllers.
 *
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Checkout implements SubscriberInterface
{
    /**
     * @param Bootstrap $bootstrap
     */
    public function __construct(Bootstrap $bootstrap)
    {
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchSecure',
            // Shopware 4 templates only
            'Shopware_Controllers_Frontend_Checkout::paymentAction::after' => 'onAfterPaymentAction',
            // Shopware 5 themes only
            'Shopware_Controllers_Frontend_Checkout::saveShippingPaymentAction::after' => 'onAfterPaymentAction'
        );
    }

    /**
     * Handles different tasks during the checkout process. First it adds the custom templates,
     * which are required for the stripe payment form. If the requested action is the 'confirm' action,
     * the configured stripe public key is passed to the view, so that it can be rendered accordingly.
     * Additionally it checks the session for possible stripe errors and passes them to the view,
     * by setting the variable and appending the template.
     * Furthermore this method checks for a stripe transaction token in the request parameters,
     * and, if present, adds it to the current session. Finally the 'sRegisterFinished' flag is
     * set to 'false', to make the detailed list of payment methods visible in the 'confirm' view.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecure(\Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        $stripeSession = Util::getStripeSession();

        // Prepare the view
        $actionName = $args->getSubject()->Request()->getActionName();
        if (in_array($actionName, array('confirm', 'shippingPayment', 'saveShippingPayment'))) {
            $stripeViewParams = array();
            // Set the stripe public key and some plugin configuration
            $stripeViewParams['publicKey'] = Util::stripePublicKey();
            $stripeViewParams['allowSavingCreditCard'] = Shopware()->Container()->get('plugins')->get('Frontend')->get('StripePayment')->Config()->get('allowSavingCreditCard', true);

            // Check for an error
            if ($stripeSession->paymentError) {
                $stripeViewParams['error'] = $stripeSession->paymentError;
                unset($stripeSession->paymentError);
            }

            if (!$stripeSession->selectedCard) {
                // Load the default card and safe it in the session
                try {
                    $stripeSession->selectedCard = Util::getDefaultStripeCard();
                } catch (\Exception $e) {
                    unset($stripeSession->selectedCard);
                }
            }

            // Update view parameters
            try {
                $stripeViewParams['availableCards'] = Util::getAllStripeCards();
            } catch (\Exception $e) {
                $stripeViewParams['availableCards'] = array();
            }
            if ($stripeSession->selectedCard) {
                // Write the card info to the template both JSON encoded and in a form usable by smarty
                $stripeViewParams['rawSelectedCard'] = json_encode($stripeSession->selectedCard);
                $stripeViewParams['selectedCard'] = $stripeSession->selectedCard;

                // Make sure the selected card is part of the list of available cards
                foreach ($stripeViewParams['availableCards'] as $card) {
                    if ($card['id'] === $stripeSession->selectedCard['id']) {
                        $cardExists = true;
                        break;
                    }
                }
                if (!$cardExists) {
                    $stripeViewParams['availableCards'][] = $stripeSession->selectedCard;
                }
            }
            $stripeViewParams['rawAvailableCards'] = json_encode($stripeViewParams['availableCards']);
            $view->stripePayment = $stripeViewParams;
        }
        if ($actionName === 'confirm' && Shopware()->Container()->get('shop')->getTemplate()->getVersion() < 3) {
            // Load the required templates (Shopware 4 templates only)
            $view->extendsTemplate('frontend/stripe_payment/checkout/confirm.tpl');
            $view->extendsTemplate('frontend/stripe_payment/checkout/card_logos.tpl');

            // Simulate a new customer to make the payment selection in the checkout process visible
            $view->sRegisterFinished = 'false';
        }

        $customer = Util::getCustomer();
        if ($customer) {
            // Add the account mode to the view
            $view->customerAccountMode = $customer->getAccountMode();
        }
    }

    /**
     * Checks the request for stripe parameters and saves them in the session for later use.
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onAfterPaymentAction(\Enlight_Hook_HookArgs $args)
    {
        $request = $args->getSubject()->Request();
        $stripeSession = Util::getStripeSession();
        if ($request->getParam('stripeSelectedCard')) {
            $stripeSession->selectedCard = json_decode($request->getParam('stripeSelectedCard'), true);
        }
        if ($request->getParam('stripeSaveCard')) {
            $stripeSession->saveCardForFutureCheckouts = $request->getParam('stripeSaveCard') === 'on';
        }
    }
}
