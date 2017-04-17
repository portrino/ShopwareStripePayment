{block name="frontend_checkout_payment_fieldset_description"}
    {if $Controller != "account" && $payment_mean.action == "StripePayment"}
        {* Inject the payment logos before the additional description *}
        <div class="grid_10 last">
            {* Payment logos *}
            {if $payment_mean.class == "StripePaymentApplePay"}
                <div class="stripe-payment-provider-logo apple-pay"></div>
            {elseif $payment_mean.class == "StripePaymentBancontact"}
                <div class="stripe-payment-provider-logo bancontact"></div>
            {elseif $payment_mean.class == "StripePaymentCard"}
                {if $payment_mean.name == "stripe_payment_card_three_d_secure"}
                    <div class="stripe-payment-provider-logo verified-by-visa"></div>
                    <div class="stripe-payment-provider-logo mastercard-secure-code"></div>
                    <div class="stripe-payment-provider-logo american-express-safe-key"></div>
                {else}
                    <div class="stripe-payment-provider-logo visa"></div>
                    <div class="stripe-payment-provider-logo mastercard"></div>
                    <div class="stripe-payment-provider-logo american-express"></div>
                {/if}
            {elseif $payment_mean.class == "StripePaymentGiropay"}
                <div class="stripe-payment-provider-logo giropay"></div>
            {elseif $payment_mean.class == "StripePaymentIdeal"}
                <div class="stripe-payment-provider-logo ideal"></div>
            {elseif $payment_mean.class == "StripePaymentSepa"}
                <div class="stripe-payment-provider-logo sepa"></div>
            {elseif $payment_mean.class == "StripePaymentSofort"}
                <div class="stripe-payment-provider-logo sofort"></div>
            {/if}

            {* Default content *}
            <div class="grid_10 last" style="margin-top: 5px; margin-left: 0px;">
                {include file="string:{$payment_mean.additionaldescription}"}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
