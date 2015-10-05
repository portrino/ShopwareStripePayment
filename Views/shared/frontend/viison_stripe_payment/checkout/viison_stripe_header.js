// Inject the ID of the Stripe payment method, which must be defined before including this file,
// and check whether the payment form shall be initialised
ViisonStripePayment.paymentMeansId = viisonStripePaymentId;
if (ViisonStripePayment.isViisonStripePaymentSelected()) {
	// Try to get Stripe related data passed to the template
	var viisonStripeFormSetupData = {
		stripePublicKey: '{$viisonStripePublicKey}',
		snippets: {
			error: {
				title: '{s namespace="frontend/plugins/payment/viison_stripe" name="error"}{/s}',
				invalidName: '{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_name"}{/s}',
				invalidNumber: '{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_number"}{/s}',
				invalidCVC: '{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_cvc"}{/s}',
				invalidExpiry: '{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_expiry"}{/s}'
			}
		}
	};
	// Pre-selected card
	{if $viisonStripeCardRaw}
		viisonStripeFormSetupData.card = JSON.parse('{$viisonStripeCardRaw}');
	{/if}
	// Available cards
	{if $viisonAllStripeCardsRaw}
		viisonStripeFormSetupData.allCards = JSON.parse('{$viisonAllStripeCardsRaw}');
	{/if}
	// Pre-selected expiry date
	{if $viisonStripeCard}
		viisonStripeFormSetupData.selectedMonth = {$viisonStripeCard.exp_month};
		viisonStripeFormSetupData.selectedYear = {$viisonStripeCard.exp_year};
	{/if}

	// Stripe form setup
	ViisonStripePayment.init(viisonStripeFormSetupData);
}
