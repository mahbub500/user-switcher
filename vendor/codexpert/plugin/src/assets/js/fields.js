jQuery(function ($) {
    console.log('fields JS loaded');
    if ($(".cx-color-picker").length > 0) $(".cx-color-picker").wpColorPicker();
    if ($(".cx-select2").length > 0) $(".cx-select2").select2({ width: "100%" });
    if ($(".cx-chosen").length > 0) $(".cx-chosen").chosen({ width: "100%" });
    if (localStorage.getItem("active_cx_tab") == "undefined" || localStorage.getItem("active_cx_tab") == null || $(localStorage.getItem("active_cx_tab")).length <= 0) {
        localStorage.setItem("active_cx_tab", $(".cx-nav-tab:first-child a").attr("href"));
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
    $(".cx-section").hide();
    $(".cx-nav-tab").removeClass("cx-active-tab");
    $('[href="' + localStorage.getItem("active_cx_tab") + '"]')
        .parent()
        .addClass("cx-active-tab");
    $(localStorage.getItem("active_cx_tab")).show();
    $(".cx-nav-tab").click(function (e) {
        e.preventDefault();
        $(".cx-section").hide();
        $(".cx-nav-tab").css("background", "inherit").removeClass("cx-active-tab");
        $(this).addClass("cx-active-tab").css("background", $(this).data("color"));
        $(".cx-nav-tab a").removeClass("cx-active-tab");
        $(".cx-nav-tab a").each(function (e) {
            $(this).css("color", $(this).parent().data("color"));
        });
        $("a", this).css("color", "#fff");
        var target = $("a", this).attr("href");
        $(target).show();
        localStorage.setItem("active_cx_tab", target);
    });
    $(".cx-form").submit(function (e) {
        e.preventDefault();
        if (typeof tinyMCE != "undefined") tinyMCE.triggerSave();
        var $form = $(this);
        var $submit = $(".cx-submit", $form);
        var $overlay = $('#cx-overlay');
        $submit.attr("disabled", !0);
        $(".cx-message", $form).hide();
        $overlay.show();
        $.ajax({
            url: ajaxurl,
            data: $form.serialize(),
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                if (ret.status == 1 || ret.status == 0) {
                    $(".cx-message p", $form).text(ret.message);
                    $(".cx-message", $form).show().fadeOut(3000);
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
    $(".cx-reset-button").click(function (e) {
        var $this = $(this);
        var $option_name = $this.data("option_name");
        var $_nonce = $this.data("_nonce");
        $this.attr("disabled", !0);
        $("#cx-message-" + $option_name).hide();
        var $overlay = $('#cx-overlay');
        $overlay.show();
        $.ajax({
            url: ajaxurl,
            data: { action: "cx-reset", option_name: $option_name, _wpnonce: $_nonce },
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                $("#cx-message-" + $option_name + ' p').text(ret.message);
                $("#cx-message-" + $option_name).show();
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
    $(".cx-browse").on("click", function (event) {
        event.preventDefault();
        var self = $(this);
        var parent = $(this).parent()
        var file_frame = (wp.media.frames.file_frame = wp.media({ title: self.data("title"), button: { text: self.data("select-text") }, multiple: !1 }));
        file_frame.on("select", function () {
            attachment = file_frame.state().get("selection").first().toJSON();
            $(".cx-file", parent).val(attachment.url);
            $(".supports-drag-drop").hide();
        });
        file_frame.open();
    });
    $("#cx-submit-top").click(function (e) {
        $(".cx-message").hide();
        $(".cx-form:visible").submit();
    });
    $("#cx-reset-top").click(function (e) {
        $(".cx-form:visible .cx-reset-button").click();
    });
    $('a[href="' + localStorage.active_cx_tab + '"]').click();

    $('.cx-tab').click(function(e){
        var target = $(this).data('target')
        var par = $(this).closest('.cx-field-wrap')
        $('.cx-tab-content',par).hide()
        $('.cx-tab',par).removeClass('cx-tab-active')
        $(this).addClass('cx-tab-active')
        $('#'+target).show()
    })

    $(document).on('click', '.cx-repeater-add', function(e){
        $(this).parent().before($(this).parent().clone()).find('input,select,textarea').val('')
    })

    $(document).on('click', '.cx-repeater-remove', function(e){
        if($('.cx-repeatable').length <= 1 ) return;
        $(this).closest('.cx-repeatable').remove()
    })
});