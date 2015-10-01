<?php

namespace Shopware\Plugins\ViisonStripePayment;

use Stripe;

/**
 * Utility functions used across this plugin.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Util
{

	/**
	 * This field is used as a cache for the Stripe customer object of the
	 * currently logged in user.
	 *
	 * @var Stripe_Customer
	 */
	private static $stripeCustomer;

	/**
	 * Sets the Stripe secret key.
	 */
	public static function initStripeAPI() {
		// Set the Stripe API key
		$stripeSecretKey = self::stripeSecretKey();
		Stripe\Stripe::setApiKey($stripeSecretKey);
	}

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
	 * and returns the customer's credit cards.
	 *
	 * @return An array containing information about all loaded credit cards.
	 * @throws An exception, if loading the Stripe customer fails.
	 */
	public static function getAllStripeCards() {
		// Get the Stripe customer
		$customer = self::getStripeCustomer();
		if ($customer === null || $customer->deleted) {
			return array();
		}

		// Get information about all cards
		$cards = array_map(function ($card) {
			return array(
				'id' => $card->id,
				'name' => $card->name,
				'brand' => $card->brand,
				'last4' => $card->last4,
				'exp_month' => $card->exp_month,
				'exp_year' => $card->exp_year
			);
		}, $customer->cards->data);

		// Sort the cards by id (which correspond to the date, the card was created/added)
		usort($cards, function($cardA, $cardB) {
			return strcmp($cardA['id'], $cardB['id']);
		});

		return $cards;
	}

	/**
	 * Uses the Stripe customer id of the active user to retrieve the customer from Stripe
	 * and returns the customer's default credit card.
	 *
	 * @return The default credit card or null, if no cards exist.
	 * @throws An exception, if loading the Stripe customer fails.
	 */
	public static function getDefaultStripeCard() {
		// Get the Stripe customer
		$customer = self::getStripeCustomer();
		if ($customer === null || $customer->deleted) {
			return null;
		}

		// Get all cards and try to find the one matching the default id
		$cards = self::getAllStripeCards();
		foreach ($cards as $card) {
			if ($card['id'] === $customer->default_card) {
				// Return the default card
				return $card;
			}
		}

		return null;
	}

	/**
	 * First tries to find currently logged in user in the database and checks their stripe customer id.
	 * If found, the customer information is loaded from Stripe and returned.
	 *
	 * @return The customer, which was loaded from Stripe or null, if e.g. the customer does not exist.
	 * @throws An exception, if Stripe could not load the customer.
	 */
	public static function getStripeCustomer() {
		self::initStripeAPI();
		// Check if customer is already loaded
		if (self::$stripeCustomer !== null) {
			return self::$stripeCustomer;
		}

		// Get the current logged in customer
		$customer = self::getCustomer();
		if ($customer === null || $customer->getAccountMode() === 1 || $customer->getAttribute() === null || $customer->getAttribute()->getViisonStripeCustomerId() === null) {
			// Customer not found, without permanent user account or has no stripe customer associated with it
			return null;
		}

		// Load, save and return the customer
		$stripeCustomerId = $customer->getAttribute()->getViisonStripeCustomerId();
		self::$stripeCustomer = Stripe\Customer::retrieve($stripeCustomerId);

		return self::$stripeCustomer;
	}

	/**
	 * Checks if a user/customer is currently logged in and tries to get and return that customer.
	 *
	 * @return The customer object of the user who is logged in, or null, if no user is logged in.
	 */
	public static function getCustomer() {
		// Check if a user is logged in
		if (empty(Shopware()->Session()->sUserId)) {
			return null;
		}

		// Try to find the customer
		$customerId = Shopware()->Session()->sUserId;
		if ($customerId === null) {
			return null;
		}
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
	 * First tries to get an existing Stripe customer. If it exists, the transaction token is used
	 * to add a new card to that customer. If not, a new Stripe customer is created and added the
	 * card represented by the transaction token.
	 *
	 * @param transactionToken The token, which will be used to add/create a new Stripe card.
	 * @return The Stripe_Card, which was created.
	 */
	public static function saveStripeCard($transactionToken) {
		self::initStripeAPI();
		// Get the Stripe customer
		$stripeCustomer = self::getStripeCustomer();
		if ($stripeCustomer !== null && !$stripeCustomer->deleted) {
			// Add the card to the existing customer
			$newCard = $stripeCustomer->cards->create(array(
				'card' => $transactionToken
			));
		} else {
			// Get the currently active user
			$customer = self::getCustomer();
			if ($customer === null || $customer->getAccountMode() === 1) {
				// Customer not found or without permanent user account
				return;
			}

			// Create a new Stripe customer and add the card to them
			$stripeCustomer = Stripe\Customer::create(array(
				'description' => self::getCustomerName(),
				'email' => $customer->getEmail(),
				'card' => $transactionToken
			));
			$newCard = $stripeCustomer->cards->data[0];

			// Save the Stripe customer id
			$customer->getAttribute()->setViisonStripeCustomerId($stripeCustomer->id);
			Shopware()->Models()->flush($customer->getAttribute());
		}

		// Return the created Stripe card
		return $newCard;
	}

	/**
	 * Tries to delete the Stripe card with the given id from
	 * the Stripe customer, which is associated with the currently
	 * logged in user.
	 *
	 * @param $cardId The Stripe id of the card, which shall be deleted.
	 */
	public static function deleteStripeCard($cardId) {
		self::initStripeAPI();
		// Get the Stripe customer
		$customer = self::getStripeCustomer();
		if ($customer === null) {
			return;
		}

		// Delete the card with the given id from Stripe
		$customer->cards->retrieve($cardId)->delete();
	}

}
