<?php
namespace SMAC\Helpers;

class WP{
	public static function get_carts( $type = 'temp', $meta = [] ){
		$temp_carts_args = [
			'post_type' => 'smac-' . $type . '-cart',
			'post_status' => 'any',
			'nopaging' => true,
		];

		if( is_array( $meta ) && !empty( $meta ) ){
			foreach( $meta as $key => $value ){
				if( $key == 'relation' ){
					$temp_carts_args['meta_query']['relation'] = $value;
				}else if( $key == 'timestamp_before' ){
					$temp_carts_args['meta_query'][] = [
						'key' => 'timestamp',
						'value' => $value,
						'compare' => '<',
						'type' => 'NUMERIC'
					];
				}else if( $key == 'timestamp_after' ){
					$temp_carts_args['meta_query'][] = [
						'key' => 'timestamp',
						'value' => $value,
						'compare' => '>',
						'type' => 'NUMERIC'
					];
				}else if( $key == 'order_id' ){
					$temp_carts_args['meta_query'][] = [
						'key' => $key,
						'value' => $value,
						'compare' => '=',
						'type' => 'NUMERIC'
					];
				}else{
					$temp_carts_args['meta_query'][] = [
						'key' => $key,
						'value' => $value,
						'compare' => 'LIKE',
					];
				}
			}
		}

		$temp_carts = get_posts( $temp_carts_args );

		if( is_array( $temp_carts ) && !empty( $temp_carts ) ){
			return $temp_carts;
		}

		return false;
	}

	public static function upsert_temp_cart( $cart_data ){
		if( !is_array( $cart_data )
			|| empty( $cart_data ) ){
				return false;
		}

		if( ( !isset( $cart_data['billing_email'] ) || !is_email( $cart_data['billing_email'] ) )
			&& ( !isset( $cart_data['billing_phone'] ) || empty( $cart_data['billing_phone'] ) ) ){
				return false;
		}
		
		$cart_items = WC()->cart->get_cart();
		$cart_total = WC()->cart->get_cart_contents_total();

		if( !is_array( $cart_items ) || empty( $cart_items ) ){
			return false;
		}

		$products = [];

		foreach( $cart_items as $item => $values ) {
			$product = wc_get_product( $values['data']->get_id() );
			$product_qty = $values['quantity'];

			$product_id = $product->get_id();
			$product_name = $product->get_name();

			$products[] = [
				'id' => $product_id,
				'parent_id' => $product->get_parent_id(),
				'name' => $product_name,
				'qty' => $product_qty
			];
		}

		$billing_email = $cart_data['billing_email'];
		$billing_phone = $cart_data['billing_phone'];

		$cart_title = $billing_email ? $billing_email : $billing_phone;

		$existing_temp_carts = self::get_carts( 'temp', [
			'billing_email' => $billing_email,
			'billing_phone' => $billing_phone,
			'relation' => 'OR'
		]);

		if( is_array( $existing_temp_carts ) && !empty( $existing_temp_carts ) ){
			if( count( $existing_temp_carts ) > 1 ){
				foreach( $existing_temp_carts as $temp_cart ){
					wp_delete_post( $temp_cart->ID, true );
				}
			}else{
				$existing_temp_cart_id = isset( array_values( $existing_temp_carts )[0]->ID ) ? array_values( $existing_temp_carts )[0]->ID : false;
			}
		}

		if( $existing_temp_cart_id ){
			$cart_id = wp_update_post([
				'ID' => $existing_temp_cart_id,
				'post_type' => 'smac-temp-cart',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_title' => $cart_title,
			]);
		}else{
			$cart_id = wp_insert_post([
				'post_type' => 'smac-temp-cart',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_title' => $cart_title,
			]);
		}
		
		if( is_numeric( $cart_id ) ){
			foreach( $cart_data as $key => $value ){
				update_post_meta( $cart_id, $key, $value );
			}

			update_post_meta( $cart_id, 'products', $products );
			update_post_meta( $cart_id, 'cart_total', $cart_total );
			update_post_meta( $cart_id, 'timestamp', time() );
		}

		return $cart_id;
	}

	public static function insert_abandoned_cart( $temp_cart_id, $meta = [] ){
		$billing_email = get_post_meta( $temp_cart_id, 'billing_email', true );
		$billing_phone = get_post_meta( $temp_cart_id, 'billing_phone', true );

		$cart_title = $billing_email ? $billing_email : $billing_phone;

		$cart_id = wp_insert_post([
			'post_type' => 'smac-abandoned-cart',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_title' => $cart_title,
		]);

		if( is_numeric( $cart_id ) ){
			$temp_cart_meta = get_post_meta( $temp_cart_id );

			if( is_array( $temp_cart_meta ) && !empty( $temp_cart_meta ) ){
				foreach( $temp_cart_meta as $key => $value ){
					if( $key == 'timestamp' ){ continue; }
					if( $key == '_edit_lock' ){ continue; }
					
					$value = is_array( $value ) ? array_values( $value )[0] : $value;
					$value = maybe_unserialize( $value );

					update_post_meta( $cart_id, $key, $value );
				}
			}

			if( is_array( $meta ) && !empty( $meta ) ){
				foreach( $meta as $key => $value ){
					update_post_meta( $cart_id, $key, $value );
				}
			}

			update_post_meta( $cart_id, 'timestamp', time() );
		}
	}
}