require('ekko-lightbox');

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip({ container: 'body' });

    function showError(messages) {
        $('.alert.errors').empty();
        for (var msg of messages) {
            var alert = msg;
            if (msg.toString().includes('status code 419')) {
                alert = 'Error: Your session has expired, please log out and log back in.';
            }
            $('.alert.errors').append(
                $(`<div class="error-message">${alert}</div>`)
            );
        }
        $('.alert').show();
        setTimeout(function () {
            $('.alert').hide();
        }, 4000);
    }
    
    $(document).on("click", ".btn-approve", function () {
        var id = $(this).data('id');
        var requester_id = $('meta[name="requesterId"]').attr('content');
        var request_timestamp = $('meta[name="requestTimestamp"]').attr('content');
        var user_id = $('meta[name="userId"]').attr('content');
        var comment = $('.comment').val();

        axios({
            method: 'post',
            url: `/projects/${id}/approve`,
            data: { requester_id, request_timestamp, user_id, comment }
        })
        .then(() => {
            window.location.href = "/projects";
        });
    });

    $(document).on("click", ".btn-reject", function () {
        var id = $(this).data('id');
        var requester_id = $('meta[name="requesterId"]').attr('content');
        var request_timestamp = $('meta[name="requestTimestamp"]').attr('content');
        var user_id = $('meta[name="userId"]').attr('content');
        var comment = $('.comment').val();

        axios({
            method: 'post',
            url: `/projects/${id}/reject`,
            data: { requester_id, request_timestamp, user_id, comment }
        })
        .then(() => {
            window.location.href = "/projects";
        });
    });

    $(".project-preview").on("click", function() {
        var id = $(this).data('id');
        axios({
            method: 'get',
            url: '/projects/'+ id +'/show',
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(function (response) {
            var data = response.data;
            var files = data.jpg_files.split(" ");
            $('#preview-images').empty();
            var anchors = [];
            for (var file of files) {
                var anchor = $(`<a href="/share?file=outputs/jpg/${file}" class="preview-image" data-gallery="preview-image-gallery"></a>`);
                anchors.push(anchor);
                $('#preview-images').append(anchor);
            }
            anchors[0].ekkoLightbox({alwaysShowClose: true});
        })
        .catch(function (response) {
            showError([response]);
        });
    });
});
