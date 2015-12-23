<?php

use Shopware\Plugins\StripePayment\Subscriber;

if (!class_exists('\Stripe\Stripe') && file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once(__DIR__ . '/vendor/autoload.php');
}

/**
 * This plugin offers a credit card payment method using Stripe.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Shopware_Plugins_Frontend_StripePayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	/**
	 * Returns the current version of this plugin.
	 *
	 * @return The current version of this plugin.
	 */
	public function getVersion() {
		return '1.0.1';
	}

	/**
	 * Gathers all information about this plugin and returns it wrapped in an array. This information
	 * will be displayed e.g. in the backend plugin manager.
	 *
	 * @return An array containing meta information about this plugin.
	 */
	public function getInfo() {
		return array(
			'label' => 'Stripe Payment',
			'description' => file_get_contents(__DIR__ . '/description.html'),
			'autor' => 'VIISON GmbH',
			'copyright' => 'Copyright © 2015, VIISON GmbH',
			'license' => 'All rights reserved.',
			'support' => 'info@stripe.com',
			'link' => 'http://www.stripe.com/',
			'version' => $this->getVersion()
		);
	}

	/**
	 * Default install method, which installs the plugin and its events.
	 *
	 * @return True if installation was successful, otherwise false.
	 */
	public function install() {
		return $this->update('install');
	}

	/**
	 * Adds new event subscriptions and configurations.
	 *
	 * @param $oldVersion The currently installed version of this plugin.
	 * @return True if the update was successful, otherwise false.
	 */
	public function update($oldVersion) {
		switch ($oldVersion) {
			case 'install':
				// Add static event subscribers
				$this->subscribeEvent(
					'Enlight_Controller_Front_DispatchLoopStartup',
					'onDispatchLoopStartup'
				);

				// Check whether the payment method already exists
				$stripePaymentMethod = $this->get('models')->getRepository('Shopware\Models\Payment\Payment')->findOneBy(array(
					'action' => 'stripe_payment'
				));
				if ($stripePaymentMethod === null) {
					// Create the stripe payment method
					$this->createPayment(
						array(
							'active' => 0,
							'name' => 'stripe_payment',
							'description' => 'Stripe Kreditkarte',
							'template' => 'stripe_payment.tpl',
							'action' => 'stripe_payment',
							'class' => 'StripePaymentMethod',
							'additionalDescription' => ''
						)
					);
				}

				// Add a config element for the stripe secret key
				$this->Form()->setElement(
					'text',
					'stripeSecretKey',
					array(
						'label' => 'Stripe Secret Key',
						'description' => 'Tragen Sie hier Ihren geheimen Schlüssel ("Secret Key") ein. Diesen finden Sie im Stripe Dashboard unter "Account Settings" > "API Keys" im Feld "Live Secret Key".',
						'value' => ''
					)
				);
				// Add a config element for the stripe public key
				$this->Form()->setElement(
					'text',
					'stripePublicKey',
					array(
						'label' => 'Stripe Publishable Key',
						'description' => 'Tragen Sie hier Ihren öffentlichen Schlüssel ("Publishable Key") ein. Diesen finden Sie im Stripe Dashboard unter "Account Settings" > "API Keys" im Feld "Live Publishable Key".',
						'value' => ''
					)
				);

				// Add an attribute to the user for storing the Stripe customer id
				$this->get('models')->addAttribute(
					's_user_attributes',
					'stripe',
					'customer_id',
					'varchar(255)'
				);

				// Rebuild the user attributes model
				$this->get('models')->generateAttributeModels(array(
					's_user_attributes'
				));
			case '1.0.0':
				// Nothing to do
			case '1.0.1':
				// Next release
				break;
			default:
				return false;
		}

		return array(
			'success' => true,
			'message' => 'Bitte leeren Sie den gesamten Shop Cache, aktivieren Sie das Plugin und Kompilieren Sie anschließend die Shop Themes neu. Aktivieren Sie abschließend die Zahlart "Stripe Kreditkarte", um sie verfügbar zu machen.',
			'invalidateCache' => array(
				'backend',
				'frontend',
				'config'
			)
		);
	}

	/**
	 * Default uninstall method.
	 *
	 * @return True if uninstallation was successful, otherwise false.
	 */
	public function uninstall() {
		try {
			// Remove database columns
			$this->get('models')->removeAttribute(
				's_user_attributes',
				'stripe',
				'customer_id'
			);

			// Rebuild the user attributes model
			$this->get('models')->generateAttributeModels(array(
				's_user_attributes'
			));
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the plugin's namespace.
	 */
	public function afterInit() {
		$this->get('Loader')->registerNamespace(
			'Shopware\Plugins\StripePayment',
			$this->Path()
		);
	}

	/**
	 * Adds all subscribers to the event manager.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onDispatchLoopStartup(\Enlight_Event_EventArgs $args) {
		$this->get('events')->addSubscriber(new Subscriber\Payment());
		$this->get('events')->addSubscriber(new Subscriber\Backend($this));
		$this->get('events')->addSubscriber(new Subscriber\Frontend($this));
		$this->get('events')->addSubscriber(new Subscriber\Theme($this));
	}

}
