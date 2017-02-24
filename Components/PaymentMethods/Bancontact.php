<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Bancontact extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode, $orderNumber)
    {
        Util::initStripeAPI();
        // Create a new Bancontact source
        $returnUrl = $this->assembleShopwareUrl(array(
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow'
        ));
        $source = Stripe\Source::create(array(
            'type' => 'bancontact',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => array(
                'name' => Util::getCustomerName()
            ),
            'bancontact' => array(
                'statement_descriptor' => $this->getLongStatementDescriptor($orderNumber)
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
    public function chargeStatementDescriptor($orderNumber)
    {
        // Bancontact payments require the statement descriptor to be part of their source
        return null;
    }
}
