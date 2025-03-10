<?php
/**
 * GTFS Fare Model
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 */

class GTFS_Fare_Model {

    /**
     * The table name for fares
     *
     * @var string
     */
    private $table_name;

    /**
     * The table name for fare rules
     *
     * @var string
     */
    private $rules_table_name;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'gtfs_fare_attributes';
        $this->rules_table_name = $wpdb->prefix . 'gtfs_fare_rules';
    }

    /**
     * Create the database tables for fares
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            fare_id VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            currency_type VARCHAR(3) NOT NULL,
            payment_method TINYINT(1) NOT NULL,
            transfers TINYINT(1),
            transfer_duration INT(11),
            agency_id VARCHAR(255),
            PRIMARY KEY (id),
            KEY fare_id (fare_id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE IF NOT EXISTS {$this->rules_table_name} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            fare_id VARCHAR(255) NOT NULL,
            route_id VARCHAR(255),
            origin_id VARCHAR(255),
            destination_id VARCHAR(255),
            contains_id VARCHAR(255),
            PRIMARY KEY (id),
            KEY fare_id (fare_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }

    /**
     * Truncate fare tables
     */
    public function truncate_tables() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        $wpdb->query("TRUNCATE TABLE {$this->rules_table_name}");
    }

    /**
     * Insert fare attribute
     */
    public function insert_fare_attribute($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'fare_id' => sanitize_text_field($data['fare_id']),
                'price' => floatval($data['price']),
                'currency_type' => sanitize_text_field($data['currency_type']),
                'payment_method' => intval($data['payment_method']),
                'transfers' => isset($data['transfers']) ? intval($data['transfers']) : null,
                'transfer_duration' => isset($data['transfer_duration']) ? intval($data['transfer_duration']) : null,
                'agency_id' => isset($data['agency_id']) ? sanitize_text_field($data['agency_id']) : null
            )
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Insert fare rule
     */
    public function insert_fare_rule($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->rules_table_name,
            array(
                'fare_id' => sanitize_text_field($data['fare_id']),
                'route_id' => isset($data['route_id']) ? sanitize_text_field($data['route_id']) : null,
                'origin_id' => isset($data['origin_id']) ? sanitize_text_field($data['origin_id']) : null,
                'destination_id' => isset($data['destination_id']) ? sanitize_text_field($data['destination_id']) : null,
                'contains_id' => isset($data['contains_id']) ? sanitize_text_field($data['contains_id']) : null
            )
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Calculate fare between two stops
     */
    public function calculate_fare($from_stop_id, $to_stop_id, $route_id = null) {
        global $wpdb;
        
        // Get zones for the stops
        require_once GTFS_IMPORTER_PLUGIN_DIR . 'models/class-stop-model.php';
        $stop_model = new GTFS_Stop_Model();
        
        $from_stop = $stop_model->get_stop_by_id($from_stop_id);
        $to_stop = $stop_model->get_stop_by_id($to_stop_id);
        
        if (!$from_stop || !$to_stop) {
            return false;
        }
        
        $from_zone = isset($from_stop->zone_id) ? $from_stop->zone_id : null;
        $to_zone = isset($to_stop->zone_id) ? $to_stop->zone_id : null;
        
        // First try route + origin + destination specific fare
        if ($route_id && $from_zone && $to_zone) {
            $fare = $this->get_fare_by_route_and_zones($route_id, $from_zone, $to_zone);
            if ($fare) {
                return $fare;
            }
        }
        
        // Then try just origin + destination fare
        if ($from_zone && $to_zone) {
            $fare = $this->get_fare_by_zones($from_zone, $to_zone);
            if ($fare) {
                return $fare;
            }
        }
        
        // Then try just route fare
        if ($route_id) {
            $fare = $this->get_fare_by_route($route_id);
            if ($fare) {
                return $fare;
            }
        }
        
        // If no specific fare, return default fare
        return $this->get_default_fare();
    }

    /**
     * Get fare by route and zones
     */
    private function get_fare_by_route_and_zones($route_id, $origin_id, $destination_id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT f.* FROM {$this->rules_table_name} r
            JOIN {$this->table_name} f ON r.fare_id = f.fare_id
            WHERE r.route_id = %s AND r.origin_id = %s AND r.destination_id = %s
            LIMIT 1",
            $route_id, $origin_id, $destination_id
        );
        
        $fare = $wpdb->get_row($query);
        return $fare;
    }

    /**
     * Get fare by zones
     */
    private function get_fare_by_zones($origin_id, $destination_id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT f.* FROM {$this->rules_table_name} r
            JOIN {$this->table_name} f ON r.fare_id = f.fare_id
            WHERE r.route_id IS NULL AND r.origin_id = %s AND r.destination_id = %s
            LIMIT 1",
            $origin_id, $destination_id
        );
        
        $fare = $wpdb->get_row($query);
        return $fare;
    }

    /**
     * Get fare by route
     */
    private function get_fare_by_route($route_id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT f.* FROM {$this->rules_table_name} r
            JOIN {$this->table_name} f ON r.fare_id = f.fare_id
            WHERE r.route_id = %s AND r.origin_id IS NULL AND r.destination_id IS NULL
            LIMIT 1",
            $route_id
        );
        
        $fare = $wpdb->get_row($query);
        return $fare;
    }

    /**
     * Get default fare
     */
    private function get_default_fare() {
        global $wpdb;
        
        $query = "SELECT * FROM {$this->table_name} LIMIT 1";
        $fare = $wpdb->get_row($query);
        return $fare;
    }
}
