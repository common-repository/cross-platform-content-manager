jQuery(document).ready(function($) {
    // tabs
    jQuery(document).ready(function($) {
        $('.nav-tab').click(function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            var tab = $(this).attr('href');
            $('.tab-pane').removeClass('active');
            $(tab).addClass('active');
        });
    });    

    var childSitesWrapper = $('#childSitesWrapper');
    var maxChildSites = 3;

    $('#addChildSite').on('click', function() {
        if (childSitesWrapper.find('.child__list-item').length >= maxChildSites) {
            alert('You have maximum allowed sites count (3).');
            return;
        }

        var newChildSiteInput = $('<div>').addClass('child__list-item').html(
            '<div class="field-group">' +
                '<label>Site Name:</label>' +
                '<input type="text" name="child_sites[new][name]">' +
            '</div>' +
            '<div class="field-group">' +
                '<label>User Name:</label>' +
                '<input type="text" name="child_sites[new][username]">' +
            '</div>' +
            '<div class="field-group">' +
                '<label>Child Site URL:</label>' +
                '<input type="text" name="child_sites[new][url]">' +
            '</div>' +
            '<div class="field-group">' +
                '<label>Application Password:</label>' +
                '<input type="text" name="child_sites[new][app_password]">' +
            '</div>' +
            '<button type="button" class="remove-site button">Remove</button>'
        );

        childSitesWrapper.append(newChildSiteInput);
    });

    // remove child site
    childSitesWrapper.on('click', '.remove-site', function() {

        var childListItem = $(this).closest('.child__list-item');
        var index = $(this).data('index');

        if (typeof index !== 'undefined') {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'remove_child_site',
                    nonce: myAjaxObject.nonce,
                    index: index
                },
                success: function(response) {
                    if (response.success) {
                        childListItem.remove();
                    } else {
                        console.log('Error: ', response);
                        alert('Error: Could not delete site');
                    }
                }
            });
        } else {
            childListItem.remove();
        }
    });


    // copying logs
    function copyLogs() {
        var copyText = document.getElementById("logs-content").value;

        navigator.clipboard.writeText(copyText).then(function() {
            alert("Logs was copied!");
        }).catch(function(error) {
            alert('Error in copying: ' + error);
        });
    }

    // clearing logs
    function clearLogs() {
        if (confirm('Are you sure you want to clear the logs?')) {
            jQuery.post(ajaxurl, {action: 'clear_logs'}, function(response) {
                if(response.success) {
                    document.getElementById("logs-content").value = '';
                } else {
                    alert('Error: ' + response.data);
                }
            }).fail(function() {
                alert('Request error');
            });
        }
    }
});
