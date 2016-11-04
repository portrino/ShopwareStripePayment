<?php
use Shopware\Plugins\StripePayment\Util;

/**
 * This controller provides two actions for listing all credit cards of the currently logged in user
 * and for deleting a selected credit card.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Shopware_Controllers_Frontend_StripePaymentAccount extends Shopware_Controllers_Frontend_Account
{
    /**
     * Override
     */
    public function preDispatch()
    {
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
    public function manageCreditCardsAction()
    {
        // Load the template
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
            // Shopware 5
            $this->View()->loadTemplate('frontend/account/stripe_payment_credit_cards.tpl');
        } else {
            // Shopware 4
            $this->View()->loadTemplate('frontend/stripe_payment/account/credit_cards.tpl');
            $this->View()->extendsTemplate('frontend/stripe_payment/account/content_right.tpl');
        }

        try {
            // Load all cards of the customer and sort them by id (which correspond to the date, the card was created/added)
            $cards = Util::getAllStripeCards();
            usort($cards, function($cardA, $cardB) {
                return strcmp($cardA['id'], $cardB['id']);
            });
        } catch (Exception $e) {
            $error = ($this->get('snippets')->getNamespace('frontend/plugins/stripe_payment/account')->get('credit_cards/error/list_cards')) ?: 'Failed to load credit cards.';
            if (Shopware()->Session()->stripeErrorMessage === null) {
                Shopware()->Session()->stripeErrorMessage = $error;
            } else {
                Shopware()->Session()->stripeErrorMessage .= "\n" . $error;
            }
        }

        // Set the view data
        $this->View()->creditCards = $cards;
        $this->View()->stripePaymentError = Shopware()->Session()->stripeErrorMessage;
        unset(Shopware()->Session()->stripeErrorMessage);
    }

    /**
     * Gets the cardId from the request and tries to delete the card with that id
     * from the Stripe account, which is associated with the currently logged in user.
     * Finally it redirects to the 'manageCreditCards' action.
     */
    public function deleteCreditCardAction()
    {
        try {
            // Delete the card with the given id
            $cardId = $this->Request()->getParam('cardId');
            if ($cardId === null) {
                throw new Exception('Missing field "cardId".');
            }
            Util::deleteStripeCard($cardId);
        } catch (Exception $e) {
            Shopware()->Session()->stripeErrorMessage = ($this->get('snippets')->getNamespace('frontend/plugins/stripe_payment/account')->get('credit_cards/error/delete_card')) ?: 'Failed to delete credit card.';
        }

        // Clear all checkout related fields from the session to avoid caching deleted credit cards
        unset(Shopware()->Session()->stripeDeleteCardAfterPayment);
        unset(Shopware()->Session()->stripeTransactionToken);
        unset(Shopware()->Session()->stripeCardId);
        unset(Shopware()->Session()->stripeCard);
        unset(Shopware()->Session()->allStripeCards);

        // Redirect to the manage action
        $this->redirect(array(
            'controller' => $this->Request()->getControllerName(),
            'action' => 'manageCreditCards'
        ));
    }
}
