<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * administrative area. This file also includes all of the plugin dependencies.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           BlessFlow_AI
 *
 * Plugin Name:       BlessFlow AI
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Agente SEO Autônomo com suporte a Gemini 2.5 Flash e Unsplash.
 * Version:           1.0.0
 * Author:            Seu Nome
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       blessflow-ai
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'BLESSFLOW_VERSION', '1.0.0' );
define( 'BLESSFLOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLESSFLOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_blessflow_ai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-activator.php';
	BlessFlow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_blessflow_ai() {
	// require_once plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-deactivator.php';
	// BlessFlow_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_blessflow_ai' );
register_deactivation_hook( __FILE__, 'deactivate_blessflow_ai' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-db.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-gemini.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-unsplash.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-blessflow-ajax.php';

/**
 * Begins execution of the plugin.
 */
function run_blessflow_ai() {
	$plugin_admin = new BlessFlow_Admin();
    $plugin_ajax = new BlessFlow_Ajax();
}

run_blessflow_ai();
