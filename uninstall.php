<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BlessFlow_AI
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// 1. Delete Options
$options = array(
    'blessflow_gemini_api_key',
    'blessflow_gemini_model',
    'blessflow_unsplash_key',
    'blessflow_db_version'
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// 2. Drop Custom Table
global $wpdb;

$table_name = $wpdb->prefix . 'blessflow_historico';

// Validate table name to avoid SQL injection risks (though prefix is trusted)
// Using prepare is not necessary for drop table but good practice to be safe with dynamic names?
// Drop table unfortunately doesn't support prepare well, but we control the string.
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Optional: Clear any transients if they existed (none for now)
// delete_transient( 'blessflow_some_transient' );

