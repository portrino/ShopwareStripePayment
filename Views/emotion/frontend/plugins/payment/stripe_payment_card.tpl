{if $Controller != "account" && $payment_mean.action == "stripe_payment_card"}
    {* Include the custom styles for the payment form *}
    <link href="{link file='frontend/stripe_payment/_resources/styles/stripe_payment_card.css'}" rel="stylesheet">
    {* Include and set up the Stripe SDK *}
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/jquery.payment.min.js"}"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/stripe_payment_card.js"}"></script>
    <script type="text/javascript">
        // Include the shared initialization of the StripePaymentCard library
        {include file='frontend/stripe_payment/checkout/stripe_payment_card/header.js'}
    </script>

    {* The main container for filling in the credit card information *}
    <div class="stripe-payment-card-form">
        {* A box for displaying general errors *}
        <div class="stripe-payment-error-box" style="display: none;">
            <div class="error-content"></div>
        </div >

        {if $stripeAllowSavingCreditCard or $allStripeCards|count > 0}
            {* Credit card selection *}
            <div class="form-group">
                <label>
                    <span class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card_selection}{/s}</span>
                    <select class="stripe-saved-cards form-input adjust-margin">
                        <option value="new" selected>
                            {s namespace=frontend/plugins/payment/stripe_payment/card name=form/card_selection/new_card}{/s}
                        </option>
                        {foreach from=$allStripeCards item=stripeCard}
                            <option value="{$stripeCard.id}"}>
                                {$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}
                            </option>
                        {/foreach}
                    </select>
                </label>
            </div>
        {/if}
        {* Card holder *}
        <div class="form-group stripe-card-field">
            <label>
                <span class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/holder}{/s} *</span>
                <input class="stripe-card-holder form-input text" type="text" size="20" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
            </label>
        </div>
        {* Card number *}
        <div class="form-group stripe-card-field">
            <label>
                <span class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/number}{/s} *</span>
                <div class="stripe-element-card-number form-input"><!-- Stripe element is inserted here --></div>
            </label>
        </div>
        {* Expiry date *}
        <div class="form-group stripe-card-field">
            <label>
                <span class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/expiry}{/s} *</span>
                <div class="stripe-element-card-expiry form-input"><!-- Stripe element is inserted here --></div>
            </label>
        </div>
        {* CVC *}
        <div class="form-group stripe-card-field">
            <label>
                <span class="control-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/cvc}{/s} *</span>
                <div class="stripe-element-card-cvc form-input"><!-- Stripe element is inserted here --></div>
            </label>
            <div class="stripe-payment-card-cvc-info-button help"></div>
        </div>
        {if $customerAccountMode == 0 and $stripeAllowSavingCreditCard}
            {* Save data *}
            <div class="form-group stripe-card-field adjust-margin">
                <label>
                    <input class="stripe-save-card form-input" type="checkbox" checked="checked">
                    <span class="control-label checkbox">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/save_card}{/s}</span>
                </label>
            </div>
        {/if}

        {* A box for displaying validation errors *}
        <div class="stripe-payment-validation-error-box" style="display: none;">
            <div class="error-content"></div>
        </div >

        {* Info *}
        <div class="form-group adjust-margin">
            <p>{s namespace=frontend/plugins/payment/stripe_payment/card name=form/description}{/s}</p>
        </div>

        {* An initially hidden CVC info popup window *}
        <div class="stripe-payment-card-cvc-info-popup-overlay">
            <div class="stripe-payment-card-cvc-info-popup">
                {strip}
                <div class="stripe-payment-card-cvc-info-popup-container">
                    <div class="stripe-payment-card-cvc-info-popup-close"></div>
                    {include file="frontend/stripe_payment/checkout/stripe_payment_card/cvc_info.tpl"}
                </div>
                {/strip}
            </div>
        </div>
    </div>
{/if}
