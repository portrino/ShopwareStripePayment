{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_error_messages" append}
    {include file="frontend/checkout/stripe_payment_error.tpl"}
{/block}

{block name="frontend_index_header_javascript_jquery" append}
    {if ($sUserData.additional.payment.class == "StripePaymentCard" && $stripePayment.selectedCard) || ($sUserData.additional.payment.class == "StripePaymentSepa" && $stripePayment.sepaSource)}
        <script type="text/javascript">
            $(document).ready(function() {
                // Add special class to body to trigger custom CSS rules in Shopware versions >= 5.0 and < 5.2.
                // Starting from Shopware 5.2, the enclosing container uses content based autoresizing, which
                // would break, if we enabled the custom CSS rule.
                if ($('#confirm--form .information--panel-wrapper').attr('data-panel-auto-resizer') !== 'true') {
                    $('body').addClass('is--stripe-payment-selected');
                }

                // Insert a new element right below the general payment information showing details of the selected payment method
                var paymentMethodClass = '{$sUserData.additional.payment.class}',
                    content;
                if (paymentMethodClass === 'StripePaymentCard') {
                    // Show details of selected credit card
                    content = '{$stripePayment.selectedCard.name} | {$stripePayment.selectedCard.brand} | &bull;&bull;&bull;&bull;{$stripePayment.selectedCard.last4} | {$stripePayment.selectedCard.exp_month|string_format:"%02d"}/{$stripePayment.selectedCard.exp_year}';
                } else if (paymentMethodClass === 'StripePaymentSepa') {
                    // Show details of SEPA source
                    content = '{$stripePayment.sepaSource.owner.name} | {$stripePayment.sepaSource.sepa_debit.country}&bull;&bull;&bull;&bull;{$stripePayment.sepaSource.sepa_debit.last4} | {$stripePayment.sepaSource.sepa_debit.mandate_reference}';
                }
                var element = $('<p class="stripe-payment-details is--bold">' + content + '</p>');
                element.insertAfter('.payment--panel .payment--content .payment--method-info');
            });
        </script>
    {/if}
{/block}
