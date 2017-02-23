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
    public function createStripeSource($amountInCents, $currencyCode, $statementDescriptor)
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
                'statement_descriptor' => $statementDescriptor
            ),
            'redirect' => array(
                'return_url' => $returnUrl
            )
        ));

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function getSnippet($name)
    {
        return ($this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/sofort')->get($name)) ?: parent::getSnippet($name);
    }

    /**
     * @inheritdoc
     */
    protected function doValidate(array $paymentData)
    {
        // Sofort payments are always valid
        return array();
    }
}
