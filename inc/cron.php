<?php
namespace SMAC;

class Cron{
	public function __construct(){
		add_filter( 'cron_schedules', [$this, 'custom_cron_schedules'] );
		add_action( 'init', [$this, 'register_cron_jobs'] );
	}
	
	public function custom_cron_schedules( $schedules ){
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Every Minute' ),
		);

		return $schedules;
	}
	
	public function register_cron_jobs(){
		if( !wp_next_scheduled( 'smac_cron_trace_temp_carts' ) ) {
			wp_schedule_event( strtotime('01:00:00'), 'every_minute', 'smac_cron_trace_temp_carts' );
		}
	}
}

new Cron();