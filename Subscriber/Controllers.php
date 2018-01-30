<?php
namespace Shopware\Plugins\StripePayment\Subscriber;

use Enlight\Event\SubscriberInterface;
use \Shopware_Plugins_Frontend_StripePayment_Bootstrap as Bootstrap;

/**
 * A subscriber returning the paths of custom controllers.
 *
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Controllers implements SubscriberInterface
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
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_StripePayment' => 'onGetControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePayment' => 'onGetControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePaymentApplePay' => 'onGetControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePaymentCard' => 'onGetControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePaymentAccount' => 'onGetControllerPath',
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPath(\Enlight_Event_EventArgs $args)
    {
        $moduleName = $args->getRequest()->getModuleName();
        $moduleName = ucfirst($moduleName);
        $controllerName = $args->getRequest()->getControllerName();
        $controllerName = str_replace('_', '', ucwords($controllerName, '_'));

        return $this->path . 'Controllers/' . $moduleName . '/' . $controllerName . '.php';
    }
}
