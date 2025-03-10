<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <?php
    // Load route model
    require_once plugin_dir_path( dirname( __FILE__ ) ) . '../models/class-route-model.php';
    $route_model = new GTFS_Route_Model();
    
    // Get routes with pagination
    $per_page = 20;
    $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $offset = ( $current_page - 1 ) * $per_page;
    
    $routes = $route_model->get_all_routes( array(
        'limit' => $per_page,
        'offset' => $offset
    ) );
    
    $total = $route_model->get_count();
    $total_pages = ceil( $total / $per_page );
    ?>
    
    <p><?php printf( __( 'Showing %d routes of %d total.', 'gtfs-importer' ), count( $routes ), $total ); ?></p>
    
    <?php if ( ! empty( $routes ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'ID', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Route ID', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Route Name', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Type', 'gtfs-importer' ); ?></th>
                    <th><?php _e( 'Color', 'gtfs-importer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $routes as $route ) : ?>
                    <tr>
                        <td><?php echo esc_html( $route->id ); ?></td>
                        <td><?php echo esc_html( $route->route_id ); ?></td>
                        <td><?php echo esc_html( $route->route_name ); ?></td>
                        <td>
                            <?php 
                            if ( isset( $route->route_type ) ) {
                                $type_names = array(
                                    0 => __( 'Tram/Light Rail', 'gtfs-importer' ),
                                    1 => __( 'Subway/Metro', 'gtfs-importer' ),
                                    2 => __( 'Rail', 'gtfs-importer' ),
                                    3 => __( 'Bus', 'gtfs-importer' ),
                                    4 => __( 'Ferry', 'gtfs-importer' ),
                                    5 => __( 'Cable Car', 'gtfs-importer' ),
                                    6 => __( 'Gondola/Suspended Cable Car', 'gtfs-importer' ),
                                    7 => __( 'Funicular', 'gtfs-importer' ),
                                );
                                echo isset( $type_names[$route->route_type] ) ? 
                                    esc_html( $type_names[$route->route_type] ) : 
                                    esc_html( $route->route_type );
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $route->route_color ) ) : ?>
                                <div style="width: 30px; height: 20px; background-color: #<?php echo esc_attr( $route->route_color ); ?>"></div>
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
        <p><?php _e( 'No routes found. Import a GTFS feed to get started.', 'gtfs-importer' ); ?></p>
    <?php endif; ?>
</div>
