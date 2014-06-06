{if $Controller != "account" && $payment_mean.name == 'viison_stripe'}
	{* Additional styling for the stripe payment box *}
	<style type="text/css">
		.first .error .normal {
			text-align: left;
			font-weight: normal;
		}
		#viison-stripe-form .error-box {
			padding: 10px;
			margin-bottom: 15px;
			color: #B94A48;
			background-color: #F2DEDE;
			border: 1px solid #DF7373;
		}
		#viison-stripe-form .form-group {
			width: 600px;
			height: 38px;
		}
		#viison-stripe-form .form-group .form-input {
			float: left;
		}
		#viison-stripe-form .adjust-margin {
			margin-top: 5px;
		}
		#viison-stripe-form label {
			width: 150px !important;
		}
	</style>
	{* Include and set up the Stripe SDK *}
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript">
		// Set the public stripe key
		Stripe.setPublishableKey('{$viisonStripePublicKey}');
		// Add the listeners for the stripe payment preparation
		$(document).ready(function() {
			// A helper method for handling stripe related errors
			function handleStripeError(message) {
				// Display the error information above the credit card form and scroll to its position
				$('#viison-stripe-form .error-box').html('<strong>' + message + '</strong>').css('display', 'block');
				$('body').animate({
					scrollTop: ($('#viison-stripe-form').offset().top - 100)
				}, 500);
			}

			// Disable the default behaviour of the checkout form submission
			var canSubmitForm = false;
			var requestPending = false;
			var form = $('#basketButton').closest('form');
			form.on('submit', function(event) {
				// Check the selected payment method
				var stripePaymentId = 'payment_mean{$payment_mean.id}';
				if (!$('#' + stripePaymentId).is(':checked')) {
					// Other method than stripe selected
					return;
				}
				// Check if the terms and conditions are checked
				if (!$('#sAGB').is(':checked')) {
					// Terms and conditions not yet confirmed, hence stop the payment processing and let the
					// default action handle the unchecked box
					return;
				}
				// Check if a token was generated and hence the form can be submitted
				if (canSubmitForm) {
					// Proceed with the submission
					return;
				} else {
					// Prevent the form from being submitted until a new stripe token is generated and received
					event.preventDefault();
				}
				// Check if a stripe request is pending
				if (requestPending) {
					return;
				}

				// Validate all fields
				if ($('#stripe-card-holder').val().length === 0) {
					handleStripeError('{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_name"}{/s}');
					return;
				}
				if (!Stripe.validateCardNumber($('#stripe-card-number').val())) {
					handleStripeError('{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_number"}{/s}');
					return;
				}
				if (!Stripe.validateCVC($('#stripe-card-cvc').val())) {
					handleStripeError('{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_cvc"}{/s}');
					return;
				}
				if (!Stripe.validateExpiry($('#stripe-card-expiry-month').val(), $('#stripe-card-expiry-year').val())) {
					handleStripeError('{s namespace="frontend/plugins/payment/viison_stripe" name="error/invalid_expiry"}{/s}');
					return;
				}

				// Send the credit card information to stripe
				requestPending = true;
				Stripe.card.createToken({
					name: $('#stripe-card-holder').val(),
					number: $('#stripe-card-number').val(),
					cvc: $('#stripe-card-cvc').val(),
					exp_month: $('#stripe-card-expiry-month').val(),
					exp_year: $('#stripe-card-expiry-year').val()
				}, function(status, response) {
					requestPending = false;
					if (response.error) {
						// Display the error
						handleStripeError('{s namespace="frontend/plugins/payment/viison_stripe" name="error"}{/s}: ' + response.error.message);
					} else {
						canSubmitForm = true;
						// Add the stripe token to the order form and submit it
						form.append('<input type="hidden" name="stripeTransactionToken" value="' + response['id'] + '" />');
						form.submit();
					}
				});
			});
		});
	</script>

	{* The main container for filling in the credit card information *}
	<div id="viison-stripe-form">
		{* An error box *}
		<div class="error-box" style="display: none;">
		</div >
		{* Card holder *}
		<div class="form-group">
			<label class="control-label" for="stripe-card-holder">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/holder"}{/s} *</label>
			<div class="form-input">
				<input id="stripe-card-holder" type="text" size="20" class="text" value="{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}">
			</div>
		</div>
		{* Card number *}
		<div class="form-group">
			<label class="control-label" for="stripe-card-number">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/number"}{/s} *</label>
			<div class="form-input">
				<input id="stripe-card-number" type="text" size="20" class="text">
			</div>
		</div>
		{* CVC *}
		<div class="form-group">
			<label class="control-label" for="stripe-card-cvc">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/cvc"}{/s} *</label>
			<div class="form-input">
				<input id="stripe-card-cvc" type="text" size="5" class="text">
			</div>
		</div>
		{* Expiry date *}
		<div class="form-group">
			<label class="control-label" for="stripe-card-expiry-month">{s namespace="frontend/plugins/payment/viison_stripe" name="form/card/expiry"}{/s} *</label>
			<div class="form-input adjust-margin">
				<select id="stripe-card-expiry-month"></select>
				<script type="text/javascript">
					var select = $('#stripe-card-expiry-month'),
						month = new Date().getMonth() + 1;
					for (var i = 1; i <= 12; i++) {
						select.append($('<option value="' + i + '" ' + ((month === i) ? 'selected' : '') + '>' + i + '</option>'));
					}
				</script>
				<span> / </span>
				<select id="stripe-card-expiry-year"></select>
				<script type="text/javascript">
					var select = $('#stripe-card-expiry-year'),
						year = new Date().getFullYear();
					for (var i = 0; i < 12; i++) {
						select.append($('<option value="' + (i + year) + '" ' + ((i === 0) ? 'selected' : '') + '>' + (i + year) + '</option>'));
					}
				</script>
			</div>
		</div>
		{* Info *}
		<div class="form-group adjust-margin">
			<p>
				{s namespace="frontend/plugins/payment/viison_stripe" name="form/description"}{/s}
			</p>
		</div>
	</div>
{/if}
