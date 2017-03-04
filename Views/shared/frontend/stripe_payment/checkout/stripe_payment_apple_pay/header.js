var stripePublicKey = '{$stripePayment.publicKey}';
var stripePaymentApplePaySnippets = {
    error: {
        notAvailable: '{s namespace=frontend/plugins/payment/stripe_payment/apple_pay name=error/not_available}{/s}',
        title: '{s namespace=frontend/plugins/payment/stripe_payment/apple_pay name=error/title}{/s}'
    }
};
var stripePaymentApplePayConfig = {
    countryCode: '{$sUserData.additional.country.countryiso}',
    currencyCode: '{$stripePayment.currency}',
    locale: '{$stripePayment.locale}',
    statementDescriptor: '{$stripePayment.applePayStatementDescriptor}',
    amount: '{$sAmount}'
};

// Initialize StripePaymentApplePay once the DOM is ready
$(document).ready(function() {
    StripePaymentApplePay.snippets = stripePaymentApplePaySnippets;
    StripePaymentApplePay.init(stripePublicKey, stripePaymentApplePayConfig);
});
