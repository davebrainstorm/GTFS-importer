<?php
class GTFS_Timetable_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'gtfs_timetable_widget',
            __('GTFS Timetable Widget', 'gtfs-importer'),
            array('description' => __('Displays a GTFS timetable.', 'gtfs-importer'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        // Reuse the shortcode
        echo do_shortcode('[gtfs_timetable]');
        echo $args['after_widget'];
    }

    public function form($instance) {
        // Admin form for widget settings
    }
}
