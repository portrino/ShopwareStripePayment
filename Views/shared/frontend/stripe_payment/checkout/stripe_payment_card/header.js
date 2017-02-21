// Configure StripePaymentCard using available template data
var stripePaymentCardConfig = {
    stripePublicKey: '{$stripePayment.publicKey}',
    snippets: {
        error: {
            api_connection_error: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/api_connection_error}{/s}',
            card_declined: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/card_declined}{/s}',
            expired_card: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/expired_card}{/s}',
            incomplete_card: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_card}{/s}',
            incomplete_cvc: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_cvc}{/s}',
            incomplete_expiry: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_expiry}{/s}',
            incomplete_number: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incomplete_number}{/s}',
            incorrect_cvc: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incorrect_cvc}{/s}',
            incorrect_number: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/incorrect_number}{/s}',
            invalid_card_holder: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_card_holder}{/s}',
            invalid_cvc: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_cvc}{/s}',
            invalid_expiry_month: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_month}{/s}',
            invalid_expiry_month_past: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_month_past}{/s}',
            invalid_expiry_year: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_year}{/s}',
            invalid_expiry_year_past: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_expiry_year_past}{/s}',
            invalid_number: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/invalid_number}{/s}',
            processing_error: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/processing_error}{/s}',
            processing_error_intransient: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/processing_error_intransient}{/s}',
            title: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/title}{/s}',
            unexpected: '{s namespace=frontend/plugins/payment/stripe_payment/card name=error/unexpected}{/s}'
        }
    }
};
if ('{$stripePayment.rawSelectedCard}') {
    stripePaymentCardConfig.card = JSON.parse('{$stripePayment.rawSelectedCard}');
}
if ('{$stripePayment.rawAvailableCards}') {
    stripePaymentCardConfig.allCards = JSON.parse('{$stripePayment.rawAvailableCards}');
}

// Initialize StripePaymentCard once the DOM is ready
$(document).ready(function() {
    StripePaymentCard.init(stripePaymentCardConfig);
});
