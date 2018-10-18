<?php
// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

namespace Shopware\Plugins\StripePayment\Subscriber\Backend;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for backend controllers.
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
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecure',
        ];
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
