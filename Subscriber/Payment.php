<?php

namespace Shopware\Plugins\ViisonStripePayment\Subscriber;

use Enlight\Event\SubscriberInterface;

/**
 * The subscriber for adding the custom ViisonStripePaymentMethod path.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Payment implements SubscriberInterface
{

	/**
	 * @return An array containing all subsciptions.
	 */
	public static function getSubscribedEvents() {
		return array(
			'Shopware_Modules_Admin_InitiatePaymentClass_AddClass' => 'onAddPaymentClass'
		);
	}

	/**
	 * Adds the path to the Stripe payment method class to the return value.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onAddPaymentClass(Enlight_Event_EventArgs $args) {
		$dirs = $args->getReturn();
		$dirs['ViisonStripePaymentMethod'] = 'Shopware\Plugins\ViisonStripePayment\Components\ViisonStripePaymentMethod';
		$args->setReturn($dirs);
	}

}
