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
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_StripePayment' => 'onGetControllerPathBackend',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePayment' => 'onGetControllerPathFrontend',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_StripePaymentAccount' => 'onGetControllerPathFrontend'
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathBackend(\Enlight_Event_EventArgs $args)
    {
        return $this->getControllerPath($args, 'Backend');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathFrontend(\Enlight_Event_EventArgs $args)
    {
        return $this->getControllerPath($args, 'Frontend');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @param string $module
     * @return string
     */
    protected function getControllerPath(\Enlight_Event_EventArgs $args, $module)
    {
        $controllerName = $args->getRequest()->getControllerName();
        $controllerName = str_replace('_', '', ucwords($controllerName, '_'));

        return $this->path . 'Controllers/' . ucfirst($module) . '/' . $controllerName .'.php';
    }
}
