<?php
/**
 * Stops management page for GTFS Importer
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <?php
    // Load stop model
    require_once plugin_dir_path( dirname( __FILE__ ) ) . '../models/class-stop-model.php';
    $stop_model = new GTFS_Stop_Model();
    
    // Get stops with pagination
    $per_page = 20;
    $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $offset = ( $current_page - 1 ) * $per_page;
    
    $stops = $stop_model->get_all_stops( array(
        'limit' => $per_page,
        'offset' => $offset
    ) );
    
    $total = $stop_model->get_count();
    $total_pages = ceil( $total / $per_page );
    ?>
    
    <p><?php printf( __( 'Showing %d stops of %d total.', 'gtfs-importer' ), count( $stops ), $total ); ?></p>
    
    <?php if ( ! empty( $stops ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'ID', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Stop ID', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Stop Name', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Latitude', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Longitude', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Map', 'gtfs-importer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $stops as $stop ) : ?>
                    <tr>
                        <td><?php echo esc_html( $stop->id ); ?></td>
                        <td><?php echo esc_html( $stop->stop_id ); ?></td>
                        <td><?php echo esc_html( $stop->stop_name ); ?></td>
                        <td><?php echo isset( $stop->stop_lat ) ? esc_html( $stop->stop_lat ) : ''; ?></td>
                        <td><?php echo isset( $stop->stop_lon ) ? esc_html( $stop->stop_lon ) : ''; ?></td>
                        <td>
                            <?php if ( isset( $stop->stop_lat ) && isset( $stop->stop_lon ) ) : ?>
                                <a href="https://www.google.com/maps?q=<?php echo esc_attr( $stop->stop_lat ); ?>,<?php echo esc_attr( $stop->stop_lon ); ?>" target="_blank">
                                    <?php _e( 'View on Map', 'gtfs-importer' ); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php
        // Pagination
        if ( $total_pages > 1 ) {
            echo '<div class="tablenav-pages">';
            echo paginate_links( array(
                'base' => add_query_arg( 'paged', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'gtfs-importer' ),
                'next_text' => __( '&raquo;', 'gtfs-importer' ),
                'total' => $total_pages,
                'current' => $current_page
            ) );
            echo '</div>';
        }
        ?>
    <?php else : ?>
        <p><?php _e( 'No stops found. Import a GTFS feed to get started.', 'gtfs-importer' ); ?></p>
    <?php endif; ?>
</div>
