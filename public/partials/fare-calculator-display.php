<div class="gtfs-container">
    <div class="gtfs-fare-calculator">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <form id="gtfs-fare-form">
            <div class="form-group">
                <label for="gtfs-from-stop"><?php _e('Origin:', 'gtfs-importer'); ?></label>
                <select id="gtfs-from-stop" name="from_stop" required>
                    <option value=""><?php _e('-- Select origin --', 'gtfs-importer'); ?></option>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?php echo esc_attr($stop->stop_id); ?>">
                            <?php echo esc_html($stop->stop_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gtfs-to-stop"><?php _e('Destination:', 'gtfs-importer'); ?></label>
                <select id="gtfs-to-stop" name="to_stop" required>
                    <option value=""><?php _e('-- Select destination --', 'gtfs-importer'); ?></option>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?php echo esc_attr($stop->stop_id); ?>">
                            <?php echo esc_html($stop->stop_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gtfs-route"><?php _e('Route (Optional):', 'gtfs-importer'); ?></label>
                <select id="gtfs-route" name="route">
                    <option value=""><?php _e('-- Any route --', 'gtfs-importer'); ?></option>
                    <?php foreach ($routes as $route): ?>
                        <option value="<?php echo esc_attr($route->route_id); ?>">
                            <?php echo esc_html($route->route_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" id="gtfs-calculate-fare"><?php echo esc_html($atts['button_text']); ?></button>
        </form>
        
        <div id="gtfs-fare-result" class="gtfs-fare-result" style="display: none;">
            <h4><?php _e('Estimated Fare:', 'gtfs-importer'); ?></h4>
            <div class="gtfs-fare-amount">
                <span id="gtfs-fare-currency"></span><span id="gtfs-fare-price"></span>
            </div>
            <div id="gtfs-fare-details"></div>
        </div>
    </div>
</div>
