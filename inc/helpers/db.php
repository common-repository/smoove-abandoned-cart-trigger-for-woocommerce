<?php
namespace SMAC\Helpers;

class DB{
	public static function create_log_table(){
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 
		
		$table_name = 'smoove_log';
		$table_name =  $wpdb->prefix.$table_name;
		
		//$wpdb->query("DROP TABLE ".$table_name );

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`date` datetime,
			`contact` varchar(255), 
			`activity` longtext, 
			`code` varchar(20), 
			UNIQUE KEY `id` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		dbDelta( $sql );

		flush_rewrite_rules();
	}

	public static function add_log_entry( $contact, $activity, $code ){
		global $wpdb;

		$table_name = 'smoove_log';
		$table_name =  $wpdb->prefix.$table_name;

		$wpdb->insert(
			$table_name,
			[
				'date' => wp_date( 'Y-m-d H:i:s' ),
				'contact' => sanitize_email( $contact ),
				'activity' => $activity,
				'code' => $code,
			]
		);
	}
}