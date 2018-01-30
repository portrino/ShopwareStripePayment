<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
class Giropay extends Base
{
    /**
     * @inheritdoc
     */
    public function createStripeSource($amountInCents, $currencyCode)
    {
        Util::initStripeAPI();
        // Create a new Giropay source
        $returnUrl = $this->assembleShopwareUrl(array(
            'controller' => 'StripePayment',
            'action' => 'completeRedirectFlow',
        ));
        $source = Stripe\Source::create(array(
            'type' => 'giropay',
            'amount' => $amountInCents,
            'currency' => $currencyCode,
            'owner' => array(
                'name' => Util::getCustomerName(),
            ),
            'giropay' => array(
                'statement_descriptor' => $this->getStatementDescriptor(),
            ),
            'redirect' => array(
                'return_url' => $returnUrl,
            ),
            'metadata' => $this->getSourceMetadata(),
        ));

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function includeStatmentDescriptorInCharge()
    {
        // Giropay payments require the statement descriptor to be part of their source
        return false;
    }
}
