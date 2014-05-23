<?php

/**
 * This plugin offers a payment method, which uses the stripe JavaScript SDK and API
 * to make payments. Because none of the credit card information is send to the server,
 * this plugin can be used without being PCI compliant.
 *
 * @copyright Copyright (c) 2014, VIISON GmbH
 */
class Shopware_Plugins_Frontend_ViisonStripePayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	/**
	 * Gathers all information about this plugin and returns it wrapped in an array. This information
	 * will be displayed e.g. in the backend plugin manager.
	 *
	 * @return An array containing meta information about this plugin.
	 */
	public function getInfo() {
		return array(
			'name' => 'Stripe Payment',
			'label' => 'Stripe Payment',
			'description' => 'Bevor Sie Kreditkartenzahlungen über Stripe abwickeln können, müssen Sie sich zunächst bei Stripe als Kunde registrieren oder einloggen. Anschließend daran werden Sie auf eine Seite weitergeleitet, die Ihnen die weiteren Schritte zur Einrichtung dieses Plugins beschreibt.<br /><br />Klicken Sie <a href="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_3ygZJhLAhsQ4jqyKTL8SwxD0zYEmBf1l&scope=read_write" target="_blank">hier</a>, um sich bei Stripe zu registrieren oder einzuloggen.',
			'autor' => 'VIISON GmbH',
			'copyright' => 'Copyright © 2014, VIISON GmbH',
			'license' => 'All rights reserved.',
			'support' => 'http://www.viison.com/',
			'link' => 'http://www.viison.com/',
			'version' => $this->getVersion()
		);
	}

	/**
	 * Returns the current version of this plugin.
	 *
	 * @return The current version of this plugin.
	 */
	public function getVersion() {
		return '1.0.0';
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
	 *		* An event subscription for Enlight_Controller_Action_PostDispatch_Frontend_Checkout
	 *		* An event subscription for Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePayment
	 *		* A payment method representing stripe credit card payments
	 *		* A configuration element for the stripe connect public key
	 *		* A configuration element for the stripe connect secret key
	 *		* A configuration element for the stripe connect refresh token
	 *
	 * @param $oldVersion The currently installed version of this plugin.
	 * @return True if the update was successful, otherwise false.
	 */
	public function update($oldVersion) {
		switch ($oldVersion) {
			case 'install':
				// Subscribe for the basic events required for the payment process
				$this->subscribeEvent(
					'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
					'onPostDispatchCheckout'
				);
				$this->subscribeEvent(
					'Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePayment',
					'onGetControllerPathFrontendViisonStripePayment'
				);
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
				$form = $this->Form();
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
				break;
			default:
				return false;
		}

		return true;
	}

	/**
	 * Default uninstall method.
	 *
	 * @return True if uninstallation was successful, otherwise false.
	 */
	public function uninstall() {
		return parent::uninstall();
	}

	/* Events & Hooks */

	/**
	 * Handles different tasks during the checkout process. First it adds the custom templates,
	 * which are required for the stripe payment form. If the requested action is the 'confirm' action,
	 * the configured stripe public key is passed to the view, so that it can be rendered accordingly.
	 * Additionally it checks the session for possible stripe errors and passes them to the view,
	 * by setting the variable and appending the template.
	 * Furthermore this method checks for a stripe transaction token in the request parameters,
	 * and, if present, adds it to the current session. Finally the 'sRegisterFinished' flag is
	 * set to 'false', to make the detailed list of payment methods visible in the 'confirm' view.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Api/ViisonPickwareConnectorVouchers controller.
	 */
	public function onPostDispatchCheckout(Enlight_Event_EventArgs $args) {
		// Add the custom templates
		$this->Application()->Template()->addTemplateDir(
			$this->Path() . 'Views/'
		);

		// Check request, response and view
		$request = $args->getRequest();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		if (!$request->isDispatched() || $response->isException() || $request->getModuleName() !== 'frontend' || !$view->hasTemplate()) {
			return;
		}

		if ($request->getActionName() === 'confirm') {
			// Add the stripe public key
			$view->viisonStripePublicKey = Shopware()->Plugins()->Frontend()->ViisonStripePayment()->Config()->get('stripePublicKey');

			// Check for an error
			$stripeError = Shopware()->Session()->viisonStripePaymentError;
			if (!empty($stripeError)) {
				unset(Shopware()->Session()->viisonStripePaymentError);
				// Append an error box to the view
				$content =	'{if $viisonStripePaymentError} ' .
								'<div class="grid_20 first">' .
									'<div class="error">' .
										'<div class="center">' .
											'Beim Bezahlen der Bestellung ist ein Fehler aufgetreten!' .
										'</div>' .
										'<br />' .
										'<div class="normal">' .
											'{$viisonStripePaymentError}' .
										'</div>' .
									'</div>' .
								'</div> ' .
							'{/if}';
				$view->extendsBlock('frontend_index_content_top', $content, 'append');
				$view->viisonStripePaymentError = $stripeError;
			}
		}

		if ($request->get('stripeTransactionToken')) {
			// Save the stripe transaction token in the session
			Shopware()->Session()->stripeTransactionToken = $request->get('stripeTransactionToken');
		} else {
			// Simulate a new customer to make the payment selection in the checkout process visible
			$view->sRegisterFinished = 'false';
		}
	}

	/**
	 * Returns the path to the Frontend/ViisonStripePayment controller used for making payments.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Frontend/ViisonStripePayment controller.
	 */
	public function onGetControllerPathFrontendViisonStripePayment(Enlight_Event_EventArgs $args) {
		return $this->Path() . 'Controllers/Frontend/ViisonStripePayment.php';
	}

}
