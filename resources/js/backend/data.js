require('bootstrap-fileinput')

$(document).ready(function() {
    $(".form-control-file").fileinput({'showUpload':false, 'previewFileType':'any'});
    
    $('select[name=action]').change(function (e){
        var val = $(this).val();
        var fileInput = $(this).closest('.form-row').find('.file-input-group');
        val == "update" ? fileInput.show() : fileInput.hide();
    });
});
