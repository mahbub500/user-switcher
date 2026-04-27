jQuery(function($){
    $(document).on('click', '.wph-survey .notice-dismiss, .wph-survey .wph-survey-btn', function(e){
        $(this).prop('disabled', true);
        var $slug = $(this).closest('.wph-survey').data('slug')
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