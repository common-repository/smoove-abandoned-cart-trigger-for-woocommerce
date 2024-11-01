<?php
/*
Plugin Name: Smoove abandoned cart trigger for WooCommerce
Description: Recover abandoned carts in your Woo store by transmitting the shopper’s info to your smoove account.
Version: 4.0.3
Author: Smoove
Author URI: https://www.smoove.io
Stable tag: 4.0.3
Requires at least: 5.5.3
Requires PHP: 7.1
License: GPLv2 or later
Text Domain: smac
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'SMAC_DEV_MODE', false );
define( 'SMAC_PLUGIN_FILE', __FILE__ );
define( 'SMAC_PLUGIN_REL_PATH', dirname( plugin_basename( __FILE__ ) ) );
define( 'SMAC_PLUGIN_DIR', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SMAC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SMAC_FILE_VER', SMAC_DEV_MODE ? time() : '1.0.1' );

add_action( 'plugins_loaded', 'smac_plugin_init', 1 );

function smac_plugin_init() {
	load_plugin_textdomain( 'smac', false, SMAC_PLUGIN_REL_PATH . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'smac_woocommerce_deactivated' );
		return;
	}

	smac_include_folder_files( SMAC_PLUGIN_PATH . '/inc' );
	smac_include_folder_files( SMAC_PLUGIN_PATH . '/inc/modules' );
	smac_include_folder_files( SMAC_PLUGIN_PATH . '/inc/helpers' );

	if( is_admin() ){
		smac_include_folder_files( SMAC_PLUGIN_PATH . '/inc/admin' );
	}
}

function smac_woocommerce_deactivated() {
	echo '<div class="notice notice-warning">';
		echo '<p>' . sprintf( esc_html__( '%s requires %s to be installed and active.', 'smac' ), '<strong>' . __('Smoove abandoned cart trigger for WooCommerce', 'smac') . '</strong>', '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p>';
	echo '</div>';
}

function smac_include_folder_files( $folder ){
	foreach( glob("{$folder}/*.php") as $filepath ){
		if( $filepath && is_readable( $filepath ) ){
			require_once $filepath;
		}
	}
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
	}
} );