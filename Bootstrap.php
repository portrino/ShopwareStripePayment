<?php

require_once(__DIR__ . '/Library/StripePHP/Stripe.php');
require_once(__DIR__ . '/Util.php');

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
	 * Returns the current version of this plugin.
	 *
	 * @return The current version of this plugin.
	 */
	public function getVersion() {
		return '1.1.0';
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
			'copyright' => 'Copyright © 2014, VIISON GmbH',
			'license' => 'All rights reserved.',
			'support' => 'http://www.viison.com/',
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
	 *		* An event subscription for Enlight_Controller_Action_PostDispatch_Frontend_Checkout
	 *		* An event subscription for Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePayment
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
				// Subscribe for the frontend account dispatch
				$this->subscribeEvent(
					'Enlight_Controller_Action_PostDispatch_Frontend_Account',
					'onPostDispatchFrontendAccount'
				);
				// Subscribe for the controller path event of the custom accound controller
				$this->subscribeEvent(
					'Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePaymentAccount',
					'onGetControllerPathFrontendViisonStripePaymentAccount'
				);
			case '1.1.0':
				// Subscribe for the post dispatch event of the backend order controller
				$this->subscribeEvent(
					'Enlight_Controller_Action_PostDispatch_Backend_Order',
					'onPostDispatchBackendOrder',
					'-100'
				);
				// Subscribe for the Stripe backend controller path
				$this->subscribeEvent(
					'Enlight_Controller_Dispatcher_ControllerPath_Backend_ViisonStripePayment',
					'onGetControllerPathBackendViisonStripePayment'
				);
				// Next release
				break;
			default:
				return false;
		}

		return array(
			'success' => true,
			'invalidateCache' => array(
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

		// Inject the credit card logos into the template
		$view->extendsTemplate('frontend/plugins/payment/viison_stripe_card_logos.tpl');

		// Set the Stripe API key
		$stripeSecretKey = Shopware_Plugins_Frontend_ViisonStripePayment_Util::stripeSecretKey();
		Stripe::setApiKey($stripeSecretKey);

		if ($request->getActionName() === 'confirm') {
			// Set the stripe public key
			$view->viisonStripePublicKey = Shopware_Plugins_Frontend_ViisonStripePayment_Util::stripePublicKey();

			// Check for an error
			$stripeError = Shopware()->Session()->viisonStripePaymentError;
			if (!empty($stripeError)) {
				unset(Shopware()->Session()->viisonStripePaymentError);

				// Append an error box to the view
				$view->extendsTemplate('frontend/plugins/payment/viison_stripe_error.tpl');
				$view->viisonStripePaymentError = $stripeError;
			}

			// Check if the Stripe cards are already loaded
			if (empty(Shopware()->Session()->allStripeCards)) {
				try {
					// Load all cards and save them in the session
					Shopware()->Session()->allStripeCards = Shopware_Plugins_Frontend_ViisonStripePayment_Util::getAllStripeCards();
				} catch (Exception $e) {
					unset(Shopware()->Session()->allStripeCards);
				}
			}

			// Check if the default Stripe card is already loaded
			if (Shopware()->Session()->stripeCard === null) {
				try {
					// Load the default card and safe it in the session
					Shopware()->Session()->stripeCard = Shopware_Plugins_Frontend_ViisonStripePayment_Util::getDefaultStripeCard();
				} catch (Exception $e) {
					unset(Shopware()->Session()->stripeCard);
				}
			}
		}

		// Update the form data
		if ($request->get('stripeTransactionToken') !== null) {
			// Save the stripe transaction token in the session
			Shopware()->Session()->stripeTransactionToken = $request->get('stripeTransactionToken');
			unset(Shopware()->Session()->stripeCardId);
		} else {
			// Simulate a new customer to make the payment selection in the checkout process visible
			$view->sRegisterFinished = 'false';
		}
		if ($request->get('stripeCardId') !== null) {
			// Save the stripe card id in the session
			Shopware()->Session()->stripeCardId = $request->get('stripeCardId');
			unset(Shopware()->Session()->stripeTransactionToken);
		}
		if ($request->get('stripeCard') !== null) {
			// Save the stripe card info in the session
			Shopware()->Session()->stripeCard = json_decode($request->get('stripeCard'), true);
		}

		// Check if a new card token is provided and shall be saved for later use
		if ($request->get('stripeSaveCard') === 'on' && Shopware()->Session()->stripeTransactionToken !== null) {
			unset(Shopware()->Session()->stripeDeleteCardAfterPayment);
			try {
				// Save the card info either in a new or an existing Stripe customer
				$transactionToken = Shopware()->Session()->stripeTransactionToken;
				$newCard = Shopware_Plugins_Frontend_ViisonStripePayment_Util::saveStripeCard($transactionToken);

				// Save the card id in the session and remove the token from the session
				Shopware()->Session()->stripeCardId = $newCard->id;
				unset(Shopware()->Session()->stripeTransactionToken);
			} catch (Exception $e) {
				// Append an error box to the view
				$view->extendsTemplate('frontend/plugins/payment/viison_stripe_error.tpl');
				$view->viisonStripePaymentError = $e->getMessage();
			}
		} else if ($request->get('stripeSaveCard') === 'off') {
			// Mark the Stripe card to be deleted after the payment
			Shopware()->Session()->stripeDeleteCardAfterPayment = true;
		}

		// Update view parameters
		if (Shopware()->Session()->allStripeCards !== null) {
			// Write all cards to the template both JSON encoded and in a form usable by smarty
			$view->viisonAllStripeCardsRaw = json_encode(Shopware()->Session()->allStripeCards);
			$view->viisonAllStripeCards = Shopware()->Session()->allStripeCards;
		}
		if (Shopware()->Session()->stripeCard !== null) {
			// Write the card info to the template both JSON encoded and in a form usable by smarty
			$view->viisonStripeCardRaw = json_encode(Shopware()->Session()->stripeCard);
			$view->viisonStripeCard = Shopware()->Session()->stripeCard;
		}
		$customer = Shopware_Plugins_Frontend_ViisonStripePayment_Util::getCustomer();
		if ($customer !== null) {
			// Add the account mode to the view
			$view->customerAccountMode = $customer->getAccountMode();
		}
	}

	/**
	 * Adds the snippets and views of this plugin to the view.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onPostDispatchFrontendAccount(Enlight_Event_EventArgs $args) {
		// Add templates
		$args->getSubject()->View()->addTemplateDir($this->Path() . 'Views/');
		$args->getSubject()->View()->extendsTemplate('frontend/plugins/viison_stripe/account/content_right.tpl');
	}

	/**
	 * Includes the custom backend order controllers, models, stores and views.
	 *
	 * @param $args The event parameters.
	 */
	public function onPostDispatchBackendOrder(Enlight_Event_EventArgs $args) {
		// Add snippets directory
		$this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');

		// Add View directory
		$view = $args->getSubject()->View();
		$view->addTemplateDir($this->Path() . 'Views/');
		if ($args->getRequest()->getActionName() === 'load') {
			$view->extendsTemplate('backend/plugins/viison_stripe/order_detail_position_refund.js');
		}
	}

	/**
	 * Returns the path to the Backend/ViisonStripePayment controller used for making payments.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Backend/ViisonStripePayment controller.
	 */
	public function onGetControllerPathBackendViisonStripePayment(Enlight_Event_EventArgs $args) {
		return $this->Path() . 'Controllers/Backend/ViisonStripePayment.php';
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

	/**
	 * Loads the snippets and views of this plugin and returns the path to the
	 * Frontend/ViisonStripePaymentAccount controller, used for managing saved credit cards.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Frontend/ViisonStripePaymentAccount controller.
	 */
	public function onGetControllerPathFrontendViisonStripePaymentAccount(Enlight_Event_EventArgs $args) {
		$this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
		$this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');

		return $this->Path() . 'Controllers/Frontend/ViisonStripePaymentAccount.php';
	}

}
