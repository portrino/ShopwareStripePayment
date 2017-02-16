// Inject the ID of the Stripe payment method, which must be defined before including this file,
// and check whether the payment form shall be initialised
StripePayment.paymentMeansId = stripePaymentId;
if (StripePayment.paymentMeansId && StripePayment.isStripePaymentSelected()) {
    // Try to get Stripe related data passed to the template
    var stripeFormSetupData = {
        stripePublicKey: '{$stripePublicKey}',
        snippets: {
            error: {
                api_connection_error: '{s namespace=frontend/plugins/payment/stripe_payment name=error/api_connection_error}{/s}',
                card_declined: '{s namespace=frontend/plugins/payment/stripe_payment name=error/card_declined}{/s}',
                expired_card: '{s namespace=frontend/plugins/payment/stripe_payment name=error/expired_card}{/s}',
                incomplete_card: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incomplete_card}{/s}',
                incomplete_cvc: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incomplete_cvc}{/s}',
                incomplete_expiry: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incomplete_expiry}{/s}',
                incomplete_number: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incomplete_number}{/s}',
                incorrect_cvc: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incorrect_cvc}{/s}',
                incorrect_number: '{s namespace=frontend/plugins/payment/stripe_payment name=error/incorrect_number}{/s}',
                invalid_card_holder: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_card_holder}{/s}',
                invalid_cvc: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_cvc}{/s}',
                invalid_expiry_month: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_expiry_month}{/s}',
                invalid_expiry_month_past: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_expiry_month_past}{/s}',
                invalid_expiry_year: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_expiry_year}{/s}',
                invalid_expiry_year_past: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_expiry_year_past}{/s}',
                invalid_number: '{s namespace=frontend/plugins/payment/stripe_payment name=error/invalid_number}{/s}',
                processing_error: '{s namespace=frontend/plugins/payment/stripe_payment name=error/processing_error}{/s}',
                processing_error_intransient: '{s namespace=frontend/plugins/payment/stripe_payment name=error/processing_error_intransient}{/s}',
                title: '{s namespace=frontend/plugins/payment/stripe_payment name=error/title}{/s}',
                unexpected: '{s namespace=frontend/plugins/payment/stripe_payment name=error/unexpected}{/s}'
            }
        }
    };
    // Pre-selected card
    if ('{$stripeCardRaw}') {
        stripeFormSetupData.card = JSON.parse('{$stripeCardRaw}');
    }
    // Available cards
    if ('{$allStripeCardsRaw}') {
        stripeFormSetupData.allCards = JSON.parse('{$allStripeCardsRaw}');
    }
    // Pre-selected expiry date
    if ('{$stripeCard}') {
        stripeFormSetupData.selectedMonth = parseInt('{$stripeCard.exp_month}');
        stripeFormSetupData.selectedYear = parseInt('{$stripeCard.exp_year}');
    }

    // Stripe form setup
    StripePayment.init(stripeFormSetupData);
}
