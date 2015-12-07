{if $stripePaymentError}
	{if !$stripeErrorTitle}
		{* Load the payment error title from the snippets *}
		{capture name="stripeErrorTitleCapture"}
			{s namespace="frontend/plugins/payment/stripe_payment" name="payment_error/title"}{/s}
		{/capture}
		{assign var="stripeErrorTitle" value=$smarty.capture.stripeErrorTitleCapture}
	{/if}

	{* Make the title bold *}
	{capture name="stripeErrorTitle"}
		<strong>{$stripeErrorTitle}</strong>
	{/capture}

	{* Include error message template with title and passed Stripe error *}
	{$errorList = [$smarty.capture.stripeErrorTitle, $stripePaymentError]}
	{include file="frontend/_includes/messages.tpl" type="error" list=$errorList}
{/if}
