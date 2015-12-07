<?php

// Inlcude the PaymentProvider interface only if it exists in ViisonPickwareConnector
$interface = __DIR__ . '/../../../Core/ViisonPickwareConnector/API/PaymentProvider.php';
if (file_exists($interface)) {
	include_once($interface);
} else if (!interface_exists('ViisonPickwareConnector_API_PaymentProvider')) {
	interface ViisonPickwareConnector_API_PaymentProvider {}
}

include_once(__DIR__ . '/../Controllers/Frontend/StripePayment.php');

use Shopware\Plugins\StripePayment\Util;


/**
 * This is a implementation of the PaymentProvider interface defined in ViisonPickwareConnector.
 * It can be instantiated to perform basic operations like preparing payments.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class StripePayment_Classes_PaymentProvider implements ViisonPickwareConnector_API_PaymentProvider
{

	const STRIPE_TOKEN_KEY = 'stripeToken';

	/**
	 * A field for temporarily storing a Stripe charge, after it was created in 'processPayment()'.
	 */
	private $charge = null;

	/* Override */
	public function getPaymentMethods($shopId) {
		// Get the payment id of this plugin
		$stripePaymentId = Shopware()->Db()->fetchOne(
		   'SELECT payment.id
			FROM s_core_paymentmeans payment
			LEFT OUTER JOIN s_core_paymentmeans_subshops shop
				ON shop.paymentID = payment.id
			WHERE payment.action = \'stripe_payment\'
			AND (
				shop.subshopID IS NULL
				OR shop.subshopID = ?
			)',
			array(
				$shopId
			)
		);

		// Get the payment method
		$paymentMethod = Shopware()->Models()->getRepository('\Shopware\Models\Payment\Payment')->findOneById($stripePaymentId);
		if ($paymentMethod === null) {
			return array();
		}

		return array(
			$paymentMethod
		);
	}

	/* Override */
	public function getPaymentMethodConfiguration($paymentMethodId) {
		// Get the public Stripe key and add it to the configuration
		return array(
			'publicKey' => Util::stripePublicKey()
		);
	}

	/* Override */
	public function getPaymentMethodRequirements($paymentMethodId) {
		// Require a credit card
		return array(
			'creditCard'
		);
	}

	/* Override */
	public function isResponsibleForPaymentMethod($paymentMethodId) {
		// Get the payment id of this plugin
		$stripePaymentId = Shopware()->Db()->fetchOne(
		   'SELECT id
			FROM s_core_paymentmeans
			WHERE action = \'stripe_payment\''
		);

		return $stripePaymentId == $paymentMethodId;
	}

	/* Override */
	public function processPayment(array $paymentData) {
		// Check for a Stripe token
		if (empty($paymentData[self::STRIPE_TOKEN_KEY])) {
			throw new Exception('Cannot process payment without "stripeToken".');
		}


		try {
			// Create a new Stripe payment controller passing an empty request and response to its constructor
			$stripePaymentController = Shopware_Controllers_Frontend_StripePayment::Instance(null, array(
					new Enlight_Controller_Request_RequestHttp(),
					new Enlight_Controller_Response_ResponseHttp()
			));

			// Prepare the charge
			Shopware()->Session()->stripeTransactionToken = $paymentData[self::STRIPE_TOKEN_KEY];
			$chargeData = $stripePaymentController->getChargeData();

			// Init the stripe payment
			Util::initStripeAPI();
			$charge = Stripe\Charge::create($chargeData);
		} catch (Exception $e) {
			throw new Exception('The payment could not be processed, because an error occurred: \"' . $e->getMessage() . '\"');
		}

		// Safe the charge for later use
		$this->charge = $charge;

		// Unset the values stored in the session
		unset(Shopware()->Session()->stripeTransactionToken);
	}

	/* Override */
	public function finishPayment($orderNumber) {
		if ($this->charge === null) {
			return;
		}

		// Get the order
		$order = Shopware()->Models()->getRepository('\Shopware\Models\Order\Order')->findOneBy(array(
			'number' => $orderNumber
		));
		if ($order === null) {
			return;
		}

		// Save the charge ID, transaction ID and new payment status in the order
		$order->setTransactionId($this->charge->id);
		$order->setTemporaryId($this->charge->balance_transaction);
		$paymentStatus = Shopware()->Models()->getRepository('\Shopware\Models\Order\Status')->findOneById(12);
		$order->setPaymentStatus($paymentStatus);
		Shopware()->Models()->flush($order);

		try {
			// Save the order number in the description of the charge
			$this->charge->description .= ' / Bestell-Nr.: ' . $orderNumber;
			$this->charge->save();
		} catch (Exception $e) {
			// Ignore exceptions in this case, because the order has already been created
			// and adding the order number is not essential to identify the payment
		}
	}

}
