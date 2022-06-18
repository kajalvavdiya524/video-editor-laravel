import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

var shadows;

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
    { width: 624, height: 1132 },
    { width: 1280, height: 300 },
    { width: 3200, height: 400 },
  ];
  var product = [
    { width: 624, height: 510, left: -15, baseline: 1070 },
    { width: 410, height: 300, left: 590, baseline: 270 },
    { width: 650, height: 400, left: 1350, baseline: 370 },
  ];
  var circle_pos = {
    0: { radius: 218, x: -22, y: 152 },
    1: { radius: 162, x: 298, y: -67 },
    2: { radius: 235, x: 645, y: -90 },
  };

  var burst_pos = {
    1: { radius: 70, x: 173, y: -16 },
    2: { radius: 117, x: 430, y: -40 },
  };
  var originCords = [];
  var max_height = 0;
  var template;
  var canvas;
  var base_url = window.location.origin;
  var circle_colors, message_options;

  circle_colors = $("#circle_text_color").data("value").split(",");

  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img",
    onTemplateChange
  );

  function onTemplateChange() {
    originCords = [];
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
        `<canvas id="canvas" width="624" height="1132"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "156px", height: "283px" },
        { cssOnly: true }
      );
    } else if (template == 1) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="1280" height="300"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "427px", height: "100px" },
        { cssOnly: true }
      );
    } else if (template == 2) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="3200" height="400"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "600px", height: "75px" },
        { cssOnly: true }
      );
    }

    canvas.on({
      "object:moving": updateControls,
      "object:scaling": updateControls,
      "object:resizing": updateControls,
      "object:rotating": updateControls,
    });

    message_options = $("#message_options").val();

    drawCircle();
    drawFeatured();
    drawShopButton();
    drawForLoading();
    setTimeout(function () {
      drawMessageText();
      drawValueText();
      drawDescription();
      if (template) {
        drawBurstText();
        drawBurst();
      }
      drawProductImage();
      drawBackgroundImage();
    }, 2500);

    setTimeout(function () {
      drawLegalText();
    }, 5000);
  }

  function onLoad() {
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: "kroger",
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      shadows = response.data.shadow;
    });

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
    var image1, image2;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image1") {
        image1 = o;
      } else if (o.id == "image2") {
        image2 = o;
      }
    });
    var bound = image1.getBoundingRect();
    var x1 = image1.left - bound.width / 2;
    var y1 = image1.top - bound.height / 2;
    $("input[name='x_offset[]']")
      .eq(template * 2)
      .val((x1 - originCords[0]["x"]).toFixed(2));
    $("input[name='y_offset[]']")
      .eq(template * 2)
      .val((y1 - originCords[0]["y"]).toFixed(2));
    $("input[name='angle[]']")
      .eq(template * 2)
      .val(image1.angle.toFixed(2));
    $("input[name='scale[]']")
      .eq(template * 2)
      .val((image1.scaleX / originCords[0]["scaleX"]).toFixed(2));
    if (image2) {
      var bound = image2.getBoundingRect();
      var x2 = image2.left - bound.width / 2;
      var y2 = image2.top - bound.height / 2;
      $("input[name='x_offset[]']")
        .eq(template * 2 + 1)
        .val((x2 - originCords[1]["x"]).toFixed(2));
      $("input[name='y_offset[]']")
        .eq(template * 2 + 1)
        .val((y2 - originCords[1]["y"]).toFixed(2));
      $("input[name='angle[]']")
        .eq(template * 2 + 1)
        .val(image2.angle.toFixed(2));
      $("input[name='scale[]']")
        .eq(template * 2 + 1)
        .val((image2.scaleX / originCords[1]["scaleX"]).toFixed(2));
    }
  }

  function drawForLoading() {
    var text_row_1 = new fabric.Text(" ", {
      id: "text_row_1",
      top: 256,
      left: 0,
      fontSize: 45,
      fill: circle_colors[0],
      fontFamily: "Proxima-Nova-Extrabld",
    });
    text_row_1.left =
      (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 - 22;
    canvas.add(text_row_1);
    var text_val_1 = new fabric.Text(" ", {
      id: "text_val_1",
      top: 301,
      left: 0,
      fontSize: 142,
      fill: circle_colors[0],
      fontFamily: "Proxima-Nova-Black",
    });
    var text_slash = new fabric.Text(" ", {
      id: "text_slash",
      top: 291,
      left: 0,
      fontSize: 162,
      fill: circle_colors[0],
      fontFamily: "Proxima-Nova-Regular-It",
    });
    var text_dollar = new fabric.Text(" ", {
      id: "text_dollar",
      top: 315,
      left: 0,
      fontSize: 82,
      fill: circle_colors[0],
      fontFamily: "Proxima-Nova-Semibold",
    });
    var text_val_2 = new fabric.Text(" ", {
      id: "text_val_2",
      top: 301,
      left: 0,
      fontSize: 144,
      fill: circle_colors[0],
      fontFamily: "Proxima-Nova-Bold",
    });
    var text_val_2 = new fabric.Text(" ", {
      id: "text_val_2",
      top: 301,
      left: 0,
      fontSize: 144,
      fill: circle_colors[0],
      fontFamily: "Arial",
    });
    var total_width =
      text_val_1.width +
      text_slash.width +
      text_dollar.width +
      text_val_2.width;
    var left = (circle_pos[template]["radius"] * 2 - total_width) / 2 - 22;
    text_val_1.left = left;
    text_slash.left = left + text_val_1.width;
    text_dollar.left = text_slash.left + text_slash.width;
    text_val_2.left = text_dollar.left + text_dollar.width;
    canvas.add(text_val_1);
    canvas.add(text_slash);
    canvas.add(text_dollar);
    canvas.add(text_val_2);
    canvas.renderAll();
  }

  function drawBackgroundImage(bThemeChanged = false) {
    var base_url = window.location.origin;
    var background = $("input[name='background']").val();
    var template = $("input[name='output_dimensions']").val();
    var theme = $("#theme").val();
    if (!background || bThemeChanged) {
      axios({
        method: "post",
        url: "/banner/background",
        data: {
          customer: "kroger",
          theme: theme,
          template: template,
          get_only: 'background',
        },
      })
        .then(function (response) {
          var files = response.data.background;
          if (files.length == 0) return;
          var path = files[0].path;
          var thumbnail = files[0].thumbnail;
          var html = "";
          html += `<img class="background-preview" src="${base_url}/share?file=${thumbnail}" />`;
          html += `<input type="hidden" name="background" value="${base_url}/share?file=${path}" />`;
          $(".selected-image").empty();
          $(".selected-image").append(html);
          setBackgroundImage(`${base_url}/share?file=${path}`);
        })
        .catch(function (response) {
          showError([response]);
        });
    } else {
      var arr = background.split("/");
      arr[arr.length - 2] = template;
      $(".background-preview").attr("src", arr.join("/"));
      $("input[name='background']").val(arr.join("/"));
      setBackgroundImage(arr.join("/"));
    }
  }

  function drawCircle() {
    // Draw circle
    canvas.getObjects().forEach(function (o) {
      if (o.id == "circle") {
        canvas.remove(o);
      }
    });
    var circle = new fabric.Circle({
      id: "circle",
      top: circle_pos[template]["y"],
      left: circle_pos[template]["x"],
      radius: circle_pos[template]["radius"],
      fill: circle_colors[0],
      selectable: false,
      evented: false,
    });
    canvas.add(circle);
    canvas.sendToBack(circle);
  }

  function drawFeatured() {
    var featured, featured_text;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "featured") {
        canvas.remove(o);
      }
    });

    if (!$("#show_featured").prop("checked")) {
      return;
    }
    if (template == 0) {
      featured = new fabric.Rect({
        id: "featured",
        top: 45,
        left: 47,
        width: 283,
        height: 86,
        rx: 16,
        ry: 16,
        fill: "#9f9a97",
        selectable: false,
        evented: false,
      });
      featured_text = new fabric.Textbox("Featured", {
        id: "featured",
        top: 65,
        left: 47,
        width: 283,
        fontSize: 50,
        fill: "#ffffff",
        fontFamily: "Arial",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
    } else if (template == 1) {
      featured = new fabric.Rect({
        id: "featured",
        top: 15,
        left: 16,
        width: 145,
        height: 40,
        rx: 3,
        ry: 3,
        fill: "#f2fafe",
        selectable: false,
        evented: false,
      });
      featured_text = new fabric.Textbox("Featured", {
        id: "featured",
        top: 20,
        left: 16,
        width: 145,
        fontSize: 30,
        fill: "#232323",
        fontFamily: "Arial",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
    } else if (template == 2) {
      featured = new fabric.Rect({
        id: "featured",
        top: 22,
        left: 25,
        width: 145,
        height: 43,
        rx: 3,
        ry: 3,
        fill: "#f2fafe",
        selectable: false,
        evented: false,
      });
      featured_text = new fabric.Textbox("Featured", {
        id: "featured",
        top: 28,
        left: 25,
        width: 145,
        fontSize: 32,
        fill: "#232323",
        fontFamily: "Arial",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
    }
    canvas.add(featured);
    canvas.add(featured_text);
    canvas.sendToBack(featured);
    canvas.renderAll();
  }

  function drawShopButton() {
    var shop_button, shop_text;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "shopnow") {
        canvas.remove(o);
      }
    });

    if (!template || !$("#show_button").prop("checked")) {
      return;
    }

    if (template == 1) {
      shop_button = new fabric.Rect({
        id: "shopnow",
        top: 111,
        left: 985,
        width: 250,
        height: 74,
        rx: 30,
        ry: 30,
        strokeWidth: 4,
        stroke: "#1d1e1f",
        fill: "#ffffff",
        selectable: false,
        evented: false,
      });
      shop_text = new fabric.Textbox("Shop Now", {
        id: "shopnow",
        top: 130,
        left: 985,
        width: 250,
        fontSize: 40,
        fill: "#1d1e1f",
        fontFamily: "Arial",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
    } else if (template == 2) {
      shop_button = new fabric.Rect({
        id: "shopnow",
        top: 158,
        left: 2814,
        width: 296,
        height: 86,
        rx: 40,
        ry: 40,
        strokeWidth: 4,
        stroke: "#1d1e1f",
        fill: "#ffffff",
        selectable: false,
        evented: false,
      });
      shop_text = new fabric.Textbox("Shop Now", {
        id: "shopnow",
        top: 183,
        left: 2814,
        width: 296,
        fontSize: 40,
        fill: "#1d1e1f",
        fontFamily: "Arial",
        textAlign: "center",
        selectable: false,
        evented: false,
      });
    }
    canvas.add(shop_button);
    canvas.add(shop_text);
    canvas.sendToBack(shop_button);
    canvas.renderAll();
  }

  function drawBurst() {
    // Draw burst
    canvas.getObjects().forEach(function (o) {
      if (o.id == "burst") {
        canvas.remove(o);
      }
    });
    var burst_colors = $("#burst_color").val().split(",");
    var burst = new fabric.Circle({
      id: "burst",
      top: burst_pos[template]["y"],
      left: burst_pos[template]["x"],
      radius: burst_pos[template]["radius"],
      fill: burst_colors[0],
      selectable: false,
      evented: false,
    });
    canvas.add(burst);
    canvas.sendToBack(burst);
  }

  function drawBurstText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "burst_text") {
        canvas.remove(o);
      }
    });
    var burst_colors = $("#burst_color").val().split(",");
    var burst_text = $("input[name='burst_text']").val();
    var burst = new fabric.Textbox(burst_text, {
      id: "burst_text",
      top: 0,
      left: burst_pos[template]["x"] + 10,
      fontSize: template == 1 ? 30 : 50,
      fill: burst_colors[1],
      fontFamily: "Proxima-Nova-Black",
      breakWords: true,
      width: burst_pos[template]["radius"] * 2 - 20,
      textAlign: "center",
      lineHeight: 0.8,
      selectable: false,
      evented: false,
    });
    burst.set({
      top:
        (burst_pos[template]["radius"] * 2 -
          burst.height +
          burst_pos[template]["y"]) /
          2 -
        12,
    });
    canvas.add(burst);
    canvas.sendToBack(burst);
  }

  function drawMessageText() {
    canvas.getObjects().forEach(function (o) {
      if (
        o.id == "text_row_1" ||
        o.id == "text_row_2" ||
        o.id == "text_row_3"
      ) {
        canvas.remove(o);
      }
    });
    if (template == 0) {
      if (message_options == 0 || message_options == 1) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 260,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 2) {
        var val1 = $("input[name='value1']").val();
        if (val1 == "") {
          val1 = $("input[name='value1']").attr("placeholder");
        }
        var text_row_1 = new fabric.Text("BUY " + val1, {
          id: "text_row_1",
          top: 254,
          left: 0,
          fontSize: 74,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 3) {
        var text_row_1 = new fabric.Text("SAVE UP TO", {
          id: "text_row_1",
          top: 256,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 280,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      }
    } else if (template == 1) {
      if (message_options == 0 || message_options == 1) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 15,
          left: 0,
          fontSize: 30,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 2) {
        var val1 = $("input[name='value1']").val();
        if (val1 == "") {
          val1 = $("input[name='value1']").attr("placeholder");
        }
        var text_row_1 = new fabric.Text("BUY " + val1, {
          id: "text_row_1",
          top: 15,
          left: 0,
          fontSize: 42,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 3) {
        var text_row_1 = new fabric.Text("SAVE UP TO", {
          id: "text_row_1",
          top: 15,
          left: 0,
          fontSize: 33,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 30,
          left: 0,
          fontSize: 30,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      }
    } else if (template == 2) {
      if (message_options == 0 || message_options == 1) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 40,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 2) {
        var val1 = $("input[name='value1']").val();
        if (val1 == "") {
          val1 = $("input[name='value1']").attr("placeholder");
        }
        var text_row_1 = new fabric.Text("BUY " + val1, {
          id: "text_row_1",
          top: 35,
          left: 0,
          fontSize: 72,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 3) {
        var text_row_1 = new fabric.Text("SAVE UP TO", {
          id: "text_row_1",
          top: 40,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_row_1 = new fabric.Text("ON SALE NOW", {
          id: "text_row_1",
          top: 50,
          left: 0,
          fontSize: 45,
          fill: circle_colors[1],
          fontFamily: "Proxima-Nova-Extrabld",
          selectable: false,
          evented: false,
        });
        text_row_1.left =
          (circle_pos[template]["radius"] * 2 - text_row_1.width) / 2 +
          circle_pos[template]["x"];
        canvas.add(text_row_1);
        canvas.renderAll();
      }
    }
  }

  function drawValueText() {
    canvas.getObjects().forEach(function (o) {
      if (
        o.id == "text_val_1" ||
        o.id == "text_val_2" ||
        o.id == "text_slash" ||
        o.id == "text_dollar"
      ) {
        canvas.remove(o);
      }
    });
    var val1 = $("input[name='value1']").val();
    var val2 = $("input[name='value2']").val();
    if (val1 == "") {
      val1 = $("input[name='value1']").attr("placeholder");
    }
    if (val2 == "") {
      val2 = $("input[name='value2']").attr("placeholder");
    }
    if (template == 0) {
      if (message_options == 0) {
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 301,
          left: 0,
          fontSize: 142,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_slash = new fabric.Text("/", {
          id: "text_slash",
          top: 291,
          left: 0,
          fontSize: 162,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 315,
          left: 0,
          fontSize: 82,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 301,
          left: 0,
          fontSize: 144,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width +
          text_slash.width +
          text_dollar.width +
          text_val_2.width +
          30;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_slash.left = left + text_val_1.width + 10;
        text_dollar.left = text_slash.left + text_slash.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_slash);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 1 || message_options == 3) {
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 318,
          left: 0,
          fontSize: 82,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 301,
          left: 0,
          fontSize: 144,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 316,
          left: 0,
          fontSize: 82,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_dollar.left = left;
        text_val_1.left = text_dollar.left + text_dollar.width + 10;
        text_val_2.left = text_val_1.left + text_val_1.width + 10;
        canvas.add(text_dollar);
        canvas.add(text_val_1);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 2) {
        var text_val_1 = new fabric.Text("SAVE ", {
          id: "text_val_1",
          top: 315,
          left: 0,
          fontSize: 90,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 327,
          left: 0,
          fontSize: 55,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 315,
          left: 0,
          fontSize: 90,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_val_1 = new fabric.Text("SAVE " + val1, {
          id: "text_val_1",
          top: 318,
          left: 0,
          fontSize: 90,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("¢", {
          id: "text_dollar",
          top: 330,
          left: 0,
          fontSize: 50,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width = text_val_1.width + text_dollar.width;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.renderAll();
      }
    } else if (template == 1) {
      if (message_options == 0) {
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 42,
          left: 0,
          fontSize: 110,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_slash = new fabric.Text("/", {
          id: "text_slash",
          top: 37,
          left: 0,
          fontSize: 120,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 50,
          left: 0,
          fontSize: 65,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 42,
          left: 0,
          fontSize: 110,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width +
          text_slash.width +
          text_dollar.width +
          text_val_2.width +
          30;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_slash.left = left + text_val_1.width + 10;
        text_dollar.left = text_slash.left + text_slash.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_slash);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 1 || message_options == 3) {
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 57,
          left: 0,
          fontSize: 60,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 40,
          left: 0,
          fontSize: 110,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 50,
          left: 0,
          fontSize: 70,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_dollar.left = left;
        text_val_1.left = text_dollar.left + text_dollar.width + 10;
        text_val_2.left = text_val_1.left + text_val_1.width + 10;
        canvas.add(text_dollar);
        canvas.add(text_val_1);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 2) {
        var text_val_1 = new fabric.Text("SAVE ", {
          id: "text_val_1",
          top: 47,
          left: 0,
          fontSize: 70,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 57,
          left: 0,
          fontSize: 40,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 47,
          left: 0,
          fontSize: 70,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_val_1 = new fabric.Text("SAVE " + val1, {
          id: "text_val_1",
          top: 57,
          left: 0,
          fontSize: 65,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("¢", {
          id: "text_dollar",
          top: 57,
          left: 0,
          fontSize: 40,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width = text_val_1.width + text_dollar.width;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.renderAll();
      }
    } else if (template == 2) {
      if (message_options == 0) {
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 75,
          left: 0,
          fontSize: 150,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_slash = new fabric.Text("/", {
          id: "text_slash",
          top: 70,
          left: 0,
          fontSize: 160,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 95,
          left: 0,
          fontSize: 70,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 75,
          left: 0,
          fontSize: 150,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width +
          text_slash.width +
          text_dollar.width +
          text_val_2.width +
          30;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_slash.left = left + text_val_1.width + 10;
        text_dollar.left = text_slash.left + text_slash.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_slash);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 1 || message_options == 3) {
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 100,
          left: 0,
          fontSize: 75,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_1 = new fabric.Text(val1, {
          id: "text_val_1",
          top: 80,
          left: 0,
          fontSize: 150,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 95,
          left: 0,
          fontSize: 75,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_dollar.left = left;
        text_val_1.left = text_dollar.left + text_dollar.width + 10;
        text_val_2.left = text_val_1.left + text_val_1.width + 10;
        canvas.add(text_dollar);
        canvas.add(text_val_1);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 2) {
        var text_val_1 = new fabric.Text("SAVE ", {
          id: "text_val_1",
          top: 90,
          left: 0,
          fontSize: 110,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("$", {
          id: "text_dollar",
          top: 105,
          left: 0,
          fontSize: 60,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_val_2 = new fabric.Text(val2, {
          id: "text_val_2",
          top: 90,
          left: 0,
          fontSize: 110,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width =
          text_val_1.width + text_dollar.width + text_val_2.width + 20;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width + 10;
        text_val_2.left = text_dollar.left + text_dollar.width + 10;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.add(text_val_2);
        canvas.renderAll();
      } else if (message_options == 4) {
        var text_val_1 = new fabric.Text("SAVE " + val1, {
          id: "text_val_1",
          top: 90,
          left: 0,
          fontSize: 95,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var text_dollar = new fabric.Text("¢", {
          id: "text_dollar",
          top: 90,
          left: 0,
          fontSize: 60,
          fill: circle_colors[2],
          fontFamily: "Proxima-Nova-Black",
          selectable: false,
          evented: false,
        });
        var total_width = text_val_1.width + text_dollar.width;
        var left =
          (circle_pos[template]["radius"] * 2 - total_width) / 2 +
          circle_pos[template]["x"];
        text_val_1.left = left;
        text_dollar.left = text_val_1.left + text_val_1.width;
        canvas.add(text_val_1);
        canvas.add(text_dollar);
        canvas.renderAll();
      }
    }
  }

  function drawDescription() {
    canvas.getObjects().forEach(function (o) {
      if (
        o.id == "product_name_row_1" ||
        o.id == "product_name_row_2" ||
        o.id == "product_name_row_3"
      ) {
        canvas.remove(o);
      }
    });
    var val1 = $("input[name='text1']").val();
    var val2 = $("input[name='text2']").val();
    var val3 = $("input[name='text3']").val();
    var top_offset = 10;
    if (message_options == 2 || message_options == 4) {
      top_offset = -20;
    }
    if (template == 0) {
      var product_name_row_1 = new fabric.Text(val1, {
        id: "product_name_row_1",
        top: 438 + top_offset,
        left: 0,
        fontSize: 26,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_1.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_1.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_1);

      var product_name_row_2 = new fabric.Text(val2, {
        id: "product_name_row_2",
        top: 468 + top_offset,
        left: 0,
        fontSize: 26,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_2.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_2.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_2);

      var product_name_row_3 = new fabric.Text(val3, {
        id: "product_name_row_3",
        top: 497 + top_offset,
        left: 0,
        fontSize: 26,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_3.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_3.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_3);
      canvas.renderAll();
    } else if (template == 1) {
      var product_name_row_1 = new fabric.Text(val1, {
        id: "product_name_row_1",
        top: 144 + top_offset,
        left: 0,
        fontSize: 18,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_1.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_1.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_1);

      var product_name_row_2 = new fabric.Text(val2, {
        id: "product_name_row_2",
        top: 168 + top_offset,
        left: 0,
        fontSize: 18,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_2.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_2.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_2);

      var product_name_row_3 = new fabric.Text(val3, {
        id: "product_name_row_3",
        top: 192 + top_offset,
        left: 0,
        fontSize: 18,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_3.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_3.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_3);
      canvas.renderAll();
    } else if (template == 2) {
      var product_name_row_1 = new fabric.Text(val1, {
        id: "product_name_row_1",
        top: 220 + top_offset,
        left: 0,
        fontSize: 27,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_1.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_1.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_1);

      var product_name_row_2 = new fabric.Text(val2, {
        id: "product_name_row_2",
        top: 256 + top_offset,
        left: 0,
        fontSize: 27,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_2.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_2.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_2);

      var product_name_row_3 = new fabric.Text(val3, {
        id: "product_name_row_3",
        top: 292 + top_offset,
        left: 0,
        fontSize: 27,
        fill: circle_colors[3],
        fontFamily: "Proxima-Nova-Semibold",
        selectable: false,
        evented: false,
      });
      product_name_row_3.left =
        (circle_pos[template]["radius"] * 2 - product_name_row_3.width) / 2 +
        circle_pos[template]["x"];
      canvas.add(product_name_row_3);
      canvas.renderAll();
    }
  }

  function drawLegalText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "legal") {
        canvas.remove(o);
      }
    });
    var legal = $("input[name='legal']").val();
    if (template == 0) {
      var legal_text = new fabric.Textbox(legal, {
        id: "legal",
        top: 0,
        left: 0,
        fontSize: 16,
        fill: "#000000",
        fontFamily: "Proxima-Nova-Regular-It",
        width: dimension[template]["width"] - 74,
        breakWords: true,
        textAlign: template ? "center" : "left",
        selectable: false,
        evented: false,
      });
      legal_text.top = dimension[template]["height"] - 60;
      legal_text.left = 37;
      if (legal != "") {
        var legal_background = new fabric.Rect({
          id: "legal",
          left: 0,
          top: dimension[template]["height"] - 80,
          fill: "#E5E5E5",
          width: dimension[template]["width"],
          height: 80,
          selectable: false,
          evented: false,
        });
        canvas.add(legal_background);
        legal_background.bringToFront();
      }
      canvas.add(legal_text);
      legal_text.bringToFront();
    } else if (template == 1) {
      var legal_text = new fabric.Textbox(legal, {
        id: "legal",
        top: 0,
        left: 0,
        fontSize: 13,
        fill: "#000000",
        fontFamily: "Proxima-Nova-Regular-It",
        width: dimension[template]["width"] - 70,
        breakWords: true,
        textAlign: template ? "center" : "left",
        selectable: false,
        evented: false,
      });
      legal_text.top = dimension[template]["height"] - legal_text.height - 7;
      legal_text.left =
        (dimension[template]["width"] - legal_text.width - 70) / 2;
      if (legal != "") {
        var legal_background = new fabric.Rect({
          id: "legal",
          left: 0,
          top: dimension[template]["height"] - legal_text.height - 15,
          fill: "#E5E5E5",
          width: dimension[template]["width"],
          height: legal_text.height + 15,
          selectable: false,
          evented: false,
        });
        canvas.add(legal_background);
        legal_background.bringToFront();
      }
      canvas.add(legal_text);
      legal_text.bringToFront();
    } else if (template == 2) {
      var legal_text = new fabric.Textbox(legal, {
        id: "legal",
        top: 0,
        left: 0,
        fontSize: 16,
        fill: "#000000",
        fontFamily: "Proxima-Nova-Regular-It",
        width: dimension[template]["width"] - 70,
        breakWords: true,
        textAlign: template ? "center" : "left",
        selectable: false,
        evented: false,
      });
      legal_text.top = dimension[template]["height"] - legal_text.height - 15;
      legal_text.left =
        (dimension[template]["width"] - legal_text.width - 70) / 2;
      if (legal != "") {
        var legal_background = new fabric.Rect({
          id: "legal",
          left: 0,
          top: dimension[template]["height"] - legal_text.height - 30,
          fill: "#E5E5E5",
          width: dimension[template]["width"],
          height: legal_text.height + 30,
          selectable: false,
          evented: false,
        });
        canvas.add(legal_background);
        legal_background.bringToFront();
      }
      canvas.add(legal_text);
      legal_text.bringToFront();
    }
    canvas.renderAll();
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
    // var url = URL.createObjectURL(url);
    var imageUrl = $('input[name="background"]').val();
    if (imageUrl == "") {
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
      top = -(img.height * scaleFactor - dimension_height) / 2;
    } else {
      var scaleFactor = dimension_height / img.height;
      top = 0;
      left = -(img.width * scaleFactor - dimension_width) / 2;
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

  function loadFabricImage(file, sum_width_dimension) {
    var product_width = product[template]["width"];
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
      if (o.id == "image1" || o.id == "image2") {
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
      var product_space = 25;
      var product_width = product[template]["width"];
      var product_height = product[template]["height"];
      var left = product[template]["left"];

      var files = response.data.files;
      if (!files || !files.length) return;
      if (files.length > 2) {
        files = files.slice(0, 2);
      }
      var sum_width_dimension = 0;
      files.forEach((file) => {
        sum_width_dimension += file.related_files[0].width;
      });
      max_height = 0;
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
      left += (product_width - total_width) / 2;
      res.forEach((item, index) => {
        if (index) {
          left += product_space;
        }
        var sh = shadows[0].list;
        var shadow = new fabric.Shadow({
          color: "#000000" + parseInt(2.5 * sh[0].value).toString(16),
          blur: Math.ceil(sh[4].value * 4),
          offsetX: -sh[2].value * 5 * Math.cos((sh[1].value * Math.PI) / 180),
          offsetY: sh[2].value * 5 * Math.sin((sh[1].value * Math.PI) / 180),
        });
        var angle = parseFloat(
          $("input[name='angle[]']")
            .eq(template * 2 + index)
            .val()
        );
        var x_offset = parseFloat(
          $("input[name='x_offset[]']")
            .eq(template * 2 + index)
            .val()
        );
        var y_offset = parseFloat(
          $("input[name='y_offset[]']")
            .eq(template * 2 + index)
            .val()
        );
        var scale = parseFloat(
          $("input[name='scale[]']")
            .eq(template * 2 + index)
            .val()
        );
        item.image.set({ left: left + (item.width * scale) / 2 + x_offset });
        item.image.scaleToWidth(item.width);
        item.image.set({
          originX: "middle",
          originY: "middle",
          lockUniScaling: true,
        });
        item.image.set({ angle: angle });
        item.image.set({
          top:
            product[template]["baseline"] -
            item.height +
            y_offset +
            (item.height * scale) / 2,
        });
        item.image.set({ id: "image" + (index + 1) });
        item.image.set({ shadow: shadow });
        item.image.set({ scaleX: item.image.scaleX * scale });
        item.image.set({ scaleY: item.image.scaleY * scale });
        canvas.add(item.image);

        left = left + item.width;
        if (angle % 90 != 0) {
          var bound = item.image.getBoundingRect();
          var bound_x = (bound.width - item.width * scale) / 2;
          var bound_y = (bound.height - item.height * scale) / 2;

          item.image.set({ left: item.image.left + bound_x });
          item.image.set({ top: item.image.top + bound_y });

          originCords.push({
            x: item.image.left - bound.width / 2 - x_offset,
            y: item.image.top - bound.height / 2 - y_offset,
            scaleX: item.image.scaleX / scale,
          });
        } else {
          originCords.push({
            x: item.image.left - (item.width * scale) / 2 - x_offset,
            y: item.image.top - (item.height * scale) / 2 - y_offset,
            scaleX: item.image.scaleX / scale,
          });
        }
        canvas.bringToFront(item.image);
      });
    });
  }

  $('input[name="file_ids"]').on("change", function () {
    $("input[name='angle[]']")
      .eq(template * 2)
      .val(-5);
    $("input[name='angle[]']")
      .eq(template * 2 + 1)
      .val(5);
    $("input[name='x_offset[]']")
      .eq(template * 2)
      .val(0);
    $("input[name='x_offset[]']")
      .eq(template * 2 + 1)
      .val(0);
    $("input[name='y_offset[]']")
      .eq(template * 2)
      .val(0);
    $("input[name='y_offset[]']")
      .eq(template * 2 + 1)
      .val(0);
    $("input[name='scale[]']")
      .eq(template * 2)
      .val(1);
    $("input[name='scale[]']")
      .eq(template * 2 + 1)
      .val(1);
    originCords = [];
    drawProductImage();
    setTimeout(function () {
      drawLegalText();
    }, 3000);
  });

  $(
    "#message_options, input[name='value1'], input[name='value2'], input[name='text1'], input[name='text2'], input[name='text3']"
  ).on("change", function () {
    message_options = $("#message_options").val();
    drawMessageText();
    drawValueText();
    drawDescription();
  });

  $("#theme").on("change", function () {
    drawBackgroundImage(true);
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: "kroger",
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      var colors = response.data.circle_text_color;
      shadows = response.data.shadow;
      var selected = $("#circle_text_color").data("value");
      colors.forEach((c, i) => {
        var cc = c.list.map((x) => x.value).join(",");
        if (i == 0) {
          circle_colors = cc.split(",");
        }
        if (selected == `${cc}`) {
          circle_colors = selected.split(",");
        }
      });
      drawMessageText();
      drawValueText();
      drawDescription();
      drawCircle();
      drawProductImage();
    });
  });

  $("#circle_text_color").on("change", function () {
    circle_colors = $("#circle_text_color").val().split(",");
    drawMessageText();
    drawValueText();
    drawDescription();
    drawCircle();
  });

  $("input[name='legal']").on("change", function () {
    drawLegalText();
  });

  $("#selectBkImgModal #submit").on("click", function () {
    var background = $("input[name='background']").val();
    setBackgroundImage(background);
  });

  $(
    "input[name='x_offset[]'], input[name='y_offset[]'], input[name='angle[]'], select[name='shadow']"
  ).on("change", function () {
    drawProductImage();
    setTimeout(function () {
      drawLegalText();
    }, 3000);
  });

  $("#burst_color, input[name='burst_text']").on("change", function () {
    if (template) {
      drawBurstText();
      drawBurst();
    }
  });

  $("#show_featured").on("change", function () {
    drawFeatured();
  });

  $("#show_button").on("change", function () {
    drawShopButton();
  });

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
          { width: "312px", height: "566px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "1281px", height: "300px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "1200px", height: "150px" },
          { cssOnly: true }
        );
      }
    } else {
      $(this).removeClass("save");
      $(this).addClass("edit");
      $(this).html('<i class="cil-pencil"></i>');
      if (template == 0) {
        canvas.setDimensions(
          { width: "156px", height: "283px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "427px", height: "100px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "600px", height: "75px" },
          { cssOnly: true }
        );
      }
    }
    $("#preview-popup").css({ right: 0, left: "auto" });
    canvas.renderAll();
  });

  $("#selectImgModal #submit").on("click", function () {
    $("input[name='angle[]']").eq(0).val(-5);
    $("input[name='angle[]']").eq(1).val(5);
    $("input[name='x_offset[]']").val(0);
    $("input[name='y_offset[]']").val(0);
    $("input[name='scale[]']").val(1);
    originCords = [];
    drawProductImage();
  });
});
