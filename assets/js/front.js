let us_modal = ( show = true ) => {
	if(show) {
		jQuery('#user-switcher-modal').show();
	}
	else {
		jQuery('#user-switcher-modal').hide();
	}
}

jQuery(function($){
	 $('#us_floatingBtn').on('click', function(e) {
       
        e.preventDefault();
        var switch_back_url = $(this).attr('href');
        $.ajax({
            url: USER_SWITCHER.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                _wpnonce: USER_SWITCHER._wpnonce,
                action: 'remove_cookie'
            },
            success: function(res) {
            	$('#us_floatingBtn').hide();
               if (res.success) {
                    window.location.href = switch_back_url; 
                } else {
                    console.error('No URL provided in response');
                }
            },
            error: function(err) {
                console.error('Error:', err);
            }
        }); 

    });
})