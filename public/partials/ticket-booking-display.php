<div class="gtfs-container">
    <h2>BUY TICKET</h2>
    <form id="gtfs-ticket-booking-form" class="gtfs-ticket-booking">
        
        <div class="form-group">
            <label for="gtfs-from-stop"><i class="fas fa-map-marker-alt"></i> From:</label>
            <select id="gtfs-from-stop" name="from_stop">
                <option value="">Please Select</option>
                <?php foreach ($stops as $stop): ?>
                    <option value="<?php echo esc_attr($stop->stop_id); ?>">
                        <?php echo esc_html($stop->stop_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="gtfs-to-stop"><i class="fas fa-map-marker-alt"></i> To:</label>
            <select id="gtfs-to-stop" name="to_stop">
                <option value="">Please Select</option>
                <?php foreach ($stops as $stop): ?>
                    <option value="<?php echo esc_attr($stop->stop_id); ?>">
                        <?php echo esc_html($stop->stop_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="gtfs-journey-date"><i class="fas fa-calendar-alt"></i> Journey Date:</label>
            <input type="date" id="gtfs-journey-date" name="journey_date">
        </div>

        <div class="form-group">
            <label for="gtfs-return-date"><i class="fas fa-calendar-alt"></i> Return Date (Optional):</label>
            <input type="date" id="gtfs-return-date" name="return_date">
        </div>

        <button type="submit" id="gtfs-search-button"><i class="fas fa-search"></i> Search</button>
    </form>

    <div id="gtfs-route-results" class="gtfs-route-results"></div>
</div>
