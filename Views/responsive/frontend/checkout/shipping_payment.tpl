{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{block name="frontend_index_header_javascript_jquery" append}
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript">
		{**
		 * Uncomment the following line the speed up development by including the custom
		 * Stripe payment library instead of loading it from the compiled Javascript file
		 *}
		{* {include file="frontend/viison_stripe_payment/_resources/javascript/viison_stripe_payment.js"} *}

		// Fix selectbox replacement for dynamically loaded payment forms
		// See also: https://github.com/shopware/shopware/pull/357
		$.subscribe('plugin/swShippingPayment/onInputChanged', function(event, shippingPayment) {
			shippingPayment.$el.find('select:not([data-no-fancy-select="true"])').swSelectboxReplacement();
		});

		$(document).ready(function() {
			// Stripe setup
			{include file="frontend/viison_stripe_payment/checkout/viison_stripe_header.js"}
		});
	</script>
{/block}
