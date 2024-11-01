<?php
namespace SMAC\Modules;

class Smoove_Api{
	public $api_url;
	public $api_key;
	public $default_response;

	public function __construct( $api_key = false  ){
		$this->api_url = 'https://rest.smoove.io/v1/';
		$this->api_key = $api_key ? $api_key : get_option('smac_api_key');
		$this->default_response = ['code' => false];
	}

	public function get( $endpoint, $body = false ){
		return $this->call( $endpoint, $body, 'GET' );
	}

	public function post( $endpoint, $body = false ){
		return $this->call( $endpoint, $body, 'POST' );
	}

	public function call( $endpoint, $body = false, $method = 'GET' ){
		if( !$this->api_key ){
			return $this->default_response;
		}

		$args = [
			// 'timeout' => 25,
			'headers' => [
				'Content-type' => 'application/json',
				'apiKey' => $this->api_key,
			],
		];

		if( is_array( $body ) && !empty( $body ) ){
			$args['body'] = json_encode( $body );
		}
		
		if( strtolower( $method ) == 'post' ){
			$response = wp_remote_post( $this->api_url . $endpoint, $args );
		}else{
			$response = wp_remote_get( $this->api_url . $endpoint, $args );
		}

		if( !is_wp_error( $response ) ){
			return [
				'api_key' => $this->api_key,
				'code' => $response['response']['code'],
				'body' => json_decode( $response['body'], true ),
				'message' => $response['response']['message'] 
			];
		}else{
			return $this->default_response;
		}
	}
}