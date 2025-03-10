<?php
/**
 * Frontend Shortcodes for GTFS Importer
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

class GTFS_Importer_Frontend_Shortcodes {

    /**
     * Initialize the shortcodes
     */
    public static function init() {
        add_shortcode('gtfs_ticket_booking', array(__CLASS__, 'ticket_booking_shortcode'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'));
        add_action('wp_ajax_gtfs_get_routes', array(__CLASS__, 'get_routes_ajax'));
        add_action('wp_ajax_nopriv_gtfs_get_routes', array(__CLASS__, 'get_routes_ajax'));
    }
    /**
 * AJAX handler for fare calculation
 */
public static function calculate_fare_ajax() {
    check_ajax_referer('gtfs_frontend_nonce', 'nonce');
    
    $from_stop = isset($_POST['from_stop']) ? sanitize_text_field($_POST['from_stop']) : '';
    $to_stop = isset($_POST['to_stop']) ? sanitize_text_field($_POST['to_stop']) : '';
    $route_id = isset($_POST['route']) ? sanitize_text_field($_POST['route']) : null;
    
    if (empty($from_stop) || empty($to_stop)) {
        wp_send_json_error(array('message' => __('Origin and destination stops are required.', 'gtfs-importer')));
    }
    
    // Load fare model
    require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-fare-model.php';
    $fare_model = new GTFS_Fare_Model();
    
    $fare = $fare_model->calculate_fare($from_stop, $to_stop, $route_id);
    
    if (!$fare) {
        wp_send_json_error(array('message' => __('Could not calculate fare for the selected stops.', 'gtfs-importer')));
    }
    
    // Format payment method
    $payment_methods = array(
        0 => __('Pay on board', 'gtfs-importer'),
        1 => __('Pay before boarding', 'gtfs-importer')
    );
    
    $payment_method = isset($payment_methods[$fare->payment_method]) 
        ? $payment_methods[$fare->payment_method] 
        : __('Unknown payment method', 'gtfs-importer');
    
    // Format transfer info
    $transfer_info = '';
    if ($fare->transfers === '0') {
        $transfer_info = __('No transfers allowed', 'gtfs-importer');
    } elseif ($fare->transfers === '1') {
        $transfer_info = __('1 transfer allowed', 'gtfs-importer');
    } elseif ($fare->transfers === '2') {
        $transfer_info = __('2 transfers allowed', 'gtfs-importer');
    } elseif ($fare->transfers === null || $fare->transfers === '') {
        $transfer_info = __('Unlimited transfers', 'gtfs-importer');
    } else {
        $transfer_info = sprintf(__('%s transfers allowed', 'gtfs-importer'), $fare->transfers);
    }
    
    // Add transfer duration if available
    if (!empty($fare->transfer_duration)) {
        $hours = floor($fare->transfer_duration / 3600);
        $minutes = floor(($fare->transfer_duration % 3600) / 60);
        
        if ($hours > 0) {
            $transfer_info .= sprintf(
                _n(' within %s hour', ' within %s hours', $hours, 'gtfs-importer'),
                $hours
            );
            
            if ($minutes > 0) {
                $transfer_info .= sprintf(
                    _n(' and %s minute', ' and %s minutes', $minutes, 'gtfs-importer'),
                    $minutes
                );
            }
        } elseif ($minutes > 0) {
            $transfer_info .= sprintf(
                _n(' within %s minute', ' within %s minutes', $minutes, 'gtfs-importer'),
                $minutes
            );
        }
    }
    
    $response = array(
        'price' => $fare->price,
        'currency' => $fare->currency_type,
        'payment_method' => $payment_method,
        'transfer_info' => $transfer_info,
        'details' => "<p>{$payment_method}. {$transfer_info}</p>"
    );
    
    wp_send_json_success($response);
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
     * Ticket booking shortcode callback
     */
    public static function ticket_booking_shortcode($atts) {
        // Get stops for dropdowns
        require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-stop-model.php';
        $stop_model = new GTFS_Stop_Model();
        $stops = $stop_model->get_all_stops(array('orderby' => 'stop_name'));

        ob_start();
        include GTFS_IMPORTER_PLUGIN_DIR . 'public/partials/ticket-booking-display.php';
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

add_action('wp_ajax_gtfs_calculate_fare', array(__CLASS__, 'calculate_fare_ajax'));
add_action('wp_ajax_nopriv_gtfs_calculate_fare', array(__CLASS__, 'calculate_fare_ajax'));


GTFS_Importer_Frontend_Shortcodes::init();
