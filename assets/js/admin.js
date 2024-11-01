jQuery( function($) {
	$(document).ready( function() {
		init_settings_conditions();
		init_api_test_form();
	});

	function init_settings_conditions(){
		let settings_form = $('#smac-settings-form');

		if( settings_form.length ){
			settings_form.find('[data-condition]').each( function(){
				let field = $(this);
				let condition = field.data('condition').split('|');
				let condition_field_id = condition[0];
				let condition_field_val = condition[1];

				if( typeof condition_field_id != 'undefined' && typeof condition_field_val != 'undefined' ){
					let condition_field = settings_form.find('#' + condition_field_id);

					if( condition_field.length ){
						condition_field.on( 'change', function(){
							
							if( condition_field.val() == condition_field_val ){
								field.closest('tr').show();
							}else{
								field.closest('tr').hide();
							}
						});

						condition_field.trigger('change');
					}
				}
			});
		}
	}

	function init_api_test_form(){
		$('body').on( 'submit', '#smac-api-test-form', function(e){
			e.preventDefault();
			
			let form = $(this);
			let contact_email = form.find('input#smac_api_test_email');

			reset_form_message( form );

			if( !contact_email.length || contact_email.val() == '' ){
				add_form_message( form, smac_localize.invalid_email, false );
				return false;
			}
			
			form.addClass('smac-loading');

			$.ajax({
				url: smac_localize.api_url + '/admin-api-test/',
				method: 'POST',
				dataType: 'json',
				data: {
					contact_email : contact_email.val(),
				},
				beforeSend: function(jqXhr) {
					jqXhr.setRequestHeader( 'X-WP-Nonce', smac_localize.api_nonce )
				},
				success: function( response ){
					// console.log( response );

					if( typeof response.success != 'undefined' && typeof response.message != 'undefined' ){
						add_form_message( form, response.message, response.success );
					}else{
						add_form_message( form, 'Something went wrong.', false );
					}

					form.removeClass('smac-loading');
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR, textStatus, errorThrown );
					form.removeClass('smac-loading');
				}
			});
		} );
	}

	function add_form_message( form, message, success = true ){
		let message_wrap = form.find('.smac-form-message');

		if( success ){
			message_wrap
				.removeClass('info')
				.removeClass('error')
				.addClass('success')
		}else{
			message_wrap
				.removeClass('info')
				.removeClass('success')
				.addClass('error')
		}

		message_wrap
			.find('p')
			.html( message );
	}

	function reset_form_message( form ){
		let message_wrap = form.find('.smac-form-message');
		let default_message = message_wrap.data('default-message');
		
		if( default_message ){
			message_wrap
				.removeClass('error')
				.removeClass('success')
				.addClass('info')
				.find('p')
				.html( default_message );
		}else{
			message_wrap.remove();
		}
	}
});
