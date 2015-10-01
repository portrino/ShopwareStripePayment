<?php

namespace Shopware\Plugins\ViisonStripePayment\Subscriber;

use Enlight\Event\SubscriberInterface,
	\Shopware_Plugins_Frontend_ViisonStripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber for backend controllers.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Backend implements SubscriberInterface
{

	private $path;

	/**
	 * @param bootstrap The plugin bootstrap, used as an DI container.
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->path = $bootstrap->Path();
	}

	/**
	 * @return An array containing all subsciptions.
	 */
	public static function getSubscribedEvents() {
		return array(
			'Enlight_Controller_Action_PostDispatch_Backend_Order' => array('onPostDispatchOrder', -100),
			'Enlight_Controller_Dispatcher_ControllerPath_Backend_ViisonStripePayment' => 'onGetControllerPathViisonStripePayment'
		);
	}

	/**
	 * Includes the custom backend order controllers, models, stores and views.
	 *
	 * @param $args The event parameters.
	 */
	public function onPostDispatchOrder(Enlight_Event_EventArgs $args) {
		if ($args->getRequest()->getActionName() === 'load') {
			$args->getSubject()->View()->extendsTemplate('backend/viison_stripe_payment/order_detail_position_refund.js');
		}
	}

	/**
	 * Returns the path to the Backend/ViisonStripePayment controller used for making payments.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Backend/ViisonStripePayment controller.
	 */
	public function onGetControllerPathViisonStripePayment(Enlight_Event_EventArgs $args) {
		return $this->path . 'Controllers/Backend/ViisonStripePayment.php';
	}

}
