<?php

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
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
