<?php

namespace Shopware\Plugins\StripePayment\Components;

use ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod,
	Shopware\Plugins\StripePayment\Util;

/**
 * A simplified payment method instance that is only used to validate the Stripe payment
 * information like transaction token or card ID primarily to prevent quick checkout in
 * Shopware 5 when neither of those exist.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
abstract class BaseStripePaymentMethod extends GenericPaymentMethod
{

	/**
	 * Validates the given payment data by checking for a Stripe transaction token or card ID.
	 *
	 * @param array $paymentData
	 * @return array List of fields containing errors
	 */
	protected function doValidate(array $paymentData) {
		// Check the payment data for a Stripe transaction token or a selected card ID
		if (empty($paymentData['stripeTransactionToken']) && empty($paymentData['stripeCardId'])) {
			return array(
				'STRIPE_VALIDATION_FAILED'
			);
		}

		return array();
	}

	/**
	 * Fetches the Stripe transaction token from the session as well as the selected Stripe card,
	 * either from the session or as fallback directly from Stripe.
	 *
	 * @param userId The ID of the user.
	 * @return array|null
	 */
	public function getCurrentPaymentDataAsArray($userId) {
		// Try to get the Stripe token and/or the currently selected Stripe card
		$stripeTransactionToken = Shopware()->Session()->stripeTransactionToken;
		$allStripeCards = Util::getAllStripeCards();
		$stripeCardId = Shopware()->Session()->stripeCardId;
		if (empty($stripeCardId) && Util::getDefaultStripeCard() !== null) {
			// Use the default card instead
			$stripeCard = Util::getDefaultStripeCard();
			$stripeCardId = $stripeCard['id'];
		}

		return array(
			'stripeTransactionToken' => $stripeTransactionToken,
			'stripeCardId' => $stripeCardId
		);
	}

}

/**
 * Returns true, if the signature of GenericPaymentMethod#validate appears consistent with Shopware before version
 * 5.0.4-RC1.
 *
 * Since version 5.0.4-RC1, the parameter must be an array (with no type hint).
 * Before, it was an \Enlight_Controller_Request_Request.
 *
 * The commit that changed the signature of #validate is
 * <https://github.com/shopware/shopware/commit/0608b1a7b05e071c93334b29ab6bd588105462d7>.
 */
function needs_legacy_validate_signature() {
	$parentClass = new \ReflectionClass('ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod');
	/* @var $parameters \ReflectionParameter[] */
	$parameters = $parentClass->getMethod('validate')->getParameters();
	foreach ($parameters as $parameter) {
		// Newer Shopware versions use an array parameter named paymentData.
		if ($parameter->getName() === 'request') {
			return true;
		}
	}
	return false;
}

if (needs_legacy_validate_signature()) {
	class StripePaymentMethod extends BaseStripePaymentMethod {
		public function validate(\Enlight_Controller_Request_Request $request) {
			return parent::doValidate($request->getParams());
		}
	}
} else {
	class StripePaymentMethod extends BaseStripePaymentMethod {
		public function validate($paymentData) {
			return parent::doValidate($paymentData);
		}
	}
}
