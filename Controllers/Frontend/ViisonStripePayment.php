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
			$url = $this->Front()->Router()->assemble(array(
				'action' => 'payment',
				'sTarget' => 'checkout',
				'sViewport' => 'account',
				'appendSession' => true,
				// 'forceSecure' => true
			));
			// TODO: Add error box to template
			$this->redirect($url . '&viison_stripe_payment_error=1');
		}

		// Calculate the application fee (in cents)
		$percentageFee = 0.0015;
		$applicationFee = round($this->getAmount() * 100 * $percentageFee);

		try {
			// Init the stripe payment
			$stripeSecretKey = Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('stripeSecretKey');
			$charge = Stripe_Charge::create(array(
				'amount' => ($this->getAmount() * 100), // Amount has to be in cents!
				'currency' => $this->getCurrencyShortName(),
				'card' => $transactionToken,
				'description' => $userEmail,
				'application_fee' => $applicationFeep
			), $stripeSecretKey);
		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			echo $e->getMessage();
			die();
		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed (maybe wrong API keys)
			echo $e->getMessage();
			die();
		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			echo $e->getMessage();
			die();
		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send an email to the developer
			echo $e->getMessage();
			die();
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			echo $e->getMessage();
			die('unkown error');
		}

		// Save the payment details in the order
		$this->saveOrder($charge->id, $charge->balance_transaction, 12); // transactionId, paymentUniqueId, [paymentStatusId, [sendStatusMail]]

		// Finish the checkout process
		$this->redirect(array(
			'controller' => 'checkout',
			'action' => 'finish',
			// 'sUniqueID' => 'SOME_ID' // This id will be displayed in the order summary with some additional text
		));
	}

}
