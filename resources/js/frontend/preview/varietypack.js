import { fabric } from "fabric";
import { countBy } from "lodash";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
    var _wrapLine = function (_line, lineIndex, desiredWidth, reservedSpace) {
        var lineWidth = 0,
            splitByGrapheme = this.splitByGrapheme,
            graphemeLines = [],
            line = [],
            // spaces in different languges?
            words = splitByGrapheme ? fabric.util.string.graphemeSplit(_line) : _line.split(this._wordJoiners),
            word = '',
            offset = 0,
            infix = splitByGrapheme ? '' : ' ',
            wordWidth = 0,
            infixWidth = 0,
            largestWordWidth = 0,
            lineJustStarted = true,
            additionalSpace = splitByGrapheme ? 0 : this._getWidthOfCharSpacing();

        reservedSpace = reservedSpace || 0;
        desiredWidth -= reservedSpace;
        for (var i = 0; i < words.length; i++) {
            // i would avoid resplitting the graphemes
            word = fabric.util.string.graphemeSplit(words[i]);
            wordWidth = this._measureWord(word, lineIndex, offset);
            offset += word.length;

            // Break the line if a word is wider than the set width
            if (this.breakWords && wordWidth >= desiredWidth) {

                if (!lineJustStarted) {
                    line.push(infix);
                    lineJustStarted = true;
                }

                // Loop through each character in word
                for (var w = 0; w < word.length; w++) {
                    var letter = word[w];
                    var letterWidth = this.getMeasuringContext().measureText(letter).width * this.fontSize / this.CACHE_FONT_SIZE;
                    if (lineWidth + letterWidth > desiredWidth) {
                        graphemeLines.push(line);
                        line = [];
                        lineWidth = 0;
                    } else {
                        line.push(letter);
                        lineWidth += letterWidth;
                    }
                }
                word = [];
            } else {
                lineWidth += infixWidth + wordWidth - additionalSpace;
            }

            if (lineWidth >= desiredWidth && !lineJustStarted) {
                graphemeLines.push(line);
                line = [];
                lineWidth = wordWidth;
                lineJustStarted = true;
            } else {
                lineWidth += additionalSpace;
            }

            if (!lineJustStarted) {
                line.push(infix);
            }
            line = line.concat(word);

            infixWidth = this._measureWord([infix], lineIndex, offset);
            offset++;
            lineJustStarted = false;
            // keep track of largest word
            if (wordWidth > largestWordWidth && !this.breakWords) {
                largestWordWidth = wordWidth;
            }
        }

        i && graphemeLines.push(line);

        if (largestWordWidth + reservedSpace > this.dynamicMinWidth) {
            this.dynamicMinWidth = largestWordWidth - additionalSpace + reservedSpace;
        }

        return graphemeLines;
    };

    fabric.util.object.extend(fabric.Textbox.prototype, {
        _wrapLine: _wrapLine,
    });

    var dimension = [
        { 'width': 3000, 'height': 3000 },
        { 'width': 3000, 'height': 3000 },
        { 'width': 3000, 'height': 3000 },
        { 'width': 1500, 'height': 1500 },
        { 'width': 3000, 'height': 3000 },
        { 'width': 3000, 'height': 3000 }
    ];
    var product = [
        { 'width': 1900, 'height': 1750, 'baseline': 1300, 'left': 0 },
        { 'width': 1900, 'height': 1750, 'baseline': 1300, 'left': 0 },
        { 'width': 3000, 'height': 3000, 'baseline': 500, 'left': 10 },
        { 'width': 1500, 'height': 1500, 'baseline': 0, 'left': 10 },
        { 'width': 3000, 'height': 3000, 'baseline': 500, 'left': 10 },
        { 'width': 3000, 'height': 3000, 'baseline': 500, 'left': 10 }
    ];
    var product_width;

    var max_height = [0, 0];

    var sum_width_dimension = 0;
    var sum_width_dimension_top = 0;

    var template = $("input[name='output_dimensions']").val();
    template = parseInt(template);
    if (isNaN(template)) {
        template = 0;
    }

    var canvas = new fabric.Canvas('canvas');
    canvas.setDimensions({ width: '300px', height: '300px' }, { cssOnly: true });
    $('#preview-popup').show();

    $('.toggle-button').on('click', function () {
        if ($(this).text() == '-') {
            $('.canvas-container').fadeOut();
            $(this).text('+');
        } else {
            $('.canvas-container').fadeIn();
            $(this).text('-');
        }
    });

    drawBoxImage();
    drawProductImage();

    function loadFabricImage(file, sum_width_dimension, index) {
        return new Promise((resolve, reject) => {
            fabric.Image.fromURL("/share?file=" + file.thumbnail, function (oImg) {
                var width = product_width * file.width / sum_width_dimension;
                var r = width / oImg.width;
                var height = oImg.height * r;
                max_height[index] = max_height[index] < height ? height : max_height[index];
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
            var product_space = parseInt($("input[name='product_spacing']").val());
            var product_height = product[template]['height'];
            var left = product[template]["left"];
            var files = response.data.files;
            if (!files) return;
            var count = files.length;
            product_width = product[template]['width'];
            product_width -= product_space * (count - 1);

            var angleList = [];
            for (var i = 0; i < count; i++) {
                if (count % 2) {
                    if (i == 0) {
                        angleList.push(0);
                    } else {
                        angleList.push(20 * Math.pow(0.5, Math.ceil(i / 2)) * Math.pow(-1, i));
                    }
                } else {
                    var idx = i + 1;
                    angleList.push(20 * Math.pow(0.5, Math.ceil(idx / 2)) * Math.pow(-1, idx));
                }
            }
            angleList.sort();

            sum_width_dimension = 0;
            files.forEach(file => {
                sum_width_dimension += file.related_files[0].width;
            });
            sum_width_dimension = sum_width_dimension_top > sum_width_dimension ? sum_width_dimension_top : sum_width_dimension;
            var res = await Promise.all(files.map((file) => loadFabricImage(file.related_files[0], sum_width_dimension, 0)));
            var r = max_height[0] > product_height ? product_height / max_height[0] : 1;
            var total_width = 0;
            res.forEach(item => {
                item.width *= r;
                item.height *= r;
                total_width += item.width;
            });
            left += (dimension[template]['width'] - total_width - product_space * (count - 1)) / 2;
            res.forEach((item, index) => {
                if (index) {
                    left = left + 170 + product_space;
                }
                item.image.set({ 'left': left });
                item.image.scaleToWidth(item.width);
                item.image.set({ angle: angleList[index] });

                item.image.set({ 'top': product[template]['baseline'] + 600 - item.height - angleList[index] * 5 });
                item.image.set({ id: "image" });
                item.image.set({
                    selectable: false, 
                    evented: false
                });
                left += item.width + product_space;
                canvas.add(item.image);
            });
            canvas.getObjects().forEach(function (o) {
                if (o.id == 'box-front') {
                    canvas.bringToFront(o);
                }
            });
        });
    }

    function drawProductImageTop() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'image_top') {
                canvas.remove(o);
            }
        });
        axios({
            method: 'post',
            url: '/banner/view',
            data: {
                file_ids: $("input[name=file_top_ids]").val(),
                show_warning: true
            }
        }).then(async function (response) {
            var product_space = parseInt($("input[name='product_spacing']").val());
            var product_height = product[template]['height'];
            var left = product[template]["left"];
            var files = response.data.files;
            if (!files) return;
            var count = files.length;
            product_width = product[template]['width'];
            product_width -= product_space * (count - 1);

            var angleList = [];
            for (var i = 0; i < count; i++) {
                if (count % 2) {
                    if (i == 0) {
                        angleList.push(0);
                    } else {
                        angleList.push(20 * Math.pow(0.5, Math.ceil(i / 2)) * Math.pow(-1, i));
                    }
                } else {
                    var idx = i + 1;
                    angleList.push(20 * Math.pow(0.5, Math.ceil(idx / 2)) * Math.pow(-1, idx));
                }
            }
            angleList.sort();

            sum_width_dimension_top = 0;
            files.forEach(file => {
                sum_width_dimension_top += file.related_files[0].width;
            });
            sum_width_dimension_top = sum_width_dimension_top > sum_width_dimension ? sum_width_dimension_top : sum_width_dimension;
            var res = await Promise.all(files.map((file) => loadFabricImage(file.related_files[0], sum_width_dimension_top, 1)));
            var r = max_height[1] > product_height ? product_height / max_height[1] : 1;
            var total_width = 0;
            res.forEach(item => {
                item.width *= r;
                item.height *= r;
                total_width += item.width;
            });
            left += (dimension[template]['width'] - total_width) / 2;
            res.forEach((item, index) => {
                if (index) {
                    left = left + 170 + product_space;
                }
                item.image.set({ 'left': left });
                item.image.scaleToWidth(item.width);
                item.image.set({ angle: angleList[index] });

                console.log(angleList[index]);
                item.image.set({ 'top': product[template]['baseline'] + 150 - item.height - angleList[index] * 5 });
                item.image.set({ id: "image_top" });
                item.image.set({
                    selectable: false, 
                    evented: false
                })
                left += item.width + product_space;
                canvas.add(item.image);
            });
            canvas.getObjects().forEach(function (o) {
                if (o.id == 'box-front') {
                    canvas.bringToFront(o);
                }
                if (o.id == 'image_top') {
                    canvas.sendToBack(o);
                }
            });
        });
    }

    function drawBoxImage() {
        var base_url = window.location.origin;
        canvas.getObjects().forEach(function (o) {
            if (o.id == 'box-front' || o.id == 'box-back') {
                canvas.remove(o);
            }
        });
        fabric.Image.fromURL(base_url + "/share?file=files/common/box-back.png", function (oImg) {
            var r = oImg.width / dimension[template]['width'];
            oImg.set({
                id: "box-back",
                left: 0,
                top: product[template]['baseline'],
                selectable: false,
                evented: false
            });
            oImg.scaleToWidth(oImg.width / r);
            oImg.scaleToHeight(oImg.height / r);
            canvas.add(oImg);
            canvas.sendToBack(oImg);
        });
        fabric.Image.fromURL(base_url + "/share?file=files/common/box-front.png", function (oImg) {
            var r = oImg.width / dimension[template]['width'];
            oImg.set({
                id: "box-front",
                left: 0,
                top: product[template]['baseline'] + 308,
                selectable: false,
                evented: false
            });
            oImg.scaleToWidth(oImg.width / r);
            oImg.scaleToHeight(oImg.height / r);
            canvas.add(oImg);
            canvas.bringToFront(oImg);
        });
    }

    $('input[name="file_ids"], input[name="product_spacing"]').on('change', function () {
        drawProductImage();
    });

    $('input[name="file_top_ids"], input[name="product_spacing"]').on('change', function () {
        drawProductImageTop();
    });
    $('#selectImgModal #submit').on('click', function () {
        drawProductImage();
        drawProductImageTop();
    });
});
