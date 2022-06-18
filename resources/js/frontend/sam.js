$(document).ready(function() {
    $("select[name='pre_header']").on('change', function () {
        var pre_header = $(this).val();
        if (pre_header == "custom") {
            $(".custom-pre-header").show();
        } else {
            $(".custom-pre-header").hide();
            $("#custom_pre_header").attr("value", "custom");
            $("input[name='custom_pre_header']").val('');
        }
    });

    $("input[name='custom_pre_header']").on('change', function () {
        var custom_pre_header = $(this).val();
        $("#custom_pre_header").attr("value", custom_pre_header);
    });
});