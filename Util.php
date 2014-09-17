<?php

class Shopware_Plugins_Frontend_ViisonStripePayment_Util
{

	/**
	 * @return If test mode is activated, the default test public key. Otherwise the Stripe public key set in the plugin configuration.
	 */
	public static function stripePublicKey() {
		$testMode = Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('testMode');
		return ($testMode) ? 'pk_test_bA2NxqEoDlCGBM2WiyTQClBN' : Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('stripePublicKey');
	}

	/**
	 * @return If test mode is activated, the default test secret key. Otherwise the Stripe secret key set in the plugin configuration.
	 */
	public static function stripeSecretKey() {
		$testMode = Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('testMode');
		return ($testMode) ? 'sk_test_8cku9VMwOVl7wMfPYFX1NUwd' : Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('stripeSecretKey');
	}

	/**
	 * Uses the Stripe customer id of the active user to retrieve the customer from Stripe
	 * and store the customer's default card in the session.
	 */
	public static function loadStripeCard() {
		$customer = self::getCustomer();
		if ($customer === null || $customer->getAccountMode() === 1 || $customer->getAttribute()->getViisonStripeCustomerId() === null) {
			// Customer not found, without permanent user account or has no stripe customer associated with it
			return;
		}

		// Load the Stripe customer and store them in the session
		try {
			$stripeCustomerId = $customer->getAttribute()->getViisonStripeCustomerId();
			$stripeCustomer = Stripe_Customer::retrieve($stripeCustomerId);
			if ($stripeCustomer->deleted) {
				// The user was deleted
				unset(Shopware()->Session()->stripeCard);
				return;
			}
		} catch (Exception $e) {
			// Ignore exceptions in this case, because as a fallback the customer can always provide
			// new credit card data
			return;
		}

		// Save the default card in the session
		foreach ($stripeCustomer->cards->data as $card) {
			if ($card->id === $stripeCustomer->default_card) {
				// Save the required card data in the session
				Shopware()->Session()->stripeCard = array(
					'id' => $card->id,
					'name' => $card->name,
					'last4' => $card->last4,
					'exp_month' => $card->exp_month,
					'exp_year' => $card->exp_year
				);
				return;
			}
		}

		// The customer has no default card
		unset(Shopware()->Session()->stripeCard);
	}

	/**
	 * Tries to get the Stripe transaction token from the current session and uses it to
	 * either add the card to an existing customer, associated with the active user, or
	 * creates a new Stripe customer with that card. Finally it saves the basic card data
	 * in the current session.
	 *
	 * @throws An exception, if creating a customer or updating an existing customer's cards throws an exception.
	 */
	public static function saveStripeCustomer() {
		$customer = self::getCustomer();
		if ($customer === null || $customer->getAccountMode() === 1) {
			// Customer not found or without permanent user account
			return;
		}

		// Check if a card token exists
		$cardToken = Shopware()->Session()->stripeTransactionToken;
		if ($cardToken === null) {
			return;
		}

		// Check for an existing customer
		$cardSaved = false;
		if ($customer->getAttribute()->getViisonStripeCustomerId() !== null) {
			// Save the card in the existing customer
			$stripeCustomerId = $customer->getAttribute()->getViisonStripeCustomerId();
			$stripeCustomer = Stripe_Customer::retrieve($stripeCustomerId);
			if ($stripeCustomer !== null && !$stripeCustomer->deleted) {
				// Add the card
				$stripeCustomer->cards->create(array(
					'card' => $cardToken
				));
				$cardSaved = true;
			}
		}
		if (!$cardSaved) {
			// Create a new customer using the card token
			$stripeCustomer = Stripe_Customer::create(array(
				'description' => self::getCustomerName(),
				'email' => $customer->getEmail(),
				'card' => $cardToken
			));

			// Save the Stripe customer id
			$customer->getAttribute()->setViisonStripeCustomerId($stripeCustomer->id);
			Shopware()->Models()->flush($customer->getAttribute());
		}

		// Remove the stripe token from the session because it is no longer valid
		unset(Shopware()->Session()->stripeTransactionToken);

		// Save the default card in the session
		foreach ($stripeCustomer->cards->data as $card) {
			if ($card->id === $stripeCustomer->default_card) {
				Shopware()->Session()->stripeCard = array(
					'id' => $card->id,
					'name' => $card->name,
					'last4' => $card->last4,
					'exp_month' => $card->exp_month,
					'exp_year' => $card->exp_year
				);
				break;
			}
		}
	}

	/**
	 * First tries to find currently logged in user in the database and checks their stripe customer id.
	 * If found, the customer information is loaded from Stripe and returned.
	 *
	 * @return The customer, which was loaded from Stripe or null, if e.g. the customer does not exist.
	 * @throws An exception, if Stripe could not load the customer.
	 */
	public static function getStripeCustomer() {
		// Get the current logged in customer
		$customer = self::getCustomer();
		if ($customer === null || $customer->getAccountMode() === 1 || $customer->getAttribute()->getViisonStripeCustomerId() === null) {
			// Customer not found, without permanent user account or has no stripe customer associated with it
			return null;
		}

		// Load and return the customer
		$stripeCustomerId = $customer->getAttribute()->getViisonStripeCustomerId();

		return Stripe_Customer::retrieve($stripeCustomerId);
	}

	/**
	 * Checks if a user/customer is currently logged in and tries to get and return that customer.
	 *
	 * @return The customer object of the user who is logged in, or null, if no user is logged in.
	 */
	public static function getCustomer() {
		// Check if a user is logged in
		if (!Shopware()->Session()->sOrderVariables['sUserLoggedIn']) {
			return null;
		}

		// Try to find the customer
		$customerId = Shopware()->Session()->sOrderVariables['sUserData']['additional']['user']['customerId'];
		$customerRepository = Shopware()->Models()->getRepository('\Shopware\Models\Customer\Customer');
		$customer = $customerRepository->find($customerId);

		return $customer;
	}

	/**
	 * @return The customers company name, if it exists. Otherwise their joined first and last name or null, if no user is logged in.
	 */
	public static function getCustomerName() {
		$customer = self::getCustomer();
		if ($customer === null) {
			return null;
		}

		// Check for company
		$company = $customer->getBilling()->getCompany();
		if (!empty($company)) {
			return $company;
		}

		// Use first and last name
		return trim($customer->getBilling()->getFirstName() . ' ' . $customer->getBilling()->getLastName());
	}

	/**
	 * Tries to delete the Stripe card with the given id from
	 * the Stripe customer, which is associated with the currently
	 * logged in user.
	 *
	 * @param $cardId The Stripe id of the card, which shall be deleted.
	 */
	public static function deleteStripeCard($cardId) {
		// Get the Stripe customer
		$customer = self::getStripeCustomer();
		if ($customer === null) {
			return;
		}

		// Delete the card with the given id from Stripe
		$customer->cards->retrieve($cardId)->delete();
	}

}
