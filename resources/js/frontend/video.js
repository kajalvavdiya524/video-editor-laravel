require('../bootstrap');
require('slick-carousel');
var jQueryBridget = require('jquery-bridget');
var Masonry = require('masonry-layout');
jQueryBridget('masonry', Masonry, $);

var indexCheckedFiles = [];
var nameCheckedFiles = [];
var headlineData = [];
var prevIndex = 0;

$(document).ready(function () {

    $('.grid').masonry({
        itemSelector: '.grid-item',
        columnWidth: 220
    });

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

    function sendNotificationEmail(email, url, projectname) {
        if (email) {
            var link = url.match(/outputs\/([a-zA-Z0-9_\.]*)\?/)[1];
            var filename = url.match(/outputs\/([a-zA-Z0-9_]*)\./)[1];
            var subject = (projectname ? projectname : filename) + " Banner";
            var mailto = document.createElement("a");
            var base_url = window.location.origin;
            mailto.href = `mailto:${email}?subject=${subject}&body=Here is the banner. ${base_url}/share?file=outputs/${link}`;
            mailto.target = "_blank";
            mailto.click();
        }
    }

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    $('.slide-popup').on('click', function (e) {
        e.stopPropagation();
    });
    $('body').on('click', function () {
        $('.slide-popup').fadeOut();
    });

    $('.selected-customer').on('click', function (e) {
        e.stopPropagation();
        $('.customers').fadeIn();
        $('.templates').fadeOut();
        $('.customers-carousel').slick({
            slidesToShow: 6
        });
    });

    $('.customers .slide-item img').on('click', function (e) {
        $('.slide-item img').removeClass('selected');
        $(this).addClass('selected');
        $('.customers').fadeOut();

        var src = $(this).attr('src');
        var title = $(this).attr('title');
        var value = $(this).data('value');
        $('.selected-customer img').attr('src', src);
        $('.selected-customer img').attr('title', title);
        $('.selected-customer img').addClass('selected');
        $('.selected-customer').show();
        $('input[name=customer]').val(value);
        var file_ids = $('input[name="file_ids"]').val();
        setCookie("file_ids", file_ids, 1);
        window.location.href = "/banner/" + value;
    });

    $('input[name=customer]').on('change', function (e) {
        var file_ids = $('input[name="file_ids"]').val();
        setCookie("file_ids", file_ids, 1);
        window.location.href = "/banner/" + $(this).val();
    });

    $('.selected-template').on('click', function (e) {
        e.stopPropagation();
        $('.templates').fadeIn();
        $('.templates-carousel').remove();
        $('.templates-carousel-hidden').clone()
            .removeClass('templates-carousel-hidden')
            .removeClass('d-none')
            .addClass('templates-carousel')
            .appendTo('.templates');
            
        var initial = 0;
        var customer = $('input[name=customer]').val();
        if (customer == "amazon_fresh") {
            initial = $('input[name=output_dimensions]').val();
            $('.templates-carousel .slide-item img').eq(initial).addClass('selected');
        }
        $('.templates-carousel').slick({
            slidesToShow: 4,
            initialSlide: initial
        });
    });

    $('.templates').on('click', '.templates-carousel .slide-item img', function (e) {
        $('.slide-item img').removeClass('selected');
        $(this).addClass('selected');
        $('.templates').fadeOut();

        var src = $(this).attr('src');
        var title = $(this).attr('title');
        var value = $(this).data('value');
        $('.selected-template img').attr('src', src);
        $('.selected-template img').attr('title', title);
        $('.selected-template img').addClass('selected');
        $('.selected-template').show();
        $('input[name=output_dimensions]').val(value);
        var file_ids = $('input[name="file_ids"]').val();
        var customer = $('input[name=customer]').val();
        setCookie("file_ids", file_ids, 1);
        if (customer != "amazon_fresh") {
            window.location.href = "/banner/" + customer + "/" + value;
        } else {
            $('input[name=output_dimensions]').trigger("change");
        }
    });

    $('select[name=customer]').on('change', function (e) {
        var file_ids = $('input[name="file_ids"]').val();
        setCookie("file_ids", file_ids, 1);
        window.location.href = "/banner/" + $(this).val();
    });

    $('select[name=product_layering]').on('change', function (e) {
        $('.product_custom_layering').removeClass('d-none');
        if ($(this).val() == 'Custom') {
            $('.product_custom_layering').show();
        } else {
            $('.product_custom_layering').hide();
        }
    });

    $('#file_ids').on('change', function (e) {
        $('#images').empty();
        $('.use-prev-text').hide();
        var file_ids = $(this).val().split(" ");
        var index = 0;
        file_ids.forEach((file) => {
            if (file != "") {
                $('#images').append(`<option value="${index}">${file}</option`);
                index++;
            }
        });
        headlineData = new Array(index);
    });

    $('input[name="use_prev_text"]').on('change', function () {
        if ($(this).prop('checked')) {
            $('.headline input').prop('disabled', true);
        } else {
            $('.headline input').prop('disabled', false);
        }
    });

    $(document).on('focus', function () {
        prevIndex = this.value;
    }).on('change', '#images', function (e) {
        if (this.value == 0) {
            $('.use-prev-text').hide();
        } else {
            $('.use-prev-text').show();
        }

        setHeadlineData(prevIndex);

        $("#top_headline").val(headlineData[this.value] ? headlineData[this.value].top_headline : "");
        $("#top_head_size").val(headlineData[this.value] ? headlineData[this.value].top_head_size : "60");
        $("#top_head_color").val(headlineData[this.value] ? headlineData[this.value].top_head_color : "#000000");
        $("#top_head_color_hex").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_color : "#000000");

        $("#top_subheadline").val(headlineData[this.value] ? headlineData[this.value].top_subheadline : "");
        $("#top_subhead_size").val(headlineData[this.value] ? headlineData[this.value].top_subhead_size : "40");
        $("#top_subhead_color").val(headlineData[this.value] ? headlineData[this.value].top_subhead_color : "#000000");
        $("#top_subhead_color_hex").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_color : "#000000");

        $("#bottom_headline").val(headlineData[this.value] ? headlineData[this.value].bottom_headline : "");
        $("#bottom_head_size").val(headlineData[this.value] ? headlineData[this.value].bottom_head_size : "60");
        $("#bottom_head_color").val(headlineData[this.value] ? headlineData[this.value].bottom_head_color : "#000000");
        $("#bottom_head_color_hex").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_color : "#000000");

        $("#bottom_subheadline").val(headlineData[this.value] ? headlineData[this.value].bottom_subheadline : "");
        $("#bottom_subhead_size").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_size : "40");
        $("#bottom_subhead_color").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_color : "#000000");
        $("#bottom_subhead_color_hex").val(headlineData[this.value] ? headlineData[this.value].bottom_subhead_color : "#000000");

        $('input[name="use_prev_text"]').prop('checked', headlineData[this.value] ? headlineData[this.value].use_prev_text : false);

        if (!$('input[name="use_prev_text"]').prop('checked')) {
            $('.headline input').prop('disabled', false);
        } else {
            $('.headline input').prop('disabled', true);
        }

        prevIndex = this.value;
    });

    $('#toggleOptionalText').on('click', function () {
        if (this.textContent == "+ Show Optional Text Fields") {
            this.textContent = "- Hide Optional Text Fields";
            $('.headline').fadeIn();
            $('.image-selector').css("display", "flex");
        } else {
            this.textContent = "+ Show Optional Text Fields";
            $('.headline').fadeOut();
            $('.image-selector').fadeOut();
        }
    });

    $(document).on('click', '.grid-item', function (e) {
        var id = Number($(this).find('input').val());
        var name = $(this).find('input').data('name');
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            indexCheckedFiles = _.pull(indexCheckedFiles, id);
            nameCheckedFiles = _.pull(nameCheckedFiles, name);
        } else {
            $(this).addClass('selected');
            indexCheckedFiles.push(id);
            nameCheckedFiles.push(name);
        }
    });

    function setHeadlineData(index) {
        headlineData[index] = {
            top_headline: $("#top_headline").val(),
            top_head_size: $("#top_head_size").val(),
            top_head_color: $("#top_head_color").val(),

            top_subheadline: $("#top_subheadline").val(),
            top_subhead_size: $("#top_subhead_size").val(),
            top_subhead_color: $("#top_subhead_color").val(),

            bottom_headline: $("#bottom_headline").val(),
            bottom_head_size: $("#bottom_head_size").val(),
            bottom_head_color: $("#bottom_head_color").val(),

            bottom_subheadline: $("#bottom_subheadline").val(),
            bottom_subhead_size: $("#bottom_subhead_size").val(),
            bottom_subhead_color: $("#bottom_subhead_color").val(),

            use_prev_text: $('input[name="use_prev_text"]').prop("checked")
        }
    }

    async function showSwalAlert(title) {
        var result = await Swal.fire({
            title: title,
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            icon: 'warning'
        });
        return result.value;
    }

    $('#generate-ads').on('click', async function (e) {
        e.preventDefault();
        var duration = $("#duration").val();
        var swalres = true;
        if (duration > 3) {
            swalres = await showSwalAlert("Display time is more than 10 seconds per image. Are you sure you want to continue?");
        }
        if (!swalres) return;

        var index = $('#adForm').find('#images').val();
        setHeadlineData(index);
        var button = $(this);
        button.prop('disabled', true);
        var formData = new FormData(document.getElementById('adForm'));
        formData.append('headlineData', JSON.stringify(headlineData));
        $('.generate-alert').css('display', 'flex');
        axios({
            method: 'post',
            url: '/banner/generate',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then((response) => {
                download_file(response.data);
                $('#saved-draft').val(response.data.url);
                $('#saved-draft').data("projectname", response.data.projectname);
                if ($('#published-project').data("projectname") != response.data.projectname) {
                    $('#published-project').val('');
                }
                button.prop('disabled', false);
                $('.generate-alert').html("Saved!");
                setTimeout(() => {
                    $('.generate-alert').css('display', 'none');
                    $('.generate-alert').html("Generating...");
                }, 1500);
            })
            .catch((response) => {
                showError([response]);
                $('.generate-alert').css('display', 'none');
                button.prop('disabled', false);
            });
    });

    $('#download-ads').on('click', async function (e) {
        e.preventDefault();
        var duration = $("#duration").val();
        var swalres = true;
        if (duration > 3) {
            swalres = await showSwalAlert("Display time is more than 10 seconds per image. Are you sure you want to continue?");
        }
        if (!swalres) return;

        var index = $('#adForm').find('#images').val();
        setHeadlineData(index);
        var button = $(this);
        var formData = new FormData(document.getElementById('adForm'));
        formData.append('headlineData', JSON.stringify(headlineData));
        button.prop('disabled', true);
        $('.generate-alert').css('display', 'flex');
        axios({
            method: 'post',
            url: '/banner/download',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(function (response) {
                download_file(response.data);
                button.prop('disabled', false);
                $('.generate-alert').css('display', 'none');
            })
            .catch(function (response) {
                showError([response]);
                button.prop('disabled', false);
                $('.generate-alert').css('display', 'none');
            });
    });

    $('#preview-ads').on('click', async function (e) {
        e.preventDefault();
        var duration = $("#duration").val();
        var swalres = true;
        if (duration > 3) {
            swalres = await showSwalAlert("Display time is more than 10 seconds per image. Are you sure you want to continue?");
        }
        if (!swalres) return;

        var index = $('#adForm').find('#images').val();
        setHeadlineData(index);
        var button = $(this);
        var formData = new FormData(document.getElementById('adForm'));
        formData.append('headlineData', JSON.stringify(headlineData));
        button.prop('disabled', true);
        $('.generate-alert').css('display', 'flex');
        axios({
            method: 'post',
            url: '/banner/preview',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(function (response) {
                var data = response.data;
                if (data.status == "error") {
                    showError(data.messages);
                } else {
                    if (data.status == "warning") {
                        showError(data.messages);
                    }
                    $("#videoModal video").html(`<source src="/${data.files[0]}" type="video/mp4"></source>`);
                    $("#videoModal video")[0].load();
                    $("#videoModal").modal();
                }
                button.prop('disabled', false);
                $('.generate-alert').css('display', 'none');
            })
            .catch(function (response) {
                showError([response]);
                button.prop('disabled', false);
                $('.generate-alert').css('display', 'none');
            });
    });

    $('#publish-team-ads').on('click', function (e) {
        e.preventDefault();
        var button = $(this);
        button.prop('disabled', true);
        var formData = new FormData(document.getElementById('adForm'));
        $('.generate-alert').css('display', 'flex');
        axios({
            method: 'post',
            url: '/banner/publish',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(function (response) {
                download_file(response.data);
                $('#published-project').val(response.data.url);
                $('#published-project').data("projectname", response.data.projectname);
                if ($('#saved-draft').data("projectname") != response.data.projectname) {
                    $('#saved-draft').val('');
                }
                $('#publish-team-ads').prop('disabled', false);
                $('.generate-alert').html("Saved!");
                setTimeout(() => {
                    $('.generate-alert').css('display', 'none');
                    $('.generate-alert').html("Generating...");
                }, 1500);
            })
            .catch(function (response) {
                showError([response]);
                $('.generate-alert').css('display', 'none');
                $('#publish-team-ads').prop('disabled', false);
            });
    });

    $('#share-ads').on('click', function (e) {
        e.preventDefault();
        var draft_url = $('#saved-draft').val();
        var project_url = $('#published-project').val();
        if (draft_url && project_url) {
            $('#shareModal #select-choice').show();
            $('#shareModal #select-choice p').text("Share draft or project:");
            $('#shareModal #select-choice label[for="save_draft"]').text("Draft");
            $('#shareModal #select-choice label[for="publish_to_team"]').text("Project");
            $('#shareModal').modal('show');
        } else if (draft_url || project_url) {
            $('#shareModal #select-choice').hide();
            $('#shareModal').modal('show');
        } else {
            var formData = new FormData(document.getElementById('adForm'));
            axios({
                method: 'post',
                url: '/banner/can_share',
                data: formData,
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(function (response) {
                    var data = response.data;
                    if (data.status == "error") {
                        showError(data.messages);
                    } else {
                        $('#shareModal').modal('show');
                    }
                })
                .catch(function (response) {
                    showError([response]);
                });
        }
    });

    $(document).on('mouseover', '.grid-item', function (e) {
        $(this).children('.overlay').fadeIn();
    }).on('mouseleave', '.grid-item', function (e) {
        $(this).children('.overlay').fadeOut();
    });

    $(document).on('click', '.grid-item a', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var base_url = window.location.origin;
        var path = $(this).data('path');
        var name = $(this).data('name');
        var width = $(this).data('width');
        var height = $(this).data('height');

        $('.available-image-grid').hide();
        $('.full-size-image').empty();
        $('.full-size-image').append($(`<a href="#" class="btn-back-grid">Back</a>`));
        $('.full-size-image').append($(`<img src="${base_url}/share?file=${path}" class="product-image" id="full-size-image" />`));
        $('#full-size-image').data('name', name);
        $('#full-size-image').data('type', "");
        $('#full-size-image').data('company_id', $(this).data('company_id'));
        $('#full-size-image').data('path', path);
        $('.full-size-image').append($(`<span class="product-image-description float-right">${name} [${width.toFixed(2)} x ${height.toFixed(2)} WxH]</span>`));
        $('.full-size-image').show();
        // crop tool
        $('#image-crop-button').show();
        $(".image-crop .button-group").hide();
        $('.image-edit-tools').show();
    });

    $(document).on('click', '.btn-back-grid', function () {
        $('.full-size-image').hide();
        $('.available-image-grid').show();
        $('.image-edit-tools').hide();
    });

    $('#selectImgModal #submit').on('click', function () {
        $("input[name=file_ids]").val(nameCheckedFiles.join(' '));
        axios({
            method: 'post',
            url: '/banner/update_product_selections',
            data: {
                file_ids: indexCheckedFiles.join(' ')
            }
        })
            .then(function (response) {
                var data = response.data;
            })
            .catch(function (response) {
                showError([response]);
            });
    });

    $('#view-img').click(function () {
        if (!($(this).hasClass('disabled'))) {
            axios({
                method: 'post',
                url: '/banner/view',
                data: {
                    file_ids: $("input[name=file_ids]").val(),
                    show_warning: true
                }
            })
                .then(function (response) {
                    var data = response.data;
                    if (data.status == "error") {
                        showError(data.messages);
                    } else {
                        if (data.status == "warning") {
                            showError(data.messages);
                        }
                        $('.full-size-image').hide();
                        $('.image-edit-tools').hide();
                        $(".image-crop .button-group").hide();
                        $('.available-image-grid').show();
                        $('#product-images').empty();
                        indexCheckedFiles = [];
                        nameCheckedFiles = [];

                        if (data.files.length >= 2 || data.files[0].related_files.length >= 2) {
                            var html = "";
                            var base_url = window.location.origin;
                            for (var file of data.files) {
                                if (file.popular_file)
                                    indexCheckedFiles.push(file.popular_file.id);
                                html += "<div class='image-grid-responsive'>";
                                html += "<p class='font-weight-bold'>" + file.name + "</p>";
                                html += "<div class='grid'>";

                                for (var rfile of file.related_files) {
                                    if (indexCheckedFiles.includes(rfile.id)) {
                                        html += "<div class='grid-item selected'>";
                                        nameCheckedFiles.push(rfile.name.split(".")[0]);
                                    } else {
                                        html += "<div class='grid-item'>";
                                    }
                                    html += "<input class='d-none' data-name='" + rfile.name.split(".")[0] + "' value='" + rfile.id + "'/>";
                                    html += `<img src='${base_url}/share?file=${rfile.thumbnail}' loading='lazy'/>`;
                                    html += "<p>" + rfile.name + "</p>";
                                    html += "<div class='overlay' style='display: none'>";
                                    html += `<a href="javascript: void(0);" data-name="${rfile.name}" data-path="${rfile.path}" data-width="${rfile.width}" data-height="${rfile.height}" data-company_id="${rfile.company_id}">`;
                                    html += "<i class='cil-search'></i> View Image</a>";
                                    html += "</div></div>";
                                }
                                html += "</div></div>";
                            }
                            $('.available-image-grid').empty();
                            $('.available-image-grid').append(html);
                            $('#selectImgModal #submit').show();
                            $('#selectImgModal').modal();
                        } else {
                            var base_url = window.location.origin;
                            for (var file of data.files[0].related_files) {
                                $('.available-image-grid').hide();
                                $('.full-size-image').empty();
                                $('.full-size-image').append($(`<img src="${base_url}/share?file=${file.path}" class="product-image" id="full-size-image" />`));
                                $('#full-size-image').data('name', file.name);
                                $('#full-size-image').data('type', "");
                                $('#full-size-image').data('company_id', file['company_id']);
                                $('#full-size-image').data('path', file.path);
                                $('.full-size-image').append($(`<span class="product-image-description float-right">${file.name} [${file.width.toFixed(2)} x ${file.height.toFixed(2)} WxH]</span>`));
                            }
                            $('.full-size-image').show();
                            $('.image-edit-tools').show();
                            $('#image-crop-button').show();
                            $('#selectImgModal #submit').hide();
                            $('#selectImgModal').modal();
                        }
                    }
                })
                .catch(function (response) {
                    showError([response]);
                });
        }
    });

    $('input[name=file_ids]').keyup(function () {
        var elem = $(this);
        var file_ids = elem.val();
        if (file_ids.length > 0) {
            $('#view-img').removeClass('disabled');
        } else {
            $('#view-img').addClass('disabled');
        }
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('#shareModal #submit').on('click', function (e) {
        e.preventDefault();
        var draft_url = $('#saved-draft').val();
        var project_url = $('#published-project').val();
        var val = $("#shareModal input[name=share_ads]:checked").val();
        var email = $('#shareModal #share_email').val();
        if (draft_url && project_url) {
            var url, projectname;
            if (val == 'save') {
                url = draft_url;
                projectname = $('#saved-draft').data('projectname');
            } else if (val == 'publish') {
                url = project_url;
                projectname = $('#published-project').data('projectname');
            }
            sendNotificationEmail(email, url, projectname);
        } else if (draft_url || project_url) {
            var url, projectname;
            if (draft_url) {
                url = draft_url;
                projectname = $('#saved-draft').data('projectname');
            } else {
                url = project_url;
                projectname = $('#published-project').data('projectname');
            }
            sendNotificationEmail(email, url, projectname);
        } else {
            if (val == 'save') {
                var formData = new FormData(document.getElementById('adForm'));
                $('.generate-alert').css('display', 'flex');
                axios({
                    method: 'post',
                    url: '/banner/generate',
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                    .then((response) => {
                        download_file(response.data);
                        $('#generate-ads').prop('disabled', false);
                        sendNotificationEmail(email, response.data.url, response.data.projectname);
                        $('.generate-alert').html("Saved!");
                        setTimeout(() => {
                            $('.generate-alert').css('display', 'none');
                            $('.generate-alert').html("Generating...");
                        }, 1500);
                    })
                    .catch((response) => {
                        showError([response]);
                        $('.generate-alert').css('display', 'none');
                        $('#generate-ads').prop('disabled', false);
                    });
            } else if (val == 'publish') {
                var formData = new FormData(document.getElementById('adForm'));
                $('.generate-alert').css('display', 'flex');
                axios({
                    method: 'post',
                    url: '/banner/publish',
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                    .then(function (response) {
                        download_file(response.data);
                        $('#publish-team-ads').prop('disabled', false);
                        sendNotificationEmail(email, response.data.url, response.data.projectname);
                        $('.generate-alert').html("Saved!");
                        setTimeout(() => {
                            $('.generate-alert').css('display', 'none');
                            $('.generate-alert').html("Generating...");
                        }, 1500);
                    })
                    .catch(function (response) {
                        showError([response]);
                        $('.generate-alert').css('display', 'none');
                        $('#publish-team-ads').prop('disabled', false);
                    });
            }
        }
    });

    function download_file(data) {
        if (data.status == "error") {
            showError(data.messages);
        } else {
            if (data.status == "warning") {
                showError(data.messages);
            }
            var url = data.url;
            var log = data.log;

            $('#logModal .log-block').text(log);
            var link = document.createElement('a');
            link.href = url;
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    }
});
