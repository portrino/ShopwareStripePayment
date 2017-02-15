/**
 * A common utility object for handling payments using Stripe.js in the StripePayment plugin.
 */
var StripePayment = {

    /**
     * The Stripe.js instance used e.g. for creating form fields and generating tokens.
     */
    stripeService: null,

    /**
     * The Stripe element containing the credit card number field.
     */
    cardNumberElement: null,

    /**
     * The currently selected or created Stripe card. You should not set this property
     * directly but use setSelectedCard() instead.
     */
    selectedCard: null,

    /**
     * An array of cards available for selection.
     */
    allCards: [],

    /**
     * A flag indicating whether a Stripe request is pending to prevent re-submission.
     */
    requestPending: false,

    /**
     * An object containing the names of all fields that are currently invalid and their resepctive error messages.
     */
    invalidFields: {},

    /**
     * The ID of the Stripe payment method.
     */
    paymentMeansId: -1,

    /**
     * The locale used to configure Stripe error messsages and placeholders.
     */
    locale: 'de',

    /**
     * The snippets used for Stripe error descriptions.
     */
    snippets: {
        error: {
            invalid_card_holder: 'Please enter the card holder\'s name.',
            title: 'Error'
        }
    },

    /**
     * Initialises the util using the given config.
     *
     * @param config A configuration object used to pre-fill the form and setup Stripe.js.
     */
    init: function(config) {
        this.setSelectedCard((typeof config.card !== 'undefined') ? config.card : null);
        this.allCards = (typeof config.allCards !== 'undefined') ? config.allCards : [];
        this.snippets = (typeof config.snippets !== 'undefined') ? config.snippets : this.snippets;

        // Configure Stripe.js
        this.stripeService = Stripe(config.stripePublicKey);

        // Prepare form fields
        this.mountStripeElements();
        this.observeCardHolderFieldChanges();

        // Add DOM listeners
        this.setupCVCPopupControl();
        this.observeFormSubmission();
        this.observeCardSelection();

        if (this.selectedCard) {
            $('#stripe-saved-cards').trigger('change');
        }
    },

    /**
     * Generates and mounts the card form fields using Stripe Elements. The styles used for shopware
     * input fields are applied to the mounted elements.
     */
    mountStripeElements: function() {
        // Define options to apply to all fields
        var cardHolderFieldEl = $('#stripe-card-holder');
        var defaultOptions = {
            style: {
                base: {
                    color: cardHolderFieldEl.css('color'),
                    fontFamily: cardHolderFieldEl.css('font-family'),
                    fontSize: cardHolderFieldEl.css('font-size'),
                    fontWeight: cardHolderFieldEl.css('font-weight'),
                    lineHeight: (cardHolderFieldEl.css('line-height') != 'normal') ? cardHolderFieldEl.css('line-height') : '16px' // Use fallback to 16px in Shopware 4
                }
            }
        };

        // Create and mount fields
        this.cardNumberElement = this.mountStripeElement('#stripe-element-card-number', 'cardNumber', defaultOptions);
        this.mountStripeElement('#stripe-element-card-expiry', 'cardExpiry', defaultOptions);
        this.mountStripeElement('#stripe-element-card-cvc', 'cardCvc', defaultOptions);
    },

    /**
     * Creates a new Stripe element of the given type and mounts it to the DOM element
     * matching the given selector. Finally a subscriber on its 'change' event is added
     * to udpate the validation error box while typing.
     *
     * @param String selector
     * @param String type
     * @param Object options
     * @return Object The created and mounted element.
     */
    mountStripeElement: function(selector, type, options) {
        var me = this,
            element = me.getStripeElements().create(type, options);
        element.mount(selector);
        element.addEventListener('change', function(event) {
            if (event.error && event.error.type === 'validation_error') {
                me.markFieldInvalid(selector, event.error.code, event.error.message);
            } else {
                me.markFieldValid(selector);
            }
        });

        return element;
    },

    /**
     * Adds a subscriber to the card holder form field that is fired when its value is changed
     * to validate the entered value.
     */
    observeCardHolderFieldChanges: function() {
        var me = this,
            selector = '#stripe-card-holder',
            elem = $(selector);
        // Save the current value and observe changes
        elem.data('oldVal', elem.val());
        elem.bind('propertychange keyup input paste', function(event) {
            // Check if value has changed
            if (elem.data('oldVal') == elem.val()) {
                return;
            }
            elem.data('oldVal', elem.val());

            // Validate the field
            if (elem.val().trim().length === 0) {
                elem.addClass('instyle_error has--error');
                me.markFieldInvalid(selector, 'invalid_card_holder');
            } else {
                elem.removeClass('instyle_error has--error');
                me.markFieldValid(selector);
            }
        });
    },

    /**
     * Removes all validation errors for the field with the given 'fieldSelector' and triggers
     * an update of the displayed validation errors.
     *
     * @param String fieldSelector
     */
    markFieldValid: function(fieldSelector) {
        delete this.invalidFields[fieldSelector];
        this.updateValidationErrors();
    },

    /**
     * Determines the error message based on the given 'errorCode' and 'message' and triggers
     * an update of the displayed validation errors.
     *
     * @param String fieldSelector
     * @param String errorCode (optional) The code used to find a localised error message.
     * @param String message (optioanl) The fallback error message used in case no 'errorCode' is provided or no respective, localised description exists.
     */
    markFieldInvalid: function(fieldSelector, errorCode, message) {
        this.invalidFields[fieldSelector] = this.snippets.error[errorCode || ''] || message || 'Unknown error';
        this.updateValidationErrors();
    },

    /**
     * Checks the list of invalid fields for any entries and, if found, joins them to
     * an error message, which is then displayed in the error box. If no invalid fields
     * are found, the error box is hidden.
     */
    updateValidationErrors: function() {
        var me = this;
        var boxEl = $('#stripe-payment-validation-error-box .error-content')
            .first()
            .empty();
        if (Object.keys(me.invalidFields).length > 0) {
            // Update the error box message and make it visible
            var listEl = $('<ul></ul>')
                .addClass('alert--list')
                .appendTo(boxEl);
            Object.keys(me.invalidFields).forEach(function(key) {
                var row = $('<li></li>')
                    .addClass('list--entry')
                    .text(me.invalidFields[key])
                    .appendTo(listEl);
            });
            $('#stripe-payment-validation-error-box').show();
        } else {
            $('#stripe-payment-validation-error-box').hide();
        }
    },

    /**
     * Saves the given card and removes all hidden Stripe fields from the form.
     * If the card exists, its ID as well as its encoded data are added to the form
     * as hidden fields.
     *
     * @param card A Stripe card object.
     */
    setSelectedCard: function(card) {
        this.selectedCard = card;
        // Remove all hidden Stripe fields from the form
        $('input[name="stripeTransactionToken"]').remove();
        $('input[name="stripeCardId"]').remove();
        $('input[name="stripeCard"]').remove();

        if (this.selectedCard) {
            // Add the data of the new card to the form
            var form = this.findForm();
            form.append('<input type="hidden" name="stripeCardId" value="' + this.selectedCard.id + '" />');
            form.append('<input type="hidden" name="stripeCard" value="" />');
            $('input[name="stripeCard"]').val(JSON.stringify(this.selectedCard));
        }
    },

    /**
     * Adds to 'click' listeners to the CVC info button as well as the info
     * popup's close button for opening/closing the popup.
     */
    setupCVCPopupControl: function() {
        var cvcInfoPopup = $('#stripe-payment-cvc-info-popup');
        if (this.isShopware5Template()) {
            // Shopware 5
            $('#stripe-payment-cvc-info-button').click(function(event) {
                // Add the CVC explanation popup to the overlay and open it
                $.overlay.getElement().append(cvcInfoPopup);
                cvcInfoPopup.show();
                $.overlay.open({
                    closeOnClick: false
                });
            });
            $('#stripe-payment-cvc-info-popup-close').click(function(event) {
                // Close the overlay and remove the CVC explanation popup from it
                $.overlay.close();
                cvcInfoPopup.hide();
                $('#stripe-payment-form').append(cvcInfoPopup);
            });
        } else {
            // Shopware 4
            $('#stripe-payment-cvc-info-button').click(function(event) {
                cvcInfoPopup.show();
                cvcInfoPopup.parent().show();
            });
            $('#stripe-payment-cvc-info-popup-close').click(function(event) {
                cvcInfoPopup.parent().hide();
                cvcInfoPopup.hide();
            });
        }
    },

    /**
     * First validates the form and payment state and, if the main form can be submitted, does nothing further.
     * If however the main from cannot be submitted, because neither a token was created nor a saved card is
     * selected, a new Stripe token is created and saved in the form, before the submission is triggered again.
     */
    observeFormSubmission: function() {
        var me = this;
        me.findForm().on('submit', function(event) {
            var form = $(this);
            // Check that the Stripe payment method is selected (Shopware 4 only)
            if (!me.isStripePaymentSelected()) {
                return;
            }

            // Check if a token/card was generated and hence the form can be submitted
            if (me.selectedCard !== null) {
                // Append the value of the checkbox, indicating whether the credit card info shall be saved
                form.append('<input type="hidden" name="stripeSaveCard" value="' + ($('#stripe-save-card').is(':checked') ? 'on' : 'off') + '" />');
                return;
            } else {
                // Prevent the form from being submitted until a new Stripe token is generated and received
                event.preventDefault();
            }

            // Check if a pending Stripe request
            if (me.requestPending) {
                return;
            }

            // Check for invalid fields
            if (Object.keys(me.invalidFields).length > 0) {
                return;
            }

            // Send the credit card information to Stripe
            me.requestPending = true;
            if (me.isShopware5Template()) {
                $.loadingIndicator.open();
            }
            me.stripeService.createToken(me.cardNumberElement, {
                name: $('#stripe-card-holder').val()
            }).then(function(result) {
                me.requestPending = false;
                if (result.error) {
                    // Only close the loading indicator if an error occurred
                    if (me.isShopware5Template()) {
                        $.loadingIndicator.close();
                    }

                    // Display the error
                    var message = me.snippets.error[result.error.code || ''] || result.error.message || 'Unknown error';
                    me.handleStripeError(me.snippets.error.title + ': ' + message);
                } else {
                    // Save the card information
                    me.setSelectedCard(result.token.card);

                    // Remove the card ID from the form (added by 'setSelectedCard') and add the new Stripe token instead
                    $('input[name="stripeCardId"]').remove();
                    form.append('<input type="hidden" name="stripeTransactionToken" value="' + result.token.id + '" />');
                    form.submit();
                }
            });
        });
    },

    /**
     * Adds a change observer to the card selection field. If an existing card is selected, all form fields
     * are hidden and the card's Stripe information is added to the form. If the 'new' option is selected,
     * all fields made visible and the Stripe card info is removed from the form.
     */
    observeCardSelection: function() {
        var me = this;
        $('#stripe-saved-cards').change(function(event) {
            var elem = $(this);
            if (elem.val() === 'new') {
                // A new, empty card was selected
                me.setSelectedCard(null);

                // Make validation errors visible
                me.updateValidationErrors();

                // Show the save check box
                $('#stripe-payment-form .stripe-card-field').show();
                $('#stripe-save-card').show().prop('checked', true);

                return;
            }

            // Find the selected card
            for (var i = 0; i < me.allCards.length; i++) {
                var selectedCard = me.allCards[i];
                if (selectedCard.id !== elem.val()) {
                    continue;
                }

                // Save the card
                me.setSelectedCard(selectedCard);

                // Hide validation errors
                $('#stripe-payment-validation-error-box').hide();

                // Hide all card fields
                $('#stripe-payment-form .stripe-card-field').hide();
                $('#stripe-save-card').hide();

                break;
            }
        });
    },

    /**
     * Sets the given message in the general error box and scrolls the page to make it visible.
     *
     * @param String message A Stripe error message.
     */
    handleStripeError: function(message) {
        // Display the error information above the credit card form and scroll to its position
        $('#stripe-payment-error-box').show().children('.error-content').html(message);
        $('body').animate({
            scrollTop: ($('#stripe-payment-form').offset().top - 100)
        }, 500);
    },

    /**
     * @return Object An Stripe.js Elements instance.
     */
    getStripeElements: function() {
        if (!this.stripeElements) {
            this.stripeElements = this.stripeService.elements({
                locale: this.locale
            });
        }

        return this.stripeElements;
    },

    /**
     * @return Object The main payment selection form element.
     */
    findForm: function() {
        return (this.isShopware5Template()) ? $('#shippingPaymentForm') : $('#basketButton').closest('form');
    },

    /**
     * @return Boolean True, if the Stripe payment method is selected. Otherwise false.
     */
    isStripePaymentSelected: function() {
        return $('#payment_mean' + this.paymentMeansId).is(':checked');
    },

    /**
     * @return Boolean True, if a Shopware 5 template is laoded. Otherwise false.
     */
    isShopware5Template: function() {
        return typeof $.overlay !== 'undefined';
    }

};
