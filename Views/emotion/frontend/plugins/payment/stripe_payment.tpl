{if $Controller != "account" && $payment_mean.action == "stripe_payment"}
    {* Additional styling for the stripe payment box *}
    <style type="text/css">
        #stripe-payment-error-box,
        #stripe-payment-validation-error-box {
            padding: 10px;
            margin-bottom: 15px;
            color: #B94A48;
            font-weight: bold;
            background-color: #F2DEDE;
            border: 1px solid #DF7373;
        }
        #stripe-payment-form .form-group {
            clear: both;
            width: 600px;
            height: 38px;
        }
        #stripe-payment-form .form-group .form-input {
            float: left;
        }
        #stripe-payment-form .adjust-margin {
            margin-top: 5px;
        }
        #stripe-payment-form label {
            cursor: pointer;
            width: 150px !important;
        }
        #stripe-payment-form input:focus {
            /* like emotion.css: input:focus */
            border-color: #666;
        }
        #stripe-payment-form .StripeElement {
            width: 365px;
            padding: 5px;
            margin: 0.5em 0;
            background: #fff;
            border: 1px solid #bbb;
        }
        #stripe-payment-form .StripeElement.StripeElement--focus {
            /* like emotion.css: input:focus */
            border-color: #666;
        }
        #stripe-payment-form .StripeElement.StripeElement--invalid {
            /* like emotion.css: instyle_error */
            border-color: #df7373;
            background: #f7e9e9;
            box-shadow: 0 0 4px #f0d5dc;
        }
        #confirm .personal-information .payment_method .bankdata .form-group label.checkbox {
            width: auto !important;
            padding-left: 5px;
            padding-top: 2px !important;
        }
        #stripe-payment-form .help {
            cursor: pointer;
            width: 22px;
            height: 100%;
            float: left;
            padding-left: 10px;
            background-size: 22px;
            background-repeat: no-repeat;
            background-position: center;
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAYAAAAehFoBAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJBN0MwQTc5REQ4RjExRTM5MzczRDk3QzQxRTkxOTIwIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJBN0MwQTdBREQ4RjExRTM5MzczRDk3QzQxRTkxOTIwIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QkE3QzBBNzdERDhGMTFFMzkzNzNEOTdDNDFFOTE5MjAiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QkE3QzBBNzhERDhGMTFFMzkzNzNEOTdDNDFFOTE5MjAiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5xa1/RAAADy0lEQVR42tRZTUhUURS+M8xCMUMjGVAQYTAppF9EEGkW4kRoTjjRDwXmJokIF26shqKoaCtDim7SRU0gNlTI9OdCa+MmmRhbZG0ExTRQnLLZTeeb7lzfDPP37ntvZvzgg/fezDv347xzzz33XIvD4WAaUEJsJtqJB4j7iFbiLv77b+JP4jfiV+IM8SPxj+yAFol3TMQTxG7iKWJxmv/u4dxPdBJvEP8SXxOfEN8SI2oGN6sU20mcI/qJZzOITYVi/q6f2zpthGAb8R1xgniI6QfY8nHbNr0EnyN+JrYy49DKxzivRTBi9T7xOXE3Mx4Yw8vHNKkVjBc8xFss98CYQ6lEpxL8kHiN5Q89XENWghFH/Sz/gIYLmQRjpg6zwsFwYvZIXDiGZCdYS0sLq6mpYXa7Pe758vIyCwQCzO/3s42NDbVmS7kmsRybFEtzJ8+zqlBWVsZ6e3uZzZY5jY6NjbGpqSkZf7iIL5Qexoy8LRVo/f2ssrJSeNPn87HZ2dnofXV1NXM6nayhoSF639XVxVZWVtj8/LzaYe7wBSYSi2GHzArW0dERJxbiY2KBxcVF5vF42PT0tHjW1tYm45eDvH4Rk65bxkpTU5O4HhwcTPm/iYntSKuvr5edgJdjgkt4JaU6dpXehTdTAZNtfX1d3CNUJACNJWZezxapfbuqqkpcLy0t5SLFQWOzhRffqhEKhaKpClhdXc1cUxZvV6Kbm5uyou0QLBVUCIF0YZA4OYuK/n/EYDAok4/FFEBI1Br5HRGv7e3t4n5yclKLuVoIrjBSbF9fn/AuQkgiBytRYeHLnyFi3W53nFiv16vVbKnZCLFIeYme1UGsqNZCegt2uVysvLxcTDK9xEIrBK/p7d3GxsbodTgcZiMjI3qaX4PgBT0t1tXViVBAXaEhhSXDAgQH9bRotVrF9dbWlt7RFjTz9tFOwQwKeBQ/v2TqiRwjTNwLD6Mx93IHePcVtMZ2HKO8w6MZAwMDIqWhpMT2SSeMKgt4dBG/FLB3oe2NUjBanncLWPA9rjGuL4Fd6fsCFPtBuZs3JXTgsVefM6ogklmKiUeJ31N1fn4QrxSQd3uUYpMJBtBefVQAYqHBm6xaS4abxMd5FDvMNbBsBWNGXic+yINYjHmVpTisSVfA4wU38SI2ujkQijEu8TEj6Qr4THhGPMbTi5GpC2M8zWbHkQ0wU3FwckbnFTHAbbYmZgOtgmNAAj9MPEkc5xWUTNU1zm0cYSpbvDInoRG+roM4okWr6zhvyKDHkXh0i7YQjm6xv0cb8xN/LoV/AgwAANj+CFLxbboAAAAASUVORK5CYII=');
        }
        .stripe-payment-cvc-info-popup-overlay {
            display: none;
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.3);
        }
        #stripe-payment-cvc-info-popup {
            display: none;
            position: absolute;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 480px;
        }
        .stripe-payment-cvc-info-popup-container {
            position: absolute;
            margin-left: -400px;
            padding: 10px 20px;
            border: 2px solid #AAA;
            border-radius: 8px;
            top: 0;
            left: 50%;
            width: 740px;
            height: 460px;
            background-color: white;
        }
        #stripe-payment-cvc-info-popup-close {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 45px;
            height: 45px;
            background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjQsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iNDVweCIgaGVpZ2h0PSI0NXB4IiB2aWV3Qm94PSIwIDAgNDUgNDUiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDQ1IDQ1IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjIyLjUiIGN5PSIyMi41IiByPSIyMi41Ii8+DQo8cGF0aCBpZD0ieC1tYXJrLTQtaWNvbiIgZmlsbD0iIzMzMzMzMyIgZD0iTTQyLjAwNCwyMi41YzAtMTAuNzgxLTguNzI1LTE5LjUwNC0xOS41MDQtMTkuNTA0Yy0xMC43ODEsMC0xOS41MDQsOC43MjUtMTkuNTA0LDE5LjUwNA0KCWMwLDEwLjc4MSw4LjcyNSwxOS41MDQsMTkuNTA0LDE5LjUwNEMzMy4yODEsNDIuMDA0LDQyLjAwNCwzMy4yNzksNDIuMDA0LDIyLjV6IE0zMC42NDYsMzUuMDEybC03LjkxLTcuOTFsLTcuOTExLDcuOTExDQoJbC00LjI1LTQuMjUxbDcuOTA5LTcuOTFsLTcuOTEtNy45MWw0LjI1MS00LjI0OWw3LjkwOSw3LjkwOGw3LjkwNy03LjkwOWw0LjI1Miw0LjI1bC03LjkwOSw3LjkwOWw3LjkxLDcuOTA4TDMwLjY0NiwzNS4wMTJ6Ii8+DQo8L3N2Zz4NCg==');
            background-repeat: no-repeat;
            background-position: top left;
            cursor: pointer;
        }
        .stripe-payment-cvc-info-popup-cardtype {
            display: inline-block;
            padding: 30px;
            width: 310px;
            vertical-align: top;
        }
        .stripe-payment-cvc-infotext {
            padding-top: 30px;
            font-size: 15px;
            line-height: 1.3;
            color: #555555;
        }
    </style>
    {* Include and set up the Stripe SDK *}
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/jquery.payment.min.js"}"></script>
    <script type="text/javascript" src="{link file="frontend/stripe_payment/_resources/javascript/stripe_payment.js"}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Stripe setup
            var stripePaymentId = {$payment_mean.id};
            {include file="frontend/stripe_payment/checkout/stripe_payment_header.js"}
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
                <label for="stripe-saved-cards" class="control-label">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card_selection"}{/s}</label>
                <select id="stripe-saved-cards" class="form-input adjust-margin" style="width: 365px;">
                    <option value="new"{if $allStripeCards|count == 0} selected{/if}>{s namespace="frontend/plugins/payment/stripe_payment" name="form/card_selection/new_card"}{/s}</option>
                    {foreach from=$allStripeCards item=stripeCard}
                        <option value="{$stripeCard.id}"{if $stripeCard.id == $stripeCard.id} selected{/if}>{$stripeCard.name} | {$stripeCard.brand} | &bull;&bull;&bull;&bull;{$stripeCard.last4} | {$stripeCard.exp_month}/{$stripeCard.exp_year}</option>
                    {/foreach}
                </select>
            </div>
        {/if}
        {* Card holder *}
        <div class="form-group stripe-card-field">
            <label for="stripe-card-holder" class="control-label">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/holder"}{/s} *</label>
            <input id="stripe-card-holder" class="form-input text" type="text" size="20" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
        </div>
        {* Card number *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-number" class="control-label">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/number"}{/s} *</label>
            <div id="stripe-element-card-number" class="form-input"><!-- Stripe element is inserted here --></div>
        </div>
        {* Expiry date *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-expiry" class="control-label">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/expiry"}{/s} *</label>
            <div id="stripe-element-card-expiry" class="form-input"><!-- Stripe element is inserted here --></div>
        </div>
        {* CVC *}
        <div class="form-group stripe-card-field">
            <label for="stripe-element-card-cvc" class="control-label">{s namespace="frontend/plugins/payment/stripe_payment" name="form/card/cvc"}{/s} *</label>
            <div id="stripe-element-card-cvc" class="form-input"><!-- Stripe element is inserted here --></div>
            <div id="stripe-payment-cvc-info-button" class="help"></div>
        </div>
        {if $customerAccountMode == 0 and $stripeAllowSavingCreditCard}
            {* Save data *}
            <div class="form-group stripe-card-field adjust-margin">
                <input id="stripe-save-card" class="form-input" type="checkbox" checked="checked">
                <label for="stripe-save-card" class="control-label checkbox">{s namespace="frontend/plugins/payment/stripe_payment" name="form/save_card"}{/s}</label>
            </div>
        {/if}

        {* A box for displaying validation errors *}
        <div id="stripe-payment-validation-error-box" style="display: none;">
            <div class="error-content"></div>
        </div >

        {* Info *}
        <div class="form-group adjust-margin">
            <p>{s namespace="frontend/plugins/payment/stripe_payment" name="form/description"}{/s}</p>
        </div>

        {* An initially hidden CVC info popup window *}
        <div class="stripe-payment-cvc-info-popup-overlay">
            <div id="stripe-payment-cvc-info-popup">
                {strip}
                <div class="stripe-payment-cvc-info-popup-container">
                    <div id="stripe-payment-cvc-info-popup-close"></div>
                    {include file="frontend/stripe_payment/checkout/stripe_payment_cvc_info.tpl"}
                </div>
                {/strip}
            </div>
        </div>
    </div>
{/if}
