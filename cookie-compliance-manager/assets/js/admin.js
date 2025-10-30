/**
 * Cookie Compliance Manager - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color pickers
        $('.ccm-color-picker').wpColorPicker();

        // Export logs functionality
        $('#ccm-export-csv').on('click', function() {
            // This will be handled by the server
            window.location.href = ajaxurl + '?action=ccm_export_logs';
        });
    });

})(jQuery);
