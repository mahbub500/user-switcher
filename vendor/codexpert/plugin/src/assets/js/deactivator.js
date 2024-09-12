jQuery(function($){

	$(document).on( 'click', '.cx-plugin-dsm-close', function(e){
		e.preventDefault()
		$('.cx-plugin-deactivation-survey-overlay').hide()
	} )

	$(document).on( 'click', '.cx-plugin-deactivation-reason', function(e){
		var par = $(this);
		if ( $( 'input', par ).prop("checked") == false ){
		 	$('label', par).removeClass('active');
		 }else{
			$('label', par).addClass('active');
		 }		 
		$('.cx-plugin-dsm-reason-details-input').slideDown();
	} )

	$(document).on( 'click', '.cx-consent-label', function(e){
		var desc = $(this).data('desc');
		alert(desc)
	} )
	

	$(document).on( 'submit', '.cx-plugin-deactivation-survey-form', function(e){
		e.preventDefault();
		var data = $(this).serializeArray()
		var parent = $(this);
		$('.cx-plugin-dsm-submit', parent).prop('disabled', true);
		 
		$.ajax({
			url: ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(resp){				
				window.location.href='';
			}
		});
	} );
})