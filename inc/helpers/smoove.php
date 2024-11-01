<?php
namespace SMAC\Helpers;

use SMAC\Modules\Smoove_Api;
use SMAC\Helpers\DB;

class Smoove{
	public static function maybe_send_cart_to_smoove( $temp_cart_id ){
		$response = false;
		
		$smoove_status = 'Not exist';
		$maybe_send_inactive = true;
		$do_send_to_smoove = false;
		$sent_to_smoove = false;

		$billing_email = get_post_meta( $temp_cart_id, 'billing_email', true );
		$billing_phone = get_post_meta( $temp_cart_id, 'billing_phone', true );

		if( !$billing_email && !$billing_phone ){
			return $response;
		}

		if( $billing_email ){
			$contact = self::get_contact_status([
				'email' => $billing_email
			] );
		}

		if( $contact['code'] != 200 && $billing_phone ){
			$contact = self::get_contact_status([
				'phone' => $billing_phone
			]);
		}

		$smac_contact_deleted_action = get_option('smac_contact_deleted_action');
		$smac_contact_unsubscribed_action = get_option('smac_contact_unsubscribed_action');

		$restore_if_deleted = ( $smac_contact_deleted_action == 'restore' ) ? true : false;
		$restore_if_unsubscribed = ( $smac_contact_unsubscribed_action == 'restore' ) ? true : false;

		if( $contact['code'] == 200 ){
			$contact_status = isset( $contact['body']['status'] ) ? $contact['body']['status'] : false;

			if( $contact_status ){
				switch( $contact_status ){
					case 'Active':
						$do_send_to_smoove = true;
						break;

					case 'Deleted':
						if( !$restore_if_deleted ){
							$maybe_send_inactive = false;
						}
						break;

					case 'Unsubscribed':
						if( !$restore_if_unsubscribed ){
							$maybe_send_inactive = false;
						}
						break;
				}

				$smoove_status = $contact_status;
			}
		}
		
		if( $smoove_status != 'Active' && $maybe_send_inactive === true ){
			$do_send_to_smoove = false;
			
			$smac_mailing_receipt_consent = get_option('smac_mailing_receipt_consent');
			$smac_mailing_receipt_consent_label = get_option('smac_mailing_receipt_consent_label');

			if( $smac_mailing_receipt_consent == 'yes' && !empty( $smac_mailing_receipt_consent_label ) ){
				if( get_post_meta( $temp_cart_id, 'smoove_consent', true ) == 'true' ){
					$do_send_to_smoove = true;
				}
			}else if( get_post_meta( $temp_cart_id, 'terms', true ) == 'true' ){
				$do_send_to_smoove = true;
			}
		}

		if( $do_send_to_smoove === true ){
			$smoove_response = self::send_cart_to_smoove( $temp_cart_id, $restore_if_deleted, $restore_if_unsubscribed );

			DB::add_log_entry( $billing_email, sprintf( __('Cart sent to Smoove (%s)', 'smac'), $smoove_status ), $smoove_response['code'] );

			if( $smoove_response['code'] == 200 ){
				$sent_to_smoove = true;
			}
		}

		// file_put_contents( SMAC_PLUGIN_PATH . '/smoove-responses.txt', PHP_EOL . '--------' . PHP_EOL . 'contact:' . PHP_EOL . print_r( $contact, true ), FILE_APPEND );

		$response = [
			'api_key' => isset( $smoove_response['api_key'] ) ? $smoove_response['api_key'] : '',
			'smoove_status' => $smoove_status,
			'sent_to_smoove' => $sent_to_smoove ? 'yes' : 'no',
		];

		return $response;
	}

	public static function send_cart_to_smoove( $temp_cart_id, $restore_if_deleted = false, $restore_if_unsubscribed = false ){
		$Smoove_Api = new Smoove_Api();

		$restore_if_deleted = $restore_if_deleted ? 'true' : 'false';
		$restore_if_unsubscribed = $restore_if_unsubscribed ? 'true' : 'false';

		$endpoint = 'ecommerce/AbandonedCartTrigger?updateIfExists=true&restoreIfDeleted=' . $restore_if_deleted . '&restoreIfUnsubscribed=' . $restore_if_unsubscribed;

		$data = self::generate_cart_data_for_smoove( $temp_cart_id );

		// file_put_contents( SMAC_PLUGIN_PATH . '/smoove-responses.txt', PHP_EOL . '--------' . PHP_EOL . 'endpoint:' . PHP_EOL . $endpoint, FILE_APPEND );

		return $Smoove_Api->post( $endpoint, $data );
	}

	public static function get_contact_status( $args = [] ){
		$Smoove_Api = new Smoove_Api();

		if( $email = $args['email'] ){
			return $Smoove_Api->get( 'Contacts/status/' . $email . '?by=email' );
		}else if( $phone = $args['phone'] ){
			return $Smoove_Api->get( 'Contacts/status/' . $phone . '?by=CellPhone' );
		}
	}

