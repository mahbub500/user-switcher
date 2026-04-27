jQuery(function ($) {
    console.log('fields JS loaded');
    if ($(".wph-color-picker").length > 0) $(".wph-color-picker").wpColorPicker();
    if ($(".wph-select2").length > 0) $(".wph-select2").select2({ width: "100%" });
    if ($(".wph-chosen").length > 0) $(".wph-chosen").chosen({ width: "100%" });
    if (localStorage.getItem("active_cx_tab") == "undefined" || localStorage.getItem("active_cx_tab") == null || $(localStorage.getItem("active_cx_tab")).length <= 0) {
        localStorage.setItem("active_cx_tab", $(".wph-nav-tab:first-child a").attr("href"));
    }
    if (typeof localStorage != "undefined") {
        active_cx_tab = localStorage.getItem("active_cx_tab");
    }
    if (window.location.hash) {
        active_cx_tab = window.location.hash;
        if (typeof localStorage != "undefined") {
            localStorage.setItem("active_cx_tab", active_cx_tab);
        }
    }
    $(".wph-section").hide();
    $(".wph-nav-tab").removeClass("wph-active-tab");
    $('[href="' + localStorage.getItem("active_cx_tab") + '"]')
        .parent()
        .addClass("wph-active-tab");
    $(localStorage.getItem("active_cx_tab")).show();
    $(".wph-nav-tab").click(function (e) {
        e.preventDefault();
        $(".wph-section").hide();
        $(".wph-nav-tab").css("background", "inherit").removeClass("wph-active-tab");
        $(this).addClass("wph-active-tab").css("background", $(this).data("color"));
        $(".wph-nav-tab a").removeClass("wph-active-tab");
        $(".wph-nav-tab a").each(function (e) {
            $(this).css("color", $(this).parent().data("color"));
        });
        $("a", this).css("color", "#fff");
        var target = $("a", this).attr("href");
        $(target).show();
        localStorage.setItem("active_cx_tab", target);
    });
    $(".wph-form").submit(function (e) {
        e.preventDefault();
        if (typeof tinyMCE != "undefined") tinyMCE.triggerSave();
        var $form = $(this);
        var $submit = $(".wph-submit", $form);
        var $overlay = $('#wph-overlay');
        $submit.attr("disabled", !0);
        $(".wph-message", $form).hide();
        $overlay.show();
        $.ajax({
            url: ajaxurl,
            data: $form.serialize(),
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                if (ret.status == 1 || ret.status == 0) {
                    $(".wph-message p", $form).text(ret.message);
                    $(".wph-message", $form).show().fadeOut(3000);
                }
                $submit.attr("disabled", !1);
                if (ret.page_load == 1)
                    setTimeout(function () {
                        window.location.href = "";
                    }, 1000);
                $overlay.hide();
            },
            erorr: function (ret) {
                $submit.attr("disabled", !1);
                $overlay.hide();
            },
        });
    });
    $(".wph-reset-button").click(function (e) {
        var $this = $(this);
        var $option_name = $this.data("option_name");
        var $_nonce = $this.data("_nonce");
        $this.attr("disabled", !0);
        $("#wph-message-" + $option_name).hide();
        var $overlay = $('#wph-overlay');
        $overlay.show();
        $.ajax({
            url: ajaxurl,
            data: { action: "wph-reset", option_name: $option_name, _wpnonce: $_nonce },
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                $("#wph-message-" + $option_name + ' p').text(ret.message);
                $("#wph-message-" + $option_name).show();
                $overlay.hide();
                setTimeout(function () {
                    window.location.href = "";
                }, 1000);
            },
            erorr: function (ret) {
                $this.attr("disabled", !1);
                $overlay.hide();
            },
        });
    });
    $(".wph-browse").on("click", function (event) {
        event.preventDefault();
        var self = $(this);
        var parent = $(this).parent()
        var file_frame = (wp.media.frames.file_frame = wp.media({ title: self.data("title"), button: { text: self.data("select-text") }, multiple: !1 }));
        file_frame.on("select", function () {
            attachment = file_frame.state().get("selection").first().toJSON();
            $(".wph-file", parent).val(attachment.url);
            $(".supports-drag-drop").hide();
        });
        file_frame.open();
    });
    $("#wph-submit-top").click(function (e) {
        $(".wph-message").hide();
        $(".wph-form:visible").submit();
    });
    $("#wph-reset-top").click(function (e) {
        $(".wph-form:visible .wph-reset-button").click();
    });
    $('a[href="' + localStorage.active_cx_tab + '"]').click();

    $('.wph-tab').click(function(e){
        var target = $(this).data('target')
        var par = $(this).closest('.wph-field-wrap')
        $('.wph-tab-content',par).hide()
        $('.wph-tab',par).removeClass('wph-tab-active')
        $(this).addClass('wph-tab-active')
        $('#'+target).show()
    })

    $(document).on('click', '.wph-repeater-add', function(e){
        $(this).parent().before($(this).parent().clone()).find('input,select,textarea').val('')
    })

    $(document).on('click', '.wph-repeater-remove', function(e){
        if($('.wph-repeatable').length <= 1 ) return;
        $(this).closest('.wph-repeatable').remove()
    })
});