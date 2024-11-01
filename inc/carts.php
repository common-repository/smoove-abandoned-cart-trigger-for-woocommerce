<?php
namespace SMAC;

use SMAC\Helpers\WP;
use SMAC\Helpers\Smoove;

class Carts{
	public function __construct(){
		add_action( 'smac_cron_trace_temp_carts', [ $this, 'trace_temp_carts' ] );
		add_action( 'smac_cron_trace_temp_carts', [ $this, 'cleanup_abandoned_carts' ] );
	}
	
	public function cleanup_abandoned_carts(){
		// $cart_lifetime = (int) get_option('smac_cart_lifetime'); //Days
		$cart_lifetime = 30; //Days

		if( !$cart_lifetime ){ return; }

		$ab_carts = WP::get_carts( 'abandoned', [
			'timestamp_before' => ( time() - ( $cart_lifetime * 60 * 60 * 24 ) )
		]);

		if( is_array( $ab_carts ) && !empty( $ab_carts ) ){
			foreach( $ab_carts as $ab_cart ){
				wp_delete_post( $ab_cart->ID, true );
			}
		}
	}

	public function trace_temp_carts(){
		$cart_interval = (int) get_option('smac_cart_interval'); //Minutes

		if( !$cart_interval ){ return; }

		$temp_carts = WP::get_carts( 'temp', [
			'timestamp_before' => ( time() - ( $cart_interval * 60 ) )
		]);

		// file_put_contents( SMAC_PLUGIN_PATH . '/temp-carts.txt', print_r( $temp_carts, true ), FILE_APPEND );

		if( is_array( $temp_carts ) && !empty( $temp_carts ) ){
			foreach( $temp_carts as $temp_cart ){
				$response = Smoove::maybe_send_cart_to_smoove( $temp_cart->ID );

				if( $response ){
					WP::insert_abandoned_cart( $temp_cart->ID, $response );
				}

				wp_delete_post( $temp_cart->ID, true );
			}
		}
	}
}

new Carts();