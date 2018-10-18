<?php
// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

class Shopware_Controllers_Frontend_StripePaymentApplePay extends Enlight_Controller_Action
{
    /**
     * Responds with the contents of the Apple Pay domain association file.
     */
    public function domainAssociationFileAction()
    {
        // Just output Stripe's domain association file
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        echo file_get_contents(__DIR__ . '/../../assets/apple-developer-merchantid-domain-association');
    }
}
