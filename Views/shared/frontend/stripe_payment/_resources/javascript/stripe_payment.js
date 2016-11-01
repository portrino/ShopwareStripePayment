/**
 * A common utility object for handling payments using Stripe.js in the StripePayment plugin.
 */
var StripePayment = {

    /**
     * The currently selected or created Stripe card. You should not set this property
     * directly but use setCard() instead.
     */
    card: null,

    /**
     * An array of cards available for selection.
     */
    allCards: [],

    /**
     * A flag indicating whether a Stripe request is pending to prevent re-submission.
     */
    requestPending: false,

    /**
     * The ID of the Stripe payment method.
     */
    paymentMeansId: -1,

    /**
     * The snippets used for Stripe error descriptions.
     */
    snippets: {
        error: {
            title: 'Error',
            invalidName: 'Invalid name',
            invalidNumber: 'Invalid card number',
            invalidCVC: 'Invalid CVC',
            invalidExpiry: 'Invalid expiry date'
        }
    },

    /**
     * Initialises the util using the given config.
     *
     * @param config A configuration object used to pre-fill the form and setup Stripe.js.
     */
    init: function(config) {
        this.setCard((typeof config.card !== 'undefined') ? config.card : null);
        this.allCards = (typeof config.allCards !== 'undefined') ? config.allCards : [];
        this.snippets = (typeof config.snippets !== 'undefined') ? config.snippets : this.snippets;

        // Set the public Stripe key as well as localised error messages
        Stripe.setPublishableKey(config.stripePublicKey);
        Stripe.setLanguage('de');

        // Fill and pre-select the expiry data selectiom
        var selectedMonth = (typeof config.selectedMonth !== 'undefined') ? config.selectedMonth : (new Date().getMonth() + 1);
        var selectedYear = (typeof config.selectedYear !== 'undefined') ? config.selectedYear : new Date().getFullYear();
        this.setupExpiryDateSelection(selectedMonth, selectedYear);

        // Add DOM listeners
        this.setupCVCPopupControl();
        this.observeFormSubmission();
        this.observeFieldChanges();
        this.observeCardSelection();

        // Add constraints to some Stripe input fields
        $('#stripe-card-number').payment('formatCardNumber');
        $('#stripe-card-cvc').payment('formatCardCVC');
    },

    /**
     * Saves the given card and removes all hidden Stripe fields from the form.
     * If the card exists, its ID as well as its encoded data are added to the form
     * as hidden fields.
     *
     * @param card A Stripe card object.
     */
    setCard: function(card) {
        this.card = card;
        // Remove all hidden Stripe fields from the form
        $('input[name="stripeTransactionToken"]').remove();
        $('input[name="stripeCardId"]').remove();
        $('input[name="stripeCard"]').remove();

        if (this.card !== null) {
            // Add the data of the new card to the form
            var form = this.findForm();
            form.append('<input type="hidden" name="stripeCardId" value="' + this.card.id + '" />');
            form.append('<input type="hidden" name="stripeCard" value="" />');
            $('input[name="stripeCard"]').val(JSON.stringify(this.card));
        }
    },

    /**
     * @return True, if a card is selected/created and hence the main form can be submitted. Otherwise false.
     */
    canSubmitForm: function() {
        return this.card !== null;
    },

    /**
     * Handles Stripe related errors.
     *
     * @param message A Stripe error message.
     */
    handleStripeError: function(message) {
        // Display the error information above the credit card form and scroll to its position
        $('#stripe-payment-error-box').css('display', 'block').children('.error-content').html(message);
        $('body').animate({
            scrollTop: ($('#stripe-payment-form').offset().top - 100)
        }, 500);
    },

    /**
     * Removes the error class from all input fields.
     */
    resetErrorFields: function() {
        $('#stripe-card-holder').removeClass('instyle_error has--error');
        $('#stripe-card-number').removeClass('instyle_error has--error');
        $('#stripe-card-cvc').removeClass('instyle_error has--error');
        // Shopware 4
        $('#stripe-card-expiry-month').parent('.outer-select').removeClass('instyle_error');
        $('#stripe-card-expiry-year').parent('.outer-select').removeClass('instyle_error');
        // Shopware 5
        $('#stripe-card-expiry-month').parent().removeClass('has--error');
        $('#stripe-card-expiry-year').parent().removeClass('has--error');
    },

    /**
     * Updates the oldVal properties of all Stripe input fields.
     */
    updateOldValues: function() {
        // Update the oldVal used for change detection
        $('input[id^="stripe-card"], select[id^="stripe-card"]').each(function() {
            $(this).data('oldVal', $(this).val());
        });
    },

    /**
     * Sets not only the value of the select element, but also the displayed value.
     *
     * @param selectElement The select element that shall be udpated.
     * @param value The 'val' of the option, that shall be selected.
     * @param keepFormFields (optional) A boolean controlling whether the content of other Stripe form fields shall be kept.
     */
    updateSelect: function(selectElement, value, keepFormFields) {
        // Update the selected options
        selectElement.val(value);
        // Fire a change event on the select elements to re-initialise its fancy selectbox UI
        // and pass the optional extra parameter, which, if set to true, prevents other custom
        // selection change listeners from clearing the form fields
        selectElement.trigger('change', [
            keepFormFields
        ]);
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
     * Dynamically fills the expiry date selects with 12 elements each, starting with the
     * current month and year, and pre-selects the passed month and year.
     */
    setupExpiryDateSelection: function(selectedMonth, selectedYear) {
        var year = new Date().getFullYear();
        $('select[id^="stripe-card-expiry"]').empty();
        for (var i = 1; i <= 12; i++) {
            $('#stripe-card-expiry-month').append($('<option value="' + i + '" ' + ((selectedMonth === i) ? 'selected' : '') + '>' + (100 + i + '').substr(1) + '</option>'));
            $('#stripe-card-expiry-year').append($('<option value="' + (year + i - 1) + '" ' + ((selectedYear === (year + i - 1)) ? 'selected' : '') + '>' + (year + i - 1) + '</option>'));
        }
        // Fire a change event on the select elements to re-initialise its fancy selectbox UI
        $('select[id^="stripe-card-expiry"]').trigger('change');
    },

    /**
     *
     *
     */
    observeFormSubmission: function() {
        var me = this;
        this.findForm().on('submit', function(event) {
            // Check that the Stripe payment method is selected (Shopware 4 only)
            if (!me.isStripePaymentSelected()) {
                // Other method than Stripe selected
                return;
            }

            // Make sure that a previously generated token won't be submitted, if the user changed one of the fields afterwards
            var valuesChanged = false;
            if (me.card !== null) {
                valuesChanged = $('#stripe-card-holder').val() !== me.card.name
                    || $('#stripe-card-number').val().replace(' ', '') !== ('XXXXXXXXXXXX' + me.card.last4)
                    || $('#stripe-card-cvc').val() !== '***'
                    || $('#stripe-card-expiry-month').val() != me.card.exp_month
                    || $('#stripe-card-expiry-year').val() != me.card.exp_year;
            }

            // Check if a token/card was generated and hence the form can be submitted
            if (me.canSubmitForm() && !valuesChanged) {
                // Append the value of the checkbox, indicating whether the credit card info shall be saved
                $(this).append('<input type="hidden" name="stripeSaveCard" value="' + ($('#stripe-save-card').is(':checked') ? 'on' : 'off') + '" />');

                // Proceed with the submission
                return;
            } else {
                // Prevent the form from being submitted until a new Stripe token is generated and received
                event.preventDefault();
            }

            // Check if a Stripe request is pending
            if (me.requestPending) {
                return;
            }

            // Remove the error class from all input fields
            me.resetErrorFields();

            // Validate all fields
            var errorMessages = [];
            if ($('#stripe-card-holder').val().length === 0) {
                errorMessages.push(me.snippets.error.invalidName);
                $('#stripe-card-holder').addClass('instyle_error has--error');
            }
            if (!Stripe.validateCardNumber($('#stripe-card-number').val())) {
                errorMessages.push(me.snippets.error.invalidNumber);
                $('#stripe-card-number').addClass('instyle_error has--error');
            }
            if (!Stripe.validateCVC($('#stripe-card-cvc').val())) {
                errorMessages.push(me.snippets.error.invalidCVC);
                $('#stripe-card-cvc').addClass('instyle_error has--error');
            }
            if (!Stripe.validateExpiry($('#stripe-card-expiry-month').val(), $('#stripe-card-expiry-year').val())) {
                errorMessages.push(me.snippets.error.invalidExpiry);
                $('#stripe-card-expiry-month').parent().addClass('instyle_error has--error');
                $('#stripe-card-expiry-year').parent().addClass('instyle_error has--error');
            }
            if (errorMessages.length > 0) {
                // At least one field is invalid
                me.handleStripeError(errorMessages.join('<br />'));
                return;
            }

            // Send the credit card information to Stripe
            me.requestPending = true;
            var form = $(this);
            Stripe.card.createToken({
                name: $('#stripe-card-holder').val(),
                number: $('#stripe-card-number').val(),
                cvc: $('#stripe-card-cvc').val(),
                exp_month: $('#stripe-card-expiry-month').val(),
                exp_year: $('#stripe-card-expiry-year').val()
            }, function(status, response) {
                me.requestPending = false;
                if (response.error) {
                    // Display the error
                    me.handleStripeError(me.snippets.error.title + ': ' + response.error.message);
                } else {
                    // Save the card information
                    me.setCard(response['card']);
                    // Replace the values of some input fields
                    $('#stripe-card-number').val('XXXXXXXXXXXX' + me.card.last4);
                    $('#stripe-card-cvc').val('***');
                    // Remove the card ID from the form (added by 'setCard') and add the new Stripe token instead
                    $('input[name="stripeCardId"]').remove();
                    form.append('<input type="hidden" name="stripeTransactionToken" value="' + response['id'] + '" />');
                    form.submit();
                }
            });
        });
    },

    /**
     * Adds an observer to all Stripe form fields, which sets the card selection to 'new card'.
     */
    observeFieldChanges: function() {
        var me = this;
        $('input[id^="stripe-card"], select[id^="stripe-card"]').each(function() {
            var elem = $(this);
            // Save the current value
            elem.data('oldVal', elem.val());
            // Observe changes
            elem.bind('propertychange keyup input paste', function(event) {
                // Check if value has changed
                if (elem.data('oldVal') != elem.val()) {
                    elem.data('oldVal', elem.val());
                    // Reset the card selection without clearing all form fields
                    me.setCard(null);
                    me.updateSelect($('#stripe-saved-cards'), 'new', true);
                    // Activate the save check box
                    $('#stripe-save-card').prop('checked', true);
                }
            });
        });
    },

    /**
     * Adds a change observer to the card selection field. If an existing card is selected, all form fields
     * are filled with the respective data and the card's Stripe information is added to the form.
     * If the 'new' option is selected, all fields are cleared and the Stripe card info is removed
     * from the form.
     */
    observeCardSelection: function() {
        var me = this;
        $('#stripe-saved-cards').change(function(event, keepFormFields) {
            if (keepFormFields === true) {
                return;
            }
            if ($(this).val() === 'new') {
                // A new, empty card was selected
                me.setCard(null);

                // Clear/reset all input fields
                me.resetErrorFields();
                $('#stripe-card-holder').val('');
                $('#stripe-card-number').val('');
                $('#stripe-card-cvc').val('');
                me.updateSelect($('#stripe-card-expiry-month'), (new Date().getMonth() + 1));
                me.updateSelect($('#stripe-card-expiry-year'), new Date().getFullYear());
                // Update the oldVal used for change detection
                me.updateOldValues();

                // Activate the save check box
                $('#stripe-save-card').prop('checked', true);

                return;
            }

            // Find the selected card
            for (var i = 0; i < me.allCards.length; i++) {
                var selectedCard = me.allCards[i];
                if (selectedCard.id !== $(this).val()) {
                    continue;
                }

                // Save the card
                me.setCard(selectedCard);

                // Update the fields with the card data
                me.resetErrorFields();
                $('#stripe-card-holder').val(selectedCard.name);
                $('#stripe-card-number').val('XXXXXXXXXXXX' + selectedCard.last4);
                $('#stripe-card-cvc').val('***');
                me.updateSelect($('#stripe-card-expiry-month'), selectedCard.exp_month);
                me.updateSelect($('#stripe-card-expiry-year'), selectedCard.exp_year);
                // Update the oldVal used for change detection
                me.updateOldValues();

                // Activate the save check box
                $('#stripe-save-card').prop('checked', true);
                break;
            }
        });
    },

    /**
     * @return The main payment selection form element.
     */
    findForm: function() {
        return (this.isShopware5Template()) ? $('#shippingPaymentForm') : $('#basketButton').closest('form');
    },

    /**
     * @return True, if the Stripe payment method is selected. Otherwise false.
     */
    isStripePaymentSelected: function() {
        return $('#payment_mean' + this.paymentMeansId).is(':checked');
    },

    /**
     * @return True, if a Shopware 5 template is laoded. Otherwise false.
     */
    isShopware5Template: function() {
        return typeof $.overlay !== 'undefined';
    }

};
