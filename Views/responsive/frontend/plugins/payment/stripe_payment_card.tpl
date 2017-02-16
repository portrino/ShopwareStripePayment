{if $Controller != "account" && $payment_mean.action == "stripe_payment_card"}
    {* The main container for filling in the credit card information *}
    <style type="text/css">
        {* Include shared CSS for credit card logo SVGs *}
        {include file="frontend/stripe_payment/_resources/styles/credit_card_logos.css"}
    </style>
    <div id="stripe-payment-form" class="payment--form-group">
        {* Credit card logos *}
        <div class="panel--table">
            <div class="panel--tr">
                <div class="panel--td card visa"></div>
                <div class="panel--td card master-card"></div>
                <div class="panel--td card amex"></div>
            </div>
        </div>

        {* A box for displaying general errors *}
        <div id="stripe-payment-error-box" class="alert is--error is--rounded" style="display: none;">
            <div class="alert--icon">
                <i class="icon--element icon--cross"></i>
            </div>
            <div class="alert--content error-content"></div>
        </div>

        {* The main form field table *}
        <div class="panel--table">
            {if $stripeAllowSavingCreditCard or $allStripeCards|count > 0}
                {* Credit card selection *}
                <div class="panel--tr saved-cards">
                    <label for="stripe-saved-cards" class="panel--td">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card_selection"}{/s}</label>
                    <select id="stripe-saved-cards" class="panel--td">
                        <option value="new"{if $allStripeCards|count == 0} selected{/if}>{s namespace="frontend/plugins/payment/stripe_payment" name="form/card_selection/new_card"}{/s}</option>
                        {foreach from=$allStripeCards item=stripeCard}
                            <option value="{$stripeCard.id}" {if $stripeCard.id == $stripeCard.id}selected{/if}>
                                {$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}
                            </option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            {* Card holder *}
            <div class="panel--tr stripe-card-field">
                <label for="stripe-card-holder" class="panel--td">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/holder"}{/s} *</label>
                <input id="stripe-card-holder" type="text" size="20" class="panel--td" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
            </div>
            {* Card number *}
            <div class="panel--tr stripe-card-field">
                <label for="stripe-element-card-number" class="panel--td">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/number"}{/s} *</label>
                <div id="stripe-element-card-number" class="panel--td"><!-- Stripe element is inserted here --></div>
            </div>
            {* Expiry date *}
            <div class="panel--tr stripe-card-field">
                <label for="stripe-element-card-expiry" class="panel--td">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/expiry"}{/s} *</label>
                <div id="stripe-element-card-expiry" class="panel--td"><!-- Stripe element is inserted here --></div>
            </div>
            {* CVC *}
            <div class="panel--tr stripe-card-field">
                <label for="stripe-element-card-cvc" class="panel--td">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/cvc"}{/s} *</label>
                <div id="stripe-element-card-cvc" class="panel--td"><!-- Stripe element is inserted here --></div>
                <div class="stripe-card-cvc--help help panel--td"
                    data-modalbox="true"
                    data-content="{url controller=StripePaymentCard action=cvcInfo forceSecure}"
                    data-mode="ajax"
                    data-height="430"
                    data-width="650">
                </div>
            </div>
            {if $customerAccountMode == 0 and $stripeAllowSavingCreditCard}
                {* Save data *}
                <div class="panel--tr stripe-card-field">
                    <span class="outer-checkbox">
                        <div class="checkbox">
                            <input id="stripe-save-card" type="checkbox" checked="checked">
                            <span class="checkbox--state"></span>
                        </div>
                    </span>
                    <label for="stripe-save-card">{s namespace="frontend/plugins/payment/stripe_payment" name="form/save_card"}{/s}</label>
                </div>
            {/if}
        </div>

        {* A box for displaying validation errors *}
        <div id="stripe-payment-validation-error-box" class="alert is--error is--rounded" style="display: none;">
            <div class="alert--icon">
                <i class="icon--element icon--cross"></i>
            </div>
            <div class="alert--content error-content"></div>
        </div>

        {* Info *}
        <div class="description">
            {s namespace="frontend/plugins/payment/stripe_payment" name="form/description"}{/s}
        </div>
    </div>
    <script type="text/javascript">
        // Save the payment ID to make it accessible
        var stripePaymentId = {$payment_mean.id};

        // Check whether jQuery is already available to account for both ways this template is loaded:
        //   a) Calling the 'shippingPayment' action when initially loading the payment page.
        //      In this case, jQuery is not ready at this point, because it is added ad the
        //      end of the site. Hence the setup will be performed later.
        //   b) Calling the 'saveShippingPayment' action, when e.g. changing the selected payment method.
        //      In this case, the content of the 'payment selection' form is loaded asynchronously and
        //      add to the DOM. Hence jQuery is already available and the setup code can be performed
        //      right away.
        if (typeof jQuery !== 'undefined') {
            // Stripe setup
            {include file="frontend/stripe_payment/checkout/stripe_payment_card/header.js"}
        }
    </script>
{/if}
