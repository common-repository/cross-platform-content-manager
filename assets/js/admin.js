jQuery(document).ready(function($) {
    // tabs
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        var tab = $(this).attr('href');
        $('.tab-pane').removeClass('active');
        $(tab).addClass('active');
    });

    // add remote site
    $('#add-child-site-form').submit(function(e) {
        e.preventDefault();

        var formData = {
            'icross_remote_sites': [
                {
                    'name': $('#new_site_name').val(),
                    'username': $('#new_username').val(),
                    'url': $('#new_url').val(),
                    'app_password': $('#new_app_password').val()
                }
            ],
            'icross_settings_nonce': $('#icross_settings_nonce').val(),
            'action': 'save_remote_site'
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('An error occurred: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to send request.');
            }
        });
    });

    // remove remote site
    $(document).on('click', '.remove-site', function() {
        var button = $(this);
        var index = button.data('index');
    
        if (confirm('Are you sure you want to remove this site?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'remove_remote_site',
                    nonce: myAjaxObject.nonce,
                    index: index
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        console.log('Error: ', response);
                        alert('Error: Could not delete site');
                    }
                },
                error: function() {
                    alert('Error: Request failed');
                }
            });
        }
    });
    
    $('#copy-logs-button').on('click', function() {
        var copyText = $('#logs-content').val();

        navigator.clipboard.writeText(copyText).then(function() {
            alert("Logs was copied!");
        }).catch(function(error) {
            alert('Error in copying: ' + error);
        });
    });

    $('#clear-logs-button').on('click', function() {
        if (confirm('Are you sure you want to clear the logs?')) {
            $.post(myAjaxObject.ajax_url, {
                action: 'clear_logs', 
                nonce: myAjaxObject.clear_logs_nonce
            }, function(response) {
                if (response.success) {
                    $('#logs-content').val('');
                    alert('Logs cleared successfully.');
                } else {
                    alert('Error: ' + response.data);
                }
            }).fail(function() {
                alert('Request error');
            });
        }
    });
});
