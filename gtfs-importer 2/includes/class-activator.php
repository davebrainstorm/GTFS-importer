<?php
/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/includes
 * @author     Your Name <email@example.com>
 */
class GTFS_Importer_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
     public static function activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Routes table
    $table_name = $wpdb->prefix . 'gtfs_routes';
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        route_id VARCHAR(100) NOT NULL,
        route_name VARCHAR(255) NOT NULL,
        route_type INT(11) NULL,
        route_color VARCHAR(50) NULL,
        route_text_color VARCHAR(50) NULL,
        PRIMARY KEY (id),
        KEY route_id (route_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Stops table
    $table_name = $wpdb->prefix . 'gtfs_stops';
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        stop_id VARCHAR(100) NOT NULL,
        stop_name VARCHAR(255) NOT NULL,
        stop_lat FLOAT NULL,
        stop_lon FLOAT NULL,
        zone_id VARCHAR(100) NULL,
        PRIMARY KEY (id),
        KEY stop_id (stop_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Fare attributes table
    $table_name = $wpdb->prefix . 'gtfs_fare_attributes';
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        fare_id VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        currency_type VARCHAR(3) NOT NULL,
        payment_method TINYINT(1) NOT NULL,
        transfers TINYINT(1) NULL,
        transfer_duration INT(11) NULL,
        agency_id VARCHAR(255) NULL,
        PRIMARY KEY (id),
        KEY fare_id (fare_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Fare rules table
    $table_name = $wpdb->prefix . 'gtfs_fare_rules';
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        fare_id VARCHAR(255) NOT NULL,
        route_id VARCHAR(255) NULL,
        origin_id VARCHAR(255) NULL,
        destination_id VARCHAR(255) NULL,
        contains_id VARCHAR(255) NULL,
        PRIMARY KEY (id),
        KEY fare_id (fare_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Initialize options
    add_option('gtfs_importer_version', '1.1.0');
    add_option('gtfs_importer_history', array());
}

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        // Routes table
        $table_name = $wpdb->prefix . 'gtfs_routes';
        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            route_id VARCHAR(100) NOT NULL,
            route_name VARCHAR(255) NOT NULL,
            route_type INT(11) NULL,
            route_color VARCHAR(50) NULL,
            route_text_color VARCHAR(50) NULL,
            PRIMARY KEY (id),
            KEY route_id (route_id)
        ) $charset_collate;";
        dbDelta( $sql );
        
        // Stops table
        $table_name = $wpdb->prefix . 'gtfs_stops';
        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            stop_id VARCHAR(100) NOT NULL,
            stop_name VARCHAR(255) NOT NULL,
            stop_lat FLOAT NULL,
            stop_lon FLOAT NULL,
            PRIMARY KEY (id),
            KEY stop_id (stop_id)
        ) $charset_collate;";
        dbDelta( $sql );
        
        // Initialize options
        add_option( 'gtfs_importer_version', '1.1.0' );
        add_option( 'gtfs_importer_history', array() );
    }
}
