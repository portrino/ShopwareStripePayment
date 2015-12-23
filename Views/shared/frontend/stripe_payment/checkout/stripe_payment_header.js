// Inject the ID of the Stripe payment method, which must be defined before including this file,
// and check whether the payment form shall be initialised
StripePayment.paymentMeansId = stripePaymentId;
if (StripePayment.paymentMeansId && StripePayment.isStripePaymentSelected()) {
	// Try to get Stripe related data passed to the template
	var stripeFormSetupData = {
		stripePublicKey: '{$stripePublicKey}',
		snippets: {
			error: {
				title: '{s namespace="frontend/plugins/payment/stripe_payment" name="error"}{/s}',
				invalidName: '{s namespace="frontend/plugins/payment/stripe_payment" name="error/invalid_name"}{/s}',
				invalidNumber: '{s namespace="frontend/plugins/payment/stripe_payment" name="error/invalid_number"}{/s}',
				invalidCVC: '{s namespace="frontend/plugins/payment/stripe_payment" name="error/invalid_cvc"}{/s}',
				invalidExpiry: '{s namespace="frontend/plugins/payment/stripe_payment" name="error/invalid_expiry"}{/s}'
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
