<?php
namespace SMAC\Admin;

class Setup{
	public function __construct(){
		add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueues'] );
	}

	public function admin_enqueues(){
		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'smac-admin', SMAC_PLUGIN_DIR . '/assets/css/admin.css', [], SMAC_FILE_VER );
		wp_enqueue_script( 'smac-admin', SMAC_PLUGIN_DIR . '/assets/js/admin.js', ['jquery'], SMAC_FILE_VER, true );

		wp_localize_script( 'smac-admin', 'smac_localize', [
			'api_url' => site_url('/wp-json/smac'),
			'api_nonce' => wp_create_nonce('wp_rest'),
			'invalid_email' => __('Please insert valid email.', 'smac')
		] );
	}
}

new Setup();