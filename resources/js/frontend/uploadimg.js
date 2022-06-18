require('bootstrap-fileinput');
require('../bootstrap');
require('jquery-form');

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $(".form-control-file").fileinput(
        {
            'showUpload': false,
            'hideThumbnailContent': true,
        }
    );

    $('#form-file-upload').ajaxForm({
        beforeSend: function () {
            var bar = $('.progress-bar');
            var percentVal = 0;
            bar.css('width', percentVal + '%');
            bar.attr('aria-valuenow', percentVal);
            bar.html(percentVal + '%');
        },
        uploadProgress: function (event, position, total, percentComplete) {
            var bar = $('.progress-bar');
            var percentVal = percentComplete;
            bar.css('width', (percentVal - 1) + '%');
            bar.attr('aria-valuenow', percentVal - 1);
            bar.html((percentVal - 1) + '%');
        },
        success: function () {
            var bar = $('.progress-bar');
            var percentVal = '100%(Wait, Saving)';
            bar.css('width', '100%');
            bar.attr('aria-valuenow', 100);
            bar.html(percentVal);
        },
        complete: function (xhr) {
            setTimeout(function () {
                window.location.href = "/file/uploadimg";
            }, 5000);
        }
    });

    $('#file_upload_button').on('click', function (e) {
        $('.progress').removeClass('d-none');
    });

    $('select[name=action]').change(function (e) {
        var val = $(this).val();
        var fileInput = $(this).closest('.form-row').find('.file-input-group');
        val == "update" ? fileInput.show() : fileInput.hide();
    });

    $(document).on('click', ".edit-upload-file", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var company_id = tr.find('#company_id').val();
        $('#editUploadImage #company').val(company_id);

        var id = Number(tr.find('#id').val());
        $('#editUploadImage #id').val(id);

        var asin = tr.children('td').eq(3).text().trim();
        $('#editUploadImage #asin').val(asin);

        var upc = tr.children('td').eq(4).text().trim();
        $('#editUploadImage #upc').val(upc);

        var gtin = tr.children('td').eq(5).text().trim();
        $('#editUploadImage #gtin').val(gtin);

        var width = Number(tr.children('td').eq(6).text());
        $('#editUploadImage #width').val(width);

        var height = Number(tr.children('td').eq(7).text());
        $('#editUploadImage #height').val(height);

        $('#editUploadImage').modal('show');
    });

    $('.reindex').on('click', function (e) {
        e.preventDefault();
        $(this).css('pointer-events', 'none');
        $('.loadingOverlay').fadeIn();
        var form = $('<form method="post" action="/file/reindex" style="display:none"></form>');
        form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
        $(document.body).append(form);
        form.submit();
    });

    $("select[name='image_type']").on('change', function () {
        var type = $(this).val();
        if (type == "background_image") {
            $(".template-selector").show();
        } else {
            $(".template-selector").hide();
        }
      
        if (type == "stock_image") {
            if ($("#company_id")){
              // if (!$("#company_id option[value='0']")){
                $('#company_id').prepend($('<option>', {
                    value: 0,
                    text: 'All companies'
                }));
                $("#company_id").val("0");

            //}
                
            }
        }else{
            $("#company_id option[value='0']").remove();
        }
        

    });
});
