<?php
/**
 * GTFS Stop Model
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 */

/**
 * GTFS Stop Model
 *
 * This class handles all database operations for stops
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/models
 * @author     Your Name <email@example.com>
 */
class GTFS_Stop_Model {

    /**
     * The table name for stops
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
        $this->table_name = $wpdb->prefix . 'gtfs_stops';
    }

    /**
     * Create a new stop record in the database
     *
     * @since    1.0.0
     * @param    array    $data    Stop data to insert
     * @return   int|false         ID of the inserted record or false on failure
     */
    public function insert_stop( $data ) {
        global $wpdb;
        
        // Sanitize data
        $stop_data = array(
            'stop_id'   => sanitize_text_field( $data['stop_id'] ),
            'stop_name' => sanitize_text_field( $data['stop_name'] ),
        );
        
        // Add coordinates if they exist
        if ( isset( $data['stop_lat'] ) && isset( $data['stop_lon'] ) ) {
            $stop_data['stop_lat'] = (float) $data['stop_lat'];
            $stop_data['stop_lon'] = (float) $data['stop_lon'];
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $stop_data
        );
        
        if ( $result === false ) {
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Get a single stop by ID
     *
     * @since    1.0.0
     * @param    string    $stop_id    The stop ID
     * @return   object|null           The stop object or null if not found
     */
    public function get_stop_by_id( $stop_id ) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE stop_id = %s",
            $stop_id
        );
        
        return $wpdb->get_row( $sql );
    }

    /**
     * Get all stops
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments
     * @return   array             Array of stop objects
     */
    public function get_all_stops( $args = array() ) {
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
        $allowed_columns = array( 'id', 'stop_id', 'stop_name' );
        $orderby = in_array( $args['orderby'], $allowed_columns ) ? $args['orderby'] : 'id';
        
        $sql = "SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order}";
        
        // Add limit and offset if specified
        if ( $args['limit'] > 0 ) {
            $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );
        }
        
        return $wpdb->get_results( $sql );
    }

    /**
     * Get the total number of stops
     *
     * @since    1.0.0
     * @return   int    The total number of stops
     */
    public function get_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        
        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Search for stops by name
     *
     * @since    1.0.0
     * @param    string    $search_term    The search term
     * @param    int       $limit          Maximum number of results
     * @return   array                     Array of stop objects
     */
    public function search_stops( $search_term, $limit = 10 ) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE stop_name LIKE %s ORDER BY stop_name ASC LIMIT %d",
            '%' . $wpdb->esc_like( $search_term ) . '%',
            $limit
        );
        
        return $wpdb->get_results( $sql );
    }

    /**
     * Update an existing stop
     *
     * @since    1.0.0
     * @param    int       $id      The record ID
     * @param    array     $data    The data to update
     * @return   bool               True on success, false on failure
     */
    public function update_stop( $id, $data ) {
        global $wpdb;
        
        // Sanitize data
        $stop_data = array();
        
        if ( isset( $data['stop_id'] ) ) {
            $stop_data['stop_id'] = sanitize_text_field( $data['stop_id'] );
        }
        
        if ( isset( $data['stop_name'] ) ) {
            $stop_data['stop_name'] = sanitize_text_field( $data['stop_name'] );
        }
        
        if ( isset( $data['stop_lat'] ) && isset( $data['stop_lon'] ) ) {
            $stop_data['stop_lat'] = (float) $data['stop_lat'];
            $stop_data['stop_lon'] = (float) $data['stop_lon'];
        }
        
        // Make sure we have some data to update
        if ( empty( $stop_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $stop_data,
            array( 'id' => $id )
        );
        
        return $result !== false;
    }

    /**
     * Delete a stop
     *
     * @since    1.0.0
     * @param    int    $id    The record ID
     * @return   bool          True on success, false on failure
     */
    public function delete_stop( $id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $id )
        );
        
        return $result !== false;
    }

    /**
     * Truncate the stops table
     *
     * @since    1.0.0
     * @return   bool    True on success, false on failure
     */
    public function truncate_table() {
        global $wpdb;
        
        $result = $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
        
        return $result !== false;
    }
    
    /**
     * Get stops for a specific route and direction
     *
     * @param string $route_id The route ID
     * @param string $direction The direction (0 = outbound, 1 = inbound)
     * @return array Array of stop objects
     */
    public function get_stops_by_route($route_id, $direction = '0') {
        global $wpdb;
        
        // In a real implementation, this would join with stop_times.txt data
        // For now, just return all stops as an example
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY stop_name ASC LIMIT 10"
        );
        
        return $wpdb->get_results($sql);
    }
}
