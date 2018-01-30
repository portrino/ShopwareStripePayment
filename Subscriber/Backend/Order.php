<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Backend;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for backend controllers.
 *
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Order implements SubscriberInterface
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
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => array(
                'onPostDispatchSecure',
                -100,
            ),
        );
    }

    /**
     * Includes the custom backend order controllers, models, stores and views.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecure(\Enlight_Event_EventArgs $args)
    {
        if ($args->getRequest()->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/order_detail_position_refund.js');
            $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/order_detail_stripe_dashboard_button.js');
        }
    }
}
