<?php
class GTFS_API_Connector {

    protected function request($url, $args = array()) {
        $response = wp_remote_get($url, $args);
        if ( is_wp_error($response) ) {
            return null;
        }
        return wp_remote_retrieve_body($response);
    }
}
