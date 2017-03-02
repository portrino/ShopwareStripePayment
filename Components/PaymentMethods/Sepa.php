<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Sepa extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode, $orderNumber)
    {
        Util::initStripeAPI();

        // Try to find the SEPA source saved in the session and validate it using the client secret
        $stripeSession = Util::getStripeSession();
        if (!$stripeSession->sepaSource) {
            throw new \Exception($this->getSnippet('payment_error/message/transaction_not_found'));
        }
        $source = Stripe\Source::retrieve($stripeSession->sepaSource['id']);
        if (!$source) {
            unset($stripeSession->sepaSource);
            throw new \Exception($this->getSnippet('payment_error/message/transaction_not_found'));
        } elseif ($source->client_secret !== $stripeSession->sepaSource['client_secret']) {
            unset($stripeSession->sepaSource);
            throw new \Exception($this->getSnippet('payment_error/message/processing_error'));
        }

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function chargeStatementDescriptor($orderNumber)
    {
        // SEPA sources can be reused several times and hence should contain a statement descriptor in the charge
        return $this->getShortStatementDescriptor($orderNumber);
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return ($this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/sepa')->get($name)) ?: parent::getSnippet($name);
    }

    /**
     * @inheritdoc
     */
    protected function doValidate(array $paymentData)
    {
        // Check the payment data for a SEPA source
        if (empty($paymentData['sepaSource'])) {
            return array(
                'STRIPE_SEPA_VALIDATION_FAILED'
            );
        }

        return array();
    }
}
