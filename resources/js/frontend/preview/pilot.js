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

  var dimension = [
    { width: 1250, height: 1042 },
    { width: 3033, height: 375 },
    { width: 3033, height: 474 },
  ];
  var product = [
    { width: 625, height: 521, left: 0, baseline: 0 },
    { width: 645, height: 325, left: 1965, baseline: 0 },
    { width: 810, height: 0, left: 1677, baseline: 0 },
  ];
  var originCords = {};
  var originCords_button = {};
  var template;
  var canvas;
  var base_url = window.location.origin;

  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img",
    onTemplateChange
  );

  function onTemplateChange() {
    originCords = {};
    originCords_button = {};
    template = $("input[name='output_dimensions']").val();
    template = parseInt(template);
    $(".canvas-container").remove();

    if ($(".edit-button").hasClass("save")) {
      $(".edit-button").removeClass("save");
      $(".edit-button").addClass("edit");
      $(".edit-button").html('<i class="cil-pencil"></i>');
    }

    if (template == 0) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="1250" height="1042"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "312.5px", height: "260.5px" },
        { cssOnly: true }
      );
    } else if (template == 1) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="3033" height="375"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "1011px", height: "125px" },
        { cssOnly: true }
      );
    } else if (template == 2) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="3033" height="474"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "1011px", height: "158px" },
        { cssOnly: true }
      );
    }

    canvas.on({
      "object:moving": updateControls,
      "object:scaling": updateControls,
      "object:resizing": updateControls,
      "object:rotating": updateControls,
    });

    drawForLoading();
    drawBackgroundColor();
    drawBackgroundImage();
    drawProductImage();
    drawLogoImage();
    drawText1();
    drawText2();
    setTimeout(function () {
      drawShopButton();
    }, 3000);
  }

  function onLoad() {
    onTemplateChange();
    $("#preview-popup").show();
    fabric.Object.prototype.transparentCorners = false;
    fabric.Object.prototype.cornerColor = "#ffffff";
    fabric.Object.prototype.cornerStyle = "circle";
    fabric.Object.prototype.cornerStrokeColor = "#000000";
    fabric.Object.prototype.setControlsVisibility({
      tr: true,
      br: true,
      bl: true,
      ml: false,
      mt: false,
      mr: false,
      mb: false,
      mtr: true,
    });

    var controlsUtils = fabric.controlsUtils;
    var rotateImg = document.createElement("img");
    rotateImg.src = base_url + "/img/brand/rotate.svg";
    fabric.Object.prototype.controls.mtr = new fabric.Control({
      x: 0,
      y: 0,
      actionHandler: controlsUtils.rotationWithSnapping,
      cursorStyleHandler: controlsUtils.rotationStyleHandler,
      actionName: "rotate",
      render: renderIcon(rotateImg),
      cornerSize: 24,
    });
  }

  function renderIcon(icon) {
    return function renderIcon(ctx, left, top, styleOverride, fabricObject) {
      var size = this.cornerSize;
      ctx.save();
      ctx.translate(left, top);
      ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
      ctx.drawImage(icon, -size / 2, -size / 2, size, size);
      ctx.restore();
    };
  }

  onLoad();

  function updateControls() {
    var image, shop_button;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image") {
        image = o;
      } else if (o.id == "shopnow_group") {
        shop_button = o;
      }
    });
    if (image) {
      var x = Math.min(
        image.oCoords.tl.x,
        image.oCoords.tr.x,
        image.oCoords.bl.x,
        image.oCoords.br.x
      );
      var y = Math.min(
        image.oCoords.tl.y,
        image.oCoords.tr.y,
        image.oCoords.bl.y,
        image.oCoords.br.y
      );
      $("input[name='x_offset[]']")
        .eq(template)
        .val((x - originCords["x"]).toFixed(2));
      $("input[name='y_offset[]']")
        .eq(template)
        .val((y - originCords["y"]).toFixed(2));
      $("input[name='angle[]']").eq(template).val(image.angle.toFixed(2));
      $("input[name='scale[]']")
        .eq(template)
        .val((image.scaleX / originCords["scaleX"]).toFixed(2));
    }
    if (shop_button) {
      var x = shop_button.left;
      var y = shop_button.top;
      $("input[name='x_offset_button[]']")
        .eq(template)
        .val((x - originCords_button["x"]).toFixed(2));
      $("input[name='y_offset_button[]']")
        .eq(template)
        .val((y - originCords_button["y"]).toFixed(2));
    }
  }

  function drawForLoading() {
    var text1 = new fabric.Text(" ", {
      id: "text1",
      top: -256,
      left: 0,
      fontSize: 45,
      fill: "#ffffff",
      fontFamily: "GothamNarrow-Ultra",
    });
    canvas.add(text1);
    var text2 = new fabric.Text(" ", {
      id: "text1",
      top: -256,
      left: 0,
      fontSize: 45,
      fill: "#ffffff",
      fontFamily: "MuseoSans-300Italic",
    });
    canvas.add(text2);
    canvas.renderAll();
  }

  function drawBackgroundColor() {
    var background_color = $("#background_color").val();
    canvas.backgroundColor = background_color;
    if ($("#background_pattern").val() == "texture") {
      var texture_url =
        base_url + "/img/backgrounds/Pilot/texture" + template + ".png";
      setBackgroundImage(texture_url);
    }
    canvas.renderAll();
  }

  function drawBackgroundImage() {
    var background = $("input[name='background']").val();
    var background_type = $("select[name='background_type']").val();
    if (background_type == "product_image") {
      drawBack("");
      return;
    }
    console.log(background);
    if (background) {
      drawBack(background);
    } else {
      axios({
        method: "post",
        url: "/banner/background",
        data: {
          customer: "pilot",
          template: template,
        },
      })
        .then(function (response) {
          var files = response.data.background;
          if (files.length > template) {
            var path = files[template].path;
            var thumbnail = files[template].thumbnail;
            var html = "";
            html += `<img class="background-preview" src="${base_url}/share?file=${thumbnail}" />`;
            html += `<input type="hidden" name="background" value="${base_url}/share?file=${path}" />`;
            $(".selected-image").empty();
            $(".selected-image").append(html);
            drawBack(`${base_url}/share?file=${path}`);
          }
        })
        .catch(function (response) {
          showError([response]);
        });
    }
  }

  function drawBack(url) {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "background_image") {
        canvas.remove(o);
      }
    });
    if (url == "") return;
    fabric.Image.fromURL(url, function (img) {
      var dimension_width = dimension[template]["width"];
      var dimension_height = dimension[template]["height"];
      var canvasAspect = dimension_width / dimension_height;
      var imgAspect = img.width / img.height;
      var left, top, scaleFactor;

      if (canvasAspect < imgAspect) {
        var scaleFactor = dimension_width / img.width;
        left = 0;
        if (template == 0) {
          top = -(img.height * scaleFactor - dimension_height);
        } else {
          top = -(img.height * scaleFactor - dimension_height) / 2;
        }
      } else {
        var scaleFactor = dimension_height / img.height;
        top = 0;
        if (template == 1 || template == 2) {
          left = -(img.width * scaleFactor - dimension_width);
        } else {
          left = -(img.width * scaleFactor - dimension_width) / 2;
        }
      }
      img.set({
        id: "background_image",
        top: top,
        left: left,
        originX: "left",
        originY: "top",
        scaleX: scaleFactor,
        scaleY: scaleFactor,
        selectable: false,
        evented: false,
      });
      canvas.add(img);
      canvas.sendToBack(img);
    });
  }

  function drawShopButton() {
    var shop_button, shop_text, group;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "shopnow" || o.id == "shopnow_group") {
        canvas.remove(o);
      }
    });

    var background_type = $("#background_type").val();
    var x_offset_button = parseFloat(
      $("input[name='x_offset_button[]']").eq(template).val()
    );
    var y_offset_button = parseFloat(
      $("input[name='y_offset_button[]']").eq(template).val()
    );

    if (template == 0) {
      var shadow = new fabric.Shadow({
        color: "#8888883f",
        blur: 0,
        offsetX: 6,
        offsetY: 8,
      });
      if (background_type == "background_image") {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 844 + y_offset_button,
          left: 45 + x_offset_button,
          width: 365,
          height: 88,
          rx: 8,
          ry: 8,
          fill: "#d41e3d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP NOW", {
          id: "shopnow",
          top: 860 + y_offset_button,
          left: 45 + x_offset_button,
          width: 365,
          fontSize: 55,
          fill: "#ffffff",
          charSpacing: 60,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 844 + y_offset_button,
          left: 45 + x_offset_button,
        });
      } else {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 873 + y_offset_button,
          left: 45 + x_offset_button,
          width: 365,
          height: 89,
          rx: 8,
          ry: 8,
          fill: "#004e7d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP NOW", {
          id: "shopnow",
          top: 890 + y_offset_button,
          left: 45 + x_offset_button,
          width: 365,
          fontSize: 55,
          fill: "#ffffff",
          charSpacing: 60,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 873 + y_offset_button,
          left: 45 + x_offset_button,
        });
      }
    } else if (template == 1) {
      var shadow = new fabric.Shadow({
        color: "#8888883f",
        blur: 0,
        offsetX: 11,
        offsetY: 10,
      });
      if (background_type == "background_image") {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 200 + y_offset_button,
          left: 2689 + x_offset_button,
          width: 278,
          height: 125,
          rx: 6,
          ry: 6,
          fill: "#d41e3d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP", {
          id: "shopnow",
          top: 227 + y_offset_button,
          left: 2689 + x_offset_button,
          width: 278,
          fontSize: 75,
          fill: "#ffffff",
          charSpacing: 70,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 200 + y_offset_button,
          left: 2689 + x_offset_button,
        });
      } else {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 120 + y_offset_button,
          left: 2668 + x_offset_button,
          width: 279,
          height: 125,
          rx: 6,
          ry: 6,
          fill: "#d41e3d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP", {
          id: "shopnow",
          top: 147 + y_offset_button,
          left: 2668 + x_offset_button,
          width: 279,
          fontSize: 75,
          fill: "#ffffff",
          charSpacing: 70,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 120 + y_offset_button,
          left: 2668 + x_offset_button,
        });
      }
    } else if (template == 2) {
      var shadow = new fabric.Shadow({
        color: "#8888883f",
        blur: 0,
        offsetX: 16,
        offsetY: 15,
      });
      if (background_type == "background_image") {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 230 + y_offset_button,
          left: 2577 + x_offset_button,
          width: 391,
          height: 175,
          rx: 15,
          ry: 15,
          fill: "#d41e3d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP", {
          id: "shopnow",
          top: 270 + y_offset_button,
          left: 2577 + x_offset_button,
          width: 391,
          fontSize: 105,
          fill: "#ffffff",
          charSpacing: 70,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 230 + y_offset_button,
          left: 2577 + x_offset_button,
        });
      } else {
        shop_button = new fabric.Rect({
          id: "shopnow",
          top: 150 + y_offset_button,
          left: 2558 + x_offset_button,
          width: 391,
          height: 175,
          rx: 15,
          ry: 15,
          fill: "#d41e3d",
          shadow: shadow,
          selectable: false,
          evented: false,
        });
        shop_text = new fabric.Textbox("SHOP", {
          id: "shopnow",
          top: 270 + y_offset_button,
          left: 2558 + x_offset_button,
          width: 391,
          fontSize: 105,
          fill: "#ffffff",
          charSpacing: 70,
          fontFamily: "GothamNarrow-Ultra",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        group = new fabric.Group([shop_button, shop_text], {
          id: "shopnow_group",
          top: 230 + y_offset_button,
          left: 2558 + x_offset_button,
        });
      }
    }
    group.hasControls = false;
    canvas.add(group);
    canvas.sendToBack(shop_button);
    canvas.renderAll();
    originCords_button = {
      x: group.left,
      y: group.top,
    };
  }

  function drawText1() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "text1") {
        canvas.remove(o);
      }
    });
    var background_type = $("#background_type").val();
    var text1 = $("input[name='text1']").val();
    var text1_color = $("input[name='text1_color']").val();
    var textbox;

    if (template == 0) {
      if (background_type == "background_image") {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 203,
          left: 49,
          fontSize: 107,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: dimension[template]["width"],
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      } else {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 203,
          left: 49,
          fontSize: 100,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: dimension[template]["width"] - 100,
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      }
    } else if (template == 1) {
      if (background_type == "background_image") {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 35,
          left: 738,
          fontSize: 102,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: 900,
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      } else {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 35,
          left: 736,
          fontSize: 96,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: 1400,
          textAlign: "left",
          lineHeight: 1,
          selectable: false,
          evented: false,
        });
      }
      textbox.top = (375 - textbox.height) / 2;
    } else if (template == 2) {
      if (background_type == "background_image") {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 89,
          left: 892,
          fontSize: 150,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: 1000,
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      } else {
        textbox = new fabric.Textbox(text1, {
          id: "text1",
          top: 89,
          left: 910,
          fontSize: 150,
          fill: text1_color,
          fontFamily: "GothamNarrow-Ultra",
          breakWords: true,
          width: 1000,
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      }
      textbox.top = (474 - textbox.height) / 2;
    }
    canvas.add(textbox);
  }

  function drawText2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "text2") {
        canvas.remove(o);
      }
    });
    var background_type = $("#background_type").val();
    var text2 = $("input[name='text2']").val();
    var text2_color = $("input[name='text2_color']").val();
    var text2_font = $("select[name='text2_font']").val();
    var text2_font_size = $("input[name='text2_font_size']").val();
    var textbox;

    if (template == 0) {
      if (background_type == "background_image") {
        textbox = new fabric.Textbox(text2, {
          id: "text2",
          top: 602,
          left: 45,
          fontSize: text2_font_size,
          fill: text2_color,
          fontFamily: text2_font,
          breakWords: true,
          width: 600,
          textAlign: "left",
          lineHeight: 0.8,
          selectable: false,
          evented: false,
        });
      } else {
        textbox = new fabric.Textbox(text2, {
          id: "text2",
          top: 631,
          left: 52,
          fontSize: text2_font_size,
          fill: text2_color,
          fontFamily: text2_font,
          breakWords: true,
          width: 500,
          textAlign: "left",
          lineHeight: 1,
          selectable: false,
          evented: false,
        });
      }
      canvas.add(textbox);
    }
  }

  function drawLogoImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "logo") {
        canvas.remove(o);
      }
    });
    var url;
    var logo = document.getElementsByName("logo")[0];
    if (logo.files.length) {
      url = URL.createObjectURL(logo.files[0]);
    } else {
      url = $("#logo_saved").val();
      console.log(url);
    }
    fabric.Image.fromURL(url, function (oImg) {
      var r;
      if (template == 0) {
        r = oImg.width / 387;
        oImg.set({
          id: "logo",
          left: 45,
          top: 63,
          selectable: false,
          evented: false,
        });
      } else if (template == 1) {
        r = oImg.width / 528;
        oImg.set({
          id: "logo",
          left: 70,
          top: (375 - oImg.height / r) / 2,
          selectable: false,
          evented: false,
        });
      } else if (template == 2) {
        r = oImg.width / 675;
        oImg.set({
          id: "logo",
          left: 99,
          top: (474 - oImg.height / r) / 2,
          selectable: false,
          evented: false,
        });
      }
      oImg.scaleToWidth(oImg.width / r);
      oImg.scaleToHeight(oImg.height / r);
      canvas.add(oImg);
    });
  }

  function getMeta(url) {
    return new Promise((resolve, reject) => {
      let img = new Image();
      img.onload = () => resolve(img);
      img.onerror = () => reject();
      img.src = url;
    });
  }

  async function setBackgroundImage(url) {
    if (url == "") {
      canvas.backgroundImage = null;
      canvas.renderAll();
      return;
    }

    var img = await getMeta(url);
    var dimension_width = dimension[template]["width"];
    var dimension_height = dimension[template]["height"];
    var canvasAspect = dimension_width / dimension_height;
    var imgAspect = img.width / img.height;
    var left, top, scaleFactor;

    if (canvasAspect < imgAspect) {
      var scaleFactor = dimension_width / img.width;
      left = 0;
      if (template == 0) {
        top = -(img.height * scaleFactor - dimension_height);
      } else {
        top = -(img.height * scaleFactor - dimension_height) / 2;
      }
    } else {
      var scaleFactor = dimension_height / img.height;
      top = 0;
      if (template == 1 || template == 2) {
        left = -(img.width * scaleFactor - dimension_width);
      } else {
        left = -(img.width * scaleFactor - dimension_width) / 2;
      }
    }

    canvas.setBackgroundImage(url, canvas.renderAll.bind(canvas), {
      top: top,
      left: left,
      originX: "left",
      originY: "top",
      scaleX: scaleFactor,
      scaleY: scaleFactor,
    });
  }

  function drawProductImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image") {
        canvas.remove(o);
      }
    });
    var background_type = $("#background_type").val();
    if (background_type == "background_image") return;
    axios({
      method: "post",
      url: "/banner/view",
      data: {
        file_ids: $("input[name=file_ids]").val(),
        show_warning: true,
      },
    }).then(async function (response) {
      var product_width = product[template]["width"];
      var product_height = product[template]["height"];
      var left = product[template]["left"];
      if (response.data.files.length == 0) return;
      var file = response.data.files[0].related_files[0];

      var angle = parseFloat($("input[name='angle[]']").eq(template).val());
      var x_offset = parseFloat(
        $("input[name='x_offset[]']").eq(template).val()
      );
      var y_offset = parseFloat(
        $("input[name='y_offset[]']").eq(template).val()
      );
      var scale = parseFloat($("input[name='scale[]']").eq(template).val());
      fabric.Image.fromURL("/share?file=" + file.path, function (oImg) {
        var r1 = 1,
          r2 = 1;
        if (template == 0) {
          r1 = oImg.width / product_width;
          r2 = oImg.height / r1 / product_height;
          oImg.set({
            id: "image",
            left:
              dimension[template]["width"] -
              70 -
              oImg.width / (r1 * r2) / 2 +
              x_offset,
            top:
              dimension[template]["height"] -
              40 -
              oImg.height / (r1 * r2) / 2 +
              y_offset,
            angle: angle,
          });
        } else if (template == 1) {
          r1 = oImg.width / product_width;
          r2 = oImg.height / r1 / product_height;
          oImg.set({
            id: "image",
            left:
              left +
              (700 - ((oImg.width / r1 / r2) * (1 - scale)) / 2) / 2 +
              x_offset,
            top:
              (dimension[template]["height"] - oImg.height / r1 / r2 / 2) / 2 +
              y_offset,
            angle: angle,
          });
        } else if (template == 2) {
          r1 = oImg.width / product_width;
          oImg.set({
            id: "image",
            left: left + x_offset,
            top:
              (dimension[template]["height"] - oImg.height / r1 / r2 / 2) / 2 +
              y_offset,
            angle: angle,
          });
        }
        oImg.set({
          originX: "middle",
          originY: "middle",
          lockUniScaling: true,
        });
        oImg.scaleToWidth((oImg.width / r1 / r2) * scale);
        oImg.scaleToHeight((oImg.height / r1 / r2) * scale);
        canvas.add(oImg);

        originCords = {
          x:
            Math.min(
              oImg.oCoords.tl.x,
              oImg.oCoords.tr.x,
              oImg.oCoords.bl.x,
              oImg.oCoords.br.x
            ) - x_offset,
          y:
            Math.min(
              oImg.oCoords.tl.y,
              oImg.oCoords.tr.y,
              oImg.oCoords.bl.y,
              oImg.oCoords.br.y
            ) - y_offset,
          scaleX: oImg.scaleX / scale,
        };
      });
    });
  }

  $("#background_color").on("change", drawBackgroundColor);

  $("#selectBkImgModal #submit").on("click", function () {
    var background = $("input[name='background']").val();
    drawBack(background);
  });

  $("input[name='text1'], #text1_color, input[name='text1_color']").on(
    "change",
    drawText1
  );
  $(
    "input[name='text2'], #text2_color, input[name='text2_color'], select[name='text2_font'], input[name='text2_font_size']"
  ).on("change", drawText2);

  $("input[name='logo']").on("change", drawLogoImage);

  $(".toggle-button").on("click", function () {
    if ($(this).html() == '<i class="cil-window-minimize"></i>') {
      $(".canvas-container").fadeOut();
      $(this).html('<i class="cil-plus"></i>');
    } else {
      $(".canvas-container").fadeIn();
      $(this).html('<i class="cil-window-minimize"></i>');
    }
  });

  $(".edit-button").on("click", function () {
    if ($(this).hasClass("edit")) {
      $(this).removeClass("edit");
      $(this).addClass("save");
      $(this).html('<i class="cil-save"></i>');
      if (template == 0) {
        canvas.setDimensions(
          { width: "625px", height: "521px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "1516.5px", height: "187.5px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "1516.5px", height: "237px" },
          { cssOnly: true }
        );
      }
    } else {
      $(this).removeClass("save");
      $(this).addClass("edit");
      $(this).html('<i class="cil-pencil"></i>');
      if (template == 0) {
        canvas.setDimensions(
          { width: "312.5px", height: "260.5px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "1011px", height: "125px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "1011px", height: "158px" },
          { cssOnly: true }
        );
      }
    }
    $("#preview-popup").css({ right: 0, left: "auto" });
    canvas.renderAll();
  });

  $("#background_type").on("change", function () {
    drawBackgroundImage();
    drawText1();
    drawText2();
    drawShopButton();
    drawProductImage();
  });

  $("input[name='file_ids']").on("change", function () {
    drawProductImage();
  });

  $("#selectBkImgModal #cancel").on("click", function () {
    drawBack("");
  });

  $("#background_pattern").on("change", function () {
    var background_pattern = $(this).val();
    if (background_pattern == "texture") {
      var texture_url =
        base_url + "/img/backgrounds/Pilot/texture" + template + ".png";
      setBackgroundImage(texture_url);
    } else {
      setBackgroundImage("");
    }
  });

  $("#selectImgModal #submit").on("click", function () {
    drawProductImage();
  });
});
