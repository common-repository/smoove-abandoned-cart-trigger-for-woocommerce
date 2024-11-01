<?php
namespace SMAC\Helpers;

class WC{
	public static function get_completed_order_statuses(){
		return [
			'completed',
			'canceled',
			'refunded',
			'processing',
			'on-hold',
		];
	}

	public static function is_completed_order( $order ){
		if( is_numeric( $order ) ){
			$order = wc_get_order( $order );
		}

		if( in_array( $order->get_status(), self::get_completed_order_statuses() ) ){
			return true;
		}

		return false;
	}
}