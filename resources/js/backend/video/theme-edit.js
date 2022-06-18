require('../../bootstrap');


require("jquery-ui");

require('vanderlee-colorpicker');


$(document).ready(function () {
    function formatOutput(optionElement) {
        return $(`<span style="font-family: ${optionElement.text}">${optionElement.text}</span>`);
    };

    $('#fonts-select').select2({
        templateResult: formatOutput
    });
    $('#stroke-colors-select').select2();
    $('#font-colors-select').select2();

    $('#fonts-select').on('select2:select', function (e) {
        const font = e.params.data.id

        $('#default-font-select').append(`<option value="${font}" style="font-family: ${font}">${font}</option>`)
    });

    $('#fonts-select').on('select2:unselect', function (e) {
        const font = e.params.data.id

        $(`#default-font-select option[value='${font}']`).each(function () {
            $(this).remove();
        });
    });

    $('#font-colors-select').on('select2:select', function (e) {
        const font = e.params.data.id;
        const font_name = e.params.data.text;

        $('#default-font-color-select').append(`<option value="${font}" style="font-family: ${font}">${font_name}</option>`)
    });

    $('#font-colors-select').on('select2:unselect', function (e) {
        const font = e.params.data.id

        $(`#default-font-color-select option[value='${font}']`).each(function () {
            $(this).remove();
        });
    });

    var colorpicker = $('#color-picker').colorpicker({
        parts: ['map', 'bar', 'hex', 'rgb'],
        colorFormat: ['RGB', '#HEX'],
        select: function (event, color) {

            $('#HEX_color').val('#' + color.hex);
            $('#RGB_Color').val(color.formatted);
        }
    });


    //Custom colors
    $('.custom_color').on('click', function () {
        $('#color-picker-modal').modal();
        $('#color_type').val($(this).data('type'));
    });


    $('#save-custom-color-btn').on('click', function () {
        var color_type = $('#color_type').val();
        var color_name = $('#color_name').val();
        var HEX_color = $('#HEX_color').val();

        if (color_name.length == 0) {
            color_name = HEX_color;
        }

        var filtered_color_type = $("#custom_colors_color_type").val().split(',').filter(Boolean)
        filtered_color_type.push(color_type)
        $("#custom_colors_color_type").val(filtered_color_type);

        var filtered_color_name = $("#custom_colors_color_name").val().split(',').filter(Boolean)
        filtered_color_name.push(color_name)
        $("#custom_colors_color_name").val(filtered_color_name);

        var filtered_HEX_color = $("#custom_colors_HEX_color").val().split(',').filter(Boolean)
        filtered_HEX_color.push(HEX_color)
        $("#custom_colors_HEX_color").val(filtered_HEX_color);
        var newOption = new Option(color_name,HEX_color, true, true);

        if (color_type == 'Font') {
            $('#font-colors-select').append(newOption).trigger('change');
        } else {
             $('#stroke-colors-select').append(newOption).trigger('change');
        }
        $('#color_type').val('');
        $('#color_name').val('');
        $('#HEX_color').val('');

    })


})
;
