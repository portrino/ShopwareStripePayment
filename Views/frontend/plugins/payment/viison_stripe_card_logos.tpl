{block name="frontend_checkout_payment_fieldset_description"}
	{if $Controller != "account" && $payment_mean.name == "viison_stripe"}
		{* Inject the credit card logos before the additional description *}
		<style type="text/css">
			{include file="frontend/plugins/_resources/styles/stripe_credit_card_logos.css"}
		</style>
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
