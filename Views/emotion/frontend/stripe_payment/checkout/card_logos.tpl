{block name="frontend_checkout_payment_fieldset_description"}
	{if $Controller != "account" && $payment_mean.name == "stripe_payment"}
		<style type="text/css">
			{* Include shared CSS for credit card logo SVGs *}
			{include file="frontend/stripe_payment/_resources/styles/credit_card_logos.css"}
		</style>
		{* Inject the credit card logos before the additional description *}
		<div class="grid_10 last">
			{* Credit card logos *}
			<div class="card visa"></div>
			<div class="card master-card"></div>
			<div class="card amex"></div>
			{* Default content *}
			<div class="grid_10 last" style="margin-top: 5px; margin-left: 0px;">
				{include file="string:{$payment_mean.additionaldescription}"}
			</div>
		</div>
	{else}
		<div class="grid_10 last">
			{include file="string:{$payment_mean.additionaldescription}"}
		</div>
	{/if}
{/block}
