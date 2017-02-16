<?php
namespace Shopware\Plugins\StripePayment\Subscriber;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for backend controllers.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Backend implements SubscriberInterface
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
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => array('onPostDispatchOrder', -100)
        );
    }

    /**
     * Includes the custom backend header extension, which loads the Stripe detial tap alongside
     * the order app.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchIndex(\Enlight_Event_EventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/index/header.tpl');
    }

    /**
     * Includes the custom backend order controllers, models, stores and views.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchOrder(\Enlight_Event_EventArgs $args)
    {
        if ($args->getRequest()->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/order_detail_position_refund.js');
            $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/order_detail_stripe_dashboard_button.js');
        }
    }
}
