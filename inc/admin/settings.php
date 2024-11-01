<?php
namespace SMAC\Admin;

class Settings{
	public $settings_page_slug = 'smac-smoove-settings';
	public $api_test_page_slug = 'smac-smoove-api-test';

	public $settings_section_id = 'smac-smoove-settings-section';

	public function __construct(){
		add_action( 'admin_menu', [$this, 'add_smoove_settings_menu'] );
		add_action( 'admin_init', [$this, 'register_smoove_settings_fields'] );
	}

	public function add_smoove_settings_menu(){
		add_submenu_page(
			'edit.php?post_type=smac-abandoned-cart',
			__('Settings', 'smac'),
			__('Smoove Settings', 'smac'),
			'manage_options',
			$this->settings_page_slug,
			[$this, 'smoove_settings_page']
		);

		add_submenu_page(
			'edit.php?post_type=smac-abandoned-cart',
			__('Plugin Test', 'smac'),
			__('Plugin Test', 'smac'),
			'manage_options',
			$this->api_test_page_slug,
			[$this, 'smoove_api_test_page']
		);
	}

	public function register_smoove_settings_fields(){
		register_setting( $this->settings_page_slug, 'smac_api_key' );
		register_setting( $this->settings_page_slug, 'smac_cart_interval' );
		register_setting( $this->settings_page_slug, 'smac_cart_lifetime' );
		register_setting( $this->settings_page_slug, 'smac_contact_unsubscribed_action' );
		register_setting( $this->settings_page_slug, 'smac_contact_deleted_action' );
		register_setting( $this->settings_page_slug, 'smac_mailing_receipt_consent' );
		register_setting( $this->settings_page_slug, 'smac_mailing_receipt_consent_label' );

		add_settings_section(
			$this->settings_section_id, // section ID
			'', // title (optional)
			'', // callback function to display the section (optional)
			$this->settings_page_slug
		);

		add_settings_field(
			'smac_api_key',
			__('API Key', 'smac'),
			[$this, 'input_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_api_key',
				'field_args' => [
					'name' => 'smac_api_key',
				]
			]
		);

		add_settings_field(
			'smac_cart_interval',
			__('Abandoned Cart Interval (mins)', 'smac'),
			[$this, 'input_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_cart_interval',
				'field_args' => [
					'type' => 'number',
					'name' => 'smac_cart_interval',
					'min' => 1,
					'value' => 15,
				],
			]
		);

		// add_settings_field(
		// 	'smac_cart_lifetime',
		// 	__('Delete abandoned carts after (days)', 'smac'),
		// 	[$this, 'input_field'],
		// 	$this->settings_page_slug,
		// 	$this->settings_section_id,
		// 	[
		// 		'label_for' => 'smac_cart_lifetime',
		// 		'field_args' => [
		// 			'type' => 'number',
		// 			'name' => 'smac_cart_lifetime',
		// 			'placeholder' => __('Forever', 'smac'),
		// 			'min' => 1,
		// 			'value' => 30,
		// 		],
		// 	]
		// );

		add_settings_field(
			'smac_contact_unsubscribed_action',
			__('Unsubscribed contact action', 'smac'),
			[$this, 'select_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_contact_unsubscribed_action',
				'field_args' => [
					'name' => 'smac_contact_unsubscribed_action',
					'value' => 'restore',
					'options' => [
						'restore' => __('Restore', 'smac'),
						'do_not_process' => __('Don\'t process', 'smac')
					]
				]
			]
		);

		add_settings_field(
			'smac_contact_deleted_action',
			__('Deleted contact action', 'smac'),
			[$this, 'select_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_contact_deleted_action',
				'field_args' => [
					'name' => 'smac_contact_deleted_action',
					'value' => 'restore',
					'options' => [
						'restore' => __('Restore', 'smac'),
						'do_not_process' => __('Don\'t process', 'smac')
					]
				]
			]
		);

		add_settings_field(
			'smac_mailing_receipt_consent',
			__('Add Email/SMS communication consent field on checkout', 'smac'),
			[$this, 'select_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_mailing_receipt_consent',
				'field_args' => [
					'name' => 'smac_mailing_receipt_consent',
					'value' => 'yes',
					'options' => [
						'yes' => __('Yes', 'smac'),
						'no' => __('No', 'smac')
					]
				]
			]
		);

		add_settings_field(
			'smac_mailing_receipt_consent_message',
			false,
			[$this, 'message_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'field_args' => [
					'id' => 'smac_mailing_receipt_consent_message',
					'value' => __('You should add Email/SMS communication consent to the terms of use', 'smac'),
					'data-condition' => 'smac_mailing_receipt_consent|no'
				]
			]
		);

