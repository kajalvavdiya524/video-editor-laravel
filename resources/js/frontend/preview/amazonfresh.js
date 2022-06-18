import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
    var product_left = 700;
    var product_width = 500;
    var product_height = 286;
    var baseline = 300;
    var max_height = 0;
    var circle_pos = {
        'top' : {'radius' : 240, 'x' : 715, 'y' : -240},
        'bottom' : {'radius' : 240, 'x' : 715, 'y' : 60},
        'center' : {'radius' : 240, 'x' : 715, 'y' : -90}
    };

    var template = $("input[name='output_dimensions']").val();
    template = parseInt(template);

    var canvas = new fabric.Canvas('canvas');
    canvas.setDimensions({ width: '486px', height: '100px' }, { cssOnly: true });
    $('#preview-popup').show();
    
    function drawForLoading() {
        var headline = new fabric.Text(" ", {
            id: "headline0",
            top: -100,
            left: -100,
            fontSize: 50,
            fill: "#000", 
            fontFamily: "Amazon-Ember-Bold"
        });
        var sub_headline = new fabric.Text(" ", {
            id: "sub_headline0",
            top: -100,
            left: -100,
            fontSize: 36,
            fill: "#000", 
            fontFamily: "Amazon-Ember"
        });
        canvas.add(sub_headline);
        canvas.add(headline);
        canvas.renderAll();
    }

    $('input[name="file_ids"], input[name="product_space"]').on('change', function () {
        drawProductImage();
    });

    function onChangeHeadline() {
        if (isNaN(template) || template == 0) {
            drawHeadline(324, 111, 0);
        } else if (template == 1) {
            console.log(template);
            drawHeadline(324, 155, 0);
        } else if (template == 2) {
            drawHeadline(326, 105, 0);
            drawHeadline(326, 160, 1);
        } else if (template == 3) {
            drawHeadline(326, 84, 0);
            drawHeadline(326, 139, 1);
        } else if (template == 4) {
            drawHeadline(326, 91, 0);
        } else if (template == 5) {
            drawHeadline(326, 73, 0);
            drawHeadline(326, 128, 1);
            drawHeadline(326, 183, 2);
        }
    }

    $('input[name^="headline"], select[name^="headline"]').on('change', function () {
        onChangeHeadline();
    });

    function onChangeSubheadline() {
        if (isNaN(template) || template == 0) {
            drawSubHeadline(324, 163, 0);
        } else if (template == 1) {
            drawSubHeadline(324, 110, 0);
        } else if (template == 3) {
            drawSubHeadline(326, 191, 0);
        } else if (template == 4) {
            drawSubHeadline(326, 143, 0);
            drawSubHeadline(326, 185, 1);
        }
    }

    $('input[name^="subheadline"], select[name^="subheadline"]').on('change', function () {
        onChangeSubheadline();
    });
    $('input[name^="CTA"], select[name^="CTA"]').on('change', function () {
        drawCTA();
    });
    $('select[name="color_name"], select[name="circle_position"]').on('change', function () {
        drawBackground();
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
    drawForLoading();
    drawProductImage();
    drawBackground();
    setTimeout(function () {
        onChangeHeadline();
        onChangeSubheadline();
    }, 1000);

    function drawBackground() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'circle') {
                canvas.remove(o);
            }
        });
        var background_color = $('input[name="background_color"]').val();
        var circle_color = $('input[name="circle_color"]').val();
        var circle_position = $('select[name="circle_position"]').val();

        if (circle_position !== "none") {
            var circle = new fabric.Circle({
                id: "circle",
                top: circle_pos[circle_position]["y"],
                left: circle_pos[circle_position]["x"],
                radius: circle_pos[circle_position]["radius"],
                fill: circle_color, 
                selectable: false,
                evented: false
            });
            canvas.add(circle);
            canvas.sendToBack(circle);
        }
        canvas.backgroundColor = background_color;
        canvas.renderAll();
    }

    function drawHeadline(left, top, index) {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'headline'+index) {
                canvas.remove(o);
            }
        });
        // headline
        var headline_text = $('input[name="headline[]"]').eq(index).val();
        if (headline_text == "") {
            headline_text = $('input[name="headline[]"]').eq(index).attr("placeholder");
        }
        var headline = new fabric.Text(headline_text, {
            id: "headline"+index,
            top: top,
            left: left,
            fontSize: 50,
            fill: "#000", 
            fontFamily: "Amazon-Ember-Bold",
            selectable: false,
            evented: false
        });
        canvas.add(headline);
        canvas.renderAll();
    }

    function drawSubHeadline(left, top, index) {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'sub_headline'+index) {
                canvas.remove(o);
            }
        });
        // sub-headline
        var subheadline_text = $('input[name="subheadline[]"]').eq(index).val();
        if (subheadline_text == "") {
            subheadline_text = $('input[name="subheadline[]"]').eq(index).attr("placeholder");
        }
        var sub_headline = new fabric.Text(subheadline_text, {
            id: "sub_headline"+index,
            top: top,
            left: left,
            fontSize: 36,
            fill: "#000", 
            fontFamily: "Amazon-Ember",
            selectable: false,
            evented: false
        });
        canvas.add(sub_headline);
        canvas.renderAll();
    }

    function drawCTA() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'cta') {
                canvas.remove(o);
            }
        });
        // call to action
        var cta_text = $('input[name="CTA"]').val();
        var cta = new fabric.Text(cta_text, {
            id: "cta",
            top: 273,
            left: 0,
            fontSize: 20,
            fill: "#000",
            selectable: false,
            evented: false
        });
        cta.left = (800 - cta.width) / 2;
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
            var bottom_position = parseInt($('input[name="bottom_position"]').val());
            var images_position = parseInt($('input[name="images_position"]').val());
            var left = product_left;
            var files = response.data.files;
            if (!files) return;
            var sum_width_dimension = 0;
            files.forEach(file => {
                sum_width_dimension += file.related_files[0].width;
            });
            product_width -= product_space * (files.length - 1);
            max_height = 0;

            var res = await Promise.all(files.map(file => loadFabricImage(file.related_files[0], sum_width_dimension)));
            var r = max_height > product_height ? product_height / max_height : 1;
            var total_width = 0;
            res.forEach(item => {
                item.width *= r;
                item.height *= r;
                total_width += item.width;
            });
            left += (product_width - total_width) / 2;
            res.forEach((item, index) => {
                if (index) {
                    left += product_space;
                }
                var shadow = new fabric.Shadow({ 
                    color: '#333', 
                    blur: 300, 
                    offsetX: -400,
                    offsetY: 200
                }); 
                item.image.set({ 'left': left + images_position });
                item.image.scaleToWidth(item.width);
                item.image.set({ 'top': baseline - item.height - bottom_position });
                item.image.set({ id: "image" });
                item.image.set({shadow: shadow});
                item.image.set({
                    selectable: false,
                    evented: false
                });
                left += item.width;
                canvas.add(item.image);
            });
        });
    }
});
