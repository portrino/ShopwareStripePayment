{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_error_messages" append}
	{include file="frontend/checkout/viison_stripe_payment_error.tpl"}
{/block}

{block name="frontend_index_header_javascript_jquery" append}
	{if $sUserData.additional.payment.action == "viison_stripe_payment" && $viisonStripeCard}
		<script type="text/javascript">
			$(document).ready(function() {
				// Add special class to body to trigger custom CSS rules
				$('body').addClass('is--viison-stripe-payment-selected');

				// Insert a new element right below the general payment information showing details of the selected credit card
				var element = $('<p class="viison-stripe-payment-details is--bold">{$viisonStripeCard.name} | {$viisonStripeCard.brand} | &bull;&bull;&bull;&bull;{$viisonStripeCard.last4} | {$viisonStripeCard.exp_month}/{$viisonStripeCard.exp_year}</p>');
				element.insertAfter('.payment--panel .payment--content .payment--method-info');
			});
		</script>
	{/if}
{/block}
