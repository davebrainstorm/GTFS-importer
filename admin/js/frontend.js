/**
 * GTFS Importer Frontend JavaScript
 */
(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initTimetables();
        initFareCalculator();
    });
    
    /**
     * Initialize timetable functionality
     */
    function initTimetables() {
        // Add any timetable-specific JavaScript here
        $('.gtfs-timetable-tabs a').on('click', function(e) {
            e.preventDefault();
            
            // Get the target tab
            var target = $(this).attr('href');
            
            // Remove active class from all tabs
            $('.gtfs-timetable-tabs a').removeClass('active');
            $('.gtfs-timetable-tab-content').removeClass('active');
            
            // Add active class to clicked tab and target content
            $(this).addClass('active');
            $(target).addClass('active');
        });
    }
    
    /**
     * Initialize fare calculator functionality
     */
    function initFareCalculator() {
        $('#gtfs-calculate-fare').on('click', function(e) {
            e.preventDefault();
            
            var fromStop = $('#gtfs-from-stop').val();
            var toStop = $('#gtfs-to-stop').val();
            
            if (!fromStop || !toStop) {
                alert('Please select both origin and destination stops.');
                return;
            }
            
            // Show loading state
            $(this).prop('disabled', true).text('Calculating...');
            
            // Make AJAX request to calculate fare
            $.ajax({
                url: gtfs_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'gtfs_calculate_fare',
                    from_stop: fromStop,
                    to_stop: toStop,
                    nonce: gtfs_data.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show fare result
                        $('#gtfs-fare-result').show();
                        $('#gtfs-fare-amount').text(response.data.fare);
                        $('#gtfs-fare-currency').text(response.data.currency);
                        $('#gtfs-fare-details').html(response.data.details || '');
                    } else {
                        alert(response.data.message || 'Failed to calculate fare.');
                    }
                },
                error: function() {
                    alert('An error occurred while calculating the fare.');
                },
                complete: function() {
                    // Reset button state
                    $('#gtfs-calculate-fare').prop('disabled', false).text('Calculate Fare');
                }
            });
        });
    }
    
})(jQuery);
