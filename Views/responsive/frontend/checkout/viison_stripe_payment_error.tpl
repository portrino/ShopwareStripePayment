{if $viisonStripePaymentError}
	{if !$viisonStripeErrorTitle}
		{* Load the payment error title from the snippets *}
		{capture name="viisonStripeErrorTitleCapture"}
			{s namespace="frontend/plugins/payment/viison_stripe" name="payment_error/title"}{/s}
		{/capture}
		{assign var="viisonStripeErrorTitle" value=$smarty.capture.viisonStripeErrorTitleCapture}
	{/if}

	{* Make the title bold *}
	{capture name="viisonStripeErrorTitle"}
		<strong>{$viisonStripeErrorTitle}</strong>
	{/capture}

	{* Include error message template with title and passed Stripe error *}
	{$errorList = [$smarty.capture.viisonStripeErrorTitle, $viisonStripePaymentError]}
	{include file="frontend/_includes/messages.tpl" type="error" list=$errorList}
{/if}
