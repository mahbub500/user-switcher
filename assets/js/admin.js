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

    $(document).ready(function() {
        $('.us-user-name').select2({
            width: '100%',
            ajax: {
                url: USER_SWITCHER.ajaxurl,
                dataType: 'json',
                delay: 250, 
                data: function (params) {
                    return {
                        keyword: params.term, 
                        action: 'search_users',
                        _wpnonce: USER_SWITCHER._wpnonce,
                    };
                },
                processResults: function(data) {
                    var options = [];
                    $.each(data.data, function(index, title) { 
                        options.push({ 
                            id: index,
                            text: title,
                        });
                    });
                    return {
                        results: options
                    };
                },
            },
            minimumInputLength: 3 
        });
    });




   
});


