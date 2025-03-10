<?php
class GTFS_Importer_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name . '-public',
            plugin_dir_url( __FILE__ ) . 'css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name . '-public',
            plugin_dir_url( __FILE__ ) . 'js/public.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    public function register_shortcodes() {
        add_shortcode( 'gtfs_timetable', array( $this, 'render_timetable_shortcode' ) );
        add_shortcode( 'gtfs_fare_calculator', array( $this, 'render_fare_calculator_shortcode' ) );
    }

    public function render_timetable_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'route'     => '',
            'direction' => 0
        ), $atts, 'gtfs_timetable' );

        ob_start();
        include plugin_dir_path( __FILE__ ) . 'partials/timetable.php';
        return ob_get_clean();
    }

    public function render_fare_calculator_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'origin'      => '',
            'destination' => '',
            'fare_class'  => 'adult'
        ), $atts, 'gtfs_fare_calculator' );

        ob_start();
        include plugin_dir_path( __FILE__ ) . 'partials/fare-calculator.php';
        return ob_get_clean();
    }
}
