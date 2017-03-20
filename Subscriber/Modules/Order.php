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
     * Checks the Stripe session for an 'orderNumber' and, if it is not set, saves the order
     * number contained in the event $args in the session and returns it. If however 'orderNumber'
     * is already set in the Stripe session, the cached value is returned. This is a workaround
     * that allows calling 'sOrder::sGetOrderNumber()' more than once for the same checkout and
     * still receiving the same order number.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFilterOrderNumber(\Enlight_Event_EventArgs $args)
    {
        // Check the selected payment method
        $session = Shopware()->Container()->get('session');
        if ($session->sOrderVariables->sPayment['action'] !== 'StripePayment') {
            return;
        }

        // Use the order number saved in the stripe session, if possible
        $stripeSession = Util::getStripeSession();
        $stripeSession->orderNumber = ($stripeSession->orderNumber) ?: $args->getReturn();

        return $stripeSession->orderNumber;
    }
}
