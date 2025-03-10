<?php
/**
 * Shortcodes for GTFS Importer
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

class GTFS_Importer_Shortcodes {

    /**
     * Initialize the shortcodes
     */
    public static function init() {
        add_shortcode('gtfs_route_search', array(__CLASS__, 'route_search_shortcode'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'));
        add_action('wp_ajax_gtfs_get_routes', array(__CLASS__, 'get_routes_ajax'));
        add_action('wp_ajax_nopriv_gtfs_get_routes', array(__CLASS__, 'get_routes_ajax'));
    }

    /**
     * Enqueue scripts and styles for frontend
     */
    public static function enqueue_frontend_assets() {
        wp_enqueue_style(
            'gtfs-frontend',
            GTFS_IMPORTER_PLUGIN_URL . 'public/css/frontend.css',
            array(),
            GTFS_IMPORTER_VERSION
        );

        wp_enqueue_script(
            'gtfs-frontend',
            GTFS_IMPORTER_PLUGIN_URL . 'public/js/frontend.js',
            array('jquery'),
            GTFS_IMPORTER_VERSION,
            true
        );

        wp_localize_script(
            'gtfs-frontend',
            'gtfs_data',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('gtfs_frontend_nonce')
            )
        );
    }

    /**
     * Route search shortcode callback
     */
    public static function route_search_shortcode($atts) {
        // Get stops for dropdowns
        require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-stop-model.php';
        $stop_model = new GTFS_Stop_Model();
        $stops = $stop_model->get_all_stops(array('orderby' => 'stop_name'));

        ob_start();
        include GTFS_IMPORTER_PLUGIN_DIR . 'public/partials/route-search-display.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler to fetch routes based on user input
     */
    public static function get_routes_ajax() {
        check_ajax_referer('gtfs_frontend_nonce', 'nonce');

        $from_stop = isset($_POST['from_stop']) ? sanitize_text_field($_POST['from_stop']) : '';
        $to_stop = isset($_POST['to_stop']) ? sanitize_text_field($_POST['to_stop']) : '';
        $journey_date = isset($_POST['journey_date']) ? sanitize_text_field($_POST['journey_date']) : '';

        if (empty($from_stop) || empty($to_stop) || empty($journey_date)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }

        // Fetch routes from the database (mock data for demonstration)
        $routes = array(
            array(
                'operator' => 'Mega Bus',
                'departure_time' => '11:00 AM',
                'arrival_time' => '3:00 PM',
                'fare' => '$10.00',
                'seats_available' => 20,
                'route_id' => '1009-CHP-DHA-1'
            ),
            array(
                'operator' => 'Bolt Bus',
                'departure_time' => '11:00 AM',
                'arrival_time' => '3:00 PM',
                'fare' => '$10.00',
                'seats_available' => 50,
                'route_id' => '1009-CHP-DHA-1'
            )
        );

        wp_send_json_success(array('routes' => $routes));
    }
}

GTFS_Importer_Shortcodes::init();
