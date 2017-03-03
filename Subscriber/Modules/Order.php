<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Modules;

use Enlight\Event\SubscriberInterface;
use Shopware\Plugins\StripePayment\Util;

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
            'Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber' => array('onFilterOrderNumber', 10000)
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
        if ($session->sOrderVariables->sPayment['action'] === 'StripePayment') {
            return;
        }

        $stripeSession = Util::getStripeSession();
        if (!isset($stripeSession->orderNumber)) {
            // Save the created order number both in the session's sOrderVariables and stripeSession
            $stripeSession->orderNumber = $args->getReturn();
            $session->sOrderVariables->sOrderNumber = $args->getReturn();
        }

        // Use the cached order number
        return $stripeSession->orderNumber;
    }
}
