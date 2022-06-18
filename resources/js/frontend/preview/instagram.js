import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
    var dimension_width = 1080;
    var dimension_height = 1080;
    var product_width = 1080;
    var product_height = 640;
    var baseline = 840;
    var max_height = 0;

    var canvas = new fabric.Canvas('canvas');
    canvas.setDimensions({ width: '360px', height: '360px' }, { cssOnly: true });
    $('#preview-popup').show();
    
    $('input[name="file_ids"], input[name="product_space"]').on('change', function () {
        drawProductImage();
    });
    $('input[name^="headline"], select[name^="headline"]').on('change', function () {
        drawHeadline();
    });
    $('input[name^="subheadline"], select[name^="subheadline"]').on('change', function () {
        drawSubHeadline();
    });
    $('input[name^="CTA"], select[name^="CTA"]').on('change', function () {
        drawCTA();
    });
    $('input[name="background"]').on('change', function () {
        setBackgroundImage(this);
    });
    $('input[id="background_color"]').on('change', function () {
        setBackgroundColor();
    });
    $('select[name="product_layouts"]').on('change', function(e) {
        var product_layouts = $(this).val();
        if (product_layouts == 1) {
        } else if (product_layouts == 2) {
            canvas.getObjects().forEach(function (o) {
                if (o.id != 'headline') {
                    canvas.remove(o);
                }
            });
            drawHeadline();
        } else if (product_layouts == 3) {
            canvas.getObjects().forEach(function (o) {
                if (o.id != 'image') {
                    canvas.remove(o);
                }
            });
            drawProductImage();
        }
    });
    
    $('#selectImgModal #submit').on('click', function () {
        drawProductImage();
    });
    
    $('.toggle-button').on('click', function() {
        if ($(this).text() == '-') {
            $('.canvas-container').fadeOut();
            $(this).text('+');
        } else {
            $('.canvas-container').fadeIn();
            $(this).text('-');
        }
    });

    // Draw product images
    drawProductImage();

    function getMeta(url) {
        return new Promise((resolve, reject) => {
            let img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject();
            img.src = url;
        });
    }

    async function setBackgroundImage(input) {
        var url = URL.createObjectURL(input.files[0]);
        var imageUrl = $('input[name="background"]').val();
        if (imageUrl == "") {
            canvas.backgroundImage = null;
            canvas.renderAll();
            return;
        }

        var img = await getMeta(url);
        var canvasAspect = dimension_width / dimension_height;
        var imgAspect = img.width / img.height;
        var left, top, scaleFactor;

        if (canvasAspect < imgAspect) {
            var scaleFactor = dimension_width / img.width;
            left = 0;
            top = -((img.height * scaleFactor) - dimension_height) / 2;
        } else {
            var scaleFactor = dimension_height / img.height;
            top = 0;
            left = -((img.width * scaleFactor) - dimension_width) / 2;
        }

        canvas.setBackgroundImage(url, canvas.renderAll.bind(canvas), {
            top: top,
            left: left,
            originX: 'left',
            originY: 'top',
            scaleX: scaleFactor,
            scaleY: scaleFactor
        });
    }

    function setBackgroundColor() {
        var color = $('input[id="background_color"]').val();
        canvas.backgroundColor = color;
        canvas.renderAll();
    }

    function drawHeadline() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'headline') {
                canvas.remove(o);
            }
        });
        // headline
        var headline_text = $('input[name="headline[]"]').val();
        var headline_font = $('select[name="headline_font[]"]').val();
        var headline_fontsize = $('input[name="headline_font_size[]"]').val();
        var headline_color = $('input[name="headline_color[]"]').val();
        var headline_align = $('select[name="headline_alignment[]"]').val();
        var headline = new fabric.Text(headline_text, {
            id: "headline",
            top: 60,
            left: 0,
            fontSize: headline_fontsize,
            fill: headline_color,
            selectable: false,
            evented: false
        });
        if (headline_align == "center") {
            headline.left = (product_width - headline.width) / 2;
        } else if (headline_align == "right") {
            headline.left = product_width - headline.width;
        }
        canvas.add(headline);
    }

    function drawSubHeadline() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'sub_headline') {
                canvas.remove(o);
            }
        });
        // sub-headline
        var subheadline_text = $('input[name="subheadline[]"]').val();
        var subheadline_font = $('select[name="subheadline_font[]"]').val();
        var subheadline_fontsize = $('input[name="subheadline_font_size[]"]').val();
        var subheadline_color = $('input[name="subheadline_color[]"]').val();
        var subheadline_align = $('select[name="subheadline_alignment[]"]').val();
        var sub_headline = new fabric.Text(subheadline_text, {
            id: "sub_headline",
            top: 260,
            left: 0,
            fontSize: subheadline_fontsize,
            fill: subheadline_color,
            fontFamily: subheadline_font,
            selectable: false,
            evented: false
        });
        if (subheadline_align == "center") {
            sub_headline.left = (product_width - sub_headline.width) / 2;
        } else if (subheadline_align == "right") {
            sub_headline.left = product_width - sub_headline.width;
        }
        canvas.add(sub_headline);
    }

    function drawCTA() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'cta') {
                canvas.remove(o);
            }
        });
        // call to action
        var cta_text = $('input[name="CTA"]').val();
        var cta_font = $('select[name="CTA_font"]').val();
        var cta_fontsize = $('input[name="CTA_font_size"]').val();
        var cta_color = $('input[name="CTA_color"]').val();
        var cta_align = $('select[name="CTA_alignment"]').val();
        var cta = new fabric.Text(cta_text, {
            id: "cta",
            top: 600,
            left: 0,
            fontSize: cta_fontsize,
            fill: cta_color,
            textAlign: 'center',
            selectable: false,
            evented: false
        });
        if (cta_align == "center") {
            cta.left = (product_width - cta.width) / 2;
        } else if (cta_align == "right") {
            cta.left = product_width - cta.width;
        }
        canvas.add(cta);
    }

    function loadFabricImage(file, sum_width_dimension) {
        return new Promise((resolve, reject) => {
            fabric.Image.fromURL("/share?file=" + file.path, function (oImg) {
                var width = product_width * file.width / sum_width_dimension;
                var r = width / oImg.width;
                var height = oImg.height * r;
                max_height = max_height < height ? height : max_height;
                resolve({ image: oImg, width, height });
            });
        })
    }

    function drawProductImage() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'image') {
                canvas.remove(o);
            }
        });
        axios({
            method: 'post',
            url: '/banner/view',
            data: {
                file_ids: $("input[name=file_ids]").val(),
                show_warning: true
            }
        }).then(async function (response) {
            var product_space = parseInt($('input[name="product_space"]').val());
            var product_layouts = $('select[name="product_layouts"]').val();
            var left = 0;
            var files = response.data.files;
            if (!files) return;
            var sum_width_dimension = 0;
            files.forEach(file => {
                sum_width_dimension += file.related_files[0].width;
            });
            product_width -= product_space * (files.length - 1);
            max_height = 0;

            if (product_layouts == 0) {
                var res = await Promise.all(files.map(file => loadFabricImage(file.related_files[0], sum_width_dimension)));
                var r = max_height > product_height ? product_height / max_height : 1;
                var total_width = 0;
                res.forEach(item => {
                    item.width *= r;
                    item.height *= r;
                    total_width += item.width;
                });
                left = (product_width - total_width) / 2;
                res.forEach((item, index) => {
                    if (index) {
                        left += product_space;
                    }
                    item.image.set({ 'left': left });
                    item.image.scaleToWidth(item.width);
                    item.image.set({ 'top': baseline - item.height });
                    item.image.set({ id: "image" });
                    item.image.set({
                        selectable: false,
                        evented: false
                    });
                    left += item.width;
                    canvas.add(item.image);
                });
            } else if (product_layouts == 3) {
                var coordinate_x = [-product_width/8, product_width*2/5, product_width/6, product_width*2/3];
                var coordinate_y = [dimension_height/5, -dimension_height/5, dimension_height*3/5, dimension_height/6];
                var res = await Promise.all(files.map(file => loadFabricImage(file.related_files[0], sum_width_dimension / 1.5)));
                var r = max_height > product_height ? product_height / max_height : 1;
                var total_width = 0;
                res.forEach(item => {
                    item.width *= r;
                    item.height *= r;
                    total_width += item.width;
                });
                left = (product_width - total_width) / 2;
                res.forEach((item, index) => {
                    var bound_width = (item.width + item.height) / Math.sqrt(2);
                    item.image.scaleToWidth(item.width);
                    item.image.set({ angle: 45 });
                    item.image.set({ 'left': coordinate_x[index] + bound_width / 2 });
                    item.image.set({ 'top': coordinate_y[index] });
                    item.image.set({ id: "image" });
                    left += item.width;
                    canvas.add(item.image);
                });
            }
            
        });
    }
});
