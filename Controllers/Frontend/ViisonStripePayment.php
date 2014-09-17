<?php

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
		$user = $this->getUser();
		$userEmail = $user['additional']['user']['email'];
		$customerNumber = $user['billingaddress']['customernumber'];

		// Calculate the application fee (in cents)
		$percentageFee = 0.3;
		$applicationFee = round($this->getAmount() * $percentageFee) + 5;

		// Set the Stripe API key
		$stripeSecretKey = Shopware_Plugins_Frontend_ViisonStripePayment_Util::stripeSecretKey();
		Stripe::setApiKey($stripeSecretKey);

		// Prepare the charge data
		$chargeData = array(
			'amount' => ($this->getAmount() * 100), // Amount has to be in cents!
			'currency' => $this->getCurrencyShortName(),
			'description' => ($userEmail . ' / Kunden-Nr.: ' . $customerNumber),
			'application_fee' => $applicationFee
		);

		if (Shopware()->Session()->stripeTransactionToken !== null) {
			// Create a new charge using the transaction token
			$chargeData['card'] = Shopware()->Session()->stripeTransactionToken;
		} else if (Shopware()->Session()->stripeCardId !== null) {
			// Create a new charge using the selected card and the customer
			$chargeData['card'] = Shopware()->Session()->stripeCardId;
			try {
				$stripeCustomer = Shopware_Plugins_Frontend_ViisonStripePayment_Util::getStripeCustomer();
				$chargeData['customer'] = $stripeCustomer->id;
			} catch (Exception $e) {
				// The Stripe customer couldn't be loaded
				Shopware()->Session()->viisonStripePaymentError = 'Die Zahlung konnte nicht durchgeführt werden, da die ausgewählte Kreditkarte nicht gefunden wurde. Bitte versuchen Sie es erneut.';
				$this->redirect(array(
					'controller' => 'checkout',
					'action' => 'confirm',
					'forceSecure' => !$this->testMode // Disable the secure mode for testing
				));
				return;
			}
		} else {
			// No payment information provided
			Shopware()->Session()->viisonStripePaymentError = 'Die Zahlung konnte nicht durchgeführt werden, da keine Stripe-Transaktion gefunden wurde. Bitte versuchen Sie es erneut.';
			$this->redirect(array(
				'controller' => 'checkout',
				'action' => 'confirm',
				'forceSecure' => !$this->testMode // Disable the secure mode for testing
			));
			return;
		}

		try {
			// Init the stripe payment
			$charge = Stripe_Charge::create($chargeData);
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
		// Use the balance_transaction as the paymentUniqueId, because altough the column in the backend
		// order list is named 'Transaktion' or 'tranaction', it displays NOT the transactionId, but
		// the field 'temporaryID', to which the paymentUniqueId is written. Additionally the
		// balance_transaction is displayed in the shop owner's Stripe account, so it can
		// be used to easily identify an order.
		$this->saveOrder($charge->id, $charge->balance_transaction, 12); // transactionId, paymentUniqueId, [paymentStatusId, [sendStatusMail]]

		try {
			// Save the order number in the description of the charge
			$charge->description .= ' / Bestell-Nr.: ' . $this->getOrderNumber();
			$charge->save();
		} catch (Exception $e) {
			// Ignore exceptions in this case, because the order has already been created
			// and adding the order number is not essential to identify the payment
		}

		if (Shopware()->Session()->stripeDeleteCardAfterPayment === true) {
			// Delete the Stripe card
			try {
				Shopware_Plugins_Frontend_ViisonStripePayment_Util::deleteStripeCard($charge->card->id);
			} catch (Exception $e) {
				// Ignore exceptions in this case, because the order has already been created
				// and deleting the credit card is assumed to be an optional operation
			}
		}

		// Unset the values stored in the session
		unset(Shopware()->Session()->stripeDeleteCardAfterPayment);
		unset(Shopware()->Session()->stripeTransactionToken);
		unset(Shopware()->Session()->stripeCardId);
		unset(Shopware()->Session()->stripeCard);
		unset(Shopware()->Session()->allStripeCards);

		// Finish the checkout process
		$this->redirect(array(
			'controller' => 'checkout',
			'action' => 'finish',
			'forceSecure' => !$this->testMode // Disable the secure mode for testing
			// 'sUniqueID' => 'SOME_ID' // This id will be displayed in the order summary with some additional text
		));
	}

}
