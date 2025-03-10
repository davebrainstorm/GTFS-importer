<?php
/**
 * Admin dashboard display for GTFS Importer
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin/partials
 */
?>

<div class="form-field">
    <h3><?php _e('Import Options', 'gtfs-importer'); ?></h3>
    <label class="checkbox-container">
        <input type="checkbox" name="import_routes" value="1" checked />
        <?php _e('Import Routes', 'gtfs-importer'); ?>
    </label>
    <label class="checkbox-container">
        <input type="checkbox" name="import_stops" value="1" checked />
        <?php _e('Import Stops', 'gtfs-importer'); ?>
    </label>
    <label class="checkbox-container">
        <input type="checkbox" name="import_fares" value="1" checked />
        <?php _e('Import Fares', 'gtfs-importer'); ?>
    </label>
    <label class="checkbox-container">
        <input type="checkbox" name="import_trips" value="1" />
        <?php _e('Import Trips (may be large)', 'gtfs-importer'); ?>
    </label>
</div>


<div class="wrap gtfs-importer-admin">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <?php
    // Display success/error messages if present
    if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) {
        $message = isset( $_GET['message'] ) ? urldecode( $_GET['message'] ) : __( 'Operation completed successfully.', 'gtfs-importer' );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
    } elseif ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) {
        $message = isset( $_GET['message'] ) ? urldecode( $_GET['message'] ) : __( 'An error occurred.', 'gtfs-importer' );
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
    }
    ?>
    
    <div class="gtfs-importer-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="#import-tab" class="nav-tab nav-tab-active"><?php _e('Import GTFS', 'gtfs-importer'); ?></a>
            <a href="#status-tab" class="nav-tab"><?php _e('Import Status', 'gtfs-importer'); ?></a>
            <a href="#help-tab" class="nav-tab"><?php _e('Help', 'gtfs-importer'); ?></a>
        </h2>
        
        <div id="import-tab" class="tab-content active">
            <div class="card">
                <h2><?php _e('Upload GTFS Feed', 'gtfs-importer'); ?></h2>
                <p><?php _e('Upload a GTFS ZIP file to import transit data into your WordPress site.', 'gtfs-importer'); ?></p>
                
                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php?action=gtfs_import_action') ); ?>" enctype="multipart/form-data" class="gtfs-import-form">
                    <?php wp_nonce_field( 'gtfs_import_nonce_action', 'gtfs_import_nonce' ); ?>
                    
                    <div class="form-field">
                        <label for="gtfs_zip"><strong><?php _e('GTFS ZIP File:', 'gtfs-importer'); ?></strong></label>
                        <input type="file" name="gtfs_zip" id="gtfs_zip" accept=".zip" required />
                        <p class="description"><?php _e('Upload a valid GTFS feed in ZIP format.', 'gtfs-importer'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <h3><?php _e('Import Options', 'gtfs-importer'); ?></h3>
                        <label class="checkbox-container">
                            <input type="checkbox" name="import_routes" value="1" checked />
                            <?php _e('Import Routes', 'gtfs-importer'); ?>
                        </label>
                        <label class="checkbox-container">
                            <input type="checkbox" name="import_stops" value="1" checked />
                            <?php _e('Import Stops', 'gtfs-importer'); ?>
                        </label>
                        <label class="checkbox-container">
                            <input type="checkbox" name="import_trips" value="1" />
                            <?php _e('Import Trips (may be large)', 'gtfs-importer'); ?>
                        </label>
                    </div>
                    
                    <?php submit_button( __('Upload & Import GTFS', 'gtfs-importer'), 'primary', 'submit', true, array('id' => 'gtfs-import-submit') ); ?>
                </form>
            </div>
        </div>
        
        <div id="status-tab" class="tab-content">
            <div class="card">
                <h2><?php _e('Import Statistics', 'gtfs-importer'); ?></h2>
                
                <?php
                // Get counts from database
                require_once plugin_dir_path( dirname( __FILE__ ) ) . '../models/class-route-model.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . '../models/class-stop-model.php';
                
                $route_model = new GTFS_Route_Model();
                $stop_model = new GTFS_Stop_Model();
                
                $route_count = $route_model->get_count();
                $stop_count = $stop_model->get_count();
                ?>
                
                <div class="gtfs-stats-container">
                    <div class="gtfs-stat-box">
                        <h3><?php _e('Routes', 'gtfs-importer'); ?></h3>
                        <div class="stat-number"><?php echo esc_html($route_count); ?></div>
                    </div>
                    
                    <div class="gtfs-stat-box">
                        <h3><?php _e('Stops', 'gtfs-importer'); ?></h3>
                        <div class="stat-number"><?php echo esc_html($stop_count); ?></div>
                    </div>
                </div>
                
                <h3><?php _e('Recent Imports', 'gtfs-importer'); ?></h3>
                
                <?php
                // Get recent imports
                $imports = get_option('gtfs_importer_history', array());
                
                if (empty($imports)) {
                    echo '<p>' . __('No import history available.', 'gtfs-importer') . '</p>';
                } else {
                    echo '<table class="wp-list-table widefat fixed striped">';
                    echo '<thead><tr>';
                    echo '<th>' . __('Date', 'gtfs-importer') . '</th>';
                    echo '<th>' . __('Filename', 'gtfs-importer') . '</th>';
                    echo '<th>' . __('Routes', 'gtfs-importer') . '</th>';
                    echo '<th>' . __('Stops', 'gtfs-importer') . '</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($imports as $import) {
                        echo '<tr>';
                        echo '<td>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $import['time'])) . '</td>';
                        echo '<td>' . esc_html($import['filename']) . '</td>';
                        echo '<td>' . esc_html($import['counts']['routes']) . '</td>';
                        echo '<td>' . esc_html($import['counts']['stops']) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                }
                ?>
            </div>
        </div>
        
        <div id="help-tab" class="tab-content">
            <div class="card">
                <h2><?php _e('GTFS Importer Help', 'gtfs-importer'); ?></h2>
                
                <h3><?php _e('What is GTFS?', 'gtfs-importer'); ?></h3>
                <p><?php _e('The General Transit Feed Specification (GTFS) is a data specification that allows public transit agencies to publish their transit data in a format that can be consumed by a wide variety of software applications.', 'gtfs-importer'); ?></p>
                
                <h3><?php _e('How to use this plugin', 'gtfs-importer'); ?></h3>
                <ol>
                    <li><?php _e('Obtain a GTFS feed from a transit agency or create your own.', 'gtfs-importer'); ?></li>
                    <li><?php _e('Upload the GTFS ZIP file using the form on the Import tab.', 'gtfs-importer'); ?></li>
                    <li><?php _e('Select which components you want to import.', 'gtfs-importer'); ?></li>
                    <li><?php _e('Click "Upload & Import GTFS" to begin the import process.', 'gtfs-importer'); ?></li>
                    <li><?php _e('Use the [gtfs_timetable] and [gtfs_fare_calculator] shortcodes to display transit information on your site.', 'gtfs-importer'); ?></li>
                </ol>
                
                <h3><?php _e('Shortcodes', 'gtfs-importer'); ?></h3>
                <p><code>[gtfs_timetable route="12" direction="0"]</code> - <?php _e('Displays a timetable for the specified route.', 'gtfs-importer'); ?></p>
                <p><code>[gtfs_fare_calculator]</code> - <?php _e('Displays a fare calculator form.', 'gtfs-importer'); ?></p>
            </div>
        </div>
    </div>
</div>
