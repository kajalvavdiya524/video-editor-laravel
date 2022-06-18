import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
  var _wrapLine = function (_line, lineIndex, desiredWidth, reservedSpace) {
    var lineWidth = 0,
      splitByGrapheme = this.splitByGrapheme,
      graphemeLines = [],
      line = [],
      // spaces in different languges?
      words = splitByGrapheme
        ? fabric.util.string.graphemeSplit(_line)
        : _line.split(this._wordJoiners),
      word = "",
      offset = 0,
      infix = splitByGrapheme ? "" : " ",
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
          var letterWidth =
            (this.getMeasuringContext().measureText(letter).width *
              this.fontSize) /
            this.CACHE_FONT_SIZE;
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

  $(".toggle-button").on("click", function () {
    if ($(this).text() == "-") {
      $(".canvas-container").fadeOut();
      $(this).text("+");
    } else {
      $(".canvas-container").fadeIn();
      $(this).text("-");
    }
  });

  var dimension = [
    { width: 3000, height: 3000 },
    { width: 3000, height: 3000 },
    { width: 3000, height: 3000 },
    { width: 3000, height: 3000 },
    { width: 1500, height: 1500 },
    { width: 1500, height: 1500 },
    { width: 1500, height: 1500 },
    { width: 1500, height: 1500 },
  ];
  var product = [
    { width: 2250, height: 3000, baseline: 3000, left: 0 },
    { width: 2250, height: 3000, baseline: 3000, left: 0 },
    { width: 3000, height: 2250, baseline: 2250, left: 0 },
    { width: 3000, height: 3000, baseline: 3000, left: 0 },
    { width: 1125, height: 1500, baseline: 1500, left: 0 },
    { width: 1125, height: 1500, baseline: 1500, left: 0 },
    { width: 1500, height: 1125, baseline: 1125, left: 0 },
    { width: 1500, height: 1500, baseline: 1500, left: 0 },
  ];

  var max_height = 0;
  var template = $("input[name='output_dimensions']").val();
  template = parseInt(template);
  if (isNaN(template)) {
    template = 0;
  }
  var offset = 0;
  if (template < 4) {
    offset = 50;
  } else {
    offset = 25;
  }

  var canvas = new fabric.Canvas("canvas");
  canvas.setDimensions({ width: "300px", height: "300px" }, { cssOnly: true });

  $("#preview-popup").show();

  fabric.Object.prototype.transparentCorners = false;
  fabric.Object.prototype.cornerColor = "#ff0000";
  fabric.Object.prototype.cornerSize = 40;
  fabric.Object.prototype.borderScaleFactor = 6;
  var controlsUtils = fabric.controlsUtils;
  fabric.Object.prototype.controls.mtr = new fabric.Control({
    x: 0,
    y: -0.5,
    offsetY: -200,
    withConnection: true,
    actionHandler: controlsUtils.rotationWithSnapping,
    cursorStyleHandler: controlsUtils.rotationStyleHandler,
    actionName: "rotate",
  });

  drawForLoading();
  drawProductImage();

  function drawForLoading() {
    var text = new fabric.Text(" ", {
      id: "pro_text",
      top: -256,
      left: -100,
      fontSize: 45,
      fill: "#ffffff",
      fontFamily: "OpenSans-Bold",
    });
    canvas.add(text);

    drawRectangles();
  }

  function loadFabricImage(file, sum_width_dimension) {
    var product_width = product[template]["width"] * 0.8;
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL("/share?file=" + file.path, function (oImg) {
        var width = (product_width * file.width) / sum_width_dimension;
        var r = width / oImg.width;
        var height = oImg.height * r;
        max_height = max_height < height ? height : max_height;
        resolve({ image: oImg, width, height });
      });
    });
  }

  function drawProductImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image") {
        canvas.remove(o);
      }
    });
    axios({
      method: "post",
      url: "/banner/view",
      data: {
        file_ids: $("input[name=file_ids]").val(),
        show_warning: true,
      },
    }).then(async function (response) {
      var product_height = product[template]["height"] * 0.8;
      var left = product[template]["left"];
      var files = response.data.files;
      if (!files) return;
      if (files.length > 1) {
        files = files.slice(0, 1);
      }
      var sum_width_dimension = 0;
      files.forEach((file) => {
        sum_width_dimension += file.related_files[0].width;
      });
      var res = await Promise.all(
        files.map((file) =>
          loadFabricImage(file.related_files[0], sum_width_dimension)
        )
      );
      var r = max_height > product_height ? product_height / max_height : 1;
      var total_width = 0;
      res.forEach((item) => {
        item.width *= r;
        item.height *= r;
        total_width += item.width;
      });
      left += (product[template]["width"] - total_width) / 2;
      res.forEach((item, index) => {
        item.image.set({ left: left });
        item.image.scaleToWidth(item.width);
        item.image.set({
          top: (product[template]["height"] - item.height) / 2,
        });
        item.image.set({ id: "image" });
        // item.image.set({
        //     selectable: false,
        //     evented: false
        // });
        left += item.width;
        canvas.add(item.image);
      });
    });
  }

  $(
    "input[name='quantity'], input[name='quantity_size'], #quantity_text_color, #quantity_text_color_picker, input[name='unit'], input[name='unit_size'], #unit_text_color, #unit_text_color_picker"
  ).on("change", function () {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "qua_text" || o.id == "unit_text") {
        canvas.remove(o);
      }
    });
    var qua_text = $("input[name='quantity']").val();
    var unit_text = $("input[name='unit']").val();
    if (template == 0 || template == 4) {
      var qtext = new fabric.Textbox(qua_text, {
        id: "qua_text",
        top: 0,
        left: dimension[template]["width"] * 0.75,
        width: dimension[template]["width"] * 0.25,
        fontSize: $("input[name='quantity_size']").val(),
        fill: $("#quantity_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var utext = new fabric.Textbox(unit_text, {
        id: "unit_text",
        top: 0,
        left: dimension[template]["width"] * 0.75,
        width: dimension[template]["width"] * 0.25,
        fontSize: $("input[name='unit_size']").val(),
        fill: $("#unit_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var total_height = qtext.height + utext.height;
      qtext.top =
        dimension[template]["height"] * 0.8 +
        offset +
        (dimension[template]["height"] * 0.2 - offset - total_height) / 2 +
        offset / 2;
      utext.top = qtext.top + qtext.height - offset / 2;
      canvas.add(qtext);
      canvas.add(utext);
    } else if (template == 1 || template == 5) {
      var qtext = new fabric.Textbox(qua_text, {
        id: "qua_text",
        top: 0,
        left: dimension[template]["width"] * 0.75,
        width: dimension[template]["width"] * 0.25,
        fontSize: $("input[name='quantity_size']").val(),
        fill: $("#quantity_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var utext = new fabric.Textbox(unit_text, {
        id: "unit_text",
        top: 0,
        left: dimension[template]["width"] * 0.75,
        width: dimension[template]["width"] * 0.25,
        fontSize: $("input[name='unit_size']").val(),
        fill: $("#unit_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var total_height = qtext.height + utext.height;
      qtext.top =
        (dimension[template]["height"] * 0.2 - offset - total_height) / 2 +
        offset / 2;
      utext.top = qtext.top + qtext.height - offset / 2;
      canvas.add(qtext);
      canvas.add(utext);
    } else if (template == 2 || template == 6) {
      var qtext = new fabric.Textbox(qua_text, {
        id: "qua_text",
        top: 0,
        left: dimension[template]["width"] * 0.75 + offset,
        width: dimension[template]["width"] * 0.25 - offset,
        fontSize: $("input[name='quantity_size']").val(),
        fill: $("#quantity_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var utext = new fabric.Textbox(unit_text, {
        id: "unit_text",
        top: 0,
        left: dimension[template]["width"] * 0.75 + offset,
        width: dimension[template]["width"] * 0.25 - offset,
        fontSize: $("input[name='unit_size']").val(),
        fill: $("#unit_text_color").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var total_height = qtext.height + utext.height;
      qtext.top =
        dimension[template]["height"] * 0.75 +
        (dimension[template]["height"] * 0.25 - total_height) / 2 +
        offset / 2;
      utext.top = qtext.top + qtext.height - offset / 2;
      canvas.add(qtext);
      canvas.add(utext);
    }
    canvas.renderAll();
  });

  $(
    "input[name='product_format'], input[name='product_size'], input[name='sub_text'], input[name='sub_text_size'], #sub_text_color, #product_format_text_color, #product_format_text_color_picker, #sub_text_color_picker"
  ).on("change", function () {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "pro_text" || o.id == "sub_text") {
        canvas.remove(o);
      }
    });
    var pro_text = $("input[name='product_format']").val();
    var sub_text = $("input[name='sub_text']").val();
    if (template == 0 || template == 4) {
      var ptext = new fabric.Textbox(pro_text, {
        id: "pro_text",
        top: dimension[template]["height"] * 0.8,
        left: 0,
        width: dimension[template]["height"] * 0.8,
        fontSize: $("input[name='product_size']").val(),
        fill: $("input[name='product_format_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        angle: -90,
        selectable: false,
        evented: false,
      });
      var total_height = ptext.height;
      var stext = new fabric.Textbox(sub_text, {
        id: "sub_text",
        top: dimension[template]["height"] * 0.8,
        left: 0,
        width: dimension[template]["height"] * 0.8,
        fontSize: $("input[name='sub_text_size']").val(),
        fill: $("input[name='sub_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        angle: -90,
        selectable: false,
        evented: false,
      });
      if (sub_text != "") {
        total_height += stext.height;
      }
      ptext.left =
        dimension[template]["width"] * 0.75 +
        (dimension[template]["width"] * 0.25 - total_height) / 2;
      stext.left = ptext.left + ptext.height;
      canvas.add(ptext);
      canvas.add(stext);
    } else if (template == 1 || template == 5) {
      var ptext = new fabric.Textbox(pro_text, {
        id: "pro_text",
        top: dimension[template]["height"],
        left: 0,
        width: dimension[template]["height"] * 0.8,
        fontSize: $("input[name='product_size']").val(),
        fill: $("input[name='product_format_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        angle: -90,
        selectable: false,
        evented: false,
      });
      var total_height = ptext.height;
      var stext = new fabric.Textbox(sub_text, {
        id: "sub_text",
        top: dimension[template]["height"],
        left: 0,
        width: dimension[template]["height"] * 0.8,
        fontSize: $("input[name='sub_text_size']").val(),
        fill: $("input[name='sub_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        angle: -90,
        selectable: false,
        evented: false,
      });
      if (sub_text != "") {
        total_height += stext.height;
      }
      ptext.left =
        dimension[template]["width"] * 0.75 +
        (dimension[template]["width"] * 0.25 - total_height) / 2;
      stext.left = ptext.left + ptext.height;
      canvas.add(ptext);
      canvas.add(stext);
    } else if (template == 2 || template == 6) {
      var ptext = new fabric.Textbox(pro_text, {
        id: "pro_text",
        top: dimension[template]["height"] * 0.75,
        left: 0,
        width: dimension[template]["width"] * 0.75,
        fontSize: $("input[name='product_size']").val(),
        fill: $("input[name='product_format_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      var total_height = ptext.height;
      var stext = new fabric.Textbox(sub_text, {
        id: "sub_text",
        top: dimension[template]["height"] * 0.75,
        left: 0,
        width: dimension[template]["width"] * 0.75,
        fontSize: $("input[name='sub_text_size']").val(),
        fill: $("input[name='sub_text_color']").val(),
        fontFamily: "OpenSans-Bold",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
      if (sub_text != "") {
        total_height += stext.height;
      }
      ptext.top =
        dimension[template]["height"] * 0.75 +
        (dimension[template]["height"] * 0.25 - total_height) / 2 +
        offset / 2;
      stext.top = ptext.top + ptext.height;
      canvas.add(ptext);
      canvas.add(stext);
    }
    canvas.renderAll();
  });

  $(
    "#product_format_bk_color, #product_format_bk_color_picker, #quantity_bk_color, #quantity_bk_color_picker"
  ).on("change", drawRectangles);

  function drawRectangles() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "product_background" || o.id == "qua_background") {
        canvas.remove(o);
      }
    });
    var product_format_bk_color = $("#product_format_bk_color").val();
    var quantity_bk_color = $("#quantity_bk_color").val();
    if (template == 0 || template == 4) {
      var product_background = new fabric.Rect({
        id: "product_background",
        left: dimension[template]["width"] * 0.75,
        top: -offset,
        fill: product_format_bk_color,
        width: dimension[template]["width"] / 4 + offset,
        height: (dimension[template]["height"] * 4) / 5,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(product_background);
      var qua_background = new fabric.Rect({
        id: "qua_background",
        left: dimension[template]["width"] * 0.75,
        top: (dimension[template]["height"] * 4) / 5 + offset,
        fill: quantity_bk_color,
        width: dimension[template]["width"] / 4 + offset,
        height: dimension[template]["height"] / 5,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(qua_background);
      canvas.sendToBack(qua_background);
      canvas.sendToBack(product_background);
    } else if (template == 1 || template == 5) {
      var product_background = new fabric.Rect({
        id: "product_background",
        left: dimension[template]["width"] * 0.75,
        top: dimension[template]["height"] / 5,
        fill: product_format_bk_color,
        width: dimension[template]["width"] / 4 + offset,
        height: (dimension[template]["height"] * 4) / 5 + offset,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(product_background);
      var qua_background = new fabric.Rect({
        id: "qua_background",
        left: dimension[template]["width"] * 0.75,
        top: -offset,
        fill: quantity_bk_color,
        width: dimension[template]["width"] / 4 + offset,
        height: dimension[template]["height"] / 5,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(qua_background);
      canvas.sendToBack(qua_background);
      canvas.sendToBack(product_background);
    } else if (template == 2 || template == 6) {
      var product_background = new fabric.Rect({
        id: "product_background",
        left: -offset,
        top: dimension[template]["height"] * 0.75,
        fill: product_format_bk_color,
        width: dimension[template]["width"] * 0.75,
        height: dimension[template]["height"] * 0.25 + offset,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(product_background);
      var qua_background = new fabric.Rect({
        id: "qua_background",
        left: dimension[template]["width"] * 0.75 + offset,
        top: dimension[template]["height"] * 0.75,
        fill: quantity_bk_color,
        width: dimension[template]["width"] * 0.25 + offset,
        height: dimension[template]["height"] * 0.25,
        rx: offset,
        ry: offset,
        selectable: false,
        evented: false,
      });
      canvas.add(qua_background);
      canvas.sendToBack(qua_background);
      canvas.sendToBack(product_background);
    }

    canvas.renderAll();
  }

  $("input[name='file_ids']").on("change", function () {
    drawProductImage();
  });

  $("#selectImgModal #submit").on("click", function () {
    drawProductImage();
  });
});
