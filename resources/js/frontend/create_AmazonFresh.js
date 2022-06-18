require('bootstrap-fileinput')
require('select2')
require('ekko-lightbox')

$(document).ready(function () {

    $(".form-control-file").fileinput({'showUpload':false, 'previewFileType':'any'});
    
    function showCharacterCount() {
        var total_length = 0;
        var badge = $(`.character-count`);
        $(`input[name='headline[]'`).each(function () {
            total_length += $(this).val().length;
        });
        $(`input[name='subheadline[]'`).each(function () {
            total_length += $(this).val().length;
        });
        badge.removeClass("badge-sucess badge-warning");
        if (total_length > 40) {
            badge.addClass("badge-warning");
        } else {
            badge.addClass("badge-success");
        }
        badge.text(`${total_length} / 40`);
    }

    $('input[name="headline[]"').on('keyup', function (e) {
        showCharacterCount();
    });
    
    $('input[name="subheadline[]"').on('keyup', function (e) {
        showCharacterCount();
    });

    $('.two-color-picker select').on('change', function (e) {
        var colors = {
            blue: ['#b8dde1', '#05a4b4'],
            teal: ['#D2F7E7', '#36C2B4'],
            green: ['#E4FDBF', '#ADE421'],
            yellow: ['#ffebb7', '#f3aa46'],
            red: ['#fcceaa', '#FF7676'],
            pink: ['#ffcfcf', '#FF7676'],
            purple: ['#ffd1f0', '#DD85D1']
        }
        var key = $(this).val();
        var back = $(this).next();
        back.removeClass();
        back.addClass('background-color');
        back.addClass(key);
        $('input[name=background_color]').val(colors[key][0]);
        $('input[name=circle_color]').val(colors[key][1]);
    });

    $('input[name=output_dimensions], select[name=output_dimensions]').on('change', function (e) {
        var template = parseInt($(this).val());
        $('.headline-label').text('Headline');
        $('.subheadline-label').text('Sub-headline');
        $('.form-row-group').removeClass('d-none');
        $('.form-row-group').show();
        $('.form-row-group .form-row').removeClass('d-none');
        $('.form-row-group .form-row').css('display', 'flex');
        $('input[name="headline[]"').val('');
        $('input[name="subheadline[]"').val('');
        switch (template) {
            case 0: // 1H+1S
                $('.headline-2').css('display', 'none');
                $('.headline-3').css('display', 'none');
                $('.subheadline-2').css('display', 'none');
                break;
            case 1: // NEW + 1H
                $('.headline-2').css('display', 'none');
                $('.headline-3').css('display', 'none');
                $('.subheadline-2').css('display', 'none');
                $('.headline-label').text('"NEW" Headline');
                $('.subheadline-label').text('Headline');
                break;
            case 2: // 2H
                $('.headline-3').css('display', 'none');
                $('.form-row-group.subheadline').hide();
                break;
            case 3: // 2H+1S
                $('.subheadline-2').css('display', 'none');
                $('.headline-3').css('display', 'none');
                break;
            case 4: // 1H+2S
                $('.headline-2').css('display', 'none');
                $('.headline-3').css('display', 'none');
                break;
            case 5: // 3H
                $('.form-row-group.subheadline').hide();
                break;
        }
        showCharacterCount();
    });


});