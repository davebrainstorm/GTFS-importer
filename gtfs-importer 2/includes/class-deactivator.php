<?php
class GTFS_Importer_Deactivator {
    public static function deactivate() {
        // Example: remove scheduled cron jobs if any
        wp_clear_scheduled_hook( 'gtfs_importer_cron_hook' );
    }
}
