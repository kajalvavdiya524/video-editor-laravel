import { fabric } from "fabric";

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
    var max_height = 0;
    var sum_width_dimension = 0;

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

    drawProductImage();

    function loadFabricImage(file, sum_width_dimension) {
        return new Promise((resolve, reject) => {
            fabric.Image.fromURL("/share?file=" + file.thumbnail, function (oImg) {
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
            
        });
    }

    $('input[name="file_ids"]').on('change', function () {
        drawProductImage();
    });
    $('#selectImgModal #submit').on('click', function () {
        drawProductImage();
    });
});
