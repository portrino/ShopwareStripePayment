<?php
namespace Shopware\Plugins\StripePayment\Components\PaymentMethods;

use Shopware\Plugins\StripePayment\Util;
use ShopwarePlugin\PaymentMethods\Components\GenericPaymentMethod;

/**
 * @copyright Copyright (c) 2017, VIISON GmbH
 */
abstract class AbstractStripePaymentMethod extends GenericPaymentMethod
{
    /**
     * Returns the source that shall be used to create a Stripe charge during checkout.
     *
     * @param int $amountInCents
     * @param string $currencyCode
     * @param string $statementDescriptor
     * @return Stripe\Source
     * @throws \Exception
     */
    abstract public function createStripeSource($amountInCents, $currencyCode, $statementDescriptor);

    /**
     * @inheritdoc
     */
    public function getCurrentPaymentDataAsArray($userId)
    {
        return (array)Util::getStripeSession();
    }

    /**
     * Returns the localized error message for the given exception.
     *
     * @param \Exception $exception
     * @return string
     */
    public function getErrorMessage(\Exception $exception)
    {
        $message = 'Payment failed: ' . $exception->getMessage();
        if ($exception->stripeCode) {
            $message = ($this->getSnippet('payment_error/message/' . $exception->stripeCode)) ?: $message;
        }

        return $message;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getSnippet($name)
    {
        return $this->get('snippets')->getNamespace('frontend/plugins/payment/stripe_payment/base')->get($name);
    }

    /**
     * Validates the given payment data. If the data is invalid, an array containing error messages or codes
     * must be returned. By default this method returns an empty array, which indicates that the $paymentData
     * is valid.
     *
     * @param array $paymentData
     * @return array
     */
    protected function doValidate(array $paymentData)
    {
        return array();
    }

    /**
     * Assembles and returns a shopware URL that can be used e.g. for a redirect return.
     *
     * @param array $components
     * @return string
     */
    protected function assembleShopwareUrl(array $components)
    {
        $front = $this->get('front');
        $url = $front->Router()->assemble($components);
        if (!preg_match('#^https?://#', $url)) {
            if (strpos($url, '/') !== 0) {
                $url = $front->Request()->getBaseUrl() . '/' . $url;
            }
            $uri = $front->Request()->getScheme() . '://' . $front->Request()->getHttpHost();
            $url = $uri . $url;
        }

        return $url;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function get($key)
    {
        return Shopware()->Container()->get($key);
    }
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
    abstract class Base extends AbstractStripePaymentMethod
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
    abstract class Base extends AbstractStripePaymentMethod
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
