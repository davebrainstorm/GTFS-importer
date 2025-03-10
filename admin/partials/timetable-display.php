<?php
/**
 * Template for timetable display
 */
?>
<div class="gtfs-container">
    <div class="gtfs-timetable-route-info">
        <h3>
            <?php if (!empty($route->route_color)): ?>
                <span class="route-type" style="background-color: #<?php echo esc_attr($route->route_color); ?>; color: #<?php echo esc_attr($route->route_text_color ?: 'ffffff'); ?>">
                    <?php echo esc_html($route->route_id); ?>
                </span>
            <?php endif; ?>
            <?php echo esc_html($route->route_name); ?>
        </h3>
        <p><?php echo esc_html(sprintf('Direction: %s', $atts['direction'] == '0' ? 'Outbound' : 'Inbound')); ?></p>
    </div>

    <?php if (!empty($stops)): ?>
        <div class="gtfs-timetable-container">
            <table class="gtfs-timetable">
                <thead class="gtfs-timetable-header">
                    <tr>
                        <th>Stop</th>
                        <th>Arrival Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stops as $stop): ?>
                        <tr>
                            <td><?php echo esc_html($stop->stop_name); ?></td>
                            <td>
                                <?php
                                // In a real implementation, we would get actual times from stop_times.txt
                                // For now, we'll display a placeholder
                                echo 'Schedule information not available';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="gtfs-timetable-note">Note: This is a simplified timetable. Times may vary on weekends and holidays.</p>
        </div>
    <?php else: ?>
        <p>No stop information available for this route.</p>
    <?php endif; ?>
</div>
