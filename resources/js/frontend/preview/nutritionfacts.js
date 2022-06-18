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

    function loadFabricNFImage(file, sum_width_dimension) {
        return new Promise((resolve, reject) => {
            var filename = file.name.split('.')[0];
            fabric.Image.fromURL("/share?file=files/" + file.company_id + "/Nutrition_Facts_Images/" + filename + ".jpg", function (oImg) {
                var width = product_width * file.width / sum_width_dimension;
                var r = width / oImg.width;
                var height = oImg.height * r;
                resolve({ image: oImg, width, height });
            });
        })
    }

    function loadFabricIngreImage(file, sum_width_dimension) {
        return new Promise((resolve, reject) => {
            var filename = file.name.split('.')[0];
            fabric.Image.fromURL("/share?file=files/" + file.company_id + "/Ingredients_Images/" + filename + ".jpg", function (oImg) {
                var width = product_width * file.width / sum_width_dimension;
                var r = width / oImg.width;
                var height = oImg.height * r;
                resolve({ image: oImg, width, height });
            });
        })
    }

    function drawProductImage() {
        var base_url = window.location.origin;
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
            var product_space = parseInt($("input[name='product_space']").val());
            var product_height = product[template]['height'];
            var left = product[template]["left"];
            var files = response.data.files;
            if (!files) return;
            var count = files.length;
            product_width = product[template]['width'];
            product_width -= product_space * (count - 1);

            max_height = 0;
            sum_width_dimension = 0;
            files.forEach(file => {
                sum_width_dimension += file.related_files[0].width;
            });
            var res = await Promise.all(files.map((file) => loadFabricImage(file.related_files[0], sum_width_dimension)));
            var nf_res = await Promise.all(files.map((file) => loadFabricNFImage(file.related_files[0], sum_width_dimension)));
            var ingre_res = await Promise.all(files.map((file) => loadFabricIngreImage(file.related_files[0], sum_width_dimension)));
            var total_width = 0;
            var total_height = 0;
            var max_h = 0;

            if (template == 2) {
                for (var i = 0; i < res.length; i++) {
                    total_height = 0;
                    res[i].image.scaleToWidth(res[i].width * 0.6);
                    total_height = res[i].height * 0.6;
                    total_width += res[i].width;
                    if (nf_res[i]) {
                        nf_res[i].image.scaleToWidth(nf_res[i].width * 0.9);
                        total_height += nf_res[i].height * 0.9 + 10;
                    }
                    if (ingre_res[i]) {
                        ingre_res[i].image.scaleToWidth(ingre_res[i].width * 0.9);
                        total_height += ingre_res[i].height * 0.9 + 10;
                    }
                    max_h = max_h < total_height ? total_height : max_h;
                }
                var r = max_h > product_height ? product_height / max_h : 1;
                total_width *= r;
                // Product Image
                left = (product_width - total_width) / 2;
                res.forEach((item) => {
                    item.image.set({ 'left': left + item.width * 0.2 * r });
                    item.image.scaleToWidth(item.width * 0.6 * r);
                    item.image.set({ 'top': (max_height - item.height) * 0.6 * r });
                    item.image.set({ id: "image" });
                    item.image.set({
                        selectable: false, 
                        evented: false
                    });
                    canvas.add(item.image);
                    left = left + item.width * r + product_space;
                });
                // Nutrition Facts Image
                var nf_res = await Promise.all(files.map((file) => loadFabricNFImage(file.related_files[0], sum_width_dimension)));
                left = (product_width - total_width) / 2;
                nf_res.forEach((item) => {
                    item.image.set({ 'left': left + (item.width * r * 0.1) / 2 });
                    item.image.scaleToWidth(item.width * r * 0.9);
                    item.image.set({ 'top': max_height * r * 0.6 + 10 });
                    item.image.set({ id: "image" });
                    item.image.set({
                        selectable: false, 
                        evented: false
                    });
                    canvas.add(item.image);
                    left = left + item.width * r + product_space;
                });
                // Ingredients Image
                var ingre_res = await Promise.all(files.map((file) => loadFabricIngreImage(file.related_files[0], sum_width_dimension)));
                left = (product_width - total_width) / 2;
                ingre_res.forEach((item, index) => {
                    item.image.set({ 'left': left + (item.width * r * 0.1) / 2 });
                    item.image.scaleToWidth(item.width * r * 0.9);
                    item.image.set({ 'top': max_height * r * 0.6 + nf_res[index].height * r * 0.9 + 10 });
                    item.image.set({ id: "image" });
                    item.image.set({
                        selectable: false, 
                        evented: false
                    });
                    canvas.add(item.image);
                    left = left + item.width * r + product_space;
                });
            }
        });
    }

    $('input[name="file_ids"], input[name="product_spacing"]').on('change', function () {
        drawProductImage();
    });
    
    $('#selectImgModal #submit').on('click', function () {
        drawProductImage();
    });
});
