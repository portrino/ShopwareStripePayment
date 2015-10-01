{extends file='parent:frontend/account/content_right.tpl'}

{block name='frontend_account_content_right_payment' append}
	{* Add Stripe credit card management *}
	<li>
		<a href="{url controller='ViisonStripePaymentAccount' action='manageCreditCards'}">
			{s name='content_right/payment/credit_cards' namespace='frontend/plugins/viison_stripe/account'}{/s}
		</a>
	</li>
{/block}
