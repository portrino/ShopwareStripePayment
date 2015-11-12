<?php

namespace Shopware\Plugins\ViisonStripePayment\Subscriber;

use Enlight\Event\SubscriberInterface,
	\Shopware_Plugins_Frontend_ViisonStripePayment_Bootstrap as Bootstrap,
	Shopware\Plugins\ViisonStripePayment\Util;

/**
 * The subscriber for frontend controllers.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Frontend implements SubscriberInterface
{

	private $path;

	/**
	 * @param bootstrap The plugin bootstrap, used as an DI container.
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->path = $bootstrap->Path();
	}

	/**
	 * @return An array containing all subsciptions.
	 */
	public static function getSubscribedEvents() {
		return array(
			'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
			'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'onPostDispatchAccount',
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePayment' => 'onGetControllerPathViisonStripePayment',
			'Enlight_Controller_Dispatcher_ControllerPath_Frontend_ViisonStripePaymentAccount' => 'onGetControllerPathViisonStripePaymentAccount'
		);
	}

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
		// Check request, response and view
		$request = $args->getRequest();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();

		$actionName = $request->getActionName();
		if (in_array($actionName, array('confirm', 'shippingPayment', 'saveShippingPayment'))) {
			if ($actionName === 'confirm' && Shopware()->Shop()->getTemplate()->getVersion() < 3) {
				// Shopware 4: Inject the error box and credit card logos into the template
				$view->extendsTemplate('frontend/viison_stripe_payment/checkout/confirm.tpl');
				$view->extendsTemplate('frontend/viison_stripe_payment/checkout/card_logos.tpl');
			}

			// Set the stripe public key
			$view->viisonStripePublicKey = Util::stripePublicKey();

			// Check for an error
			$stripeError = Shopware()->Session()->viisonStripePaymentError;
			if (!empty($stripeError)) {
				unset(Shopware()->Session()->viisonStripePaymentError);
				// Write the error message to the view
				$view->viisonStripePaymentError = $stripeError;
			}

			// Check if the Stripe cards are already loaded
			if (empty(Shopware()->Session()->allStripeCards)) {
				try {
					// Load all cards and save them in the session
					Shopware()->Session()->allStripeCards = Util::getAllStripeCards();
				} catch (\Exception $e) {
					unset(Shopware()->Session()->allStripeCards);
				}
			}

			// Check if the default Stripe card is already loaded
			if (Shopware()->Session()->stripeCard === null) {
				try {
					// Load the default card and safe it in the session
					Shopware()->Session()->stripeCard = Util::getDefaultStripeCard();
				} catch (\Exception $e) {
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
				$newCard = Util::saveStripeCard($transactionToken);

				// Save the card id in the session and remove the token from the session
				Shopware()->Session()->stripeCardId = $newCard->id;
				unset(Shopware()->Session()->stripeTransactionToken);
			} catch (\Exception $e) {
				// Write the error message to the view
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
		$customer = Util::getCustomer();
		if ($customer !== null) {
			// Add the account mode to the view
			$view->customerAccountMode = $customer->getAccountMode();
		}
	}

	/**
	 * Adds views of this plugin to the account template.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onPostDispatchAccount(Enlight_Event_EventArgs $args) {
		if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
			// Shopware 4
			$args->getSubject()->View()->extendsTemplate('frontend/viison_stripe_payment/account/content_right.tpl');
		}
	}

	/**
	 * Returns the path to the Frontend/ViisonStripePayment controller used for making payments.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Frontend/ViisonStripePayment controller.
	 */
	public function onGetControllerPathViisonStripePayment(Enlight_Event_EventArgs $args) {
		return $this->path . 'Controllers/Frontend/ViisonStripePayment.php';
	}

	/**
	 * Returns the path to the Frontend/ViisonStripePaymentAccount controller used for managing saved credit cards.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 * @return The path to the Frontend/ViisonStripePaymentAccount controller.
	 */
	public function onGetControllerPathViisonStripePaymentAccount(Enlight_Event_EventArgs $args) {
		return $this->path . 'Controllers/Frontend/ViisonStripePaymentAccount.php';
	}

}
