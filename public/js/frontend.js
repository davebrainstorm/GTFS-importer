(function ($) {
    $(document).ready(function () {
        
        $('#gtfs-ticket-booking-form').on('submit', function (e) {
            e.preventDefault();

            const data = {
                action: 'gtfs_get_routes',
                nonce: gtfs_data.nonce,
                from_stop: $('#gtfs-from-stop').val(),
                to_stop: $('#gtfs-to-stop').val(),
                journey_date: $('#gtfs-journey-date').val(),
                return_date: $('#gtfs-return-date').val()
            };

            $.post(gtfs_data.ajax_url, data, function (response) {
                if (response.success) {
                    const routes = response.data.routes;
                    let html = '<ul>';
                    routes.forEach(route => {
                        html += `<li>${route.operator} - ${route.departure_time} to ${route.arrival_time} - ${route.fare}</li>`;
                    });
                    html += '</ul>';
                    $('#gtfs-route-results').html(html);
                } else {
                    alert(response.data.message);
                }
            });
            
         });
         // Fare calculator functionality
$('#gtfs-fare-form').on('submit', function(e) {
    e.preventDefault();
    
    const fromStop = $('#gtfs-from-stop').val();
    const toStop = $('#gtfs-to-stop').val();
    const route = $('#gtfs-route').val();
    
    if (!fromStop || !toStop) {
        alert('Please select both origin and destination stops.');
        return;
    }
    
    // Show loading state
    $('#gtfs-calculate-fare').prop('disabled', true).text('Calculating...');
    
    // Make AJAX request to calculate fare
    $.ajax({
        url: gtfs_data.ajax_url,
        type: 'POST',
        data: {
            action: 'gtfs_calculate_fare',
            from_stop: fromStop,
            to_stop: toStop,
            route: route,
            nonce: gtfs_data.nonce
        },
        success: function(response) {
            if (response.success) {
                // Show fare result
                $('#gtfs-fare-result').show();
                $('#gtfs-fare-price').text(response.data.price);
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

        
     });
})(jQuery);
