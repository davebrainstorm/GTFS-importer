<?php
class GTFS_Importer {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'gtfs-importer';
        $this->version = '1.1.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( __FILE__ ) . 'class-loader.php';
        $this->loader = new GTFS_Importer_Loader();

        require_once plugin_dir_path( __FILE__ ) . 'class-i18n.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-activator.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-deactivator.php';

        // Admin & Public
        require_once plugin_dir_path( __FILE__ ) . '../admin/class-admin.php';
        require_once plugin_dir_path( __FILE__ ) . '../public/class-public.php';

        // Models & Parsers
        require_once plugin_dir_path( __FILE__ ) . '../models/class-gtfs-parser.php';
        require_once plugin_dir_path( __FILE__ ) . '../models/class-route-model.php';

        // You can also require stops, trips, fare classes, etc., as needed.
    }

    private function set_locale() {
        $plugin_i18n = new GTFS_Importer_i18n();
        $plugin_i18n->set_domain( $this->get_plugin_name() );
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $plugin_admin = new GTFS_Importer_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // The admin menu page
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

        // Handle form POST for GTFS import
        // This is a custom action we define in the form to handle file uploads
        $this->loader->add_action( 'admin_post_gtfs_import_action', $plugin_admin, 'handle_gtfs_import' );
    }

    private function define_public_hooks() {
        $plugin_public = new GTFS_Importer_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Register shortcodes
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
