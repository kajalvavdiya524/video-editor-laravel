require('bootstrap-fileinput')

$(document).ready(function() {
    $(".form-control-file").fileinput({'showUpload':false, 'previewFileType':'any'});
    $("#company").CreateMultiCheckBox({ defaultText : 'Please select companies', height:'250px' });
});
