<?php
namespace SMAC;

use SMAC\Helpers\WC;
use SMAC\Helpers\WP;

class Checkout{
	public function __construct(){
		add_action( 'woocommerce_review_order_before_submit', [$this, 'mailing_receipt_consent_checkbox'], 10 );
		add_action( 'woocommerce_order_status_changed', [$this, 'order_status_changed'], 10, 4 );
	}
	
	public function mailing_receipt_consent_checkbox(){
		$smac_mailing_receipt_consent = get_option('smac_mailing_receipt_consent');
		$smac_mailing_receipt_consent_label = get_option('smac_mailing_receipt_consent_label');

		if( $smac_mailing_receipt_consent == 'yes' && !empty( $smac_mailing_receipt_consent_label ) ){
			woocommerce_form_field( 'smoove-consent', [
				'type'          => 'checkbox',
				'class'         => ['form-row smac-mailing-consent'],
				'label_class'   => ['woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'],
				'input_class'   => ['woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'],
				'required'      => false,
				'label'         => $smac_mailing_receipt_consent_label,
			]);  
		}

		echo '<input type="hidden" name="payment-method-changed" id="payment-method-changed" value="false" />';
	}
	
	public function order_status_changed( $order_id, $old_status, $new_status, $order ){
		$billing_email = $order->get_billing_email();

		if( !$billing_email ){
			return;
		}

		$is_completed_order = WC::is_completed_order( $order );
		$temp_carts = WP::get_carts( 'temp', [
			'billing_email' => $billing_email
		] );

		if( is_array( $temp_carts ) && !empty( $temp_carts ) ){
			foreach( $temp_carts as $temp_cart ){
				if( $is_completed_order ){
					wp_delete_post( $temp_cart->ID, true );
				}else{
					update_post_meta( $temp_cart->ID, 'order_id', $order_id );
					update_post_meta( $temp_cart->ID, 'order_status', $new_status );
				}
			}
		}

		$abandoned_carts = WP::get_carts( 'abandoned', [
			'order_id' => $order_id,
		] );

		if( is_array( $abandoned_carts ) && !empty( $abandoned_carts ) ){
			foreach( $abandoned_carts as $abandoned_cart ){
				if( $is_completed_order ){
					wp_delete_post( $abandoned_cart->ID, true );
				}else{
					update_post_meta( $abandoned_cart->ID, 'order_id', $order_id );
					update_post_meta( $abandoned_cart->ID, 'order_status', $new_status );
				}
			}
		}
	}
}

new Checkout();