{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{block name="frontend_index_header_javascript_jquery" append}
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        {**
         * Uncomment the following line the speed up development by including the custom
         * Stripe payment library instead of loading it from the compiled Javascript file
         *}
        {* {include file="frontend/stripe_payment/_resources/javascript/stripe_payment_card.js"} *}

        // Fix selectbox replacement for dynamically loaded payment forms
        // See also: https://github.com/shopware/shopware/pull/357
        $.subscribe('plugin/swShippingPayment/onInputChanged', function(event, shippingPayment) {
            shippingPayment.$el.find('select:not([data-no-fancy-select="true"])').swSelectboxReplacement();
            shippingPayment.$el.find('.stripe-card-cvc--help').swModalbox();
        });

        // Include the shared initialization of the StripePaymentCard library
        {include file='frontend/stripe_payment/checkout/stripe_payment_card/header.js'}
    </script>
{/block}
