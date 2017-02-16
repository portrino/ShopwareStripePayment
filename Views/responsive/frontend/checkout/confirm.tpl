{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_error_messages" append}
    {include file="frontend/checkout/stripe_payment_error.tpl"}
{/block}

{block name="frontend_index_header_javascript_jquery" append}
    {if $sUserData.additional.payment.action == "stripe_payment_card" && $stripeCard}
        <script type="text/javascript">
            $(document).ready(function() {
                // Add special class to body to trigger custom CSS rules in Shopware versions >= 5.0 and < 5.2.
                // Starting from Shopware 5.2, the enclosing container uses content based autoresizing, which
                // would break, if we enabled the custom CSS rule.
                if ($('#confirm--form .information--panel-wrapper').attr('data-panel-auto-resizer') !== 'true') {
                    $('body').addClass('is--stripe-payment-selected');
                }

                // Insert a new element right below the general payment information showing details of the selected credit card
                var element = $('<p class="stripe-payment-details is--bold">{$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}</p>');
                element.insertAfter('.payment--panel .payment--content .payment--method-info');
            });
        </script>
    {/if}
{/block}
