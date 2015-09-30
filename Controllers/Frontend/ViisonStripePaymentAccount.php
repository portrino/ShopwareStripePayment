<?php

use Shopware\Plugins\ViisonStripePayment\Util;

/**
 * This controller provides two actions for listing all credit cards of the currently logged in user
 * and for deleting a selected credit card.
 *
 * @copyright Copyright (c) 2014, Viison GmbH
 */
class Shopware_Controllers_Frontend_ViisonStripePaymentAccount extends Shopware_Controllers_Frontend_Account
{

	/**
	 * Override
	 */
	public function preDispatch() {
		parent::preDispatch();

		// Check if user is logged in
		if (!$this->admin->sCheckUser()) {
			unset($this->View()->sUserData);
			return $this->forward('login', 'Account');
		}
	}

	/**
	 * Loads all Stripe credit cards for the currently logged in user and
	 * adds them to the custom template.
	 */
	public function manageCreditCardsAction() {
		// Load the template
		$this->View()->loadTemplate('frontend/plugins/viison_stripe/account/credit_cards.tpl');
		$this->View()->extendsTemplate('frontend/plugins/viison_stripe/account/content_right.tpl');

		// Set the Stripe API key
		$stripeSecretKey = Util::stripeSecretKey();
		Stripe::setApiKey($stripeSecretKey);

		try {
			// Get the customer
			$customer = Util::getStripeCustomer();
			if ($customer === null) {
				// No Stripe customer found, hence return no cards
				$this->View()->creditCards = array();
				return;
			}

			// Save the cards in the list
			$cards = array_map(function ($card) {
				return array(
					'id' => $card->id,
					'holder' => $card->name,
					'type' => $card->brand,
					'number' => ('XXXXXXXXXXXX ' . $card->last4),
					'expiryDate' => (((strlen($card->exp_month) === 1) ? '0' : '') . $card->exp_month . '/' . $card->exp_year)
				);
			}, $customer->cards->data);

			// Sort the cards by id (which correspond to the date, the card was created/added)
			usort($cards, function($cardA, $cardB) {
				return strcmp($cardA['id'], $cardB['id']);
			});
		} catch (Exception $e) {
			$error = 'Beim Laden der Kreditkarten ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.';
			if (Shopware()->Session()->stripeErrorMessage === null) {
				Shopware()->Session()->stripeErrorMessage = $error;
			} else {
				Shopware()->Session()->stripeErrorMessage .= "\n" . $error;
			}
		}

		// Set the view data
		$this->View()->creditCards = $cards;
		$this->View()->errorMessage = Shopware()->Session()->stripeErrorMessage;
		unset(Shopware()->Session()->stripeErrorMessage);
	}

	/**
	 * Gets the cardId from the request and tries to delete the card with that id
	 * from the Stripe account, which is associated with the currently logged in user.
	 * Finally it redirects to the 'manageCreditCards' action.
	 */
	public function deleteCreditCardAction() {
		// Set the Stripe API key
		$stripeSecretKey = Util::stripeSecretKey();
		Stripe::setApiKey($stripeSecretKey);

		try {
			// Delete the card with the given id
			$cardId = $this->Request()->getParam('cardId');
			if ($cardId === null) {
				throw new Exception('Missing field "cardId".');
			}
			Util::deleteStripeCard($cardId);
		} catch (Exception $e) {
			Shopware()->Session()->stripeErrorMessage = 'Beim Löschen der Kreditkarte ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.';
		}

		// Redirect to the manage action
		$this->redirect(array(
			'controller' => $this->Request()->getControllerName(),
			'action' => 'manageCreditCards'
		));
	}

}
