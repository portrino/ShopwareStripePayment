var stripePublicKey = '{$stripePayment.publicKey}';
var stripePaymentCardSnippets = {
    error: {
        api_connection_error: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/api_connection_error}{/stripe_snippet}',
        card_declined: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/card_declined}{/stripe_snippet}',
        expired_card: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/expired_card}{/stripe_snippet}',
        incomplete_card: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_card}{/stripe_snippet}',
        incomplete_cvc: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_cvc}{/stripe_snippet}',
        incomplete_expiry: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_expiry}{/stripe_snippet}',
        incomplete_number: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_number}{/stripe_snippet}',
        incorrect_cvc: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incorrect_cvc}{/stripe_snippet}',
        incorrect_number: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/incorrect_number}{/stripe_snippet}',
        invalid_card_holder: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_card_holder}{/stripe_snippet}',
        invalid_cvc: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_cvc}{/stripe_snippet}',
        invalid_expiry_month: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_month}{/stripe_snippet}',
        invalid_expiry_month_past: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_month_past}{/stripe_snippet}',
        invalid_expiry_year: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_year}{/stripe_snippet}',
        invalid_expiry_year_past: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_year_past}{/stripe_snippet}',
        invalid_number: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_number}{/stripe_snippet}',
        processing_error: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/processing_error}{/stripe_snippet}',
        processing_error_intransient: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/processing_error_intransient}{/stripe_snippet}',
        title: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/title}{/stripe_snippet}',
        unexpected: '{stripe_snippet namespace=frontend/plugins/payment/stripe_payment/card name=error/unexpected}{/stripe_snippet}'
    }
};
var stripePaymentCardConfig = {
    locale: '{$stripePayment.locale}'
};
if ('{$stripePayment.rawSelectedCard}') {
    stripePaymentCardConfig.card = JSON.parse('{$stripePayment.rawSelectedCard}');
}
if ('{$stripePayment.rawAvailableCards}') {
    stripePaymentCardConfig.allCards = JSON.parse('{$stripePayment.rawAvailableCards}');
}

// Initialize StripePaymentCard once the DOM is ready
$(document).ready(function() {
    StripePaymentCard.snippets = stripePaymentCardSnippets;
    StripePaymentCard.init(stripePublicKey, stripePaymentCardConfig);
});
