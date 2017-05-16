<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Sofort extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();
        // Create a new SOFORT source
        $returnUrl = $this->assembleShopwareUrl(array(
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow'
        ));
        $source = Stripe\Source::create(array(
            'type' => 'sofort',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => array(
                'name' => Util::getCustomerName()
            ),
            'sofort' => array(
                'country' => $this->get('session')->sOrderVariables->sCountry['countryiso'],
                'statement_descriptor' => $this->getStatementDescriptor()
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
    public function includeStatmentDescriptorInCharge()
    {
        // SOFORT payments require the statement descriptor to be part of their source
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return ($this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/sofort')->get($name)) ?: parent::getSnippet($name);
    }
}
