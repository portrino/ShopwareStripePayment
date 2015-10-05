{if $Controller != "account" && $payment_mean.name == 'viison_stripe'}
	{* The main container for filling in the credit card information *}
	<style type="text/css">
		{* Include shared CSS for credit card logo SVGs *}
		{include file="frontend/viison_stripe_payment/_resources/styles/credit_card_logos.css"}
	</style>
	<div id="viison-stripe-form" class="payment--form-group">
		{* Credit card logos *}
		<div class="panel--table">
			<div class="panel--tr">
				<div class="panel--td card visa"></div>
				<div class="panel--td card master-card"></div>
				<div class="panel--td card amex"></div>
			</div>
		</div>

		{* An error box *}
		<div id="viison-stripe-error-box" class="panel alert has--border is--rounded is--error outer-error-box" style="display: none;">
			<div class="panel--body is--wide error-content"></div>
		</div>

		{* The mail form field table *}
		<div class="panel--table">
			{* Credit card selection *}
			<div class="panel--tr saved-cards">
				<label for="stripe-saved-cards" class="panel--td">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card_selection"}{/s}</label>
				<select id="stripe-saved-cards" class="panel--td">
					<option value="new"{if $viisonAllStripeCards|count == 0} selected{/if}>Neue Karte</option>
					{foreach from=$viisonAllStripeCards item=stripeCard}
						<option value="{$stripeCard.id}" {if $stripeCard.id == $viisonStripeCard.id}selected{/if}>
							{$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}
						</option>
					{/foreach}
				</select>
			</div>
			{* Card holder *}
			<div class="panel--tr">
				<label for="stripe-card-holder" class="panel--td">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/holder"}{/s} *</label>
				<input id="stripe-card-holder" type="text" size="20" class="panel--td" value="{if $viisonStripeCard}{$viisonStripeCard.name}{else}{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}{/if}">
			</div>
			{* Card number *}
			<div class="panel--tr">
				<label for="stripe-card-number" class="panel--td">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/number"}{/s} *</label>
				{* Try to use the last 4 digits of a previously created Stripe card *}
				<input id="stripe-card-number" type="text" size="20" class="panel--td" value="{if $viisonStripeCard}XXXXXXXXXXXX{$viisonStripeCard.last4}{/if}">
			</div>
			{* CVC *}
			<div class="panel--tr">
				<label for="stripe-card-cvc" class="panel--td">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/cvc"}{/s} *</label>
				{* Set a placeholder, if a previously created card is set *}
				<input id="stripe-card-cvc" type="text" size="5" class="panel--td" value="{if $viisonStripeCard}***{/if}">
				<div id="viison-stripe-cvc-info-button" class="help panel--td"></div>
			</div>
			{* Expiry date *}
			<div class="panel--tr expiry-date">
				<label for="stripe-card-expiry-month" class="panel--td">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/expiry"}{/s} *</label>
				<select id="stripe-card-expiry-month" class="panel--td"></select>
				<select id="stripe-card-expiry-year" class="panel--td"></select>
			</div>
		</div>

		{if $customerAccountMode == 0}
			{* Save data *}
			<span class="outer-checkbox">
				<div class="checkbox">
					<input id="stripe-save-card" type="checkbox" checked="checked">
					<span class="checkbox--state"></span>
				</div>
			</span>
			<label for="stripe-save-card">{s namespace="frontend/plugins/payment/viison_stripe" name="form/save_card"}{/s}</label>
		{/if}

		{* Info *}
		<div class="description">
			{s namespace="frontend/plugins/payment/viison_stripe" name="form/description"}{/s}
		</div>

		{* An initially hidden CVC info popup window *}
		{include file="frontend/viison_stripe_payment/checkout/viison_stripe_cvc_info_popup.tpl"}
	</div>
	<script type="text/javascript">
		// Save the payment ID to make it accessible
		var viisonStripePaymentId = {$payment_mean.id};

		// Check whether jQuery is already available to account for both ways this template is loaded:
		//   a) Calling the 'shippingPayment' action when initially loading the payment page.
		//		In this case, jQuery is not ready at this point, because it is added ad the
		//		end of the site. Hence the setup will be performed later.
		//   b) Calling the 'saveShippingPayment' action, when e.g. changing the selected payment method.
		//		In this case, the content of the 'payment selection' form is loaded asynchronously and
		//		add to the DOM. Hence jQuery is already available and the setup code can be performed
		//		right away.
		if (typeof jQuery !== 'undefined') {
			// Stripe setup
			{include file="frontend/viison_stripe_payment/checkout/viison_stripe_header.js"}
		}
	</script>
{/if}
