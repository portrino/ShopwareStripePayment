{block name="frontend_checkout_finishs_transaction_number"}
    {$smarty.block.parent}
    {if $stripePaymentSepatMandateUrl}
        <link href="{link file='frontend/stripe_payment/_resources/styles/stripe_payment/checkout.css'}" rel="stylesheet">
        <div class="stripe-payment-sepa-mandate">
            <div class="mandate-info">
                <strong>{s namespace=frontend/plugins/payment/stripe_payment/sepa name=checkout/finish/mandate_info/title}{/s}</strong>
                <p>{s namespace=frontend/plugins/payment/stripe_payment/sepa name=checkout/finish/mandate_info/text}{/s}</p>
            </div>
            <a class="button-middle small" href="{$stripePaymentSepatMandateUrl}" target="_blank">{s namespace=frontend/plugins/payment/stripe_payment/sepa name=checkout/finish/mandate_info/open_mandate_button/title}{/s}</a>
        </div>
        <div class="doublespace">&nbsp;</div>
    {/if}
{/block}
