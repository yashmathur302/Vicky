$(document).ready(function() {
    let typingTimer;                
    let doneTypingInterval = 300;  

    // GENERIC AJAX FILTERING FOR ALL MODULES
    // Any input with class '.ajax-filter-input' will trigger this
    $('.ajax-filter-input').on('keyup change', function () {
        clearTimeout(typingTimer);
        let $container = $(this).closest('.ajax-filter-container'); // Wrap your filters in this class
        typingTimer = setTimeout(function() { fetchFilteredData($container); }, doneTypingInterval);
    });

    function fetchFilteredData($container) {
        let ajaxUrl = $container.data('url'); // <div class="ajax-filter-container" data-url="{{ route(...) }}">
        
        // Collect all filter values automatically within this container
        let dataPayload = {};
        $container.find('.ajax-filter-input').each(function() {
            let fieldName = $(this).attr('name') || $(this).attr('id');
            dataPayload[fieldName] = $(this).val();
        });

        $.ajax({
            url: ajaxUrl,
            type: "GET",
            data: dataPayload,
            success: function(response) {
                // Dynamically replace table body and pagination
                // Wrap your table body in <tbody class="ajax-table-body">
                $('.ajax-table-body').html($(response).find('.ajax-table-body').html());
                $('.ajax-pagination').html($(response).find('.ajax-pagination').html());
            },
            error: function() {
                if (typeof showToastMessage === "function") {
                    showToastMessage('error', "Failed to load filtered data.");
                }
            }
        });
    }
});