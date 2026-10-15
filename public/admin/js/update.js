

$(document).ready(function() {

    //Event Update JS - AJAX Form Submission
    $('#updateEventForm').on('submit', function(e) {


        e.preventDefault(); 

        let form = $(this);

        let submitBtn = $('#submitBtn'); 

        let formData = new FormData(this);



        let sDate = formData.get('start_date');

        let eDate = formData.get('end_date');

        let sTime = formData.get('start_time');

        let eTime = formData.get('end_time');



        // --- SMART TIME VALIDATION --- //

        if (eDate && !eTime) {

            showToastMessage('error', "Please provide an end time if you have selected an end date.");

            return false; 

        }



        if (sDate && eDate && (sDate === eDate)) {

            if (sTime && eTime && (eTime <= sTime)) {

                showToastMessage('error', "Event end time is incorrect. It cannot end before or exactly when it starts on the same day.");

                return false; 

            }

        }

        // Check karein ki kya user ne nayi cover image select ki hai
        let hasNewFiles = $('#cover_image')[0].files.length > 0;

        // Processing state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                
                if (hasNewFiles) {
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            
                            // Progress bar show karein
                            $('.upload-progress-container').removeClass('d-none');
                            
                            // Width aur text update karein
                            $('.upload-progress-bar')
                                .css('width', percentComplete + '%')
                                .attr('aria-valuenow', percentComplete)
                                .text(percentComplete + '%');
                                
                            $('.upload-status-text').text('Uploading image...');
                            
                            // 100% upload hone ke baad processing message
                            if (percentComplete === 100) {
                                $('.upload-status-text').text('Processing on Server...');
                                $('.upload-progress-bar')
                                    .removeClass('bg-success')
                                    .addClass('bg-info')
                                    .text('100% (Processing)');
                            }
                        }
                    }, false);
                }
                return xhr;
            },
            
            success: function(response) {
                if(response.redirect) { 
                    // Success state update
                    $('.upload-status-text').text('Completed!');
                    $('.upload-progress-bar').removeClass('bg-info').addClass('bg-success');
                    window.location.href = response.redirect; 
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text('Update Event'); 
                
                // Error aane par progress bar hide/reset karein
                $('.upload-progress-container').addClass('d-none');
                $('.upload-progress-bar').css('width', '0%').removeClass('bg-info').addClass('bg-success');
                
                if(xhr.status === 422) {
                    showToastMessage('error', xhr.responseJSON.message);
                } else {
                    showToastMessage('error', 'Something went wrong. Please try again.');
                }
            }
        });

    });

    //Users Update JS
    if ($('#updateUserForm').length) {

        let tagifyInterests, tagifyEducation;

        if (document.querySelector('#interests')) {

            tagifyInterests = new Tagify(document.querySelector('#interests'), {

                originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),

                addTagOnBlur: false 

            });

        }

        

        if (document.querySelector('#education')) {

            tagifyEducation = new Tagify(document.querySelector('#education'), {

                originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),

                addTagOnBlur: false 

            });

        }



        $('#updateUserForm').on('submit', function(e) {

            e.preventDefault(); 



            if (tagifyInterests && tagifyInterests.state.inputText) {

                tagifyInterests.addTags(tagifyInterests.state.inputText);

            }

            if (tagifyEducation && tagifyEducation.state.inputText) {

                tagifyEducation.addTags(tagifyEducation.state.inputText);

            }



            let form = $(this);

            let submitBtn = $('#submitBtn');

            let formData = new FormData(this);

            

            // Check karein ki kya user ne sach me koi nayi image select ki hai

            let hasNewFiles = $('#profile_picture')[0].files.length > 0 || $('#cover_image')[0].files.length > 0;



            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');



            $.ajax({

                url: form.attr('action'),

                type: "POST",

                data: formData,

                contentType: false,

                processData: false,

                

                xhr: function() {

                    let xhr = new window.XMLHttpRequest();

                    

                    if (hasNewFiles) {

                        xhr.upload.addEventListener("progress", function(evt) {

                            if (evt.lengthComputable) {

                                // Live Percentage calculate karein

                                let percentComplete = Math.round((evt.loaded / evt.total) * 100);

                                

                                // Progress container show karein

                                $('.upload-progress-container').removeClass('d-none');

                                

                                // Bar ki width badhayein aur percentage TEXT uske andar daalein

                                $('.upload-progress-bar')

                                    .css('width', percentComplete + '%')

                                    .attr('aria-valuenow', percentComplete)

                                    .text(percentComplete + '%'); // Bar ke andar percentage dikhega

                                    

                                $('.upload-status-text').text('Uploading image...');

                                

                                // Jab 100% upload ho jaye

                                if (percentComplete === 100) {

                                    $('.upload-status-text').text('Processing on Server...');

                                    $('.upload-progress-bar')

                                        .removeClass('bg-success')

                                        .addClass('bg-info')

                                        .text('100% (Processing)');

                                }

                            }

                        }, false);

                    }

                    return xhr;

                },

                success: function(response) {

                    if(response.redirect) {

                        // Success hone par complete state dikhayein

                        $('.upload-progress-text').text('Completed!');

                        $('.upload-progress-bar').removeClass('bg-info').addClass('bg-success');

                        window.location.href = response.redirect;

                    }

                },

                error: function(xhr) {

                    submitBtn.prop('disabled', false).text('Save Changes');

                    

                    // ERROR EDGE CASE: Agar error aaye toh progress bar ko wapas hide aur reset kar dein

                    $('.upload-progress-container').addClass('d-none');

                    $('.upload-progress-bar').css('width', '0%').removeClass('bg-info').addClass('bg-success');

                    

                    if(xhr.status === 422) {

                        showToastMessage('error', xhr.responseJSON.message);

                    } else {

                        showToastMessage('error', 'Something went wrong. Please try again.');

                    }

                }

            });

        });

        let loginHistoryContainer = $('#login-history-table-container');
        
        if (loginHistoryContainer.length) {
            let historyUrl = window.location.href.split('#')[0]; 
            let isPolling = false;

            $(document).on('click', '#login-history-table-container .pagination a', function(e) {
                historyUrl = $(this).attr('href');
            });

            setInterval(function() {
                
                if (($('#security-tab').hasClass('active') || $('#security').hasClass('active')) && !isPolling) {
                    isPolling = true;

                    $.ajax({
                        url: historyUrl,
                        type: 'GET',
                        global: false,
                        cache: false, // Ye browser ko force karega ki purana cache use na kare aur har baar server se fresh naya data laye!
                        success: function(response) {
                            
                            // NATIVE DOM PARSER. Ye poore HTML page ko bina tode memory me load karta hai aur perfectly exact ID uthata hai.
                            let parser = new DOMParser();
                            let doc = parser.parseFromString(response, "text/html");
                            
                            let targetContainer = doc.querySelector('#login-history-table-container');
                            if (targetContainer && targetContainer.innerHTML.trim() !== '') {
                                loginHistoryContainer.html(targetContainer.innerHTML);
                            }

                            let newLastLogin = doc.querySelector('#last-login-text');
                            if (newLastLogin) {
                                $('#last-login-text').html(newLastLogin.innerHTML);
                            }
                        },
                        complete: function() {
                            isPolling = false; 
                        }
                    });
                }
            }, 5000); 
        }

    }

});