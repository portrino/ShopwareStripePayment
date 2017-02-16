<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Frontend;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for frontend controllers.
 *
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Account implements SubscriberInterface
{
    /**
     * @var string $path
     */
    private $path;

    /**
     * @param Bootstrap $bootstrap
     */
    public function __construct(Bootstrap $bootstrap)
    {
        $this->path = $bootstrap->Path();
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'onPostDispatchSecure'
        );
    }

    /**
     * Adds views of this plugin to the account template.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecure(\Enlight_Event_EventArgs $args)
    {
        if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
            // Shopware 4
            $args->getSubject()->View()->extendsTemplate('frontend/stripe_payment/account/content_right.tpl');
        }
    }
}
