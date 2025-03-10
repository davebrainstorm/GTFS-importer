<?php
/**
 * Plugin Name: GTFS Importer
 * Plugin URI: https://example.com/gtfs-importer
 * Description: Import GTFS transit data into WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: gtfs-importer
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('GTFS_IMPORTER_VERSION', '1.0.0');
define('GTFS_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GTFS_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_gtfs_importer() {
    require_once GTFS_IMPORTER_PLUGIN_DIR . 'includes/class-activator.php';
    GTFS_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_gtfs_importer() {
    // Deactivation tasks if needed
}

register_activation_hook(__FILE__, 'activate_gtfs_importer');
register_deactivation_hook(__FILE__, 'deactivate_gtfs_importer');

/**
 * Load the required dependencies for this plugin.
 */
require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-route-model.php';
require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-stop-model.php';
require_once GTFS_IMPORTER_PLUGIN_DIR . 'admin/class-admin.php';
require_once GTFS_IMPORTER_PLUGIN_DIR . 'includes/class-shortcodes.php';

/**
 * Handle AJAX request for fare calculation
 */
function gtfs_calculate_fare_ajax() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gtfs_frontend_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    // Get parameters
    $from_stop = isset($_POST['from_stop']) ? sanitize_text_field($_POST['from_stop']) : '';
    $to_stop = isset($_POST['to_stop']) ? sanitize_text_field($_POST['to_stop']) : '';
    
    if (empty($from_stop) || empty($to_stop)) {
        wp_send_json_error(array('message' => 'Origin and destination stops are required.'));
    }
    
    // In a real implementation, we would calculate the fare based on GTFS fare data
    // For demonstration, return a placeholder fare
    
    // Sample response
    $response = array(
        'fare' => '2.50',
        'currency' => '€',
        'details' => '<p>Standard adult fare between these stops.</p>'
    );
    
    wp_send_json_success($response);
}

// Register AJAX handlers
add_action('wp_ajax_gtfs_calculate_fare', 'gtfs_calculate_fare_ajax');
add_action('wp_ajax_nopriv_gtfs_calculate_fare', 'gtfs_calculate_fare_ajax');

/**
 * Begins execution of the plugin.
 */
function run_gtfs_importer() {
    // Register the admin class
    $plugin_admin = new GTFS_Importer_Admin('gtfs-importer', GTFS_IMPORTER_VERSION);
    
    // Add admin menu
    add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
    
    // Add admin assets
    add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
    add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
    
    // Handle the GTFS import action
    add_action('admin_post_gtfs_import_action', array($plugin_admin, 'handle_gtfs_import'));
}
require_once GTFS_IMPORTER_PLUGIN_DIR . '/includes/class-frontend-shortcodes.php';


// Start the plugin
run_gtfs_importer();
