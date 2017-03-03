<?php
namespace Shopware\Plugins\StripePayment\Subscriber\Frontend;

use Enlight\Event\SubscriberInterface;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Frontend implements SubscriberInterface
{
    /**
     * The path Apple Pay expects the domain association file to be found at.
     */
    const APPLE_PAY_DOMAIN_ASSOCIATION_FILE_PATH = '/.well-known/apple-developer-merchantid-domain-association';

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Front_RouteShutdown' => 'onRouteShutdown'
        );
    }

    /**
     * Checks whether the the Apple Pay domain association file is request and, if it is,
     * reoroutes the request to '/StripePaymentApplePay/domainAssociationFile'.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onRouteShutdown(\Enlight_Event_EventArgs $args)
    {
        $request = $args->getRequest();
        if ($request->getPathInfo() === self::APPLE_PAY_DOMAIN_ASSOCIATION_FILE_PATH) {
            $request->setControllerName('StripePaymentApplePay');
            $request->setActionName('domainAssociationFile');
        }
    }
}
