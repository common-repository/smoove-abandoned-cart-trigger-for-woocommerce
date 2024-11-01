jQuery( function($) {
	$(document).ready( function() {
		init_checkout_triggers();
	});

	function init_checkout_triggers(){
		$('body').on( 'change', 'input#billing_email', maybe_create_temp_cart );
		$('body').on( 'change', 'input#billing_phone', maybe_create_temp_cart );
		$('body').on( 'change', 'input#billing_first_name', maybe_create_temp_cart );
		$('body').on( 'change', 'input#billing_last_name', maybe_create_temp_cart );
		$('body').on( 'change', 'input#smoove-consent', maybe_create_temp_cart );
		$('body').on( 'change', 'input#terms', maybe_create_temp_cart );
		$('body').on( 'change', 'input#payment-method-changed', maybe_create_temp_cart );
		
		$('body').on( 'change', 'input[name="payment_method"]', function(){
			$('input#payment-method-changed').val('true').trigger('change');
		} );
	}

	function maybe_create_temp_cart(){
		let checkout_data = get_checkout_data();

		$.ajax({
			url: smac_localize.api_url + '/create-temp-cart/',
			method: 'POST',
			dataType: 'json',
			data: {
				checkout_data : checkout_data,
			},
			beforeSend: function(jqXhr) {
				jqXhr.setRequestHeader( 'X-WP-Nonce', smac_localize.api_nonce )
			},
			success: function( response ){
				// console.log( response );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				console.log( jqXHR, textStatus, errorThrown );
			}
		});
	}

	function get_checkout_data(){
		let terms_checkbox = $('input#terms');
		let consent_checkbox = $('input#smoove-consent');

		let data = {
			billing_first_name: $('input#billing_first_name').val(),
			billing_last_name: $('input#billing_last_name').val(),
			billing_phone: $('input#billing_phone').val(),
			billing_email: $('input#billing_email').val(),
			billing_country: $('input#billing_country').val(),
			billing_address_1: $('input#billing_address_1').val() + ', ' + $('input#billing_city').val(),
			payment_method_changed: $('input#payment-method-changed').val(),
			terms: terms_checkbox.length ? terms_checkbox.is(":checked") : false,
			smoove_consent: consent_checkbox.length ? consent_checkbox.is(":checked") : false,
		};

		return data;
	}
});
