<?php
/**
 * Settings page display for GTFS Importer
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Display success/error messages if present
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'gtfs-importer') . '</p></div>';
    }
    ?>
    
    <div class="card">
        <h2><?php _e('GTFS Import Settings', 'gtfs-importer'); ?></h2>
        <p><?php _e('Configure how the GTFS data import works.', 'gtfs-importer'); ?></p>
        
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting
            settings_fields('gtfs_importer_settings');
            ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Auto-Update Frequency', 'gtfs-importer'); ?></th>
                    <td>
                        <select name="gtfs_auto_update_frequency" id="gtfs_auto_update_frequency">
                            <option value="never" <?php selected(get_option('gtfs_auto_update_frequency', 'never'), 'never'); ?>>
                                <?php _e('Never (manual updates only)', 'gtfs-importer'); ?>
                            </option>
                            <option value="daily" <?php selected(get_option('gtfs_auto_update_frequency'), 'daily'); ?>>
                                <?php _e('Daily', 'gtfs-importer'); ?>
                            </option>
                            <option value="weekly" <?php selected(get_option('gtfs_auto_update_frequency'), 'weekly'); ?>>
                                <?php _e('Weekly', 'gtfs-importer'); ?>
                            </option>
                            <option value="monthly" <?php selected(get_option('gtfs_auto_update_frequency'), 'monthly'); ?>>
                                <?php _e('Monthly', 'gtfs-importer'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Remote GTFS URL', 'gtfs-importer'); ?></th>
                    <td>
                        <input type="url" name="gtfs_remote_url" id="gtfs_remote_url" class="regular-text" 
                               placeholder="https://example.com/gtfs.zip" 
                               value="<?php echo esc_attr(get_option('gtfs_remote_url', '')); ?>" />
                        <p class="description"><?php _e('URL to a GTFS feed for automatic updates (optional)', 'gtfs-importer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Data Management', 'gtfs-importer'); ?></th>
                    <td>
                        <fieldset>
                            <label for="gtfs_clear_on_import">
                                <input type="checkbox" name="gtfs_clear_on_import" id="gtfs_clear_on_import" 
                                       value="1" <?php checked(get_option('gtfs_clear_on_import', '1'), '1'); ?> />
                                <?php _e('Clear existing data before import', 'gtfs-importer'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <div class="card">
        <h2><?php _e('Debugging', 'gtfs-importer'); ?></h2>
        <p><?php _e('If you are having trouble with imports, check these settings:', 'gtfs-importer'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('PHP Version', 'gtfs-importer'); ?></th>
                <td><?php echo PHP_VERSION; ?> (<?php echo PHP_VERSION_ID < 70200 ? '<span style="color:red">Minimum 7.2 recommended</span>' : '<span style="color:green">OK</span>'; ?>)</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Memory Limit', 'gtfs-importer'); ?></th>
                <td><?php echo ini_get('memory_limit'); ?> (<?php echo intval(ini_get('memory_limit')) < 128 ? '<span style="color:orange">128M+ recommended</span>' : '<span style="color:green">OK</span>'; ?>)</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Max Execution Time', 'gtfs-importer'); ?></th>
                <td><?php echo ini_get('max_execution_time'); ?>s (<?php echo intval(ini_get('max_execution_time')) < 60 && intval(ini_get('max_execution_time')) != 0 ? '<span style="color:orange">60s+ recommended</span>' : '<span style="color:green">OK</span>'; ?>)</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Max Upload Size', 'gtfs-importer'); ?></th>
                <td><?php echo ini_get('upload_max_filesize'); ?> (<?php echo intval(ini_get('upload_max_filesize')) < 10 ? '<span style="color:orange">10M+ recommended</span>' : '<span style="color:green">OK</span>'; ?>)</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('ZipArchive Extension', 'gtfs-importer'); ?></th>
                <td><?php echo class_exists('ZipArchive') ? '<span style="color:green">Available</span>' : '<span style="color:red">Not available - required for GTFS imports</span>'; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Error Log', 'gtfs-importer'); ?></th>
                <td>
                    <?php
                    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                        echo '<span style="color:green">Enabled</span> - Check your WordPress debug.log file for GTFS import messages';
                    } else {
                        echo '<span style="color:orange">Not enabled</span> - Add the following to wp-config.php for better debugging:';
                        echo '<pre>define(\'WP_DEBUG\', true);<br>define(\'WP_DEBUG_LOG\', true);<br>define(\'WP_DEBUG_DISPLAY\', false);</pre>';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
