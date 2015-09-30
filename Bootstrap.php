<?php

use Shopware\Plugins\ViisonStripePayment\Subscriber;

require_once(__DIR__ . '/Library/StripePHP/Stripe.php');

/**
 * This plugin offers a payment method, which uses the stripe JavaScript SDK and API
 * to make payments. Because none of the credit card information is send to the server,
 * this plugin can be used without being PCI compliant.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Shopware_Plugins_Frontend_ViisonStripePayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	/**
	 * Returns the current version of this plugin.
	 *
	 * @return The current version of this plugin.
	 */
	public function getVersion() {
		return '1.2.0';
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
			'support' => 'support@viison.com',
			'link' => 'http://www.viison.com/',
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
	 * Adds new events and configurations:
	 *	- since 1.0.0:
	 *		* A payment method representing stripe credit card payments
	 *		* A configuration element for the stripe connect public key
	 *		* A configuration element for the stripe connect secret key
	 *		* A configuration element for the stripe connect refresh token
	 *		* A configuration element for enabling the test mode
	 *	- since 1.0.1:
	 *		* A custom user attribute field for storing the Stripe customer id
	 *
	 * @param $oldVersion The currently installed version of this plugin.
	 * @return True if the update was successful, otherwise false.
	 */
	public function update($oldVersion) {
		$form = $this->Form();
		switch ($oldVersion) {
			case 'install':
				// Create the stripe payment method
				$this->createPayment(
					array(
						'active' => 0,
						'name' => 'viison_stripe',
						'description' => 'Stripe',
						'template' => 'viison_stripe.tpl',
						'action' => 'viison_stripe_payment',
						'additionalDescription' => ''
					)
				);
				// Add a config element for the stripe public key
				$form->setElement(
					'text',
					'stripePublicKey',
					array(
						'label' => 'Stripe Public Key',
						'value' => '',
						'description' => 'Tragen Sie hier Ihren öffentlichen Schlüssel ("Public Key") ein, den Sie im Zuge der Registrierung bei Stripe erhalten haben.',
						'scope' => Shopware_Components_Form::SCOPE_SHOP
					)
				);
				// Add a config element for the stripe secret key
				$form->setElement(
					'text',
					'stripeSecretKey',
					array(
						'label' => 'Stripe Secret Key',
						'value' => '',
						'description' => 'Tragen Sie hier Ihren geheimen Schlüssel ("Secret Key") ein, den Sie im Zuge der Registrierung bei Stripe erhalten haben.',
						'scope' => Shopware_Components_Form::SCOPE_SHOP
					)
				);
				// Add a config element for the stripe refresh token
				$form->setElement(
					'text',
					'stripeRefreshToken',
					array(
						'label' => 'Stripe Refresh Token',
						'value' => '',
						'description' => 'Tragen Sie hier Ihren Token zur aktualisierung der Schlüssel ("Refresh Token") ein, den Sie im Zuge der Registrierung bei Stripe erhalten haben.',
						'scope' => Shopware_Components_Form::SCOPE_SHOP
					)
				);
				// Add a config element for activating the test mode
				$form->setElement(
					'checkbox',
					'testMode',
					array(
						'label' => 'Testmodus aktivieren',
						'value' => false,
						'description' => 'Im Testmodus werden die verwendeten Kreditkarten nicht belastet. Hinweis: Die eingegebenen Zugangsdaten werden bei im Testmodus durch Testdaten ersetzt.',
						'scope' => Shopware_Components_Form::SCOPE_SHOP
					)
				);
			case '1.0.0':
				// Add an attribute to the user for storing the Stripe customer id
				$this->Application()->Models()->addAttribute(
					's_user_attributes',
					'viison',
					'stripe_customer_id',
					'varchar(255)'
				);

				// Rebuild the user attributes model
				$this->Application()->Models()->generateAttributeModels(array(
					's_user_attributes'
				));
			case '1.0.1':
				// Nothing to do
			case '1.1.0':
				// Nothing to do
			case '1.2.0':
				// Add static event subscribers
				$this->subscribeEvent(
					'Enlight_Controller_Front_DispatchLoopStartup',
					'onDispatchLoopStartup'
				);
				break;
			default:
				return false;
		}

		return array(
			'success' => true,
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
			$this->Application()->Models()->removeAttribute(
				's_user_attributes',
				'viison',
				'stripe_customer_id'
			);

			// Rebuild the user attributes model
			$this->Application()->Models()->generateAttributeModels(array(
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
			'Shopware\Plugins\ViisonStripePayment',
			$this->Path()
		);
	}

	/* Events & Hooks */

	/**
	 * Adds all subscribers to the event manager.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onDispatchLoopStartup(Enlight_Event_EventArgs $args) {
		$this->get('events')->addSubscriber(new Subscriber\Backend($this));
		$this->get('events')->addSubscriber(new Subscriber\Frontend($this));
	}

}
