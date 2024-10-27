let us_modal = ( show = true ) => {
	if(show) {
		jQuery('#user-switcher-modal').show();
	}
	else {
		jQuery('#user-switcher-modal').hide();
	}
}

jQuery(document).ready(function($) {
    $('#switch-to-guest-button').on('click', function() {
        $('#us-switcher-modal').show();
    });

    $('.us-switcher-close').on('click', function() {
        $('#us-switcher-modal').hide();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#us-switcher-modal')) {
            $('#us-switcher-modal').hide();
        }
    });

    $('#us-switcher-name').on('blur', function(e) {
        e.preventDefault(); 
        var searchValue = $('#us-switcher-name').val();

        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                action: 'search_users',
                search: searchValue
            },
            success: function(response) {
                $('#us-switcher-results').html(response);
            }
        });
    });
});


