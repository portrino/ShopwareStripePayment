<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class ApplePay extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();

        // Determine the card source
        $stripeSession = Util::getStripeSession();
        if (!$stripeSession->applePayToken) {
            throw new \Exception($this->getSnippet('payment_error/message/transaction_not_found'));
        }

        // Use the token to create a new Stripe card source
        $source = Stripe\Source::create(array(
            'type' => 'card',
            'token' => $stripeSession->applePayToken,
            'metadata' => $this->getSourceMetadata()
        ));
        // Remove the token, since it can only be consumed once
        unset($stripeSession->applePayToken);

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function chargeStatementDescriptor()
    {
        // Apple Pay sources should contain a statement descriptor in the charge
        return $this->getStatementDescriptor();
    }
}
