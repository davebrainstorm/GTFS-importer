<?php
class GTFS_Fare_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'gtfs_fare_widget',
            __('GTFS Fare Widget', 'gtfs-importer'),
            array('description' => __('Displays a GTFS fare calculator.', 'gtfs-importer'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[gtfs_fare_calculator]');
        echo $args['after_widget'];
    }

    public function form($instance) {
        // Admin form for widget settings
    }
}
