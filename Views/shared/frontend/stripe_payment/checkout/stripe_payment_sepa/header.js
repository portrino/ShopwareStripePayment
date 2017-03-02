var stripePublicKey = '{$stripePayment.publicKey}';
var stripePaymentSepaSnippets = {
    error: {
        invalid_account_owner: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_account_owner}{/s}',
        invalid_city: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_city}{/s}',
        invalid_country: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_country}{/s}',
        invalid_iban: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_iban}{/s}',
        invalid_street: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_street}{/s}',
        invalid_zip_code: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/invalid_zip_code}{/s}',
        title: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=error/title}{/s}',
        sourceCreation: {
            invalid_bank_account_iban: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=payment_error/message/invalid_bank_account_iban}{/s}',
            invalid_owner_name: '{s namespace=frontend/plugins/payment/stripe_payment/sepa name=payment_error/message/invalid_owner_name}{/s}'
        }
    }
};
var stripePaymentSepaConfig = {
    currency: '{$stripePayment.currency}'
};
if ('{$stripePayment.rawSepaSource}') {
    stripePaymentSepaConfig.sepaSource = JSON.parse('{$stripePayment.rawSepaSource}');
}

// Initialize StripePaymentSepa once the DOM is ready
$(document).ready(function() {
    StripePaymentSepa.snippets = stripePaymentSepaSnippets;
    StripePaymentSepa.init(stripePublicKey, stripePaymentSepaConfig);
});
