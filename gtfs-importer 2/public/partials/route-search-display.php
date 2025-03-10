<div class="gtfs-container">
    <form id="gtfs-route-search-form" class="gtfs-route-search">
        <div class="form-group">
            <label for="gtfs-from-stop">From:</label>
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
            <label for="gtfs-to-stop">To:</label>
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
            <label for="gtfs-journey-date">Journey Date:</label>
            <input type="date" id="gtfs-journey-date" name="journey_date">
        </div>

        <button type="submit" id="gtfs-search-button">Search</button>
    </form>

    <div id="gtfs-route-results" class="gtfs-route-results"></div>
</div>
