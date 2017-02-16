{if $Controller != "account" && $payment_mean.action == "stripe_payment_card"}
    <style type="text/css">
        {* Include shared CSS for credit card logo SVGs *}
        {include file="frontend/stripe_payment/_resources/styles/credit_card_logos.css"}
    </style>
    {* The main container for filling in the credit card information *}
    <div class="stripe-payment-card-form payment--form-group">
        {* Credit card logos *}
        <div class="panel--table">
            <div class="panel--tr">
                <div class="panel--td card visa"></div>
                <div class="panel--td card master-card"></div>
                <div class="panel--td card amex"></div>
            </div>
        </div>

        {* A box for displaying general errors *}
        <div class="stripe-payment-error-box alert is--error is--rounded" style="display: none;">
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
                    <label>
                        <span class="panel--td">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card_selection}{/s}</span>
                        <select class="stripe-saved-cards" class="panel--td">
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
            <div class="panel--tr stripe-card-field">
                <label>
                    <span class="panel--td">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/holder}{/s} *</span>
                    <input type="text" size="20" class="stripe-card-holder panel--td" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
                </label>
            </div>
            {* Card number *}
            <div class="panel--tr stripe-card-field">
                <label>
                    <span class="panel--td">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/number}{/s} *</span>
                    <div class="stripe-element-card-number panel--td"><!-- Stripe element is inserted here --></div>
                </label>
            </div>
            {* Expiry date *}
            <div class="panel--tr stripe-card-field">
                <label>
                    <span class="panel--td">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/expiry}{/s} *</span>
                    <div class="stripe-element-card-expiry panel--td"><!-- Stripe element is inserted here --></div>
                </label>
            </div>
            {* CVC *}
            <div class="panel--tr stripe-card-field">
                <label>
                    <span class="panel--td">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/card/cvc}{/s} *</span>
                    <div class="stripe-element-card-cvc panel--td"><!-- Stripe element is inserted here --></div>
                </label>
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
                    <label>
                        <span class="outer-checkbox">
                            <div class="checkbox">
                                <input class="stripe-save-card" type="checkbox" checked="checked">
                                <span class="checkbox--state"></span>
                            </div>
                        </span>
                        <span class="checkox-label">{s namespace=frontend/plugins/payment/stripe_payment/card name=form/save_card}{/s}</span>
                    </label>
                </div>
            {/if}
        </div>

        {* A box for displaying validation errors *}
        <div class="stripe-payment-validation-error-box alert is--error is--rounded" style="display: none;">
            <div class="alert--icon">
                <i class="icon--element icon--cross"></i>
            </div>
            <div class="alert--content error-content"></div>
        </div>

        {* Info *}
        <div class="description">
            {s namespace=frontend/plugins/payment/stripe_payment/card name=form/description}{/s}
        </div>
    </div>
{/if}
