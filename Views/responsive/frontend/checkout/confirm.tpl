{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_error_messages" append}
	{include file="frontend/checkout/viison_stripe_payment_error.tpl"}
{/block}
