<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Ideal extends AbstractStripePaymentMethod
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();
        // Create a new iDEAL source
        $returnUrl = $this->assembleShopwareUrl([
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow',
        ]);
        $source = Stripe\Source::create([
            'type' => 'ideal',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => [
                'name' => Util::getCustomerName(),
            ],
            'ideal' => [
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
        // iDEAL payments require the statement descriptor to be part of their source
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return ($this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/ideal')->get($name)) ?: parent::getSnippet($name);
    }
}
