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
class StripePaymentMethod extends GenericPaymentMethod
{

	/**
	 * Validates the given payment data by checking for a Stripe transaction token or card ID.
	 *
	 * @param array $paymentData
	 * @return array List of fields containing errors
	 */
	public function validate($paymentData) {
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
