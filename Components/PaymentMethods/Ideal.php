<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Ideal extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode, $orderNumber)
    {
        Util::initStripeAPI();
        // Create a new iDEAL source
        $returnUrl = $this->assembleShopwareUrl(array(
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow'
        ));
        $source = Stripe\Source::create(array(
            'type' => 'ideal',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => array(
                'name' => Util::getCustomerName()
            ),
            'ideal' => array(
                'statement_descriptor' => $this->getLongStatementDescriptor($orderNumber)
            ),
            'redirect' => array(
                'return_url' => $returnUrl
            ),
            'metadata' => $this->getSourceMetadata()
        ));

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function chargeStatementDescriptor($orderNumber)
    {
        // iDEAL payments require the statement descriptor to be part of their source
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return (Util::getUnescapedSnippet('frontend/plugins/payment/stripe_payment/ideal', $name)) ?: parent::getSnippet($name);
    }
}
