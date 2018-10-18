<?php
// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use Stripe;

class ApplePay extends AbstractStripePaymentMethod
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
        $source = Stripe\Source::create([
            'type' => 'card',
            'token' => $stripeSession->applePayToken,
            'metadata' => $this->getSourceMetadata(),
        ]);
        // Remove the token, since it can only be consumed once
        unset($stripeSession->applePayToken);

        return $source;
    }

    /**
     * @inheritdoc
     */
    public function includeStatmentDescriptorInCharge()
    {
        // Apple Pay sources should contain a statement descriptor in the charge
        return true;
    }
}
