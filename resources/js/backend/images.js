require("../bootstrap");
require("bootstrap-fileinput");
require("ekko-lightbox");

$(document).ready(function () {
    $(".form-control-file").fileinput({
        showUpload: false,
        previewFileType: "any",
    });

    $(document).on("click", ".btn-view", function () {
        var url = $(this).parent().find("input[type='hidden']").val();
        $("#preview-images").empty();
        var anchor = $(
            `<a href="/share?file=${url}" class="preview-image" data-gallery="preview-image-gallery"></a>`
        );
        $("#preview-images").append(anchor);
        anchor.ekkoLightbox({ alwaysShowClose: true, wrapping: false });
    });
});
