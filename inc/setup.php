<?php
namespace SMAC;

use SMAC\Helpers\DB;
use SMAC\Helpers\WP;
use SMAC\Modules\Smoove_Api;

class Setup{
	public function __construct(){
		register_activation_hook( SMAC_PLUGIN_FILE, [$this, 'plugin_activated'] );
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'], 999 );

		// add_action( 'before_woocommerce_init', [$this, 'declare_cart_checkout_blocks_incompatibility'] );
	}
	
	public function plugin_activated(){
		DB::create_log_table();
	}

	public function enqueue_scripts(){
		wp_enqueue_script( 'jquery' );

		if( is_checkout() ){
			wp_enqueue_style( 'smac-style', SMAC_PLUGIN_DIR . '/assets/css/front.css', [], SMAC_FILE_VER );

			wp_enqueue_script( 'smac-front', SMAC_PLUGIN_DIR . '/assets/js/front.js', ['jquery'], SMAC_FILE_VER, true );
			wp_localize_script( 'smac-front', 'smac_localize', [
				'api_url' => site_url('/wp-json/smac'),
				'api_nonce' => wp_create_nonce('wp_rest')
			] );
		}
	}

	// public function declare_cart_checkout_blocks_incompatibility(){
	// 	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
	// 		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
	// 	}
	// }
}

new Setup();