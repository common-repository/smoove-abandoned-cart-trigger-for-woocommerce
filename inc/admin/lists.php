<?php
namespace SMAC\Admin;

class Lists{
	public $date_format;
	public $time_format;

	public function __construct(){
		$this->date_format = get_option('date_format');
		$this->time_format = get_option('time_format');

		add_filter( 'manage_smac-abandoned-cart_posts_columns', [$this, 'abandoned_cart_list_columns'], 100 );
		add_action( 'manage_smac-abandoned-cart_posts_custom_column', [$this, 'abandoned_cart_list_column_data'], 100, 2 );
		add_action( 'manage_edit-smac-abandoned-cart_sortable_columns', [$this, 'abandoned_cart_list_sortable_columns'], 100, 2 );

		add_action( 'pre_get_posts', [ $this,'abandoned_cart_list_ordering'] );

		add_filter( 'post_row_actions', [$this, 'abandoned_cart_row_actions'], 100, 2 );
		add_filter( 'bulk_actions-edit-smac-abandoned-cart', [$this, 'abandoned_cart_bulk_actions'], 100, 2 );
	}

	public function abandoned_cart_list_columns( $columns ){
		$columns = [
			'cb' => '<input type="checkbox">',
			'email' => __( 'Email', 'smac' ),
			'phone' => __( 'Phone', 'smac' ),
			'contact_name' => __( 'Contact Name', 'smac' ),
			'ab_time' => __( 'Abandonment Date & Time', 'smac' ),
			'sent_to_smoove' => __( 'Sent to Smoove', 'smac' ),
			'smoove_status' => __( 'Smoove Status', 'smac' ),
			'order_id' => __( 'Order ID', 'smac' ),
			'products' => __( 'Products', 'smac' ),
			'api_key' => __( 'API Key', 'smac' )
		];

		return $columns;
	}
	
	function abandoned_cart_list_sortable_columns( $columns  ){
		$columns['contact_name'] = 'contact_name';
		$columns['ab_time'] = 'ab_time';
		$columns['sent_to_smoove'] = 'sent_to_smoove';
		$columns['smoove_status'] = 'smoove_status';
		$columns['order_id'] = 'order_id';

    	return $columns;
	}

	function abandoned_cart_list_ordering( $query ){
		if( $query->get( 'post_type') != 'smac-abandoned-cart' ){
			return;
		}

		$orderby = $query->get( 'orderby');

		if( $orderby == 'contact_name' ) {
			$query->set( 'meta_key','billing_first_name' );
			$query->set( 'orderby','meta_value' );
		}

		if( $orderby == 'ab_time' ) {
			$query->set( 'meta_key', 'timestamp' );
			$query->set( 'orderby','meta_value' );
		}

		if( $orderby == 'sent_to_smoove' ) {
			$query->set( 'meta_key', 'sent_to_smoove');
			$query->set( 'orderby', 'meta_value');
		}

		if( $orderby == 'smoove_status' ) {
			$query->set( 'meta_key', 'smoove_status' );
			$query->set( 'orderby', 'meta_value' );
		}

		if( $orderby == 'order_id' ) {
			$query->set( 'meta_key', 'order_id' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	public function abandoned_cart_list_column_data( $column_name, $id ){
		if( $column_name === 'email' ){
			echo get_post_meta( $id, 'billing_email', true );
		}

		if( $column_name === 'phone' ){
			echo get_post_meta( $id, 'billing_phone', true );
		}

		if( $column_name === 'contact_name' ){
			$name_parts = [];

			if( $first_name = get_post_meta( $id, 'billing_first_name', true ) ){
				$name_parts[] = $first_name;
			}

			if( $last_name = get_post_meta( $id, 'billing_last_name', true ) ){
				$name_parts[] = $last_name;
			}

			echo implode( ' ', $name_parts );
		}

		if( $column_name === 'ab_time' ){
			$timestamp = (int) get_post_meta( $id, 'timestamp', true );

			if( $timestamp ){
				echo wp_date( $this->date_format . ' ' . $this->time_format, $timestamp );
			}
		}

		if( $column_name === 'sent_to_smoove' ){
			$sent_to_smoove = get_post_meta( $id, 'sent_to_smoove', true );

			echo ( $sent_to_smoove == 'yes' ) ? __( 'Yes', 'smac' ) : __( 'No', 'smac' );
		}

		if( $column_name === 'smoove_status' ){
			echo get_post_meta( $id, 'smoove_status', true );
		}

		if( $column_name === 'order_id' ){
			$order_id = get_post_meta( $id, 'order_id', true );

			if( $order_id ){
				echo '<a href="' . get_edit_post_link( $order_id ) . '" target="_blank">#' . $order_id . '</a>';
			}
		}

		if( $column_name === 'products' ){
			$cart_products = get_post_meta( $id, 'products', true );

			if( is_array( $cart_products ) && !empty( $cart_products ) ){
				$products = [];

				foreach( $cart_products as $cart_product ){
					$main_product_id = $cart_product['parent_id'] ? $cart_product['parent_id'] : $cart_product['id'];
					$products[] = '<a href="' . get_edit_post_link( $main_product_id ) . '" target="_blank">' . $cart_product['name'] . '</a>' . ' x ' . $cart_product['qty'] . '';
				}

				echo implode( ' | ', $products );
			}
		}

		if( $column_name === 'api_key' ){
			if( $api_key = esc_html( get_post_meta( $id, 'api_key', true ) ) ){
				$api_key = explode('-', $api_key);

				echo !empty( $api_key ) ? 'XXXXX-' . $api_key[ count( $api_key ) - 1 ] : '';
			}
		}
	}
	
	public function abandoned_cart_row_actions( $actions, $post ){
		if( $post->post_type == 'smac-abandoned-cart' ){
			$actions = array_filter( $actions, function( $key ) {
				return $key == 'trash';
			}, ARRAY_FILTER_USE_KEY );
		}

		return $actions;
	}
	
	public function abandoned_cart_bulk_actions( $actions ){
		unset( $actions['edit'] );

		return $actions;
	}
}

new Lists();