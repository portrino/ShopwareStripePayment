<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
abstract class BaseValidator extends GenericPaymentMethod
{
    /**
     * Validates the given payment data. If the data is invalid, an array containing error messages or codes
     * must be returned. Otherwiese an empty array i returned.
     *
     * @param array $paymentData
     * @return array
     */
    abstract protected function doValidate(array $paymentData);
}

/**
 * Returns true, if the signature of GenericPaymentMethod#validate appears consistent with Shopware before version
 * 5.0.4-RC1.
 *
 * Since version 5.0.4-RC1, the parameter must be an array (with no type hint).
 * Before, it was an \Enlight_Controller_Request_Request.
 *
 * The commit that changed the signature of #validate is
 * <https://github.com/shopware/shopware/commit/0608b1a7b05e071c93334b29ab6bd588105462d7>.
 */
function needs_legacy_validate_signature()
{
    $parentClass = new \ReflectionClass('ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod');
    /* @var $parameters \ReflectionParameter[] */
    $parameters = $parentClass->getMethod('validate')->getParameters();
    foreach ($parameters as $parameter) {
        // Newer Shopware versions use an array parameter named paymentData.
        if ($parameter->getName() === 'request') {
            return true;
        }
    }

    return false;
}

if (needs_legacy_validate_signature()) {
    /**
     * Shopware < 5.0.4
     */
    abstract class Base extends BaseValidator
    {
        /**
         * @inheritdoc
         */
        public function validate(\Enlight_Controller_Request_Request $request)
        {
            return $this->doValidate($request->getParams());
        }
    }
} else {
    /**
     * Shopware >= 5.0.4
     */
    abstract class Base extends BaseValidator
    {
        /**
         * @inheritdoc
         */
        public function validate($paymentData)
        {
            return $this->doValidate($paymentData);
        }
    }
}
