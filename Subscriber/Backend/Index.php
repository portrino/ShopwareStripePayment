<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Backend;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for backend controllers.
 *
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Index implements SubscriberInterface
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
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecure',
        );
    }

    /**
     * Includes the custom backend header extension, which loads the Stripe detial tap alongside
     * the order app.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecure(\Enlight_Event_EventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate('backend/stripe_payment/index/header.tpl');
    }
}
