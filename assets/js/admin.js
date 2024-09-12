let us_modal = ( show = true ) => {
	if(show) {
		jQuery('#user-switcher-modal').show();
	}
	else {
		jQuery('#user-switcher-modal').hide();
	}
}

jQuery(function($){
	
	$('#user-switcher_report-copy').click(function(e) {
		e.preventDefault();
		$('#user-switcher_tools-report').select();

		try {
			if( document.execCommand('copy') ){
				$(this).html('<span class="dashicons dashicons-saved"></span>');
			}
		} catch (err) {
			console.log('Oops, unable to copy!');
		}
	});
})