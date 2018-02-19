/**
 * A common utility object for handling Apple Pay payments using Stripe.js in the StripePayment plugin.
 */
var StripePaymentApplePay = {

    /**
     * The country code to use for creating Stripe token.
     */
    countryCode: null,

    /**
     * The currency to use for creating Stripe token.
     */
    currencyCode: null,

    /**
     * The statemend desctipor to use for creating Stripe token.
     */
    statementDescriptor: null,

    /**
     * The amount to use for creating Stripe token.
     */
    amount: null,

    /**
     * The Stripe Apple Pay token used for completing the checkout.
     */
    applePayToken: null,

    /**
     * The snippets used for Stripe error descriptions.
     */
    snippets: {
        error: {
            notAvailable: 'Apple Pay is not available on this device/ in this browser. Please select a different payment method.',
            title: 'Error'
        }
    },

    /**
     * Saves the given config, initializes the Stripe service using the given public key,
     * and adds a submit listener on the checkout form for triggering the Apple Pay flow,
     * if available.
     *
     * @param String stripePublicKey
     * @param Object config
     */
    init: function(stripePublicKey, config) {
        var me = this;
        // Save config
        me.countryCode = (config.countryCode) ? config.countryCode.toUpperCase() : null;
        me.currencyCode = (config.currencyCode) ? config.currencyCode.toUpperCase() : null;
        me.statementDescriptor = config.statementDescriptor || null;
        me.amount = config.amount || null;

        // Configure Stripe.js (v2)
        Stripe.setPublishableKey(stripePublicKey);
        Stripe.setLanguage(config.locale || 'en');

        if (me.isShopware5Template()) {
            // Save the original submit button content and add a listiner on the preloader
            // event to be able to reset it
            me.submitButtonContent = me.findForm().parent().find('button[form="confirm--form"]').html();
            $.subscribe('plugin/swPreloaderButton/onShowPreloader', function(event, button) {
                if (me.shouldResetSubmitButton) {
                    me.shouldResetSubmitButton = false;
                    me.resetSubmitButton(button.$el);
                }
            });
        }

        // Add a listener on the form
        me.findForm().on('submit', { scope: me }, me.onFormSubmission);
    },

    /**
     * First validates the form and payment state and, if the main form can be submitted, does nothing further.
     * If however the main form cannot be submitted, because no Apple Pay token exist, the Apple Pay flow is
     * triggered and its token is saved in the form, before the submission is triggered again.
     *
     * @param Event event
     */
    onFormSubmission: function(event) {
        var me = event.data.scope,
            form = $(this);
        // Make sure the AGB checkbox is checked, if it exists. Please note that this check is necessary in both
        // Shopware 4 and Shopware 5 themes, for different reasons. In Shopware 4 templates the checkout form will
        // always be submitted, even if the checkbox is not checked. Hence we don't want to trigger the payment,
        // if not checked. Shopware 5 themes on the other hand validate the checkbox before submitting the
        // checkout form. This validation however does not work on mobile (e.g. iOS Safari), which makes
        // it necessary to always check ourselves.
        if ($('input#sAGB').length === 1 && !$('input#sAGB').is(':checked')) {
            return;
        }

        // Check if a Stripe Apple Pay token was generated and hence the form can be submitted
        if (me.applePayToken) {
            return;
        }

        // Prevent the form from being submitted until a new Stripe Apple Pay token is generated and received
        event.preventDefault();

        // Try to start the Apple Pay flow
        $('#stripe-payment-apple-pay-error-box').hide();
        Stripe.applePay.checkAvailability(function(available) {
            if (available) {
                me.startApplePayFlow(form);
            } else {
                me.shouldResetSubmitButton = true;
                me.handleStripeError(me.snippets.error.notAvailable);
            }
        });
    },

    /**
     * Tiggers the Apple Pay flow and, once successful, the created token is saved in the form,
     * which is then submitted.
     *
     * @param jQuery form
     */
    startApplePayFlow: function(form) {
        var me = this;
        // Trigger the Apple Pay process
        Stripe.applePay.buildSession({
            countryCode: me.countryCode,
            currencyCode: me.currencyCode,
            total: {
                label: me.statementDescriptor,
                amount: me.amount
            }
        }, function(result, completion) {
            me.applePayToken = result.token.id;
            // Add the created Stripe token to the form
            $('input[name="stripeApplePayToken"]').remove();
            $('<input type="hidden" name="stripeApplePayToken" />')
                .val(me.applePayToken)
                .appendTo(form);

            // Complete the Apple Pay flow
            completion(ApplePaySession.STATUS_SUCCESS);

            // Submit the form again to finish the payment process
            form.submit();
        }, function(error) {
            // Reset the submit button and display the error
            me.shouldResetSubmitButton = true;
            var message = me.snippets.error[error.code || ''] || error.message || 'Unknown error';
            me.handleStripeError(message);
        }).begin();
    },

    /**
     * Finds the submit button on the page and resets it by removing the 'disabled' attribute
     * as well as the loading indicator.
     *
     * Note: If called in a shopware environment < v5, this method does nothing.
     */
    resetSubmitButton: function(button) {
        if (this.isShopware5Template()) {
            button.html(this.submitButtonContent).removeAttr('disabled').find('.js--loading').remove();
        }
    },

    /**
     * Sets the given message in the general error box and scrolls the page to make it visible.
     *
     * @param String message A Stripe error message.
     */
    handleStripeError: function(message) {
        // Display the error message and scroll to its position
        var errorBox = $('#stripe-payment-apple-pay-error-box');
        errorBox.show().find('.error-content').html(this.snippets.error.title + ': ' + message);
        $('body').animate({
            scrollTop: (errorBox.offset().top - 50)
        }, 500);
    },

    /**
     * @return jQuery The main checkout form element.
     */
    findForm: function() {
        return (this.isShopware5Template()) ? $('form#confirm--form') : $('.additional_footer form');
    },

    /**
     * @return Boolean True, if a Shopware 5 template is laoded. Otherwise false.
     */
    isShopware5Template: function() {
        return typeof $.overlay !== 'undefined';
    }

};
