require('bootstrap-fileinput');
require('../bootstrap');
require('multiple-select');

var percent = 0;

$(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip();
    $(".form-control-file").fileinput({ 'showUpload': false, 'previewFileType': 'any' });

    $('#scheduled_days_of_week').multipleSelect({
        ellipsis: true
    });

    $('select[name=action]').change(function (e) {
        var val = $(this).val();
        var fileInput = $(this).closest('.form-row').find('.file-input-group');
        val == "update" ? fileInput.show() : fileInput.hide();
    });

    $('.file-info').on('click', function (e) {
        e.preventDefault();
        axios({
            method: 'post',
            url: 'exception/export_file_list',
            data: { 'type': $(this).data('type') },
        }).then(function (response) {
            var link = document.createElement('a');
            link.href = "data:text/csv;base64," + response.data;
            link.setAttribute('download', "file_" + $(this).data('type') + ".xlsx");
            document.body.appendChild(link);
            link.click();
            link.remove();
        });
    });
    
    $('.download-new-prod-file-btn').on('click', function (e) {
        e.preventDefault();
        var urlsfile_id = $(this).parent().children('.urlsfile_id').val();
        var rows = $(this).parent().parent().find('.new-rows').text();
        var _this = this;
        axios({
            method: 'post',
            url: '/admin/auth/settings/updatefile/download_files',
            data: { 
                'id': urlsfile_id, 
                'type': 'new' 
            },
        }).then(function (response) {
            $(_this).addClass("disabled");
            setInterval(function() {
                percent += 1500 / (3 * rows);
                if (percent > 100) 
                {
                    percent = 100;
                    downloadFile();
                }
                $(_this).html(`<i class='fa fa-spinner fa-spin'></i> Compressing Files <span style="width: 45px; display: inline-block;">(${percent.toFixed(0)}%)</span>`);
            }, 1000);
        });
    });

    $('.download-prod-file-btn').on('click', function (e) {
        e.preventDefault();
        var urlsfile_id = $(this).parent().children('.urlsfile_id').val();
        var rows = $(this).parent().parent().find('.new-rows').text();
        var _this = this;
        axios({
            method: 'post',
            url: '/admin/auth/settings/updatefile/download_files',
            data: { 
                'id': urlsfile_id, 
                'type': 'prod' 
            },
        }).then(function (response) {
            $(_this).addClass("disabled");
            setInterval(function() {
                percent += 1500 / (3 * rows);
                if (percent > 100) 
                {
                    percent = 100;
                    downloadFile();
                }
                $(_this).html(`<i class='fa fa-spinner fa-spin'></i> Compressing Files <span style="width: 45px; display: inline-block;">(${percent.toFixed(0)}%)</span>`);
            }, 1000);
        });
    });
    
    $('.download-nfi-file-btn').on('click', function (e) {
        e.preventDefault();
        var urlsfile_id = $(this).parent().children('.urlsfile_id').val();
        var rows = $(this).parent().parent().find('.new-rows').text();
        var _this = this;
        axios({
            method: 'post',
            url: '/admin/auth/settings/updatefile/download_files',
            data: { 
                'id': urlsfile_id, 
                'type': 'nfi' 
            },
        }).then(function (response) {
            $(_this).addClass("disabled");
            setInterval(function() {
                percent += 1500 / (3 * rows);
                if (percent > 100) 
                {
                    percent = 100;
                    downloadFile();
                }
                $(_this).html(`<i class='fa fa-spinner fa-spin'></i> Compressing Files <span style="width: 45px; display: inline-block;">(${percent.toFixed(0)}%)</span>`);
            }, 1000);
        });
    });

    $('.get-btn').on('click', function (e) {
        e.preventDefault();
        $(this).addClass('disabled');
        var urlsfile_id = $(this).parent().children('.urlsfile_id').val();
        axios({
            method: 'post',
            url: '/admin/auth/settings/updatefile/get_files',
            data: { 'id': urlsfile_id },
        }).then(function (response) {
            if (response.data == "duplicated") {
                location.reload();
            } else {
                startUploading();
            }
        });
    });

    $('.download-list-btn').on('click', function (e) {
        e.preventDefault();
        var urlsfile_id = $(this).parent().children('.urlsfile_id').val();
        var form = $('<form method="post" action="/admin/auth/settings/updatefile/download_list" style="display:none"></form>');
        form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
        form.append($('<input type="hidden" name="id" id="id" value="' + urlsfile_id + '" />'));
        $(document.body).append(form);
        form.submit();
    });

    $('#run_now').on('click', function (e) {
        var form = $('<form method="post" action="/admin/auth/settings/updatefile/run_schedule" style="display:none"></form>');
        form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
        $(document.body).append(form);
        form.submit();
    });

    $('.btn-expand-contract').on('click', function (e) {
        e.preventDefault();
        if ($('#schedule-form .card-body').is(':hidden')) {
            $('#schedule-form .card-body').fadeIn();
            $('#schedule-form .card-footer').fadeIn();
            $(this).html('<i class="c-icon cil-minus"></i>Contract');
        } else {
            $('#schedule-form .card-body').fadeOut();
            $('#schedule-form .card-footer').fadeOut();
            $(this).html('<i class="c-icon cil-plus"></i>Expand');
        }
    });

    axios({
        method: 'get',
        url: '/admin/auth/settings/updatefile/ajax_uploading_progress'
    }).then(function (response) {
        var data = response.data;
        if (data != "") {
            startUploading();
        }
    });

});

function downloadFile() {
    var base_url = window.location.origin;
    // setInterval(function () {
        axios({
            method: 'get',
            url: '/admin/auth/settings/updatefile/ajax_check_compressing'
        })
            .then(function (response) {
                var data = response.data;
                if (data) {
                    console.log(data);
                    var i;
                    for (i = 0; i < data.count; i++) {
                        var link = document.createElement('a');
                        link.href = base_url + "/" + data.filename + i + ".zip";
                        link.setAttribute('download', decodeURI(data.filename) + i + ".zip");
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            });
    // }, 3000);

}

function startUploading() {
    setInterval(function () {
        axios({
            method: 'get',
            url: '/admin/auth/settings/updatefile/ajax_uploading_progress'
        })
            .then(function (response) {
                var data = response.data;
                if (data != "") {
                    $(".uploading-progress-pane #progress").text(data.current + "/" + data.total);
                    $(".uploading-progress-pane").show();
                    if (data.current == data.total) {
                        axios({
                            method: 'post',
                            url: '/admin/auth/settings/updatefile/stop_upload_progress'
                        }).then(function (response) {
                            location.reload();
                        });
                    }
                }
            });
    }, 1500);
}
