jQuery(function($){
    $(document).on('click', '.cx-survey .notice-dismiss, .cx-survey .cx-survey-btn', function(e){
        $(this).prop('disabled', true);
        var $slug = $(this).closest('.cx-survey').data('slug')
        $.ajax({
            url: ajaxurl,
            data: { 'action' : $slug + '_survey', 'participate' : $(this).data('participate') },
            type: 'POST',
            success: function(ret) {
                $('#'+$slug+'-survey-notice').slideToggle(500)
            }
        })
    })
})