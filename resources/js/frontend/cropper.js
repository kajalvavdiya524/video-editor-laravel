var Croppr = require('croppr');
var cropper;

$("#image-crop-button").on('click', function (e) {
    e.preventDefault();
    $("#image-crop-button").hide();
    $(".image-crop .button-group").show();
    $('.image-edit-tools').show();
    var cropper_container = $('#full-size-image').clone();
    cropper_container.attr('id', 'cropper-container').insertAfter("#full-size-image");
    var width = $('#full-size-image').width();
    var height = $('#full-size-image').height();
    console.log(width, height);
    $('#full-size-image').hide();
    var ratio = height / width;
    cropper = new Croppr("#cropper-container", {
        aspectRatio: ratio,
        startSize: [width.toFixed(2), height.toFixed(2), 'px']
    });
});

$("#crop-fix-ratio").on("change", function (e) {
    if ($(this).prop('checked')) {
        var width = $("#full-size-image").width();
        var height = $("#full-size-image").height();
        var ratio = height / width;
        cropper.options.aspectRatio = ratio;
    } else {
        cropper.options.aspectRatio = null;
    }
    cropper.reset();
});

$("#crop-original-button").on('click', function (e) {
    e.preventDefault();
    // To do
    var base_url = window.location.origin;
    var type = $("#full-size-image").data('type'),
        name = $("#full-size-image").data('name'),
        company_id = $("#full-size-image").data('company_id');

    var formData = new FormData();
    formData.append('company_id', company_id);
    formData.append('name', name);
    formData.append('type', type);
    axios({
        method: 'post',
        url: '/file/restore_original_image',
        data: formData
    })
        .then(function (response) {
            $("#full-size-image").attr('src', base_url + '/share?file=' + response.data);
            // finish cropping
            $("#image-crop-button").show();
            $(".image-crop .button-group").hide();
            $(".croppr-container").remove();
            $("#full-size-image").show();
        });
});

$("#crop-cancel-button").on('click', function (e) {
    e.preventDefault();
    $("#image-crop-button").show();
    $(".image-crop .button-group").hide();
    $(".croppr-container").remove();
    $("#full-size-image").show();
    // To do
});

$("#crop-save-button").on('click', function (e) {
    var base_url = window.location.origin;
    e.preventDefault();
    $("#image-crop-button").show();
    $(".image-crop .button-group").hide();
    $(".croppr-container").remove();
    $("#full-size-image").show();
    $(".crop-overlay").css('display', 'flex');
    // To do
    var type = $("#full-size-image").data('type'),
        name = $("#full-size-image").data('name'),
        company_id = $("#full-size-image").data('company_id'),
        path = $("#full-size-image").data('path'),
        uri = `${base_url}/share?file=${path}`;

    var src_width = $("#full-size-image").width();
    var src_height = $("#full-size-image").height();
    var cv = {
        "x": cropper.box.x1,
        "y": cropper.box.y1,
        "width": cropper.box.x2 - cropper.box.x1,
        "height": cropper.box.y2 - cropper.box.y1,
    };

    var canvas = document.createElement('canvas'),
        context = canvas.getContext('2d'),
        image = new Image();
    image.addEventListener('load', function () {
        var r_width = image.width / src_width;
        var r_height = image.height / src_height;
        canvas.width = cv.width * r_width;
        canvas.height = cv.height * r_height;
        context.drawImage(image, cv.x * r_width, cv.y * r_height, cv.width * r_width, cv.height * r_height, 0, 0, cv.width * r_width, cv.height * r_height);
        var imageData = canvas.toDataURL("image/png");

        var formData = new FormData();
        formData.append('croppedImage', imageData);
        formData.append('company_id', company_id);
        formData.append('name', name);
        formData.append('type', type);

        axios({
            method: 'post',
            url: '/file/save_cropped_image',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(function (response) {
                $("#full-size-image").attr('src', base_url + '/share?file=' + response.data);
                $(".crop-overlay").hide();

                // finish cropping
                $("#image-crop-button").show();
                $(".image-crop .button-group").hide();
                $(".croppr-container").remove();
                $("#full-size-image").show();
            });
    }, false);
    image.crossOrigin = "anonymous";
    image.src = uri;
});
