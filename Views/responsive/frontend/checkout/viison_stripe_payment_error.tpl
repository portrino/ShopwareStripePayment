{if $viisonStripePaymentError}
	{* Load the bold title from the snippets *}
	{capture name="viisonStripeErrorTitle"}
		<strong>{s namespace="frontend/plugins/payment/viison_stripe" name="payment_error/title"}{/s}</strong>
	{/capture}

	{* Include error message template with title and passed Stripe error *}
	{$errorList = [$smarty.capture.viisonStripeErrorTitle, $viisonStripePaymentError]}
	{include file="frontend/_includes/messages.tpl" type="error" list=$errorList}
{/if}
