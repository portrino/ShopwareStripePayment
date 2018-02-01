<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Bancontact extends AbstractStripePaymentMethod
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();
        // Create a new Bancontact source
        $returnUrl = $this->assembleShopwareUrl([
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow',
        ]);
        $source = Stripe\Source::create([
            'type' => 'bancontact',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => [
                'name' => Util::getCustomerName(),
            ],
            'bancontact' => [
                'statement_descriptor' => $this->getStatementDescriptor(),
            ],
            'redirect' => [
                'return_url' => $returnUrl,
            ],
            'metadata' => $this->getSourceMetadata(),
        ]);

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function includeStatmentDescriptorInCharge()
    {
        // Bancontact payments require the statement descriptor to be part of their source
        return false;
    }
}
