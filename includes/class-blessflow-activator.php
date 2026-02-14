<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BlessFlow_AI
 * @subpackage BlessFlow_AI/includes
 */

class BlessFlow_Activator {

	/**
	 * Create the custom database table for history logging.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'blessflow_historico';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			data_hora datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			post_id bigint(20) DEFAULT 0,
			topico varchar(255) NOT NULL,
			status varchar(50) NOT NULL,
			tokens_input int(11) DEFAULT 0,
			tokens_output int(11) DEFAULT 0,
			custo_estimado decimal(10,6) DEFAULT 0.000000,
			modelo_usado varchar(100) DEFAULT '',
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Add version option
		add_option( 'blessflow_db_version', '1.0.0' );
	}

}
