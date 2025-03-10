/**
 * GTFS Importer Admin JavaScript
 */
(function( $ ) {
    'use strict';

    $(function() {
        // Tab navigation
        $('.gtfs-importer-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            
            // Update active tab
            $('.gtfs-importer-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show the selected tab content
            var target = $(this).attr('href');
            $('.gtfs-importer-tabs .tab-content').removeClass('active');
            $(target).addClass('active');
        });
        
        // File upload validation
        $('#gtfs_zip').on('change', function() {
            var file = this.files[0];
            var fileType = file.name.split('.').pop().toLowerCase();
            
            if (fileType !== 'zip') {
                alert(gtfs_importer_admin.messages.zip_only);
                $(this).val('');
                return false;
            }
            
            // 20MB max size by default (can be filtered in PHP)
            var maxSize = 20 * 1024 * 1024; // 20MB in bytes
            if (file.size > maxSize) {
                alert(gtfs_importer_admin.messages.file_too_large);
                $(this).val('');
                return false;
            }
        });
        
        // Form submission handling
        $('.gtfs-import-form').on('submit', function() {
            // Ensure at least one import option is selected
            if (!$('input[name="import_routes"]').is(':checked') && 
                !$('input[name="import_stops"]').is(':checked') && 
                !$('input[name="import_trips"]').is(':checked')) {
                
                alert(gtfs_importer_admin.messages.select_import_option);
                return false;
            }
            
            // Disable submit button and show loading state
            $('#gtfs-import-submit').prop('disabled', true)
                                    .val(gtfs_importer_admin.messages.importing);
        });
    });

})( jQuery );
