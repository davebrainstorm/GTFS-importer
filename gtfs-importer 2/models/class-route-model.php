<?php
/**
 * GTFS Route Model
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 */

/**
 * GTFS Route Model
 *
 * This class handles all database operations for routes
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 * @author     Your Name <email@example.com>
 */
class GTFS_Route_Model {

    /**
     * The table name for routes
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The table name
     */
    private $table_name;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'gtfs_routes';
    }

    /**
     * Create a new route record in the database
     *
     * @since    1.0.0
     * @param    array    $data    Route data to insert
     * @return   int|false         ID of the inserted record or false on failure
     */
    public function insert_route( $data ) {
        global $wpdb;
        
        // Sanitize data
        $route_data = array(
            'route_id'      => sanitize_text_field( $data['route_id'] ),
            'route_name'    => sanitize_text_field( $data['route_name'] ),
        );
        
        // Add optional fields if they exist
        if ( isset( $data['route_type'] ) ) {
            $route_data['route_type'] = intval( $data['route_type'] );
        }
        
        if ( isset( $data['route_color'] ) ) {
            $route_data['route_color'] = sanitize_text_field( $data['route_color'] );
        }
        
        if ( isset( $data['route_text_color'] ) ) {
            $route_data['route_text_color'] = sanitize_text_field( $data['route_text_color'] );
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $route_data
        );
        
        if ( $result === false ) {
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Get a single route by ID
     *
     * @since    1.0.0
     * @param    string    $route_id    The route ID
     * @return   object|null            The route object or null if not found
     */
    public function get_route_by_id( $route_id ) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE route_id = %s",
            $route_id
        );
        
        return $wpdb->get_row( $sql );
    }

    /**
     * Get all routes
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments
     * @return   array             Array of route objects
     */
    public function get_all_routes( $args = array() ) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'id',
            'order'   => 'ASC',
            'limit'   => 0,
            'offset'  => 0
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Sanitize the order and orderby values
        $order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';
        
        // Only allow valid columns for ordering
        $allowed_columns = array( 'id', 'route_id', 'route_name', 'route_type' );
        $orderby = in_array( $args['orderby'], $allowed_columns ) ? $args['orderby'] : 'id';
        
        $sql = "SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order}";
        
        // Add limit and offset if specified
        if ( $args['limit'] > 0 ) {
            $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );
        }
        
        return $wpdb->get_results( $sql );
    }

    /**
     * Get the total number of routes
     *
     * @since    1.0.0
     * @return   int    The total number of routes
     */
    public function get_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        
        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Update an existing route
     *
     * @since    1.0.0
     * @param    int       $id      The record ID
     * @param    array     $data    The data to update
     * @return   bool               True on success, false on failure
     */
    public function update_route( $id, $data ) {
        global $wpdb;
        
        // Sanitize data
        $route_data = array();
        
        if ( isset( $data['route_id'] ) ) {
            $route_data['route_id'] = sanitize_text_field( $data['route_id'] );
        }
        
        if ( isset( $data['route_name'] ) ) {
            $route_data['route_name'] = sanitize_text_field( $data['route_name'] );
        }
        
        if ( isset( $data['route_type'] ) ) {
            $route_data['route_type'] = intval( $data['route_type'] );
        }
        
        if ( isset( $data['route_color'] ) ) {
            $route_data['route_color'] = sanitize_text_field( $data['route_color'] );
        }
        
        if ( isset( $data['route_text_color'] ) ) {
            $route_data['route_text_color'] = sanitize_text_field( $data['route_text_color'] );
        }
        
        // Make sure we have some data to update
        if ( empty( $route_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $route_data,
            array( 'id' => $id )
        );
        
        return $result !== false;
    }

    /**
     * Delete a route
     *
     * @since    1.0.0
     * @param    int    $id    The record ID
     * @return   bool          True on success, false on failure
     */
    public function delete_route( $id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $id )
        );
        
        return $result !== false;
    }

    /**
     * Truncate the routes table
     *
     * @since    1.0.0
     * @return   bool    True on success, false on failure
     */
    public function truncate_table() {
        global $wpdb;
        
        $result = $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
        
        return $result !== false;
    }
}
