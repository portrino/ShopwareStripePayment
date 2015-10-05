{extends file="parent:frontend/account/sidebar.tpl"}

{block name="frontend_account_menu_link_payment" append}
	<li class="navigation--entry">
		<a href="{url controller='ViisonStripePaymentAccount' action='manageCreditCards'}" title="{s namespace='frontend/plugins/viison_stripe/account' name='credit_cards/title'}{/s}" class="navigation--link{if $sAction == 'manageCreditCards'} is--active{/if}">
			{s namespace='frontend/plugins/viison_stripe/account' name='credit_cards/title'}{/s}
		</a>
	</li>
{/block}
