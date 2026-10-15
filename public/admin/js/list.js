// Events List JS 

$(document).ready(function() {

    let typingTimer;                

    let doneTypingInterval = 300;  



    // EVENTS list js

    if ($('#event-table-body').length) {

        $('.filter-input').on('keyup change', function () {

            clearTimeout(typingTimer);

            typingTimer = setTimeout(fetchEvents, doneTypingInterval);

        });



        function fetchEvents() {

            let search_title = $('#search_title').val();

            let category_id = $('#filter_category').val();

            let status = $('#filter_status').val();

            let ajaxUrl = $('#event-filters-container').data('url'); // Events ka URL



            $.ajax({

                url: ajaxUrl,

                type: "GET",

                data: { search_title: search_title, category_id: category_id, status: status },

                success: function(response) {

                    $('#event-table-body').html($(response).find('#event-table-body').html());

                    $('#pagination-container').html($(response).find('#pagination-container').html());

                },

                error: function() { showToastMessage('error', "Failed to load filtered events."); }

            });

        }

    }



    // USERS list js

    if ($('#user-table-body').length) {

        $('.filter-input').on('keyup', function () {

            clearTimeout(typingTimer);

            typingTimer = setTimeout(fetchUsers, doneTypingInterval);
            // typingTimer = setTimeout(fetchUsers); // for instant filtering 

        });



        function fetchUsers() {

            let search_name = $('#search_name').val();

            let search_username = $('#search_username').val();

            let ajaxUrl = $('#users-filters-container').data('url'); // Users ka URL



            $.ajax({

                url: ajaxUrl,

                type: "GET",

                data: { search_name: search_name, search_username: search_username },

                success: function(response) {

                    $('#user-table-body').html($(response).find('#user-table-body').html());

                    $('#pagination-container').html($(response).find('#pagination-container').html());

                },

                error: function() { showToastMessage('error', "Failed to load filtered users."); }

            });

        }

    }

    // BANNED USERS LIST JS
    if ($('#banned-user-table-body').length) {
        
        // Keyup event par typing timer trigger hoga
        $('.filter-input').on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(fetchBannedUsers, doneTypingInterval);
        });

        function fetchBannedUsers() {
            // Nayi input ID se data fetch kar rahe hain
            let search_name = $('#search_banned_name').val();
            let ajaxUrl = $('#banned-users-filters-container').data('url');

            $.ajax({
                url: ajaxUrl,
                type: "GET",
                // search_name key bhej rahe hain, taaki controller ka purana filter block exact match kare
                data: { search_name: search_name },
                success: function(response) {
                    $('#banned-user-table-body').html($(response).find('#banned-user-table-body').html());
                    $('#pagination-container').html($(response).find('#pagination-container').html());
                },
                error: function() { showToastMessage('error', "Failed to load filtered banned users."); }
            });
        }
    }



    //Category List JS

    if ($('#category-table-body').length) {

        $('.filter-input').on('keyup', function () {

            clearTimeout(typingTimer); 

            typingTimer = setTimeout(fetchCategories, doneTypingInterval); 

        });



        function fetchCategories() {

            let search_name = $('#search_name').val();

            let ajaxUrl = $('#categories-filters-container').data('url'); // Users ka URL



            $.ajax({

                url: ajaxUrl,

                type: "GET",

                data: { search_name: search_name, },

                success: function(response) {

                    $('#category-table-body').html($(response).find('#category-table-body').html());

                    $('#pagination-container').html($(response).find('#pagination-container').html());

                },

                error: function() { showToastMessage('error', "Failed to load filtered categories."); }

            });

        }



        // 3. Create Form AJAX Submission

        $('#createCategoryForm').on('submit', function(e) {

            e.preventDefault(); 

            

            let $btn = $('#saveCategoryBtn');

            let form = $(this);



            $.ajax({

                url: form.attr('action'),

                type: 'POST',

                data: form.serialize(),

                beforeSend: function() {

                    $btn.prop('disabled', true).text('Saving...');

                },

                success: function(response) {

                    if(response.status === 'success') {

                        // Reload page to show new data

                        window.location.href = response.redirect || window.location.href; 

                        // window.location.reload();

                    }

                },

                error: function(xhr) {

                    $btn.prop('disabled', false).text('Save');

                    if (xhr.status === 422) {

                        showToastMessage('warning', xhr.responseJSON.message); 

                    } else {

                        showToastMessage('error', "Something went wrong. Please try again.");

                    }

                }

            });

        });



        // 4. Populate Update Modal with Data

        // Using delegation $(document).on() so it works even after AJAX filtering

        $(document).on('click', '.edit-category-btn', function() {

            let actionUrl = $(this).data('action');

            let name = $(this).data('name');

            let status = $(this).data('status');



            // Set Form Data

            $('#updateCategoryForm').attr('action', actionUrl);

            $('#update_name').val(name);

            $('#update_status').val(status);



            // Open Modal

            $('#updateCategoryModal').modal('show');

        });



        // 5. Update Form AJAX Submission

        $('#updateCategoryForm').on('submit', function(e) {

            e.preventDefault(); 

            

            let $btn = $('#updateCategoryBtn');

            let form = $(this);



            $.ajax({

                url: form.attr('action'),

                type: 'POST',

                data: form.serialize(),

                beforeSend: function() {

                    $btn.prop('disabled', true).text('Updating...');

                },

                success: function(response) {

                    if(response.status === 'success') {

                        window.location.reload();

                    }

                },

                error: function(xhr) {

                    $btn.prop('disabled', false).text('Update');

                    

                    // ALERT ko hata kar TOAST lagaya gaya hai UX consistency ke liye

                    if (xhr.status === 422) {

                        showToastMessage('warning', xhr.responseJSON.message);

                    } else {

                        showToastMessage('error', "Something went wrong. Please try again.");

                    }

                }

                // error: function(xhr) {

                //     $btn.prop('disabled', false).text('Update');

                //     if (xhr.status === 422) {

                //         alert(xhr.responseJSON.message);

                //     } else {

                //         alert("Something went wrong. Please try again.");

                //     }

                // }

            });

        });



        // 6. Modal Clear Logic

        $('#createCategoryModal').on('hidden.bs.modal', function () {

            $('#createCategoryForm')[0].reset();

            $('#saveCategoryBtn').prop('disabled', false).text('Save');

        });



        $('#updateCategoryModal').on('hidden.bs.modal', function () {

            $('#updateCategoryForm')[0].reset();

            $('#updateCategoryBtn').prop('disabled', false).text('Update');

        });

    }

});