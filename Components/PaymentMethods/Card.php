<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Card extends AbstractStripePaymentMethod
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();

        // Determine the card source
        $stripeSession = Util::getStripeSession();
        if (!$stripeSession->selectedCard) {
            throw new \Exception($this->getSnippet('payment_error/message/no_card_selected'));
        } elseif ($stripeSession->selectedCard['token_id']) {
            // Use the token to create a new Stripe card source
            $source = Stripe\Source::create(array(
                'type' => 'card',
                'token' => $stripeSession->selectedCard['token_id'],
                'metadata' => $this->getSourceMetadata(),
            ));

            // Remove the token from the selected card, since it can only be consumed once
            unset($stripeSession->selectedCard['token_id']);

            if ($stripeSession->saveCardForFutureCheckouts) {
                // Add the card to the Stripe customer
                $stripeCustomer = Util::getStripeCustomer();
                if (!$stripeCustomer) {
                    $stripeCustomer = Util::createStripeCustomer();
                }
                $source = $stripeCustomer->sources->create(array(
                    'source' => $source->id,
                ));
                unset($stripeSession->saveCardForFutureCheckouts);
            }

            // Overwrite the card's id to allow using it again in case of an error
            $stripeSession->selectedCard['id'] = $source->id;
        } else {
            // Try to find the source corresponding to the selected card
            $source = Stripe\Source::retrieve($stripeSession->selectedCard['id']);
        }
        if (!$source) {
            throw new \Exception($this->getSnippet('payment_error/message/transaction_not_found'));
        }

        // Check the created/retrieved source
        $paymentMethod = $this->get('session')->sOrderVariables->sPayment['name'];
        if ($source->card->three_d_secure === 'required' || ($source->card->three_d_secure !== 'not_supported' && $paymentMethod === 'stripe_payment_card_three_d_secure')) {
            // The card requires the 3D-Secure flow or supports it and the selected payment method requires it,
            // hence create a new 3D-Secure source that is based on the card source
            $returnUrl = $this->assembleShopwareUrl(array(
                'controller' => 'StripePayment',
                'action' => 'completeRedirectFlow',
            ));
            try {
                $source = Stripe\Source::create(array(
                    'type' => 'three_d_secure',
                    'amount' => $amountInCents,
                    'currency' => $currencyCode,
                    'three_d_secure' => array(
                        'card' => $source->id,
                    ),
                    'redirect' => array(
                        'return_url' => $returnUrl,
                    ),
                    'metadata' => $this->getSourceMetadata(),
                ));
            } catch (\Exception $e) {
                throw new \Exception($this->getErrorMessage($e), 0, $e);
            }
        }

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function includeStatmentDescriptorInCharge()
    {
        // Card sources can be reused several times and hence should contain a statement descriptor in charge
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return ($this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/card')->get($name)) ?: parent::getSnippet($name);
    }

    /**
     * @inheritdoc
     */
    public function validate($paymentData)
    {
        // Check the payment data for a selected card
        if (empty($paymentData['selectedCard'])) {
            return array(
                'STRIPE_CARD_VALIDATION_FAILED'
            );
        }

        return array();
    }
}
