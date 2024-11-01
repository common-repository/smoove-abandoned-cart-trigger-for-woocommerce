<?php
namespace SMAC;

use SMAC\Helpers\WP;
use SMAC\Helpers\Smoove;

class API{
	public function __construct(){
		add_action( 'rest_api_init', [$this, 'register_rest_routes'] );
	}

	public function register_rest_routes(){
		if ( empty( WC()->cart ) ) {
			WC()->frontend_includes();
			wc_load_cart();
		}

		register_rest_route( 'smac', '/create-temp-cart/', array(
			'methods' => 'POST',
			'callback' => [$this, 'create_temp_cart'],
		));

		register_rest_route( 'smac', '/admin-api-test/', array(
			'methods' => 'POST',
			'callback' => [$this, 'admin_api_test'],
		));
	}

	public function create_temp_cart( $request ){
		$request_params = $request->get_params();
		$checkout_data = $request_params['checkout_data'];

		$response = [
			'success' => false,
			'checkout_data' => $checkout_data,
			'message' => __('Something went wrong.', 'smac')
		];

		$checkout_data['billing_email'] = ( isset( $checkout_data['billing_email'] ) && is_email( $checkout_data['billing_email'] ) ) ? $checkout_data['billing_email'] : false;
		$checkout_data['billing_phone'] = ( isset( $checkout_data['billing_phone'] ) && !empty( $checkout_data['billing_phone'] ) ) ? preg_replace('/\D+/', '', $checkout_data['billing_phone']) : false;

		if( $checkout_data['billing_email'] || $checkout_data['billing_phone'] ){
			$temp_cart_id = WP::upsert_temp_cart( $checkout_data );

			if( is_numeric( $temp_cart_id ) ){
				$response = [
					'success' => true,
					'temp_cart_id' => $temp_cart_id,
					'checkout_data' => $checkout_data
				];
			}
		}

		return $response;
	}

	public function admin_api_test( $request ){
		$request_params = $request->get_params();
		$contact_email = $request_params['contact_email'];

		$response = [
			'success' => false,
			'message' => __('API test failed.', 'smac')
		];

		if( is_email( $contact_email ) ){
			$smoove_response = Smoove::get_contact_status([
				'email' => $contact_email
			]);

			if( is_array( $smoove_response ) && !empty( $smoove_response ) ){
				$message = '<ul>';
					foreach( $smoove_response as $key => $value ){
						$message .= '<li>';
							$message .= '<strong>[' . $key . ']</strong>';

							switch( $key ){
								case 'body':
									$body = $value;
									
									if( is_array( $body ) && !empty( $body ) ){
										$message .= '<ul>';

										foreach( $body as $body_key => $body_value ){
											$message .= '<li>';
												$message .= '<strong>' . $body_key . ':</strong> ' . $body_value;
											$message .= '</li>';
										}

										$message .= '</ul>';
									}					
									break;

								default:
									$message .= ' ' . $value;
									break;
							}
						$message .= '</li>';
					}
				$message .= '</ul>';

				$response = [
					'success' => true,
					'message' => $message,
				];
			}
		}else{
			$response = [
				'success' => false,
				'message' => __('Email is not valid.', 'smac')
			];
		}

		return $response;
	}
}

new API();