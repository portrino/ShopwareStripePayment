var stripePublicKey = '{$stripePayment.publicKey}';
var stripePaymentApplePaySnippets = {
    error: {
        connectionNotSecure: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/apple_pay name=error/connection_not_secure}{/stripe_snippet}',
        notAvailable: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/apple_pay name=error/not_available}{/stripe_snippet}',
        title: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/apple_pay name=error/title}{/stripe_snippet}'
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