	public static function generate_cart_data_for_smoove( $temp_cart_id ){
		global $woocommerce;	

		$data = [];

	    $order_id = (int) get_post_meta( $temp_cart_id, 'order_id', true );

	    $terms = get_post_meta( $temp_cart_id, 'terms', true );
		
		$products = get_post_meta( $temp_cart_id, 'products', true  );
		$first_name = get_post_meta( $temp_cart_id, 'billing_first_name', true );
		$last_name = get_post_meta( $temp_cart_id, 'billing_last_name', true );
		$email = get_post_meta( $temp_cart_id, 'billing_email', true );		

		$billing_phone = get_post_meta( $temp_cart_id, 'billing_phone', true );		
		$billing_address_1 = get_post_meta( $temp_cart_id, 'billing_address_1', true );
		$billing_country = get_post_meta( $temp_cart_id, 'billing_country', true );		

		$cart_total = get_post_meta( $temp_cart_id, 'cart_total', true );
		$timestamp = (int) get_post_meta( $temp_cart_id, 'timestamp', true );

		$data['email'] = $email;
		$data['first_name'] = $first_name;
		$data['last_name'] = $last_name;

		if( strlen( $billing_phone ) >= 9 ){
			$data['cellPhone'] = $billing_phone;
		}
		
		$data['country'] = $billing_country;
		$data['address'] = $billing_address_1;
	 
		$cart_data = [];
		$cart_data['abandoned_timestamp_gmt'] = date( 'Y-m-d\TH:i:s', $timestamp );
		$cart_data['cart_total'] = $cart_total;
		$cart_data['externalId'] = home_url( '?smoove_cid=' . $temp_cart_id );
	    						
		$abandoned_cart_url = wc_get_cart_url();					
		$cart_data['abandoned_cart_url'] = ( empty( $abandoned_cart_url ) && $order_id ) ? get_permalink( $order_id ) : $abandoned_cart_url;				
		$cart_data['terms'] = $terms;

		$products_final = [];

		foreach( $products as $s_prod ){
			$single_product = [];

			$inner_prod = wc_get_product( $s_prod['id'] );
			$main_product_id = $s_prod['parent_id'] ? $s_prod['parent_id'] : $s_prod['id'];

			$date_created_obj = $inner_prod->get_date_created();
			$date_modified_obj = $inner_prod->get_date_modified();

			$date_created = is_a( $date_created_obj, 'WC_DateTime' ) ? date( 'Y-m-d\TH:i:s', $inner_prod->get_date_created()->getTimestamp() ) : '';
			$date_modified = is_a( $date_created_obj, 'WC_DateTime' ) ? date( 'Y-m-d\TH:i:s', $inner_prod->get_date_modified()->getTimestamp() ) : '';

			$single_product['externalId'] = $s_prod['id'];
			$single_product['name'] = $inner_prod->get_name();
			$single_product['description'] = $inner_prod->get_description();
			$single_product['date_created_gmt'] = $date_created;
			$single_product['date_modified_gmt'] = $date_modified;
			$single_product['status'] = $inner_prod->get_status();
			$single_product['price'] = $inner_prod->get_price();
			$single_product['regular_price'] = $inner_prod->get_regular_price();
			$single_product['sale_price'] = $inner_prod->get_sale_price();	
			$single_product['currency'] = get_option('woocommerce_currency');
			$single_product['amount'] = $s_prod['qty'];
			$single_product['storage_quantity'] = $s_prod['qty'];

			$all_cats = [];

			$product_cats = wp_get_post_terms( $main_product_id, 'product_cat');

			foreach( $product_cats as $s_cat ){
				$all_cats[] = ['externalId' => $s_cat->term_id, 'name' => $s_cat->name];
			}

			$single_product['categories'] = $all_cats;

			$all_tags = [];
			$product_tags = wp_get_post_terms( $main_product_id, 'product_tag');

			foreach( $product_tags as $s_tag ){
				$all_tags[] = ['externalId' => $s_tag->term_id, 'name' => $s_tag->name];
			}

			$single_product['tags'] = $all_tags;

			$all_images = [];
			$featured_id = get_post_meta( $s_prod['id'], '_thumbnail_id', true );
			
			if( !$featured_id ){
				$featured_id = get_post_meta( $main_product_id, '_thumbnail_id', true );
			}

			if( $featured_id ){
				$url = wp_get_attachment_image_url( $featured_id, 'full');

				$all_images[] = [ 
					'isPrimary' => true,
					'src' => $url,
					'name' => get_post( $featured_id )->post_title,
					'altText' => get_post_meta( $featured_id, '_wp_attachment_image_alt', true),
				 ];
			}

			$_product_image_gallery = get_post_meta( $main_product_id, '_product_image_gallery', true );

			if( $_product_image_gallery ){
				$image_ids = explode( ',', $_product_image_gallery );

				foreach( $image_ids as $s_image ){
					if( $s_image == $featured_id ){
						continue;
					}

					$url = wp_get_attachment_image_url( $s_image, 'full');

					$all_images[] = [ 
						'isPrimary' => false,
						'src' => $url,
						'name' => get_post( $s_image )->post_title,
						'altText' => get_post_meta( $s_image, '_wp_attachment_image_alt', true),
					 ];
				}
			}

			$single_product['images'] = $all_images;
			$products_final[] = $single_product;
		}

		$cart_data['products'] = $products_final;
		$data['cart'] = $cart_data;		

		return $data;
	}
}