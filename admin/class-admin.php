<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the admin area functionality of the plugin.
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin
 * @author     Your Name <email@example.com>
 */
class GTFS_Importer_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/admin.js',
            array( 'jquery' ),
            $this->version,
            true
        );
        
        // Add localized script data for our admin JS
        wp_localize_script(
            $this->plugin_name,
            'gtfs_importer_admin',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'gtfs_importer_nonce' ),
                'messages' => array(
                    'zip_only'          => __('Only ZIP files are accepted.', 'gtfs-importer'),
                    'file_too_large'    => __('The file is too large.', 'gtfs-importer'),
                    'select_import_option' => __('Please select at least one import option.', 'gtfs-importer'),
                    'importing'         => __('Importing...', 'gtfs-importer')
                )
            )
        );
    }

    /**
     * Add menu item for the plugin in WP Admin
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __( 'GTFS Importer', 'gtfs-importer' ),
            __( 'GTFS Importer', 'gtfs-importer' ),
            'manage_options',
            'gtfs-importer',
            array( $this, 'display_plugin_admin_dashboard' ),
            'dashicons-schedule', // More appropriate icon for transit
            65
        );
        
        // Add submenu pages for various GTFS components
        add_submenu_page(
            'gtfs-importer',
            __( 'Import GTFS Data', 'gtfs-importer' ),
            __( 'Import', 'gtfs-importer' ),
            'manage_options',
            'gtfs-importer',
            array( $this, 'display_plugin_admin_dashboard' )
        );
        
        add_submenu_page(
            'gtfs-importer',
            __( 'Manage Routes', 'gtfs-importer' ),
            __( 'Routes', 'gtfs-importer' ),
            'manage_options',
            'gtfs-importer-routes',
            array( $this, 'display_routes_page' )
        );
        
        add_submenu_page(
            'gtfs-importer',
            __( 'Manage Stops', 'gtfs-importer' ),
            __( 'Stops', 'gtfs-importer' ),
            'manage_options',
            'gtfs-importer-stops',
            array( $this, 'display_stops_page' )
        );
        
        add_submenu_page(
            'gtfs-importer',
            __( 'Settings', 'gtfs-importer' ),
            __( 'Settings', 'gtfs-importer' ),
            'manage_options',
            'gtfs-importer-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Render the main admin dashboard
     */
    public function display_plugin_admin_dashboard() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/admin-display.php';
    }
    
    /**
     * Render the routes management page
     */
    public function display_routes_page() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/routes-display.php';
    }
    
    /**
     * Render the stops management page
     */
    public function display_stops_page() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/stops-display.php';
    }
    
    /**
     * Render the settings page
     */
    public function display_settings_page() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/settings-display.php';
    }

    /**
     * Handle GTFS import form submission
     */
    public function handle_gtfs_import() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'gtfs-importer' ) );
        }

        // Verify nonce
        check_admin_referer( 'gtfs_import_nonce_action', 'gtfs_import_nonce' );

        // Initialize result array for user feedback
        $result = array(
            'success' => false,
            'message' => '',
            'counts'  => array(
                'routes' => 0,
                'stops'  => 0,
                'trips'  => 0,
            )
        );

        // Check for errors in file upload
        if ( ! isset( $_FILES['gtfs_zip'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( __( 'No file was uploaded.', 'gtfs-importer' ) ) ) );
            exit;
        }
        
        $file = $_FILES['gtfs_zip'];
        
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            $error_message = $this->get_upload_error_message( $file['error'] );
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( $error_message ) ) );
            exit;
        }
        
        // Validate file type
        $file_type = wp_check_filetype( $file['name'] );
        if ( $file_type['ext'] !== 'zip' ) {
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( __( 'Only ZIP files are accepted.', 'gtfs-importer' ) ) ) );
            exit;
        }
        
        // Validate file size (20MB limit by default, filterable)
        $max_size = apply_filters( 'gtfs_importer_max_upload_size', 20 * 1024 * 1024 ); // 20MB default
        if ( $file['size'] > $max_size ) {
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( __( 'The file exceeds the maximum upload size.', 'gtfs-importer' ) ) ) );
            exit;
        }

        // Move the uploaded file to a temporary location
        $uploaded_file = $_FILES['gtfs_zip'];
        $tmp_path      = $uploaded_file['tmp_name'];
        $filename      = sanitize_file_name($uploaded_file['name']);

        // Create a unique name in WP's uploads directory
        $upload_dir  = wp_upload_dir();
        $dest_path   = $upload_dir['basedir'] . '/gtfs-imports';
        if ( ! file_exists( $dest_path ) ) {
            wp_mkdir_p( $dest_path );
            
            // Protect directory with an index.php file
            $index_file = $dest_path . '/index.php';
            if ( ! file_exists( $index_file ) ) {
                file_put_contents( $index_file, '<?php // Silence is golden.' );
            }
            
            // Add .htaccess protection
            $htaccess_file = $dest_path . '/.htaccess';
            if ( ! file_exists( $htaccess_file ) ) {
                file_put_contents( $htaccess_file, 'Deny from all' );
            }
        }
        
        $final_path = $dest_path . '/' . wp_unique_filename( $dest_path, $filename );
        
        if ( ! move_uploaded_file( $tmp_path, $final_path ) ) {
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( __( 'Error saving the uploaded file.', 'gtfs-importer' ) ) ) );
            exit;
        }

        // Process the GTFS ZIP file
        try {
            // Load required model classes
            require_once plugin_dir_path( __FILE__ ) . '../models/class-gtfs-parser.php';
            require_once plugin_dir_path( __FILE__ ) . '../models/class-route-model.php';
            require_once plugin_dir_path( __FILE__ ) . '../models/class-stop-model.php';

            $parser       = new GTFS_Parser();
            $route_model  = new GTFS_Route_Model();
            $stop_model   = new GTFS_Stop_Model();
            
            // Import settings - which files to process
            $import_settings = array(
                'routes' => isset( $_POST['import_routes'] ) ? true : false,
                'stops'  => isset( $_POST['import_stops'] ) ? true : false,
                'trips'  => isset( $_POST['import_trips'] ) ? true : false,
            );
            // Import settings - which files to process
$import_settings = array(
    'routes' => isset($_POST['import_routes']) ? true : false,
    'stops'  => isset($_POST['import_stops']) ? true : false,
    'trips'  => isset($_POST['import_trips']) ? true : false,
    'fares'  => isset($_POST['import_fares']) ? true : false,
);

// Load required model classes
require_once plugin_dir_path(__FILE__) . '../models/class-gtfs-parser.php';
require_once plugin_dir_path(__FILE__) . '../models/class-route-model.php';
require_once plugin_dir_path(__FILE__) . '../models/class-stop-model.php';
require_once plugin_dir_path(__FILE__) . '../models/class-fare-model.php';

$parser       = new GTFS_Parser();
$route_model  = new GTFS_Route_Model();
$stop_model   = new GTFS_Stop_Model();
$fare_model   = new GTFS_Fare_Model();

// Start processing - pass in all models
$results = $parser->process_gtfs_zip($final_path, array(
    'route_model' => $route_model,
    'stop_model'  => $stop_model,
    'fare_model'  => $fare_model,
), $import_settings);

// Generate success message
$message = sprintf(
    __('GTFS data imported successfully. Imported %d routes, %d stops, and %d fares.', 'gtfs-importer'),
    $results['routes'],
    $results['stops'],
    $results['fares']
);


            // Start processing - pass in all models
            $results = $parser->process_gtfs_zip( $final_path, array(
                'route_model' => $route_model,
                'stop_model'  => $stop_model,
            ), $import_settings );

            // Generate success message
            $message = sprintf(
                __( 'GTFS data imported successfully. Imported %d routes and %d stops.', 'gtfs-importer' ),
                $results['routes'],
                $results['stops']
            );
            
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&success=1&message=' . urlencode( $message ) . '&routes=' . $results['routes'] . '&stops=' . $results['stops'] ) );
            exit;
        } catch ( Exception $e ) {
            wp_redirect( admin_url( 'admin.php?page=gtfs-importer&error=1&message=' . urlencode( $e->getMessage() ) ) );
            exit;
        }
    }
    
    /**
     * Get human-readable upload error message
     */
    private function get_upload_error_message( $error_code ) {
        switch ( $error_code ) {
            case UPLOAD_ERR_INI_SIZE:
                return __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'gtfs-importer' );
            case UPLOAD_ERR_FORM_SIZE:
                return __( 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.', 'gtfs-importer' );
            case UPLOAD_ERR_PARTIAL:
                return __( 'The uploaded file was only partially uploaded.', 'gtfs-importer' );
            case UPLOAD_ERR_NO_FILE:
                return __( 'No file was uploaded.', 'gtfs-importer' );
            case UPLOAD_ERR_NO_TMP_DIR:
                return __( 'Missing a temporary folder.', 'gtfs-importer' );
            case UPLOAD_ERR_CANT_WRITE:
                return __( 'Failed to write file to disk.', 'gtfs-importer' );
            case UPLOAD_ERR_EXTENSION:
                return __( 'A PHP extension stopped the file upload.', 'gtfs-importer' );
            default:
                return __( 'Unknown upload error.', 'gtfs-importer' );
        }
    }
}
