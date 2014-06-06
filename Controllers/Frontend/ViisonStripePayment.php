<?php

include_once(__DIR__ . '/../../lib/StripePHP/Stripe.php');

/**
 * The controller handling the main payment process using the stripe API.
 *
 * @copyright Copyright (c) 2014, Viison GmbH
 */
class Shopware_Controllers_Frontend_ViisonStripePayment extends Shopware_Controllers_Frontend_Payment
{

	/**
	 * A boolean indicating whether the test mode is active.
	 */
	private $testMode = false;

	/**
	 * Updates the testMode flag.
	 */
	public function preDispatch() {
		$this->testMode = Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('testMode');
	}

	/**
	 * Retrieves the generated stripe transaction token and uses it to
	 * charge the customer via the stripe API. After a successful payment,
	 * the stripe transaction id is safed in the order and its status is updated to
	 * 'payed'. Finally the user is redirected to the 'finish' action of the checkout process.
	 */
	public function indexAction() {
		// Get the necessary user info
		$user = Shopware()->Session()->sOrderVariables['sUserData'];
		$userEmail = $user['additional']['user']['email'];

		// Try to find the transaction token
		$transactionToken = Shopware()->Session()->stripeTransactionToken;
		if (empty($transactionToken)) {
			// Missing the transaction token
			Shopware()->Session()->viisonStripePaymentError = 'Die Zahlung konnte nicht durchgeführt werden, da keine Stripe-Transaktion gefunden wurde. Bitte versuchen Sie es erneut.';
			$this->redirect(array(
				'controller' => 'checkout',
				'action' => 'confirm',
				'forceSecure' => !$this->testMode // Disable the secure mode for testing
			));
			return;
		}

		// Calculate the application fee (in cents)
		$percentageFee = 0.3;
		$applicationFee = round($this->getAmount() * $percentageFee) + 5;

		try {
			// Select the secret key based on the current mode (live/test)
			$stripeSecretKey = ($this->testMode) ? 'sk_test_8cku9VMwOVl7wMfPYFX1NUwd' : Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('stripeSecretKey');

			// Init the stripe payment
			$charge = Stripe_Charge::create(array(
				'amount' => ($this->getAmount() * 100), // Amount has to be in cents!
				'currency' => $this->getCurrencyShortName(),
				'card' => $transactionToken,
				'description' => $userEmail,
				'application_fee' => $applicationFee
			), $stripeSecretKey);
		} catch (Exception $e) {
			// Save the exception message in the session and redirect to the checkout confirm view
			Shopware()->Session()->viisonStripePaymentError = 'Die Zahlung konnte nicht durchgeführt werden, da folgender Fehler aufgetreten ist: ' . $e->getMessage();
			$this->redirect(array(
				'controller' => 'checkout',
				'action' => 'confirm',
				'forceSecure' => !$this->testMode // Disable the secure mode for testing
			));
			return;
		}

		// Save the payment details in the order
		$this->saveOrder($charge->id, $charge->balance_transaction, 12); // transactionId, paymentUniqueId, [paymentStatusId, [sendStatusMail]]

		// Unset the values stored in the session
		unset(Shopware()->Session()->stripeTransactionToken);
		unset(Shopware()->Session()->stripeCard);

		// Finish the checkout process
		$this->redirect(array(
			'controller' => 'checkout',
			'action' => 'finish',
			// 'sUniqueID' => 'SOME_ID' // This id will be displayed in the order summary with some additional text
		));
	}

}