		add_settings_field(
			'smac_mailing_receipt_consent_label',
			__('Email/SMS communication consent field label', 'smac'),
			[$this, 'input_field'],
			$this->settings_page_slug,
			$this->settings_section_id,
			[
				'label_for' => 'smac_mailing_receipt_consent_label',
				'field_args' => [
					'name' => 'smac_mailing_receipt_consent_label',
					'value' => __('I consent to receiving marketing messages', 'smac'),
					'data-condition' => 'smac_mailing_receipt_consent|yes'
				]
			]
		);
	}

	public function message_field( $args ){
		$field_args = wp_parse_args( $args['field_args'], [
			'class' => 'smac-form-message info',
			'id' => isset( $args['id'] ) ? $args['id'] : false,
		]);

		$field_attrs = [];
	
		foreach( $field_args as $key => $value ){
			$field_attrs[] = $key . '="' . $value . '"';
		}

		echo '<div ' . implode( ' ', $field_attrs ) . '><p>' . $field_args['value'] . '</p></div>';
	}

	public function input_field( $args ){
		$field_args = wp_parse_args( $args['field_args'], [
			'type' => 'text',
			'class' => 'smac-form-control',
			'id' => isset( $args['label_for'] ) ? $args['label_for'] : false,
		]);

		$default_value = isset( $field_args['value'] ) ? $field_args['value'] : '';
		$field_args['value'] = isset( $field_args['name'] ) ? get_option( $field_args['name'], $default_value ) : '';

		if( empty( $field_args['value'] ) && isset( $args['field_args']['value'] ) ){
			$field_args['value'] = $args['field_args']['value'];
		}

		$field_attrs = [];
	
		foreach( $field_args as $key => $value ){
			$field_attrs[] = $key . '="' . $value . '"';
		}

		echo '<input ' . implode( ' ', $field_attrs ) . ' />';
	}

	public function select_field( $args ){
		$field_args = wp_parse_args( $args['field_args'], [
			'class' => 'smac-form-control',
			'id' => isset( $args['label_for'] ) ? $args['label_for'] : false,
			'options' => []
		]);
		
		$field_options = $field_args['options'];
		$field_value = isset( $field_args['name'] ) ? get_option( $field_args['name'], $field_args['value'] ) : '';
		
		unset( $field_args['options'] );
		unset( $field_args['value'] );

		$field_attrs = [];

		foreach( $field_args as $key => $value ){
			$field_attrs[] = $key . '="' . $value . '"';
		}

		echo '<select ' . implode( ' ', $field_attrs ) . '>';
			foreach( $field_options as $value => $label ){
				echo '<option value="' . $value . '"' . selected( $field_value, $value, false ) . '>' . $label . '</option>';
			}
		echo '</select>';
	}

	public function smoove_settings_page(){
		if( !current_user_can( 'manage_options' ) ){
			return;
		}

		echo '<div class="wrap smac-settings-wrap">';
			echo '<div class="smac-settings-header">';
					echo '<img src="' . SMAC_PLUGIN_DIR . '/assets/images/smoove-settings-logo.jpg" class="smac-settings-logo">';
					echo '<h1 class="smac-settings-title">' . get_admin_page_title() . '</h1>';
				echo '</div>';

			echo '<div class="smac-settings-body">';
				echo '<form id="smac-settings-form" class="smac-form" action="options.php" method="post">';
					settings_fields( $this->settings_page_slug );
					do_settings_sections( $this->settings_page_slug );
					submit_button();
				echo '</form>';
			echo '</div>';
		echo '</div>';
	}

	public function smoove_api_test_page(){
		if( !current_user_can( 'manage_options' ) ){
			return;
		}
	
		$current_user = wp_get_current_user();

		echo '<div class="wrap smac-settings-wrap">';
			echo '<div class="smac-settings-header">';
					echo '<img src="' . SMAC_PLUGIN_DIR . '/assets/images/smoove-settings-logo.jpg" class="smac-settings-logo">';
					echo '<h1 class="smac-settings-title">' . get_admin_page_title() . '</h1>';
					echo '<p>' . __('Test the connection between the plugin and your Smoove account by entering a contact’s email (can be "dummy" email) and press "Test". A successful test will result with the contact info in your Smoove account.', 'smac') . '</p>';
				echo '</div>';

			echo '<div class="smac-settings-body">';
				echo '<form id="smac-api-test-form" class="smac-form">';
					echo '<table class="form-table" role="presentation">';
						echo '<tbody>';							
							echo '<tr>';
								echo '<th scope="row">';
									echo '<label for="smac_api_test_email">' . __('Contact Email', 'smac') . '</label>';
								echo '</th>';
								
								echo '<td>';
									echo '<input type="text" class="smac-form-control" id="smac_api_test_email" name="smac_api_test_email" value="' . $current_user->user_email . '" />';
								echo '</td>';
							echo '</tr>';
						echo '</tbody>';
					echo '</table>';

					echo '<p class="submit">';
						echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __('Test', 'smac') . '">';
					echo '</p>';

					$default_message = __('Server response will appear here', 'smac');

					echo '<div class="smac-form-message info" data-default-message="' . $default_message . '">';
						echo '<p>' . $default_message . '</p>';
					echo '</div>';
				echo '</form>';
			echo '</div>';
		echo '</div>';
	}
}

new Settings();