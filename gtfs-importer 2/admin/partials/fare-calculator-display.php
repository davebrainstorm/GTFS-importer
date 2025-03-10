<?php
/**
 * Template for fare calculator display
 */
?>
<div class="gtfs-container">
    <div class="gtfs-fare-calculator">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <form id="gtfs-fare-form">
            <div class="form-group">
                <label for="gtfs-from-stop">Origin:</label>
                <select id="gtfs-from-stop" name="from_stop">
                    <option value="">-- Select origin --</option>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?php echo esc_attr($stop->stop_id); ?>">
                            <?php echo esc_html($stop->stop_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gtfs-to-stop">Destination:</label>
                <select id="gtfs-to-stop" name="to_stop">
                    <option value="">-- Select destination --</option>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?php echo esc_attr($stop->stop_id); ?>">
                            <?php echo esc_html($stop->stop_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" id="gtfs-calculate-fare"><?php echo esc_html($atts['button_text']); ?></button>
        </form>
        
        <div id="gtfs-fare-result" class="gtfs-fare-result">
            <p>Estimated fare: <span id="gtfs-fare-currency">€</span><span id="gtfs-fare-amount">0.00</span></p>
            <div id="gtfs-fare-details"></div>
        </div>
    </div>
</div>
