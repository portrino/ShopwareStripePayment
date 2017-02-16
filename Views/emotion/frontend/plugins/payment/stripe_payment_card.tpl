{if $Controller != "account" && $payment_mean.action == "stripe_payment_card"}
    {* Include the custom styles for the payment form *}
    <link href="{link file="frontend/stripe_payment/_resources/styles/stripe_payment_card.css"}" rel="stylesheet">
    {* Include and set up the Stripe SDK *}
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/jquery.payment.min.js"}"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/stripe_payment_card.js"}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Stripe setup
            var stripeCardPaymentId = {$payment_mean.id};
            {include file="frontend/stripe_payment/checkout/stripe_payment_card/header.js"}
        });
    </script>

    {* The main container for filling in the credit card information *}
    <div id="stripe-payment-form">
        {* A box for displaying general errors *}
        <div id="stripe-payment-error-box" style="display: none;">
            <div class="error-content"></div>
        </div >

        {if $stripeAllowSavingCreditCard or $allStripeCards|count > 0}
            {* Credit card selection *}
            <div class="form-group">
                <label for="stripe-saved-cards" class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card_selection}{/s}</label>
                <select id="stripe-saved-cards" class="form-input adjust-margin" style="width: 365px;">
                    <option value="new"{if $allStripeCards|count == 0} selected{/if}>{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card_selection/new_card}{/s}</option>
                    {foreach from=$allStripeCards item=stripeCard}
                        <option value="{$stripeCard.id}"{if $stripeCard.id == $stripeCard.id} selected{/if}>{$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}</option>
                    {/foreach}
                </select>
            </div>
        {/if}
        {* Card holder *}
        <div class="form-group stripe-card-field">
            <label for="stripe-card-holder" class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/holder}{/s} *</label>
            <input id="stripe-card-holder" class="form-input text" type="text" size="20" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
        </div>
        {* Card number *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-number" class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/number}{/s} *</label>
            <div id="stripe-element-card-number" class="form-input"><!-- Stripe element is inserted here --></div>
        </div>
        {* Expiry date *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-expiry" class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/expiry}{/s} *</label>
            <div id="stripe-element-card-expiry" class="form-input"><!-- Stripe element is inserted here --></div>
        </div>
        {* CVC *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-cvc" class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/cvc}{/s} *</label>
            <div id="stripe-element-card-cvc" class="form-input"><!-- Stripe element is inserted here --></div>
            <div id="stripe-payment-card-cvc-info-button" class="help"></div>
        </div>
        {if $customerAccountMode == 0 and $stripeAllowSavingCreditCard}
            {* Save data *}
            <div class="form-group stripe-card-field adjust-margin">
                <input id="stripe-save-card" class="form-input" type="checkbox" checked="checked">
                <label for="stripe-save-card" class="control-label checkbox">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/save_card}{/s}</label>
            </div>
        {/if}

        {* A box for displaying validation errors *}
        <div id="stripe-payment-validation-error-box" style="display: none;">
            <div class="error-content"></div>
        </div >

        {* Info *}
        <div class="form-group adjust-margin">
            <p>{s namespace=frontend/plugins/payment/stripe_payment/card name=form/description}{/s}</p>
        </div>

        {* An initially hidden CVC info popup window *}
        <div class="stripe-payment-card-cvc-info-popup-overlay">
            <div id="stripe-payment-card-cvc-info-popup">
                {strip}
                <div class="stripe-payment-card-cvc-info-popup-container">
                    <div id="stripe-payment-card-cvc-info-popup-close"></div>
                    {include file="frontend/stripe_payment/checkout/stripe_payment_card/cvc_info.tpl"}
                </div>
                {/strip}
            </div>
        </div>
    </div>
{/if}
