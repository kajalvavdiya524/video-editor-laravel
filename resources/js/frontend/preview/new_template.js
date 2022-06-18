import { fabric } from "fabric";
import readXlsxFile from "read-excel-file";
import { font_list } from "../../fonts.js";
var fonts = Object.keys(font_list);

fabric.perfLimitSizeTotal = 16777216;

var hero_image_index = -1;
var shadows = [];
var template_data = {};
window.template_data = template_data;

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

  fabric.CustomRect = fabric.util.createClass(fabric.Rect, {
    type: "CustomRect",
    initialize: function (options) {
      options || (options = {});
      options.corners = options.corners || [0, 0, 0, 0];
      this.set(options);
      self = this;
    },
    _render: function (ctx) {
      // 1x1 case (used in spray brush) optimization was removed because
      // with caching and higher zoom level this makes more damage than help

      var rx = this.rx ? Math.min(this.rx, this.width / 2) : 0,
        ry = this.ry ? Math.min(this.ry, this.height / 2) : 0,
        w = this.width,
        h = this.height,
        x = -this.width / 2,
        y = -this.height / 2,
        /* "magic number" for bezier approximations of arcs (http://itc.ktu.lt/itc354/Riskus354.pdf) */
        k = 1 - 0.5522847498;
      ctx.beginPath();

      // top left corner
      ctx.moveTo(x + rx * this.corners[0], y);
      // moving to top right
      ctx.lineTo(x + w - rx * this.corners[3], y);
      this.corners[3] &&
        ctx.bezierCurveTo(
          x + w - k * rx * this.corners[3],
          y,
          x + w,
          y + k * ry * this.corners[3],
          x + w,
          y + ry * this.corners[3]
        );

      // bottom right corner
      ctx.lineTo(x + w, y + h - ry * this.corners[2]);
      this.corners[2] &&
        ctx.bezierCurveTo(
          x + w,
          y + h - k * ry * this.corners[2],
          x + w - k * rx * this.corners[2],
          y + h,
          x + w - rx * this.corners[2],
          y + h
        );

      ctx.lineTo(x + rx * this.corners[1], y + h);
      this.corners[1] &&
        ctx.bezierCurveTo(
          x + k * rx * this.corners[1],
          y + h,
          x,
          y + h - k * ry * this.corners[1],
          x,
          y + h - ry * this.corners[1]
        );

      ctx.lineTo(x, y + ry * this.corners[0]);
      this.corners[0] &&
        ctx.bezierCurveTo(
          x,
          y + k * ry * this.corners[0],
          x + k * rx * this.corners[0],
          y,
          x + rx * this.corners[0],
          y
        );

      ctx.closePath();

      this._renderPaintInOrder(ctx);
    },
  });

  fabric.Textbox.prototype.getHeightOfLine = function (lineIndex) {
    var line = this._textLines[lineIndex],
      maxHeight = this.getHeightOfChar(lineIndex, 0);
    for (var i = 1, len = line.length; i < len; i++) {
      maxHeight = Math.max(this.getHeightOfChar(lineIndex, i), maxHeight);
    }

    if (this.lineHeight < 2) {
      return (this.__lineHeights[lineIndex] =
        maxHeight * this.lineHeight * this._fontSizeMult);
    } else {
      return (this.__lineHeights[lineIndex] = this.lineHeight);
    }
  };

  var dimension = {};
  var canvas_dimension = {};
  var product = {};
  var background_theme_image = [];
  var img_from_bk = [];
  var uploaded_image = {};
  var product_image_settings = [];
  var originCoords = [];
  var textCoords = [];
  var stTextCoords = [];
  var imgCoords = [];
  var stImgCoords = [];
  var lineCoords = [];
  var bkImgCoords = [];
  var imgFromBkCoords = [];
  var circleCoords = [];
  var cirtypeCoords = [];
  var rectCoords = [];
  var iconCoords = [];
  var max_height = 0;
  var smartObjCoords = {};
  var canvas;
  var base_url = window.location.origin;
  var grid_density = 0;
  var spacingFieldX = 0;
  var spacingFieldWidth = 0;
  var spacingFieldAlignment = "left";
  var spacingFieldValues = [];
  var spacingFields = [];
  var spacingFieldPosition = {};
  var additionalTexts = [];
  var additionalRectangles = [];
  var additionalCircles = [];

  function formatOutput(optionElement) {
    return $(
      `<div style="font-family: ${optionElement.id}">${optionElement.text}</div>`
    );
  }

  function onLoad() {
    originCoords = [];

    $(".canvas-container").remove();
    if ($(".edit-button").hasClass("save")) {
      $(".edit-button").removeClass("save");
      $(".edit-button").addClass("edit");
      $(".edit-button").html('<i class="cil-pencil"></i>');
    }

    if ($("#theme").val() !== undefined) {
      axios({
        method: "post",
        url: "/banner/kroger_template_settings",
        data: {
          customer: $('input[name="customer"]').val(),
          color_scheme: $("#theme").val(),
        },
      }).then(function (response) {
        shadows = response.data.shadow;
      });
    }

    axios({
      method: "post",
      url: "/banner/template_settings",
      data: {
        template_id: $("input[name='template_id']").val(),
      },
    }).then(function (response) {
      template_data = response.data;
      window.template_data = template_data;
      let html = "";

      template_data.fields.forEach((field) => {
        if (field.type == "Product Dimensions") {
          var options = JSON.parse(field.options);
          product["left"] = parseInt(options.X);
          product["top"] = parseInt(options.Y);
          product["width"] = parseInt(options.Width);
          product["height"] = parseInt(options.Height);
          product["alignment"] = options.Alignment;
        } else if (field.type == "Product Image") {
          product_image_settings.push(JSON.parse(field.options));
        } else if (field.type == "Background Theme Image") {
          var options = JSON.parse(field.options);
          var bt_image = {};
          bt_image["name"] = field.name;
          bt_image["left"] = parseInt(options.X);
          bt_image["top"] = parseInt(options.Y);
          bt_image["width"] = parseInt(options.Width);
          bt_image["height"] = parseInt(options.Height);
          bt_image["order"] = parseInt(options.Order);
          bt_image["crop"] = options["Option5"] == "crop";
          bt_image["moveable"] = options["Moveable"] == "Yes";
          background_theme_image.push(bt_image);
        } else if (field.type == "Image From Background") {
          var options = JSON.parse(field.options);
          var bt_image = {};
          bt_image["name"] = field.name;
          bt_image["left"] = parseInt(options.X);
          bt_image["top"] = parseInt(options.Y);
          bt_image["width"] = parseInt(options.Width);
          bt_image["height"] = parseInt(options.Height);
          bt_image["order"] = parseInt(options.Order);
          bt_image["crop"] = options["Option5"] == "crop";
          bt_image["moveable"] = options["Moveable"] == "Yes";
          bt_image["Group Name"] = options["Group Name"];
          img_from_bk.push(bt_image);
        } else if (field.type == "Canvas") {
          var options = JSON.parse(field.options);
          canvas_dimension["width"] = parseInt(options.Width);
          canvas_dimension["height"] = parseInt(options.Height);
          canvas_dimension["left"] = options.X ? parseInt(options.X) : 0;
          canvas_dimension["top"] = options.Y ? parseInt(options.Y) : 0;
        } else if (field.type == "Smart Object") {
          var options = JSON.parse(field.options);
          var groupName = options["Group Name"];
          smartObjCoords[groupName] = options;
        } else if (field.type == "Field Spacing") {
          const options = JSON.parse(field.options);
          const field_spacing_names = options["Option1"].split(",");
          spacingFieldValues = options["Option2"].split(",");
          spacingFieldX = +options["X"];
          spacingFieldWidth = +options["Width"];
          if (spacingFieldWidth == 0) {
            spacingFieldWidth = template_data.width;
          }
          spacingFieldAlignment = options["Alignment"];
          for (let i = 0; i < field_spacing_names.length; i++) {
            const spacing_field = template_data.fields.find(
              (f) => f.name === field_spacing_names[i].trim()
            );
            if (spacing_field) {
              const spacing_field_options = JSON.parse(spacing_field.options);
              let spacing_field_width = +spacing_field_options["Width"];
              let textVal = $(
                `input[name="${spacing_field.element_id}"]`
              ).val();
              if (spacing_field.type.includes("Text") && textVal) {
                var { text, styles } = parseText(textVal, "#000000");
                const textBox = new fabric.Text(text, {
                  fontFamily: spacing_field_options["Font"],
                  fontSize: spacing_field_options["Font Size"],
                  styles,
                });
                spacing_field_width = textBox.width;
              }
              if (i < field_spacing_names.length - 1) {
                spacing_field_width += +spacingFieldValues[i];
              }
              spacingFields.push({
                name: spacing_field.name,
                element_id: spacing_field.element_id,
                width: spacing_field_width,
              });
            }
          }

          updateSpacingFieldPosition();
        } else if (field.type == "Safe Zone") {
          html += `<li name="${field.name}">${field.name}</li>`;
        }
      });
      if (html == "") {
        $(".safe-zone-list").remove();
      } else {
        $(".safe-zone-list").html(html);
      }

      if (!dimension["width"] || !dimension["height"]) {
        dimension["width"] = template_data.width;
        dimension["height"] = template_data.height;
        dimension["left"] = 0;
        dimension["top"] = 0;
      }
      $("#footer").before(
        `<canvas id="canvas" width="${dimension["width"]}" height="${dimension["height"]}"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        preserveObjectStacking: true,
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      var width, height;
      if (dimension["width"] > dimension["height"]) {
        width = 300;
        height = (width * dimension["height"]) / dimension["width"];
      } else {
        height = 300;
        width = (height * dimension["width"]) / dimension["height"];
      }
      canvas.setDimensions(
        { width: width + "px", height: height + "px" },
        { cssOnly: true }
      );
      window.previewCanvas = canvas;
      canvas.on({
        "object:moving": updateControls,
        "object:scaling": updateControls,
        "object:resizing": updateControls,
        "object:rotating": updateControls,
        "object:added": canvasModifiedCallback,
        "after:render": canvasUpdatePosition,
        "selection:created": selectionUpdated,
        "selection:cleared": selectionUpdated,
        "selection:updated": selectionUpdated,
      });

      canvas.on("text:changed", function (e) {
        let target = e.target;
        let element_id = target.element_id;
        let text = target.text;
        $(`input[name="${element_id}"]`).val(text);
        if (target.fixedWidth) {
          let element_id = target.element_id;
          if (target.width != target.fixedWidth) {
            target.fontSize *= target.fixedWidth / (target.width + 1);
          } else {
            while (target.width <= target.fixedWidth) {
              target.fontSize = target.fontSize + 1;
              canvas.renderAll();
            }
          }
          target.width = target.fixedWidth;
          $(`input[name='${element_id}_fontsize']`).val(target.fontSize);
        }
      });

      $("#preview-popup").show();

      // init
      drawForLoading();
      setBackgroundColor();
      setBackgroundImage();
      drawUploadedImage();
      drawStaticImage();
      drawUploadedBackgroundImage();
      drawImageFromBackground();
      drawRectangle();
      drawCircle();
      drawCircleType();
      drawOverlayArea();
      setTimeout(() => {
        if ($("#show_text").length == 0 || $("#show_text").is(":checked")) {
          drawTextNewTemplate();
          drawStaticText();
          drawAdditionalText();
        }
        drawProductImage();
        drawMarker();
        drawLine();
        drawImageList();
        drawGrid(grid_density);
      }, 3000);

      setTimeout(() => {
        drawSmartObject();
      }, 5000);

      // Additional components
      drawAdditionalRectangle();
      drawAdditionalCircle();

      fabric.Object.prototype.transparentCorners = false;
      fabric.Object.prototype.cornerColor = "#ffffff";
      fabric.Object.prototype.cornerStyle = "circle";
      fabric.Object.prototype.cornerSize =
        (dimension["width"] > dimension["height"]
          ? dimension["width"]
          : dimension["height"]) / 70;
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
        cornerSize:
          (dimension["width"] > dimension["height"]
            ? dimension["width"]
            : dimension["height"]) / 40,
      });
    });

    $(".additionalText").each((index, obj) => {
      let name = $(obj).attr("name");
      additionalTexts.push(name);
    });
    $(".additionalRectangle").each((index, obj) => {
      let name = $(obj).attr("name");
      additionalRectangles.push(name);
    });
    $(".additionalCircle").each((index, obj) => {
      let name = $(obj).attr("name");
      additionalCircles.push(name);
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

  function updateSpacingFieldPosition() {
    let group_width = 0;
    for (let i = 0; i < spacingFields.length; i++) {
      group_width += spacingFields[i].width;
    }

    let x = spacingFieldX;
    if (spacingFieldAlignment == "center") {
      x += (spacingFieldWidth - group_width) / 2;
    } else if (spacingFieldAlignment == "right") {
      x += spacingFieldWidth - group_width;
    }

    spacingFieldPosition[spacingFields[0].name] = {
      x,
      width: spacingFields[0].width,
    };
    for (let i = 1; i < spacingFields.length; i++) {
      x += spacingFields[i - 1].width;
      spacingFieldPosition[spacingFields[i].name] = {
        x,
        width: spacingFields[i].width,
      };
    }
  }

  function canvasUpdatePosition() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "canvas_template_rectangle") {
        canvas.bringToFront(o);
      }
    });
  }

  function selectionUpdated() {
    var obj = canvas.getActiveObject();
    if (obj) {
      let width = obj.width * obj.scaleX,
        height = obj.height * obj.scaleY,
        left = obj.left,
        top = obj.top;
      if (obj.originX == "middle") {
        left -= width / 2;
        $("#x_value").val(left.toFixed(2));
      } else {
        $("#x_value").val(obj.left.toFixed(2));
      }
      if (obj.originY == "middle") {
        top -= height / 2;
        $("#y_value").val(top.toFixed(2));
      } else {
        $("#y_value").val(obj.top.toFixed(2));
      }
      $("#w_value").val(width.toFixed(2));
      $("#h_value").val(height.toFixed(2));
    } else {
      $("#x_value").val("");
      $("#y_value").val("");
      $("#w_value").val("");
      $("#h_value").val("");
    }
  }

  function updateControls() {
    let obj = canvas.getActiveObject();
    let width = obj.width * obj.scaleX,
      height = obj.height * obj.scaleY,
      left = obj.left,
      top = obj.top;
    if (obj.originX == "middle") {
      left -= width / 2;
      $("#x_value").val(left.toFixed(2));
    } else {
      $("#x_value").val(obj.left.toFixed(2));
    }
    if (obj.originY == "middle") {
      top -= height / 2;
      $("#y_value").val(top.toFixed(2));
    } else {
      $("#y_value").val(obj.top.toFixed(2));
    }
    $("#w_value").val(width.toFixed(2));
    $("#h_value").val(height.toFixed(2));
    if (obj.id.includes("image")) {
      var obj_id = parseInt(obj.id.replace("image", ""));
      var x = obj.left;
      var y = obj.top;
      var angle = obj.angle.toFixed(2);
      var scale = (obj.scaleX / originCoords[obj_id]["scaleX"]).toFixed(2);
      var x_offset = (x - originCoords[obj_id]["x"]).toFixed(2);
      var y_offset = (y - originCoords[obj_id]["y"]).toFixed(2);
      $("input[name='x_offset[]']").eq(obj_id).val(x_offset);
      $("input[name='y_offset[]']").eq(obj_id).val(y_offset);
      $("input[name='angle[]']").eq(obj_id).val(angle);
      $("input[name='scale[]']").eq(obj_id).val(scale);

      if ($(".save-image-position").length > 0 && obj_id == hero_image_index) {
        localStorage.setItem(
          "hero_image_position",
          JSON.stringify({ x_offset, y_offset, angle, scale })
        );
      } else if (
        $(".save-image-position").length > 0 &&
        obj_id != hero_image_index
      ) {
        localStorage.setItem(
          "image_position_" + obj_id,
          JSON.stringify({ x_offset, y_offset, angle, scale })
        );
      }
      var bound = obj.getBoundingRect();
      $("input[name='p_width[]']").eq(obj_id).val(bound.width);
      $("input[name='p_height[]']").eq(obj_id).val(bound.height);
    } else if (obj.id.includes("additional_text")) {
      var obj_id = parseInt(obj.id.replace("additional_text", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(x.toFixed(2));
      $(`input[name='${element_id}_offset_y']`).val(y.toFixed(2));
      $(`input[name='${element_id}_width']`).val(width);
      $(`input[name='${element_id}_angle']`).val(obj.angle.toFixed(2));
    } else if (obj.id.includes("text")) {
      var obj_id = parseInt(obj.id.replace("text", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;

      $(`input[name='${element_id}_offset_x']`).val(
        (x - textCoords[obj_id]["x"]).toFixed(2)
      );

      $(`input[name='${element_id}_offset_y']`).val(
        (y - textCoords[obj_id]["y"]).toFixed(2)
      );

      $(`input[name='${element_id}_width']`).val(width);
      $(`input[name='${element_id}_angle']`).val(obj.angle.toFixed(2));
      $(`input[name='${element_id}_fontsize']`).val(
        (obj.fontSize * obj.scaleY).toFixed(0)
      );
    } else if (obj.id.includes("static_txt")) {
      var obj_id = parseInt(obj.id.replace("static_txt", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - stTextCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - stTextCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_width']`).val(width);
    } else if (obj.id.includes("shape_")) {
      var obj_id = parseInt(obj.id.replace("shape_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - lineCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - lineCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / lineCoords[obj_id]["scaleX"]).toFixed(2)
      );
    } else if (obj.id.includes("additional_circle_")) {
      var obj_id = parseInt(obj.id.replace("additional_circle_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(x.toFixed(2));
      $(`input[name='${element_id}_offset_y']`).val(y.toFixed(2));
      $(`input[name='${element_id}_radius']`).val((width / 2).toFixed(2));
    } else if (obj.id.includes("circle_")) {
      var obj_id = parseInt(obj.id.replace("circle_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - circleCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - circleCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / circleCoords[obj_id]["scaleX"]).toFixed(2)
      );
    } else if (obj.id.includes("circletype_")) {
      var obj_id = parseInt(obj.id.replace("circletype_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - cirtypeCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - cirtypeCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / cirtypeCoords[obj_id]["scaleX"]).toFixed(2)
      );
    } else if (obj.id.includes("additional_rectangle_")) {
      var obj_id = parseInt(obj.id.replace("additional_rectangle_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(x.toFixed(2));
      $(`input[name='${element_id}_offset_y']`).val(y.toFixed(2));
      $(`input[name='${element_id}_width']`).val(width.toFixed(2));
      $(`input[name='${element_id}_height']`).val(height.toFixed(2));
    } else if (obj.id.includes("rectangle_")) {
      var obj_id = parseInt(obj.id.replace("rectangle_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      obj.width *= obj.scaleX;
      obj.height *= obj.scaleY;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - rectCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - rectCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_scaleX']`).val(
        (obj.width / obj.originWidth).toFixed(2)
      );
      $(`input[name='${element_id}_scaleY']`).val(
        (obj.height / obj.originHeight).toFixed(2)
      );
      obj.scaleX = 1;
      obj.scaleY = 1;
    } else if (obj.id.includes("st_img_")) {
      var obj_id = parseInt(obj.id.replace("st_img_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - stImgCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - stImgCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_angle']`).val(obj.angle.toFixed(2));
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / stImgCoords[obj_id]["scaleX"]).toFixed(2)
      );
    } else if (obj.id.includes("icon_")) {
      var obj_id = parseInt(obj.id.replace("icon_", ""));
      var element_id = obj.element_id;
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - iconCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - iconCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_angle']`).val(obj.angle.toFixed(2));
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / iconCoords[obj_id]["scaleX"]).toFixed(2)
      );
    } else if (obj.id.includes("bk_theme_img_")) {
      var obj_id = parseInt(obj.id.replace("bk_theme_img_", ""));
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='bk_img_offset_x[]']`)
        .eq(obj_id)
        .val((x - bkImgCoords[obj_id]["x"]).toFixed(2));
      $(`input[name='bk_img_offset_y[]']`)
        .eq(obj_id)
        .val((y - bkImgCoords[obj_id]["y"]).toFixed(2));
      $(`input[name='bk_img_scale[]']`)
        .eq(obj_id)
        .val((obj.scaleX / bkImgCoords[obj_id]["scaleX"]).toFixed(2));
    } else if (obj.id.includes("img_from_bk_")) {
      imgFromBkCoords.sort(function (a, b) {
        return a.id - b.id;
      });
      var obj_id = parseInt(obj.id.replace("img_from_bk_", ""));
      var x = obj.oCoords.tl.x;
      var y = obj.oCoords.tl.y;
      $(`input[name='img_from_bk_offset_x[]']`)
        .eq(obj_id)
        .val((x - imgFromBkCoords[obj_id]["x"]).toFixed(2));
      $(`input[name='img_from_bk_offset_y[]']`)
        .eq(obj_id)
        .val((y - imgFromBkCoords[obj_id]["y"]).toFixed(2));
      if (imgFromBkCoords[obj_id]["scaleX"]) {
        $(`input[name='img_from_bk_scale[]']`)
          .eq(obj_id)
          .val((obj.scaleX / imgFromBkCoords[obj_id]["scaleX"]).toFixed(2));
      } else {
        $(`input[name='img_from_bk_scale[]']`).eq(obj_id).val(1);
      }
    } else {
      var obj_id = parseInt(obj.id.replace("upload_img_", ""));
      var element_id = obj.element_id;
      var x = obj.left;
      var y = obj.top;
      $(`input[name='${element_id}_offset_x']`).val(
        (x - imgCoords[obj_id]["x"]).toFixed(2)
      );
      $(`input[name='${element_id}_offset_y']`).val(
        (y - imgCoords[obj_id]["y"]).toFixed(2)
      );
      $(`input[name='${element_id}_angle']`).val(obj.angle.toFixed(2));
      $(`input[name='${element_id}_scale']`).val(
        (obj.scaleX / imgCoords[obj_id]["scaleX"]).toFixed(2)
      );

      var bound = obj.getBoundingRect();
      $(`input[name='${element_id}_width']`).val(bound.width);
      $(`input[name='${element_id}_height']`).val(bound.height);
    }

    if ($("#snap_to").prop("checked")) {
      if (obj.originX == "middle") {
        obj.set({ left: Math.round(left / 10) * 10 + width / 2 });
      } else {
        obj.set({ left: Math.round(left / 10) * 10 });
      }
      $("#x_value").val(Math.round(left / 10) * 10);
      if (obj.originY == "middle") {
        obj.set({ top: Math.round(top / 10) * 10 + height / 2 });
      } else {
        obj.set({ top: Math.round(top / 10) * 10 });
      }
      $("#y_value").val(Math.round(top / 10) * 10);
      if (Math.abs(left) <= dimension["width"] / 60) {
        if (obj.originX == "middle") {
          obj.set({ left: width / 2 });
        } else {
          obj.set({ left: 0 });
        }
        $("#x_value").val(0);
      }
      if (Math.abs(top) <= dimension["height"] / 60) {
        if (obj.originY == "middle") {
          obj.set({ top: height / 2 });
        } else {
          obj.set({ top: 0 });
        }
        $("#y_value").val(0);
      }
      if (
        left + width >= dimension["width"] - dimension["width"] / 60 &&
        left + width < dimension["width"] + dimension["width"] / 60
      ) {
        if (obj.originX == "middle") {
          obj.set({
            left: dimension["width"] - width / 2,
          });
        } else {
          obj.set({
            left: dimension["width"] - width,
          });
        }
        $("#x_value").val(dimension["width"] - width);
      }
      if (
        top + height >= dimension["height"] - dimension["height"] / 60 &&
        top + height < dimension["height"] + dimension["height"] / 60
      ) {
        if (obj.originY == "middle") {
          obj.set({
            top: dimension["height"] - height / 2,
          });
        } else {
          obj.set({
            top: dimension["height"] - height,
          });
        }
        $("#y_value").val(dimension["height"] - height);
      }
    }
  }

  function getPositioningOption(field) {
    if (typeof positioningOptions !== "undefined") {
      const fields = template_data.fields.filter(
        ({ type }) =>
          type == "Text" ||
          type == "Text Options" ||
          type == "Text from Spreadsheet"
      );
      const fieldFlags = {};
      $(".template-text-field").each(function () {
        const element_id = $(this).attr("name");
        const textField = fields.find((f) => f.element_id === element_id);
        fieldFlags[textField.name] = !!$(this).val();
      });

      const parsedOptions = positioningOptions.map((option) => {
        const parsedOption = {};
        option.fields.map(({ field_name, fields, x, y, width }) => {
          parsedOption[field_name] = { fields, x, y, width };
        });
        return parsedOption;
      });

      for (const option of parsedOptions) {
        let res = true;
        for (const key in option) {
          if (
            (option[key].fields == 1 && fieldFlags[key] !== true) ||
            (option[key].fields == null && fieldFlags[key] == true)
          ) {
            res = false;
            break;
          }
        }
        if (res) {
          return option[field.name];
        }
      }
    }
    return null;
  }

  function drawForLoading() {
    fonts.forEach((f) => {
      var t = new fabric.Text(" ", {
        id: "text",
        fontFamily: f,
        top: -256,
        left: 0,
        fontSize: 45,
        fill: "#000000",
      });
      canvas.add(t);
    });
    canvas.renderAll();
  }

  async function setBackgroundImage() {
    bkImgCoords = [];
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("bk_theme_img_")) {
        canvas.remove(o);
      }
    });

    var stored_settings = read_settings();
    var settings = stored_settings[0];
    var data = stored_settings[1];

    for (let i = 0; i < background_theme_image.length; i++) {
      const b = background_theme_image[i];

      await new Promise((resolve, reject) => {
        var url = $('input[name="background[]"]').eq(i).val();
        if (url == "" || url === undefined) {
          bkImgCoords.push({
            x: 0,
            y: 0,
            scaleX: 1,
          });
          resolve();
          // return; //removed because there could be more than one input of this type
        } else {
          // read again because it might have changed
          stored_settings = read_settings();
          settings = stored_settings[0];
          data = stored_settings[1];
          data["background[" + i + "]"] = url;

          var dimension_width = b["width"];
          var dimension_height = b["height"];
          var offset_x = parseFloat(
            $(`input[name='bk_img_offset_x[]']`).eq(i).val()
          );
          data["bk_img_offset_x[" + i + "]"] = offset_x;

          var offset_y = parseFloat(
            $(`input[name='bk_img_offset_y[]']`).eq(i).val()
          );
          data["bk_img_offset_y[" + i + "]"] = offset_y;

          var scale = parseFloat($(`input[name='bk_img_scale[]']`).eq(i).val());
          data["bk_img_scale[" + i + "]"] = scale;

          save_settings(data);

          var positioningOption = getPositioningOption(b);
          var left = b["left"];
          var top = b["top"];
          if (positioningOption) {
            left = positioningOption.x == null ? left : positioningOption.x;
            top = positioningOption.y == null ? top : positioningOption.y;
            dimension_width =
              positioningOption.width == null
                ? dimension_width
                : positioningOption.width;
          }
          fabric.Image.fromURL(url, function (oImg) {
            if (!dimension_width) {
              dimension_width = oImg.width;
            }
            if (!dimension_height) {
              dimension_height = oImg.height;
            }
            var config = {
              id: "bk_theme_img_" + i,
              order: b["order"],
              left: spacingFieldPosition[b.name]
                ? spacingFieldPosition[b.name].x
                : left + offset_x,
              top: top + offset_y,
              originX: "left",
              originY: "top",
              scaleX: (dimension_width / oImg.width) * scale,
              scaleY: (dimension_height / oImg.height) * scale,
              selectable: b["moveable"],
              evented: b["moveable"],
            };
            if (b["crop"]) {
              config["cropX"] = 0;
              config["cropY"] = 0;
              config["scaleX"] = scale;
              config["scaleY"] = scale;
              config["width"] = dimension_width;
              config["height"] = dimension_height;
            }
            oImg.set(config);
            canvas.add(oImg);

            bkImgCoords.push({
              x: oImg.left - offset_x,
              y: oImg.top - offset_y,
              scaleX: oImg.scaleX / scale,
            });
            setOrder();
            resolve();
          });
        }
      });
    }
  }

  async function drawImageFromBackground() {
    imgFromBkCoords = [];
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("img_from_bk_")) {
        canvas.remove(o);
      }
    });

    for (let i = 0; i < img_from_bk.length; i++) {
      const b = img_from_bk[i];
      await new Promise((resolve, reject) => {
        var url = $('input[name="img_from_bk[]"]').eq(i).val();
        if (url == "" || url === undefined) {
          imgFromBkCoords.push({
            id: i,
            x: 0,
            y: 0,
            scaleX: 1,
          });
          resolve();
          return;
        }
        var dimension_width = b["width"];
        var dimension_height = b["height"];
        var offset_x = parseFloat(
          $(`input[name='img_from_bk_offset_x[]']`).eq(i).val()
        );
        var offset_y = parseFloat(
          $(`input[name='img_from_bk_offset_y[]']`).eq(i).val()
        );
        var scale = parseFloat(
          $(`input[name='img_from_bk_scale[]']`).eq(i).val()
        );
        var positioningOption = getPositioningOption(b);
        var left = b["left"];
        var top = b["top"];
        if (positioningOption) {
          left = positioningOption.x == null ? left : positioningOption.x;
          top = positioningOption.y == null ? top : positioningOption.y;
          dimension_width =
            positioningOption.width == null
              ? dimension_width
              : positioningOption.width;
        }
        fabric.Image.fromURL(url, function (oImg) {
          var config = {
            id: "img_from_bk_" + i,
            groupName: b["Group Name"],
            order: b["order"],
            left: spacingFieldPosition[b.name]
              ? spacingFieldPosition[b.name].x
              : left + offset_x,
            top: top + offset_y,
            originX: "left",
            originY: "top",
            selectable: b["moveable"],
            evented: b["moveable"],
          };
          oImg.set(config);
          oImg.scaleToWidth(dimension_width * scale);
          canvas.add(oImg);

          imgFromBkCoords.push({
            id: i,
            x: oImg.left - offset_x,
            y: oImg.top - offset_y,
            scaleX: oImg.scaleX / scale,
          });
          setOrder();

          resolve();
        });
      });
    }
  }

  function setBackgroundColor() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "pixel_background") {
        canvas.remove(o);
      }
    });

    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Background Theme Color") {
        var options = JSON.parse(field.options);
        var color, colors;
        var isGradient = false;
        var gradient;
        if ($("select[name='background_color[]']").length) {
          color = $("select[name='background_color[]']")
            .eq(index++)
            .val();
          if (color) {
            colors = color.split(",");
            if (colors[0] == "solid") {
              color = colors[1];
            } else if (colors[0] == "gradient") {
              isGradient = true;
              gradient = new fabric.Gradient({
                coords: {
                  x1: 0,
                  y1: 0,
                  x2: 0,
                  y2: 1,
                },
                gradientUnits: "percentage",
                colorStops: [
                  {
                    offset: "0",
                    color: colors[1],
                  },
                  {
                    offset: "1",
                    color: colors[2],
                  },
                ],
              });
            }
          }
        } else {
          color = $("input[name='background_color']").val();
        }
        var rect = new fabric.Rect({
          id: "pixel_background",
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]) + 1,
          fill: isGradient ? gradient : color,
          selectable: false,
          evented: false,
        });
        canvas.add(rect);
        canvas.sendToBack(rect);
      }
    });
    canvas.renderAll();
  }

  function loadFabricImage(file, sum_width_dimension, product_width) {
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL("/share?file=" + file.path, function (oImg) {
        var width;
        if (sum_width_dimension) {
          width = (product_width * file.width) / sum_width_dimension;
        } else {
          width = product_width;
        }
        var r = width / oImg.width;
        var height = oImg.height * r;
        max_height = max_height < height ? height : max_height;
        resolve({ image: oImg, width, height });
      });
    });
  }

  function save_settings(data) {
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";
    const current_template_id = $("#template_id").val();
    var settings = JSON.parse(localStorage.getItem(key));
    if (settings) {
      settings["template_" + current_template_id + "_edited"] =
        JSON.stringify(data);
      localStorage.setItem(key, JSON.stringify(settings));
    }
  }

  function read_settings() {
    // read current template stored settings
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";
    const current_template_id = $("#template_id").val();
    var settings = JSON.parse(localStorage.getItem(key));
    var data = {};
    if (settings) {
      var current_template_settings =
        settings["template_" + current_template_id + "_edited"];
      if (current_template_settings)
        data = JSON.parse(current_template_settings);
    }
    return [settings, data];
  }

  async function drawUploadedBackgroundImage() {
    const canvasObjects = canvas.getObjects();
    for (const canvasObject of canvasObjects) {
      if (canvasObject.id.includes("upload_bk_img_")) {
        canvas.remove(canvasObject);
      }
    }
    var index = 0;

    var stored_settings = read_settings();
    var settings = stored_settings[0];
    var data = stored_settings[1];

    for (const field of template_data.fields) {
      if (field.type == "Background Image Upload") {
        var options = JSON.parse(field.options);
        uploaded_image[field.element_id] = options;
        var id = field.element_id;

        var url;
        var img = document.getElementsByName(id)[0];

        // upload image if its not the same one
        if (
          img.files.length &&
          "/share?file=" + data[id + "_saved"] != img.files[0].name
        ) {
          url = URL.createObjectURL(img.files[0]);
          var formData = new FormData();
          formData.append("file", img.files[0]);
          formData.append("url", url);

          const { data: u } = await axios({
            method: "post",
            url: "/banner/store_remote_image",
            data: formData,
            headers: {
              "Content-Type": "multipart/form-data",
            },
          });
          url = "/" + u;

          // mark field "_saved" as edited
          $(`input[name='${id}_saved']`).val(base_url + "/share?file=" + u);

          // read again because it might have changed
          stored_settings = read_settings();
          settings = stored_settings[0];
          data = stored_settings[1];
          data[id + "_saved"] = base_url + "/share?file=" + u;
          save_settings(data);
        } else {
          let saved_url = $(`#${id}_saved`).val();
          if (!saved_url.includes("/share?file=")) {
            url = base_url + "/share?file=" + saved_url;
            $(`#${id}_saved`).val(url);
          } else {
            url = saved_url;
          }
        }
        var url_arr = url.split("/share?file=");
        if (url == "" || url_arr[1] == "") {
          $(`#${id}_saved`).val("");
        } else {
          await new Promise((resolve, reject) => {
            fabric.Image.fromURL(url, function (oImg) {
              // calculate and set a default size and offset if is not defined
              var aspect_ratio = parseInt(oImg.height) / parseInt(oImg.width);
              var width = isNaN(parseInt(options.Width))
                ? ""
                : parseInt(options.Width);
              var height = isNaN(parseInt(options.Height))
                ? ""
                : parseInt(options.Height);
              var x = isNaN(parseInt(options.X)) ? 0 : parseInt(options.X);
              var y = isNaN(parseInt(options.Y)) ? 0 : parseInt(options.Y);

              if (width && height) {
                // we are good
              } else {
                if (width && !height) {
                  height = width * aspect_ratio;
                } else {
                  if (!width && height) {
                    width = Math.floor(height / aspect_ratio);
                  } else {
                    // nothing was defiened...
                    width = 500;
                    height = width * aspect_ratio;
                  }
                }
              }

              var r, r1;
              r = oImg.width / width;
              r1 = oImg.height / r / height;
              oImg.set({
                id: "upload_bk_img_" + index,
                element_id: id,
                order: 1001,
                left: spacingFieldPosition[field.name]
                  ? spacingFieldPosition[field.name].x
                  : x,
                top: y,
              });
              oImg.scaleToWidth(oImg.width / r / r1);
              oImg.scaleToHeight(oImg.height / r / r1);
              canvas.add(oImg);
              index++;
              setOrder();
              resolve();
            });
          });
          $("#" + id + "_loading").addClass("d-none");
        }
      }
    }
  }

  function drawProductImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("image")) {
        canvas.remove(o);
      }
    });
    originCoords = [];
    return new Promise((resolve, reject) => {
      var file_ids = $("input[name=file_ids]").val();
      if (!file_ids) {
        file_ids = "";
      }
      file_ids = file_ids.replace(/  +/g, " ");
      axios({
        method: "post",
        url: "/banner/view",
        data: {
          file_ids: file_ids,
          show_warning: true,
        },
      }).then(async function (response) {
        var product_width = product["width"];
        var product_height = product["height"];
        var left = 0;
        var top = product["top"];
        var margin_array = [];
        if ($("input[name*='product_space']").val() != undefined) {
          margin_array = $.map(
            $("input[name*='product_space']").val().split(","),
            function (value) {
              return parseInt(value, 10);
            }
          );
        }
        var files = response.data.files;
        if (!files) return;
        if (files.length > product_image_settings.length) {
          files = files.slice(0, product_image_settings.length);
        }
        var sum_width_dimension = 0;
        var gname;
        files.forEach((file, index) => {
          if (product_image_settings[index]["Option1"] != "Hero") {
            sum_width_dimension += file.related_files[0].width;
          }
          if (product_image_settings[index]["Group Name"]) {
            gname = product_image_settings[index]["Group Name"];
          }
        });
        if (
          smartObjCoords[gname] &&
          smartObjCoords[gname]["Width"] &&
          smartObjCoords[gname]["Height"]
        ) {
          product_width = parseInt(smartObjCoords[gname]["Width"]);
          product_height = parseInt(smartObjCoords[gname]["Height"]);
        }
        product_width -= margin_array.reduce(function (pv, cv) {
          return pv + cv;
        }, 0);
        max_height = 0;
        var res = await Promise.all(
          files.map((file) =>
            loadFabricImage(
              file.related_files[0],
              sum_width_dimension,
              product_width
            )
          )
        );
        var r = max_height > product_height ? product_height / max_height : 1;
        var total_width = 0;
        res.forEach((item) => {
          item.width *= r;
          item.height *= r;
          total_width += item.width;
        });

        if (product["alignment"] == "center") {
          left += (product_width - total_width) / 2;
        } else if (product["alignment"] == "right") {
          left += product_width - total_width;
        }
        var idx = 0;
        res.forEach((item, index) => {
          if (shadows.length) {
            var sh = shadows[0].list;
            var shadow = new fabric.Shadow({
              color: "#000000" + parseInt(2.5 * sh[0].value).toString(16),
              blur: Math.ceil(sh[4].value * 4),
              offsetX:
                -sh[2].value * 5 * Math.cos((sh[1].value * Math.PI) / 180),
              offsetY:
                sh[2].value * 5 * Math.sin((sh[1].value * Math.PI) / 180),
            });
          }

          var angle = parseFloat($("input[name='angle[]']").eq(index).val());
          var x_offset = parseFloat(
            $("input[name='x_offset[]']").eq(index).val()
          );
          var y_offset = parseFloat(
            $("input[name='y_offset[]']").eq(index).val()
          );
          var scale = parseFloat($("input[name='scale[]']").eq(index).val());
          var moveable = $("input[name='moveable[]']").eq(index).val();
          var w, h;
          if (product_image_settings[index]["Option1"] == "Hero") {
            hero_image_index = index;

            var hero_image_position = localStorage.getItem(
              "hero_image_position"
            );
            if (hero_image_position) {
              var hero_image_position_obj = JSON.parse(hero_image_position);
              x_offset = parseFloat(hero_image_position_obj.x_offset);
              y_offset = parseFloat(hero_image_position_obj.y_offset);
              angle = parseFloat(hero_image_position_obj.angle);
              scale = parseFloat(hero_image_position_obj.scale);
              $("input[name='x_offset[]']").eq(index).val(x_offset);
              $("input[name='y_offset[]']").eq(index).val(y_offset);
              $("input[name='scale[]']").eq(index).val(scale);
              $("input[name='angle[]']").eq(index).val(angle);
            }

            w = parseInt(product_image_settings[index]["Width"]);
            var ratio1 =
              parseInt(product_image_settings[index]["Width"]) / item.width;
            h = item.height * ratio1;
            var ratio2 = h / parseInt(product_image_settings[index]["Height"]);
            if (ratio2 > 1) {
              h = parseInt(product_image_settings[index]["Height"]);
              w = w / ratio2;
            }
            var option2 = product_image_settings[index]["Option2"];
            var hero_left =
              parseInt(product_image_settings[index]["X"]) + w / 2;
            var hero_top = parseInt(product_image_settings[index]["Y"]) + h / 2;
            if (option2.startsWith("W-")) {
              hero_left =
                dimension["width"] - parseInt(option2.split("-")[1]) - w / 2;
            }
            item.image.set({ left: hero_left + x_offset });
            item.image.set({ top: hero_top + y_offset });
            item.image.scaleToWidth(w);
            // left += margin;
          } else {
            var image_position = localStorage.getItem(
              "image_position_" + index
            );
            if (image_position) {
              var image_position_obj = JSON.parse(image_position);
              x_offset = parseFloat(image_position_obj.x_offset);
              y_offset = parseFloat(image_position_obj.y_offset);
              angle = parseFloat(image_position_obj.angle);
              scale = parseFloat(image_position_obj.scale);
              $("input[name='x_offset[]']").eq(index).val(x_offset);
              $("input[name='y_offset[]']").eq(index).val(y_offset);
              $("input[name='scale[]']").eq(index).val(scale);
              $("input[name='angle[]']").eq(index).val(angle);
            }

            var wr = item.width * scale;
            var hr = item.height * scale;
            w = item.width;
            h = item.height;
            var x = left + product["left"] + w / 2 + x_offset;
            var y = top + (product_height - hr) / 2 + y_offset + hr / 2;
            item.image.set({ left: x });
            item.image.set({ top: y });
            item.image.scaleToWidth(item.width);
            left = left + x_offset + wr / 2 + w / 2;
            left += margin_array[idx]
              ? margin_array[idx]
              : margin_array[0]
              ? margin_array[0]
              : 0;
            idx++;
          }

          item.image.set({
            originX: "middle",
            originY: "middle",
            lockUniScaling: true,
            selectable: moveable == "Yes",
            evented: moveable == "Yes",
          });
          item.image.set({ angle: angle });
          item.image.set({ id: "image" + index });
          item.image.set({
            order: parseInt(product_image_settings[index]["Order"]),
          });
          if (shadows.length) {
            item.image.set({ shadow: shadow });
          }
          item.image.set({ scaleX: item.image.scaleX * scale });
          item.image.set({ scaleY: item.image.scaleY * scale });

          var group_x = 0,
            group_y = 0;
          var groupName = product_image_settings[index]["Group Name"];
          if (product_image_settings[index]["Group Name"]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          item.image.set({
            groupName: groupName,
            left: item.image.left + group_x,
            top: item.image.top + group_y,
          });
          canvas.add(item.image);

          var bundle_width = $("input[name='p_width[]']").eq(index).val();
          if (!!bundle_width) {
            var bound = item.image.getBoundingRect();
            $("input[name='p_width[]']").eq(index).val(bound.width);
            $("input[name='p_height[]']").eq(index).val(bound.height);
          }

          originCoords.push({
            x: item.image.left - x_offset,
            y: item.image.top - y_offset,
            scaleX: item.image.scaleX / scale,
          });
        });
        setOrder();

        resolve();
      });
    });
  }

  function drawStaticImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("st_img_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Static Image") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var id = field.element_id;

        if (!options["Filename"]) {
          return;
        }
        var url = base_url + "/share?file=" + options["Filename"];
        var offset_x = $(`#${id}_offset_x`).val();
        var offset_y = $(`#${id}_offset_y`).val();
        var angle = $(`#${id}_angle`).val();
        var scale = parseFloat($(`#${id}_scale`).val());

        var extension = options["Filename"].split(".").slice(-1)[0];
        if (extension.toLowerCase() == "svg") {
          var filename = options["Filename"].split("/").slice(-1)[0];
          url = base_url + "/img/upload/" + filename;
          fabric.loadSVGFromURL(url, function (objects, opt) {
            var oImg = fabric.util.groupSVGElements(objects, opt);
            var r, r1;
            r = oImg.width / parseInt(options["Width"]);
            r1 = oImg.height / r / parseInt(options["Height"]);
            oImg.set({
              id: "st_img_" + index,
              groupName: groupName,
              element_id: field.element_id,
              order: parseInt(options["Order"]),
              left: spacingFieldPosition[field.name]
                ? spacingFieldPosition[field.name].x
                : parseInt(options["X"]) + parseFloat(offset_x),
              top: parseInt(options["Y"]) + parseFloat(offset_y),
              angle: parseInt(angle),
              selectable: options["Moveable"] == "Yes",
              evented: options["Moveable"] == "Yes",
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });

            var group_x = 0,
              group_y = 0;
            if (groupName && smartObjCoords[groupName]) {
              group_x = parseInt(smartObjCoords[groupName]["X"]);
              group_y = parseInt(smartObjCoords[groupName]["Y"]);
            }
            oImg.set({ left: oImg.left + group_x });
            oImg.set({ top: oImg.top + group_y });

            canvas.add(oImg);
            stImgCoords.push({
              x: parseFloat(options.X) + group_x,
              y: parseFloat(options.Y) + group_y,
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder();
          });
        } else {
          fabric.Image.fromURL(url, function (oImg) {
            var r, r1;
            r = oImg.width / parseInt(options.Width);
            r1 = oImg.height / r / parseInt(options.Height);
            oImg.set({
              id: "st_img_" + index,
              groupName: groupName,
              element_id: id,
              left: parseInt(options.X) + parseFloat(offset_x),
              top: parseInt(options.Y) + parseFloat(offset_y),
              angle: parseInt(angle),
              order: parseInt(options.Order),
              selectable: options.Moveable == "Yes",
              evented: options.Moveable == "Yes",
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });

            var group_x = 0,
              group_y = 0;
            if (groupName && smartObjCoords[groupName]) {
              group_x = parseInt(smartObjCoords[groupName]["X"]);
              group_y = parseInt(smartObjCoords[groupName]["Y"]);
            }
            oImg.set({ left: oImg.left + group_x });
            oImg.set({ top: oImg.top + group_y });

            canvas.add(oImg);
            stImgCoords.push({
              x: parseFloat(options.X) + group_x,
              y: parseFloat(options.Y) + group_y,
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder();
          });
        }
      }
    });
  }

  function parseText(input, default_color) {
    if (!input) {
      return { text: "", styles: {} };
    }
    var styles = {};
    var charIndex = 0;
    var lineIndex = 0;
    var lineStyle = {};
    var text = "";
    var textArray = input
      .replaceAll("\\r", "\n")
      .replaceAll("</u>", "<u>")
      .split("<u>");
    textArray.forEach((t, i) => {
      var color = "";
      var subArray = t.split("<c ");
      subArray.forEach((element, j) => {
        var subText = element;
        if (j) {
          var pos = element.indexOf("'>");
          color = element.slice(1, pos);
          subText = element.slice(pos + 2, element.length);
        }
        for (let k = 0; k < subText.length; k++) {
          if (subText[k] == "\n") {
            styles = { ...styles, [lineIndex]: lineStyle };
            lineStyle = {};
            lineIndex += 1;
            charIndex = 0;
          } else {
            const charStyle = {
              fill: color || default_color,
              underline: i % 2 == 1,
            };
            lineStyle = { ...lineStyle, [charIndex]: charStyle };
            charIndex += 1;
          }
        }
        text += subText;
      });
    });
    styles = { ...styles, [lineIndex]: lineStyle };
    return { text, styles };
  }

  window.drawTextNewTemplate = function () {
    // this seems to erase duplicate objects
    if (canvas) {
      canvas.getObjects().forEach(function (o) {
        if (o.id.includes("text")) {
          canvas.remove(o);
        }
      });
      var index = 0;
      textCoords = [];
      const textFields = template_data.fields.filter(
        (field) =>
          field.type == "Text" ||
          field.type == "Text Options" ||
          field.type == "Text from Spreadsheet"
      );

      textFields.forEach((field) => {
        const positioningOption = getPositioningOption(field);
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var text_val = $(
          `${field.type == "Text Options" ? "select" : "input"}[name="${
            field.element_id
          }"]`
        ).val();
        var color = $(`#${field.element_id}_color`).val();
        var font = $(`#${field.element_id}_font`).val();
        var font_size = $(`#${field.element_id}_fontsize`).val();
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        var alignment = $(`[name=${field.element_id}_alignment]`).val();
        var width = parseFloat($(`#${field.element_id}_width`).val());
        width = width ? width : parseFloat(options["Width"]);
        var angle = $(`#${field.element_id}_angle`).val();
        var x = parseFloat(options["X"]);
        var y = parseFloat(options["Y"]);

        $(".group-color.color-hex").each((i, obj) => {
          let group = $(obj).data("group");
          let items = group.split(",");
          if (items.includes(options["Name"])) {
            color = $(obj).val();
          }
        });

        $(".group-font").each((i, obj) => {
          let group = $(obj).data("group");
          let items = group.split(",");
          if (items.includes(options["Name"])) {
            font = $(obj).val();
          }
        });

        if (positioningOption) {
          x = positioningOption.x == null ? x : positioningOption.x;
          y = positioningOption.y == null ? y : positioningOption.y;
          width =
            positioningOption.width == null ? width : positioningOption.width;
        }
        if (text_val != "") {
          var { text, styles } = parseText(
            text_val,
            color
              ? color
              : options["Font Color"] == ""
              ? "#000000"
              : options["Font Color"]
          );
          var textBox = new fabric.Textbox(text, {
            id: "text" + index,
            groupName: groupName,
            element_id: field.element_id,
            order: parseInt(options["Order"]),
            top: y + parseFloat(offset_y),
            left: spacingFieldPosition[field.name]
              ? spacingFieldPosition[field.name].x
              : x + parseFloat(offset_x),
            width: width,
            fixedWidth: options["Size To Fit"] ? width : 0,
            textAlign: alignment
              ? alignment
              : options["Alignment"]
              ? options["Alignment"]
              : "left",
            fontSize: font_size ? font_size : parseInt(options["Font Size"]),
            lineHeight: options["Leading"] ? parseFloat(options["Leading"]) : 1,
            fontFamily: font
              ? font
              : options["Font"]
              ? options["Font"]
              : "Proxima-Nova-Semibold",
            selectable: options["Moveable"] == "Yes",
            evented: options["Moveable"] == "Yes",
            charSpacing: options["Text Tracking"]
              ? parseInt(options["Text Tracking"])
              : 0,
            angle: angle ? parseFloat(angle) : 0,
            styles: styles,
            originX: "left",
            originY: "top",
          });

          var group_x = 0,
            group_y = 0;
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          textBox.set({ left: textBox.left + group_x });
          textBox.set({ top: textBox.top + group_y });
          textBox.setControlsVisibility({
            tr: true,
            br: true,
            bl: true,
            ml: true,
            mt: false,
            mr: true,
            mb: false,
            mtr: true,
          });

          canvas.add(textBox);
          // if (options["Size To Fit"]) {
          //   let element_id = textBox.element_id;
          //   while (textBox.fontSize < 999) {
          //     textBox.fontSize++;
          //     console.log(textBox.width);
          //     canvas.renderAll();
          //   }
          //   console.log(textBox.fontSize);
          //   textBox.fontSize *= textBox.fixedWidth / (textBox.width + 1);
          //   textBox.width = textBox.fixedWidth;
          //   $(`input[name='${element_id}_fontsize']`).val(textBox.fontSize);
          // }

          var overflow_width =
            textBox.width > width ? (textBox.width - width) / 2 : 0;
          textBox.set({ left: textBox.left - overflow_width });

          // I dont understand why "group_x" was beeing added to the coords, it was
          // storing the wrong coordinates by dooing that

          /*
        textCoords.push({
          x: x + group_x,
          y: y + group_y,
        });
        */

          // this is to fix a bug with language selection and positioning,
          // might need to look deeper into this but its working good now

          textCoords[index] = {
            x: x,
            y: y,
          };

          index++;
        }
      });
      setOrder();
    }
  };

  function drawAdditionalText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("additional_text")) {
        canvas.remove(o);
      }
    });
    additionalTexts.forEach((field, index) => {
      var offset_x = $(`#${field}_offset_x`).val() || "0";
      var offset_y = $(`#${field}_offset_y`).val() || "0";
      var angle = $(`#${field}_angle`).val() || "0";
      var color = $(`#${field}_color`).val();
      var font = $(`#${field}_font`).val();
      var width = parseInt($(`#${field}_width`).val()) || 300;
      var fontSize = $(`#${field}_fontsize`).val();
      var x = 0;
      var y = 0;
      var text_val = $(`input[name="${field}"]`).val();
      if (text_val != "") {
        var textBox = new fabric.Textbox(text_val, {
          id: "additional_text" + index,
          element_id: field,
          top: y + parseFloat(offset_y),
          left: x + parseFloat(offset_x),
          width: width,
          fontFamily: font,
          fontSize: parseInt(fontSize),
          fill: color,
          selectable: true,
          evented: true,
          angle: angle ? parseFloat(angle) : 0,
        });
        textBox.setControlsVisibility({
          tr: true,
          br: true,
          bl: true,
          ml: true,
          mt: false,
          mr: true,
          mb: false,
          mtr: true,
        });
        canvas.add(textBox);
      }
    });
  }

  function drawAdditionalRectangle() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("additional_rectangle_")) {
        canvas.remove(o);
      }
    });
    additionalRectangles.forEach((field, index) => {
      var fill_color = $(`#${field}_fill_color_hex`).val();
      var strokecolor = $(`#${field}_stroke_color`).val();
      var offset_x = $(`#${field}_offset_x`).val() || "0";
      var offset_y = $(`#${field}_offset_y`).val() || "0";
      var width = parseInt($(`#${field}_width`).val()) || 300;
      var height = parseInt($(`#${field}_height`).val()) || 300;
      var visible = $(`#${field}_toggle_shape`).prop("checked");
      if (visible != undefined && !visible) {
        return;
      }
      var x = 0;
      var y = 0;
      var rect = new fabric.CustomRect({
        id: "additional_rectangle_" + index,
        element_id: field,
        top: y + parseFloat(offset_y),
        left: x + parseFloat(offset_x),
        width: width,
        height: height,
        fill: fill_color,
        stroke: strokecolor,
        strokeWidth: 5,
        selectable: true,
        evented: true,
      });
      rect.setControlsVisibility({
        tr: true,
        br: true,
        bl: true,
        ml: true,
        mt: true,
        mr: true,
        mb: true,
        mtr: true,
      });
      canvas.add(rect);
    });
  }

  function drawAdditionalCircle() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("additional_circle_")) {
        canvas.remove(o);
      }
    });
    additionalCircles.forEach((field, index) => {
      var fill_color = $(`#${field}_fill_color_hex`).val();
      var strokecolor = $(`#${field}_stroke_color`).val();
      var offset_x = $(`#${field}_offset_x`).val() || "0";
      var offset_y = $(`#${field}_offset_y`).val() || "0";
      var radius = parseInt($(`#${field}_radius`).val()) || 150;
      var visible = $(`#${field}_toggle_shape`).prop("checked");
      if (visible != undefined && !visible) {
        return;
      }
      var x = 0;
      var y = 0;
      var circle = new fabric.Circle({
        id: "additional_circle_" + index,
        element_id: field,
        top: y + parseFloat(offset_y),
        left: x + parseFloat(offset_x),
        radius: radius,
        fill: fill_color,
        stroke: strokecolor,
        strokeWidth: 5,
        selectable: true,
        evented: true,
      });
      circle.setControlsVisibility({
        tl: true,
        tr: true,
        br: true,
        bl: true,
        ml: false,
        mt: false,
        mr: false,
        mb: false,
        mtr: false,
      });
      canvas.add(circle);
    });
  }

  function drawStaticText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("static_txt")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    stTextCoords = [];
    template_data.fields.forEach((field) => {
      if (field.type == "Static Text") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var text_val = options["Option1"] || options["Placeholder"];
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        if (text_val != "") {
          var { text, styles } = parseText(text_val, options["Font Color"]);
          var text = new fabric.Textbox(text, {
            id: "static_txt" + index,
            groupName: groupName,
            element_id: field.element_id,
            order: parseInt(options["Order"]),
            top: parseFloat(options["Y"]) + parseFloat(offset_y),
            left: spacingFieldPosition[field.name]
              ? spacingFieldPosition[field.name].x
              : parseFloat(options["X"]) + parseFloat(offset_x),
            width: parseInt(options["Width"]),
            textAlign: options["Alignment"] ? options["Alignment"] : "left",
            fontSize: options["Font Size"]
              ? parseInt(options["Font Size"])
              : 50,
            lineHeight: options["Leading"] ? parseFloat(options["Leading"]) : 1,
            fill: options["Font Color"] ? options["Font Color"] : "#000000",
            fontFamily: options["Font"]
              ? options["Font"]
              : "Proxima-Nova-Semibold",
            selectable: options["Moveable"] == "Yes",
            evented: options["Moveable"] == "Yes",
            charSpacing: options["Text Tracking"]
              ? parseInt(options["Text Tracking"])
              : 0,
            editable: false,
            styles: styles,
          });

          var group_x = 0,
            group_y = 0;
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          text.set({ left: text.left + group_x });
          text.set({ top: text.top + group_y });

          canvas.add(text);
          stTextCoords.push({
            x: parseFloat(options["X"]) + group_x,
            y: parseFloat(options["Y"]) + group_y,
          });
          index++;
        }
      }
    });
    setOrder();
  }

  function drawRectangle() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("rectangle_")) {
        canvas.remove(o);
      }
    });
    var index = 0;

    template_data.fields.forEach((field) => {
      if (field.type == "Rectangle") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var opacity = parseFloat(options["Option5"]) || 1;
        var fill_color = $("#" + field.element_id + "_fill_color_hex").val();
        var strokecolor = $("#" + field.element_id + "_stroke_color").val();
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        var scaleX = parseFloat($(`#${field.element_id}_scaleX`).val());
        var scaleY = parseFloat($(`#${field.element_id}_scaleY`).val());
        var visible = $("#" + field.element_id + "_toggle_shape").prop(
          "checked"
        );
        var cornerOptions = [];
        if (options["Option4"]) {
          cornerOptions = $.map(
            options["Option4"].toString().split(","),
            function (value) {
              return parseInt(value);
            }
          );
        }
        var radius = cornerOptions.length < 5 ? 0 : cornerOptions[4];
        var corners =
          cornerOptions.length < 5 ? [1, 1, 1, 1] : cornerOptions.slice(0, 4);
        if (cornerOptions.length == 1) {
          radius = parseInt(cornerOptions[0]);
        }
        if (visible != undefined && !visible) {
          return;
        }
        var x = parseInt(options["X"]);
        var y = parseInt(options["Y"]);
        var width = parseInt(options["Width"]);
        const positioningOption = getPositioningOption(field);
        if (positioningOption) {
          x = positioningOption.x == null ? x : positioningOption.x;
          y = positioningOption.y == null ? y : positioningOption.y;
          width =
            positioningOption.width == null ? width : positioningOption.width;
        }
        var rect = new fabric.CustomRect({
          id: "rectangle_" + index,
          groupName: groupName,
          element_id: field.element_id,
          order: parseInt(options["Order"]),
          top: y + parseFloat(offset_y),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : x + parseFloat(offset_x),
          originWidth: width,
          originHeight: parseInt(options["Height"]),
          width: width * scaleX,
          height: parseInt(options["Height"]) * scaleY,
          fill: fill_color
            ? fill_color
            : options["Option3"]
            ? options["Option3"]
            : "#ffffff",
          stroke: strokecolor
            ? strokecolor
            : options["Option1"]
            ? options["Option1"]
            : "#ffffff",
          strokeWidth: options["Option2"] ? parseInt(options["Option2"]) : 0,
          rx: radius,
          ry: radius,
          corners: corners,
          selectable: options["Moveable"] == "Yes",
          evented: options["Moveable"] == "Yes",
          opacity: opacity,
          objectCaching: false,
        });

        rect.setControlsVisibility({
          tr: true,
          br: true,
          bl: true,
          ml: true,
          mt: true,
          mr: true,
          mb: true,
          mtr: true,
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        rect.set({ left: rect.left + group_x });
        rect.set({ top: rect.top + group_y });

        canvas.add(rect);
        rectCoords.push({
          x: x + group_x,
          y: y + group_y,
          scaleX: rect.scaleX / scaleX,
          scaleY: rect.scaleY / scaleY,
        });
        index++;
      }
    });
    setOrder();
  }

  function drawCircle() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("circle_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Circle") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var opacity = parseFloat(options["Option5"]) || 1;
        var fill_color = $("#" + field.element_id + "_fill_color_hex").val();
        var stroke_color = $("#" + field.element_id + "_stroke_color").val();
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        var scale = parseFloat($(`#${field.element_id}_scaleX`).val());
        var visible = $("#" + field.element_id + "_toggle_shape").prop(
          "checked"
        );
        if (visible !== undefined && !visible) {
          return;
        }
        var circle = new fabric.Circle({
          id: "circle_" + index,
          groupName: groupName,
          element_id: field.element_id,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + parseFloat(offset_y),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + parseFloat(offset_x),
          radius: (parseInt(options["Width"]) * scale) / 2,
          fill: fill_color
            ? fill_color
            : options["Option3"]
            ? options["Option3"]
            : "#ffffff",
          stroke: stroke_color
            ? stroke_color
            : options["Option1"]
            ? options["Option1"]
            : "#ffffff",
          strokeWidth: options["Option2"] ? parseInt(options["Option2"]) : 0,
          selectable: options["Moveable"] == "Yes",
          evented: options["Moveable"] == "Yes",
          opacity: opacity,
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        circle.set({ left: circle.left + group_x });
        circle.set({ top: circle.top + group_y });

        canvas.add(circle);
        circleCoords.push({
          x: parseInt(options["X"]) + group_x,
          y: parseInt(options["Y"]) + group_y,
          scaleX: circle.scaleX / scale,
        });
        index++;
      }
    });
    setOrder();
  }

  function drawCircleType() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("circletype_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Circle Type") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var fill_color = $("#" + field.element_id + "_fill_color").val();
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        var scale = parseFloat($(`#${field.element_id}_scale`).val());
        var circle = new fabric.Circle({
          id: "circletype_" + index,
          groupName: groupName,
          element_id: field.element_id,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + parseFloat(offset_y),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + parseFloat(offset_x),
          radius: (parseInt(options["Width"]) * scale) / 2,
          fill: fill_color
            ? fill_color
            : options["Option1"]
            ? options["Option1"]
            : "#ffffff",
          selectable: options["Moveable"] == "Yes",
          evented: options["Moveable"] == "Yes",
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        circle.set({ left: circle.left + group_x });
        circle.set({ top: circle.top + group_y });

        canvas.add(circle);
        cirtypeCoords.push({
          x: parseInt(options["X"]) + group_x,
          y: parseInt(options["Y"]) + group_y,
          scaleX: circle.scaleX / scale,
        });
        index++;
      }
    });
    setOrder();
  }

  function drawOverlayArea() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("overlay_area_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Overlay Area") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var overlay = new fabric.Rect({
          id: "overlay_area_" + index,
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          fill: options["Option1"] ? options["Option1"] : "#ffffff00",
          selectable: false,
          evented: false,
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        overlay.set({ left: overlay.left + group_x });
        overlay.set({ top: overlay.top + group_y });

        canvas.add(overlay);
        index++;
      }
    });
    setOrder();
  }

  function drawImageList() {
    iconCoords = [];
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("icon")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    const imageListFields = template_data.fields.filter(
      (field) => field.type == "Image List"
    );
    imageListFields.forEach((field) => {
      var options = JSON.parse(field.options);
      var image_url = $(`#${field.element_id}`).val();
      $(".image-list-group").each((i, obj) => {
        let group = $(obj).data("group");
        let items = group.split(",");
        if (items.includes(options["Name"])) {
          image_url = $(obj).val();
        }
      });

      if (image_url != "none") {
        var groupName = options["Group Name"];
        var url = base_url + "/share?file=" + image_url;
        var offset_x = $(`#${field.element_id}_offset_x`).val();
        var offset_y = $(`#${field.element_id}_offset_y`).val();
        var angle = $(`#${field.element_id}_angle`).val();
        var scale = parseFloat($(`#${field.element_id}_scale`).val());
        var extension = image_url.split(".").slice(-1)[0];
        if (extension.toLowerCase() == "svg") {
          var filename = image_url.split("/").slice(-1)[0];
          url = base_url + "/img/list/" + filename;
          fabric.loadSVGFromURL(url, function (objects, opt) {
            var oImg = fabric.util.groupSVGElements(objects, opt);
            var r, r1;
            r = oImg.width / parseInt(options["Width"]);
            if (oImg.height / r > parseInt(options["Height"])) {
              r1 = oImg.height / r / parseInt(options["Height"]);
            } else {
              r1 = 1;
            }
            oImg.set({
              id: "icon_" + index,
              groupName: groupName,
              element_id: field.element_id,
              order: parseInt(options["Order"]),
              left: spacingFieldPosition[field.name]
                ? spacingFieldPosition[field.name].x
                : parseInt(options["X"]) + parseFloat(offset_x),
              top: parseInt(options["Y"]) + parseFloat(offset_y),
              angle: parseInt(angle),
              selectable: options["Moveable"] == "Yes",
              evented: options["Moveable"] == "Yes",
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });

            var group_x = 0,
              group_y = 0;
            if (groupName && smartObjCoords[groupName]) {
              group_x = parseInt(smartObjCoords[groupName]["X"]);
              group_y = parseInt(smartObjCoords[groupName]["Y"]);
            }
            oImg.set({ left: oImg.left + group_x });
            oImg.set({ top: oImg.top + group_y });

            canvas.add(oImg);
            iconCoords.push({
              x: parseInt(options["X"]) + group_x,
              y: parseInt(options["Y"]) + group_y,
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder();
          });
        } else {
          fabric.Image.fromURL(url, function (oImg) {
            var r, r1;
            r = oImg.width / parseInt(options["Width"]);
            if (oImg.height / r > parseInt(options["Height"])) {
              r1 = oImg.height / r / parseInt(options["Height"]);
            } else {
              r1 = 1;
            }
            oImg.set({
              id: "icon_" + index,
              element_id: field.element_id,
              order: parseInt(options["Order"]),
              left: parseInt(options["X"]) + parseFloat(offset_x),
              top: parseInt(options["Y"]) + parseFloat(offset_y),
              angle: parseInt(angle),
              selectable: options["Moveable"] == "Yes",
              evented: options["Moveable"] == "Yes",
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });
            canvas.add(oImg);
            iconCoords.push({
              x: parseInt(options["X"]),
              y: parseInt(options["Y"]),
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder();
          });
        }
      }
    });
  }

  async function drawUploadedImage() {
    let canvasObjects = canvas.getObjects();
    for (const canvasObj of canvasObjects) {
      if (canvasObj.id.includes("upload_img_")) {
        canvas.remove(canvasObj);
      }
    }
    var index = 0;

    var stored_settings = read_settings();
    var settings = stored_settings[0];
    var data = stored_settings[1];

    for (const field of template_data.fields) {
      if (field.type == "Upload Image") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        uploaded_image[field.element_id] = options;
        var id = field.element_id;

        var img = document.getElementsByName(id)[0];
        var formData = new FormData();
        var url;
        // upload image if its not the same one
        if (
          img.files.length &&
          "/share?file=" + data[id + "_saved"] != img.files[0].name
        ) {
          formData.append("file", img.files[0]);
          formData.append("url", URL.createObjectURL(img.files[0]));

          const { data: u } = await axios({
            method: "post",
            url: "/banner/store_remote_image",
            data: formData,
            headers: {
              "Content-Type": "multipart/form-data",
            },
          });
          url = "/" + u;

          // mark field "_saved" as edited
          $(`input[name='${id}_saved']`).val(base_url + "/share?file=" + u);
          if (settings) {
            // read again because it might have changed
            stored_settings = read_settings();
            settings = stored_settings[0];
            data = stored_settings[1];
            data[id + "_saved"] = base_url + "/share?file=" + u;
            save_settings(data);
          }
        } else {
          let saved_url = $(`#${id}_saved`).val();
          if (!saved_url.includes("/share?file=")) {
            url = base_url + "/share?file=" + saved_url;
            $(`#${id}_saved`).val(url);
          } else {
            url = saved_url;
          }
        }

        var offset_x = $(`#${id}_offset_x`).val();
        var offset_y = $(`#${id}_offset_y`).val();
        var angle = $(`#${id}_angle`).val();
        var scale = parseFloat($(`#${id}_scale`).val());
        var url_arr = url.split("/share?file=");
        if (url == "" || url_arr[1] == "") {
          $(`#${id}_saved`).val("");

          // removed this return because if there was more than 1 file input
          // it would exit the function at the first empty field and not
          // process the rest
          // return;
        } else {
          await new Promise((resolve, reject) => {
            fabric.Image.fromURL(url, function (oImg) {
              // calculate and set a default size and offset if is not defined
              var aspect_ratio = parseInt(oImg.height) / parseInt(oImg.width);
              var width = isNaN(parseInt(options.Width))
                ? ""
                : parseInt(options.Width);
              var height = isNaN(parseInt(options.Height))
                ? ""
                : parseInt(options.Height);
              var x = isNaN(parseInt(options.X)) ? 0 : parseInt(options.X);
              var y = isNaN(parseInt(options.Y)) ? 0 : parseInt(options.Y);
              if (width && height) {
                // we are good
              } else {
                if (width && !height) {
                  height = width * aspect_ratio;
                } else {
                  if (!width && height) {
                    width = Math.floor(height / aspect_ratio);
                  } else {
                    // nothing was defiened...
                    width = 500;
                    height = width * aspect_ratio;
                  }
                }
              }

              var w = parseInt(width);
              var h = parseInt(height);
              var r;
              h = (w * oImg.height) / oImg.width;
              r = h / parseInt(height);
              r = r > 1 ? r : 1;
              w /= r;
              h /= r;

              if (options.Option1 && options.Option1.includes("auto_height")) {
                w = dimension.width;
                h = (dimension.width / oImg.width) * oImg.height;
              }
              if (options.Option1 && options.Option1.includes("fix_y")) {
                oImg.set({
                  lockMovementX: false,
                  lockMovementY: true,
                });
              }
              if (options.Option1 && options.Option1.includes("fix_x")) {
                oImg.set({
                  lockMovementX: true,
                  lockMovementY: false,
                });
              }

              oImg.set({
                id: "upload_img_" + index,
                groupName: groupName,
                element_id: id,
                order: parseInt(options["Order"]),
                left: spacingFieldPosition[field.name]
                  ? spacingFieldPosition[field.name].x
                  : x + w / 2 + parseFloat(offset_x),
                top: y + h / 2 + parseFloat(offset_y),
                // angle: parseFloat(angle),
                originX: "middle",
                originY: "middle",
                selectable: options.Moveable == "Yes",
                evented: options.Moveable == "Yes",
              });

              oImg.scaleToWidth(w * scale);
              oImg.set({ angle: parseFloat(angle) });
              // oImg.set({ scaleX: oImg.scaleX * scale });
              // oImg.set({ scaleY: oImg.scaleY * scale });

              var group_x = 0,
                group_y = 0;
              if (groupName && smartObjCoords[groupName]) {
                group_x = parseInt(smartObjCoords[groupName]["X"]);
                group_y = parseInt(smartObjCoords[groupName]["Y"]);
              }
              oImg.set({ left: oImg.left + group_x });
              oImg.set({ top: oImg.top + group_y });
              canvas.add(oImg);

              var bundle_width = $(`input[name='${id}_width']`).val();
              if (!!bundle_width) {
                var bound = oImg.getBoundingRect();
                $(`input[name='${id}_width']`).val(bound.width);
                $(`input[name='${id}_height']`).val(bound.height);
              }

              imgCoords.push({
                x: oImg.left - parseFloat(offset_x),
                y: oImg.top - parseFloat(offset_y),
                scaleX: oImg.scaleX / scale,
              });
              index++;
              setOrder();

              resolve();
            });
          });
          $("#" + id + "_loading").addClass("d-none");
        }
      }
    }

    var element_id_list = [];
    canvasObjects = canvas.getObjects();
    for (const canvasObj of canvasObjects) {
      if (canvasObj.id.includes("upload_img_")) {
        console.log(element_id_list);
        if (element_id_list.includes(canvasObj.element_id)) {
          canvas.remove(canvasObj);
        } else {
          element_id_list.push(canvasObj.element_id);
        }
      }
    }
  }

  function drawMarker() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "circle" || o.id == "square" || o.id == "list") {
        canvas.remove(o);
      }
    });
    var list_type = $("select[name='list_type']").val();
    template_data.fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      var fill_color = $(`#list_fill_color`).val();
      fill_color = fill_color
        ? fill_color
        : options["Option4"]
        ? options["Option4"]
        : "#00000000";
      var stroke_color = $(`#list_stroke_color`).val();
      stroke_color = stroke_color
        ? stroke_color
        : options["Option2"]
        ? options["Option2"]
        : "#ffffff";
      var text_color = $(`#list_text_color`).val();
      text_color = text_color ? text_color : "#ffffff";
      var strokeWidth = options["Option3"] ? parseInt(options["Option3"]) : 10;
      if (
        field.type == "List Numbered Circle" ||
        field.type == "List Checkmark" ||
        field.type == "List Star" ||
        ((list_type == "circle" ||
          list_type == "checkmark" ||
          list_type == "star") &&
          field.type == "List All")
      ) {
        var circle = new fabric.Circle({
          id: "circle",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          radius: parseInt(options["Width"]) / 2,
          stroke: stroke_color,
          strokeWidth: strokeWidth,
          fill: fill_color,
          selectable: false,
          evented: false,
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        circle.set({ left: circle.left + group_x });
        circle.set({ top: circle.top + group_y });

        canvas.add(circle);
      } else if (
        field.type == "List Numbered Square" ||
        (list_type == "square" && field.type == "List All")
      ) {
        var rect = new fabric.Rect({
          id: "square",
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          stroke: stroke_color,
          strokeWidth: strokeWidth,
          fill: fill_color,
          selectable: false,
          evented: false,
        });

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        rect.set({ left: rect.left + group_x });
        rect.set({ top: rect.top + group_y });

        canvas.add(rect);
      }
      if (
        field.type == "List Numbered Square" ||
        field.type == "List Numbered Circle" ||
        ((list_type == "circle" || list_type == "square") &&
          field.type == "List All")
      ) {
        var text = new fabric.Textbox(options["Option1"], {
          id: "list",
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + strokeWidth / 2,
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + strokeWidth / 2,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: text_color,
          fontFamily: "Proxima-Nova-Semibold",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x });
        text.set({ top: text.top + group_y });

        canvas.add(text);
      } else if (
        field.type == "List Checkmark" ||
        (list_type == "checkmark" && field.type == "List All")
      ) {
        var text = new fabric.Textbox("✓", {
          id: "list",
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + strokeWidth,
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + strokeWidth / 2,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: text_color,
          fontFamily: "ARIALUNI",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x });
        text.set({ top: text.top + group_y });

        canvas.add(text);
      } else if (
        field.type == "List Star" ||
        (list_type == "star" && field.type == "List All")
      ) {
        var text = new fabric.Textbox("★", {
          id: "list",
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + strokeWidth / 2,
          left: spacingFieldPosition[field.name]
            ? spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + strokeWidth / 2,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: text_color,
          fontFamily: "ARIALUNI",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x });
        text.set({ top: text.top + group_y });

        canvas.add(text);
      }
    });
    setOrder();
  }

  function drawLine() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("shape_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Line") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        var offset_x = parseInt($(`#${field.element_id}_offset_x`).val());
        var offset_y = parseInt($(`#${field.element_id}_offset_y`).val());
        var scale = parseFloat($(`#${field.element_id}_scale`).val());
        var width = parseInt(options["Width"]);
        var height = parseInt(options["Height"]);
        var coords = [
          parseInt(options["X"]) + offset_x,
          parseInt(options["Y"]) + offset_y,
          parseInt(options["X"]) + offset_x + (width > height ? width : 0),
          parseInt(options["Y"]) + offset_y + (width < height ? height : 0),
        ];
        var line = new fabric.Line(coords, {
          id: "shape_" + index,
          groupName: groupName,
          element_id: field.element_id,
          order: parseInt(options["Order"]),
          fill: options["Option1"],
          stroke: options["Option1"],
          strokeWidth: parseInt(options["Option2"]),
          selectable: options["Moveable"] == "Yes",
          evented: options["Moveable"] == "Yes",
        });

        var group_x = 0,
          group_y = 0;
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        line.set({ left: line.left + group_x });
        line.set({ top: line.top + group_y });

        canvas.add(line);
        lineCoords.push({
          x: parseFloat(options["X"]) + group_x,
          y: parseFloat(options["Y"]) + group_y,
          scaleX: line.scaleX / scale,
        });
        index++;
      }
    });
    setOrder();
  }

  function setOrder() {
    var objects = canvas.getObjects();
    objects.sort((a, b) => {
      return b.order - a.order;
    });
    objects.forEach((element) => {
      canvas.bringToFront(element);
    });
  }

  function drawGrid(grid_count) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("grid")) {
        canvas.remove(o);
      }
    });
    if (grid_count == 0) {
      return;
    }

    var canvasWidth = canvas.getWidth();
    var canvasHeight = canvas.getHeight();
    var interval_width = canvasWidth / grid_count;
    var interval_height = canvasHeight / grid_count;
    for (var i = 0; i < grid_count; i++) {
      canvas.add(
        new fabric.Line(
          [i * interval_width, 0, i * interval_width, canvasHeight],
          {
            id: "grid",
            stroke: "#0000003f",
            strokeWidth: 5,
            selectable: false,
          }
        )
      );
      canvas.add(
        new fabric.Line(
          [0, i * interval_height, canvasWidth, i * interval_height],
          {
            id: "grid",
            stroke: "#0000003f",
            strokeWidth: 5,
            selectable: false,
          }
        )
      );
    }
  }

  function drawSmartObject() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("mask")) {
        canvas.remove(o);
      }
    });
    template_data.fields.forEach((field) => {
      if (field.type == "Smart Object") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        if (options["Option5"]) {
          var width = parseInt(options["Width"]);
          var height = parseInt(options["Height"]);
          var left = parseInt(options["X"]);
          var top = parseInt(options["Y"]);
          var option5 = JSON.parse(options["Option5"]);
          var radius = parseInt(option5.mask.radius);
          var order = parseInt(options["Order"]);

          var rect = new fabric.Rect({
            id: "mask",
            order: order,
            left: left,
            top: top,
            width: width,
            height: height,
            fill: "red",
            rx: radius,
            ry: radius,
            absolutePositioned: true,
          });

          var shadow = option5.shadow;
          if (shadow) {
            shadow = new fabric.Shadow({
              color: `rgba(0, 0, 0, ${parseFloat(shadow.opacity) / 100})`,
              blur: (parseFloat(shadow.size) || 1) * 2,
              offsetX:
                -1 *
                parseFloat(shadow.distance) *
                Math.sin((Math.PI * 2 * parseFloat(shadow.angle)) / 360),
              offsetY:
                parseFloat(shadow.distance) *
                Math.cos((Math.PI * 2 * parseFloat(shadow.angle)) / 360),
            });
          }
          var frame = new fabric.Rect({
            id: "mask",
            order: order + 1,
            left: left,
            top: top,
            width: width,
            height: height,
            fill: "#ffffffff",
            rx: radius,
            ry: radius,
            shadow: shadow,
            absolutePositioned: true,
            selectable: false,
            evented: false,
          });
          canvas.add(frame);

          canvas.getObjects().forEach(function (o) {
            if (o.groupName == groupName) {
              o.clipPath = rect;
            }
          });
        }
      }
    });
    setOrder();
  }

  function drawSafeZone(safeZone) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("safe_zone_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type != "Safe Zone" || field.name != safeZone) return;
      var options = JSON.parse(field.options);
      var opacity = parseFloat(options["Option4"]) || 0.2;
      var fill_color = options["Option3"] || "#000000";
      var strokeColor = options["Option1"];
      var strokeWidth =
        options["Option1"] && options["Option2"]
          ? parseInt(options["Option2"])
          : 0;
      var rect = new fabric.CustomRect({
        id: "safe_zone_" + index,
        element_id: safeZone,
        order: parseInt(options["Order"]),
        top: parseInt(options["Y"]),
        left: parseInt(options["X"]),
        width: parseInt(options["Width"]),
        height: parseInt(options["Height"]),
        fill: fill_color,
        stroke: strokeColor,
        strokeWidth: strokeWidth,
        opacity: opacity,
        selectable: false,
        evented: false,
      });
      canvas.add(rect);
      index++;
    });
    setOrder();
  }

  $(".btn-clear").on("click", function () {
    $(this).closest(".form-group").find("input").val("");
    $(this).closest(".form-group").find("datalist").empty();
    drawTextNewTemplate();
  });

  $(".text-from-spreadsheet").on("change", function () {
    var input = $(this).closest(".form-group").find("input[type='text']");
    var datalist = $(this).closest(".form-group").find("datalist");
    var header = input.data("header");
    var index = 0;
    var found = 0;
    var html = "";
    readXlsxFile($(this).prop("files")[0]).then((rows) => {
      rows.forEach((row, rowIndex) => {
        if (header && rowIndex == 0) {
          row.forEach((col, colIndex) => {
            if (col == header) {
              index = colIndex;
              found = 1;
            }
          });
          if (found == 0) {
            html += `<option value="${row[0]}">${row[0]}</option>`;
          }
        } else {
          html += `<option value="${row[index]}">${row[index]}</option>`;
        }
      });
      datalist.html(html);
    });
  });
  $("input[name*='text_from_spreadsheet_']").on("change", function () {
    drawRectangle();
    drawTextNewTemplate();
    setBackgroundImage();
    drawImageFromBackground();
  });

  $("#theme").on("change", function () {
    setTimeout(() => {
      setBackgroundColor();
    }, 1000);

    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: $('input[name="customer"]').val(),
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      shadows = response.data.shadow;
      drawProductImage();
    });
  });

  function resetPosition() {
    $("input[name='x_offset[]']").each((i, obj) => {
      var v = $(obj).attr("default-value");
      $(obj).val(v);
    });
    $("input[name='y_offset[]']").each((i, obj) => {
      var v = $(obj).attr("default-value");
      $(obj).val(v);
    });
    $("input[name='angle[]']").each((i, obj) => {
      var v = $(obj).attr("default-value");
      $(obj).val(v);
    });
    $("input[name='scale[]']").each((i, obj) => {
      var v = $(obj).attr("default-value");
      $(obj).val(v);
    });
    originCoords = [];

    $(".upload_image_offset input[name*='_offset_x']").val(0);
    $(".upload_image_offset input[name*='_offset_y']").val(0);
    $(".upload_image_offset input[name*='_angle']").val(0);
    $(".upload_image_offset input[name*='_scale']").val(1);

    Promise.all([drawProductImage(), drawUploadedImage()]).then(() =>
      drawSmartObject()
    );
  }

  $(
    'input[name="file_ids"], select[name="country_id"], select[name="language_id"]'
  ).on("change", function () {
    if ($(".save-image-position").length > 0) {
      drawProductImage().then(() => drawSmartObject());
    } else {
      // we want to restore the previously saved text position.
      resetPosition();
    }
  });

  $('input[name*="product_space"]').on("change", function () {
    drawProductImage();
  });

  $('input[type="file"]').on("change", function () {
    imgCoords = [];
    $(this).closest(".form-group").find("input[type='hidden']").val("");
    Promise.all([drawUploadedImage(), drawUploadedBackgroundImage()]).then(() =>
      drawSmartObject()
    );
  });

  $(".fileinput-remove-button").on("click", function () {
    imgCoords = [];
    var file_input = $(this)
      .closest(".input-group-btn")
      .find("input[type='file']");
    var fid = file_input.attr("name");
    $(`#${fid}_saved`).val("");
    drawUploadedImage();
    drawUploadedBackgroundImage();
  });

  $(".fileinput-cancel-button").on("click", function () {
    imgCoords = [];
    var file_input = $(this)
      .closest(".input-group-btn")
      .find("input[type='file']");
    var fid = file_input.attr("name");
    $(`#${fid}_saved`).val("");
    drawUploadedImage();
    drawUploadedBackgroundImage();
  });

  $("input[name='show_stroke']").on("change", function () {
    var stroke_color = $("#stroke_color").val();
    var stroke_width = parseInt($("#stroke_width").val());
    if ($(this).prop("checked")) {
      var rect = new fabric.Rect({
        id: "stroke",
        top: 0,
        left: 0,
        width: dimension.width - stroke_width,
        height: dimension.height - stroke_width,
        fill: "#00000000",
        stroke: stroke_color,
        strokeWidth: stroke_width,
        selectable: false,
        evented: false,
      });
      canvas.add(rect);
      canvas.bringToFront(rect);
    } else {
      canvas.getObjects().forEach(function (o) {
        if (o.id == "stroke") {
          canvas.remove(o);
        }
      });
    }
  });

  $("#selectBkImgModal #submit").on("click", function () {
    if ($("#selectBkImgModal").data("type") == "Background Theme Image") {
      setBackgroundImage().then(() => drawSmartObject());
    } else {
      drawImageFromBackground().then(() => drawSmartObject());
    }
  });

  $('input[name="background_color"], select[name="background_color[]"]').on(
    "change",
    setBackgroundColor
  );

  $('input[type="text"], input[type="color"]').on("change", function () {
    if ($("#show_text").length == 0 || $("#show_text").is(":checked")) {
      const element_id = $(this).attr("name");
      const index = spacingFields.findIndex((f) => f.element_id === element_id);
      if (index >= 0) {
        const textField = template_data.fields.find(
          (x) => x.element_id === element_id
        );
        const textFieldOptions = JSON.parse(textField.options);
        const { text, styles } = parseText($(this).val(), "#000000");
        if (text) {
          const textBox = new fabric.Text(text, {
            fontFamily: textFieldOptions["Font"],
            fontSize: textFieldOptions["Font Size"],
            styles,
          });
          spacingFields[index].width = textBox.width;
        } else {
          spacingFields[index].width = 0;
        }
        if (index < spacingFieldValues.length) {
          spacingFields[index].width += +spacingFieldValues[index];
        }
        updateSpacingFieldPosition();
      }
      drawRectangle();
      drawAdditionalRectangle();
      drawTextNewTemplate();
      drawStaticText();
      drawMarker();
      setBackgroundImage();
      drawImageFromBackground();
    }
  });

  $(document).on("focusin", 'input[type="text"]', function (e) {
    $(this).data("val", $(this).val());
  });

  $(document).on("change", 'input[type="text"]', function (e) {
    drawAdditionalText();
  });

  $('select[name*="_alignment"]').on("change", drawTextNewTemplate);

  $(".group-color").on("change", function () {
    if ($("#show_text").length == 0 || $("#show_text").is(":checked")) {
      drawTextNewTemplate();
      drawStaticText();
      drawMarker();
    }
  });

  $('select[name*="text_options_"]').on("change", drawTextNewTemplate);

  $("#show_text").on("change", function () {
    if ($(this).prop("checked")) {
      drawTextNewTemplate();
      drawStaticText();
      drawAdditionalText();
      // drawRectangleText();
    } else {
      canvas.getObjects().forEach(function (o) {
        if (o.id.includes("text")) {
          canvas.remove(o);
        }
      });
    }
  });

  $("select.image-list").on("change", function () {
    drawImageList();
  });

  $('input[id*="circle_"]').on("change", function () {
    drawCircle();
    drawCircleType();
  });
  $(document).on("change", 'input[id*="add_circle_"]', drawAdditionalCircle);

  $('input[id*="rectangle_"]').on("change", drawRectangle);
  $(document).on(
    "change",
    'input[id*="add_rectangle_"]',
    drawAdditionalRectangle
  );

  $(document).on("change", 'input[class="toggle-shape"]', function () {
    drawCircle();
    drawRectangle();
    drawAdditionalRectangle();
    drawAdditionalCircle();
  });

  $(document).on(
    "change",
    'input[name*="font"], select[name*="font"]',
    function () {
      if ($("#show_text").length == 0 || $("#show_text").is(":checked")) {
        drawTextNewTemplate();
        drawAdditionalText();
      }
    }
  );

  $(document).on("keydown", function (e) {
    if (
      e.originalEvent.code === "Delete" ||
      e.originalEvent.code === "Backspace"
    ) {
      let obj = canvas.getActiveObject();
      if (obj && obj.id.includes("additional_")) {
        let index = additionalTexts.indexOf(obj.element_id);
        if (index > -1) {
          additionalTexts.splice(index, 1);
        }
        index = additionalRectangles.indexOf(obj.element_id);
        if (index > -1) {
          additionalRectangles.splice(index, 1);
        }
        index = additionalCircles.indexOf(obj.element_id);
        if (index > -1) {
          additionalCircles.splice(index, 1);
        }
        $(`.${obj.element_id}`).remove();
        canvas.remove(obj);
      }
    }
  });

  $('select[name="list_type"]').on("change", drawMarker);

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
    var dw, dh;
    if ($(".canvas-button").hasClass("canvas")) {
      dw = canvas_dimension["width"];
      dh = canvas_dimension["height"];
    } else if ($(".canvas-button").hasClass("canvas-template-rectangle")) {
      dw = canvas_dimension["width"];
      dh = canvas_dimension["height"];
    } else {
      dw = template_data.width;
      dh = template_data.height;
    }
    if ($(this).hasClass("edit")) {
      $(this).removeClass("edit");
      $(this).addClass("save");
      $(this).html('<i class="cil-save"></i>');
      var width, height;
      if (dw > dh) {
        width = 700;
        height = (width * dh) / dw;
      } else {
        height = 600;
        width = (height * dw) / dh;
      }
      canvas.setDimensions(
        { width: width + "px", height: height + "px" },
        { cssOnly: true }
      );
    } else {
      $(this).removeClass("save");
      $(this).addClass("edit");
      $(this).html('<i class="cil-pencil"></i>');
      var width, height;
      if (dw > dh) {
        width = 300;
        height = (width * dh) / dw;
      } else {
        height = 300;
        width = (height * dw) / dh;
      }
      canvas.setDimensions(
        { width: width + "px", height: height + "px" },
        { cssOnly: true }
      );
    }
    $("#preview-popup").css({ right: 0, left: "auto" });

    canvas.renderAll();
  });

  $(".canvas-button").on("click", function () {
    var w, h;
    if ($(this).hasClass("canvas")) {
      $(this).removeClass("canvas");
      $(this).addClass("canvas-template-rectangle");
      var rect = new fabric.Rect({
        id: "canvas_template_rectangle",
        top: 0,
        left: 0,
        width: template_data.width,
        height: template_data.height,
        fill: "#00000000",
        stroke: "#ff0000",
        strokeDashArray: [20, 20],
        strokeWidth: 10,
        selectable: false,
        evented: false,
      });
      canvas.add(rect);
      canvas.bringToFront(rect);
      w = canvas_dimension["width"];
      h = canvas_dimension["height"];
    } else if ($(this).hasClass("canvas-template-rectangle")) {
      canvas.getObjects().forEach(function (o) {
        if (o.id == "canvas_template_rectangle") {
          canvas.remove(o);
        }
      });
      $(this).removeClass("canvas-template-rectangle");
      $(this).addClass("psd");
      w = template_data.width;
      h = template_data.height;
    } else {
      canvas.getObjects().forEach(function (o) {
        if (o.id == "canvas_template_rectangle") {
          canvas.remove(o);
        }
      });
      $(this).removeClass("psd");
      $(this).addClass("canvas");
      w = canvas_dimension["width"];
      h = canvas_dimension["height"];
    }
    canvas.setWidth(w);
    canvas.setHeight(h);

    var edit_btn = $(".edit-button");
    var width, height;
    if (edit_btn.hasClass("save")) {
      if (w > h) {
        width = 700;
        height = (width * h) / w;
      } else {
        height = 600;
        width = (height * w) / h;
      }
    } else {
      if (w > h) {
        width = 300;
        height = (width * h) / w;
      } else {
        height = 300;
        width = (height * w) / h;
      }
    }
    canvas.setDimensions(
      { width: width + "px", height: height + "px" },
      { cssOnly: true }
    );
    $("#preview-popup").css({ right: 0, left: "auto" });
    canvas.renderAll();
  });

  $(".reset-hero-button").on("click", function () {
    localStorage.clear();
    resetPosition();
  });

  $(".toggle-grid-button").on("click", function () {
    if (grid_density == 10) {
      grid_density = 16;
    } else if (grid_density == 16) {
      grid_density = 0;
    } else if (grid_density == 0) {
      grid_density = 10;
    }
    drawGrid(grid_density);
  });

  $(".rotate-button").on("click", function () {
    var obj = canvas.getActiveObject();
    var angle = obj.angle;
    angle = parseInt(angle / 90) * 90;
    angle = (angle + 90) % 360;

    obj.set({ angle });
    canvas.renderAll();

    updateControls();
  });

  $(".safe-zone-button").on("click", function () {
    $(".safe-zone-list").toggle();
  });

  $(document).on("click", ".safe-zone-list li", function () {
    let isChecked = $(this).hasClass("checked");
    if (isChecked) {
      drawSafeZone("");
      $(this).removeClass("checked");
    } else {
      let safe_zone = $(this).text();
      $(".safe-zone-list").hide();
      drawSafeZone(safe_zone);
      $(".safe-zone-list li").removeClass("checked");
      $(this).addClass("checked");
    }
  });

  $(".add-button").on("click", function () {
    $(".list-field-type").toggle();
  });

  $(".list-field-type li").on("click", function () {
    let field_type = $(this).text();
    $(".list-field-type").hide();
    let timestamp = Date.now();
    let fieldname = "add_" + field_type.toLowerCase() + "_" + timestamp;
    let html = "";
    if (field_type == "Text") {
      html = `
        <div class="col-md-3 form-group ${fieldname}">
          <label class="text-label">Text</label>
          <input type="text" ward name="${fieldname}" class="form-control additionalText" />
        </div>
        <div class="col-md-4 form-row ${fieldname}">
          <div class="col-md-6 col-sm-6 form-group">
            <label>Font</label>
            <select name="${fieldname}_font" id="${fieldname}_font" class="form-control font-select" style="width: 100%; height: 38px;">
      `;
      fonts.forEach((font) => {
        html += `<option value="${font}">${font}</option>`;
      });
      html += `</select>
          </div>
          <div class="col-md-6 col-sm-6 form-group">
            <label>Font Size</label>
            <input type="number" name="${fieldname}_fontsize" id="${fieldname}_fontsize" class="form-control" value="50" />
          </div>
        </div>
        <div class="col-md-2 ${fieldname}">
          <label>Text Color</label>
          <div class="form-row">
            <div class="col-md-6 col-sm-6 form-group">
              <input type="text" name="${fieldname}_color" id="${fieldname}_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000" />
            </div>
            <div class="col-md-6 col-sm-6 form-group">
              <input type="color" id="${fieldname}_color" class="form-control" />
            </div>
          </div>
        </div>
        <div class="offset d-none ${fieldname}">
          <input type="number" name="${fieldname}_offset_x" id="${fieldname}_offset_x" class="form-control" />
          <input type="number" name="${fieldname}_offset_y" id="${fieldname}_offset_y" class="form-control" />
          <input type="number" name="${fieldname}_width" id="${fieldname}_width" class="form-control" />
          <input type="number" name="${fieldname}_angle" id="${fieldname}_angle" class="form-control" />
        </div>
      `;
      additionalTexts.push(fieldname);
    } else if (field_type == "Rectangle" || field_type == "Circle") {
      html = `
        <div class="col-md-2 ${fieldname}">
          <label>${field_type} Color</label>
          <div class="form-row">
            <input type="hidden" name="${fieldname}" class="additional${field_type}" />
            <div class="col-md-6 col-sm-6 form-group">
              <input type="text" name="${fieldname}_fill_color" id="${fieldname}_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000" />
              <input type="checkbox" class="toggle-shape" name="${fieldname}_toggle_shape" id="${fieldname}_toggle_shape" checked />
            </div>
            <div class="col-md-6 col-sm-6 form-group">
              <input type="color" id="${fieldname}_fill_color" class="form-control" />
            </div>
          </div>
        </div>
        <div class="col-md-2 ${fieldname}">
          <label>${field_type} Stroke Color</label>
          <div class="form-row">
            <div class="col-md-6 col-sm-6 form-group">
              <input type="text" name="${fieldname}_stroke_color" id="${fieldname}_strke_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000" />
            </div>
            <div class="col-md-6 col-sm-6 form-group">
              <input type="color" id="${fieldname}_stroke_color" class="form-control" />
            </div>
          </div>
        </div>
        <div class="offset d-none ${fieldname}">
          <input type="number" name="${fieldname}_offset_x" id="${fieldname}_offset_x" class="form-control" value="0" />
          <input type="number" name="${fieldname}_offset_y" id="${fieldname}_offset_y" class="form-control" value="0" />
          <input type="number" name="${fieldname}_angle" id="${fieldname}_angle" class="form-control" value="0" />
          <input type="number" name="${fieldname}_width" id="${fieldname}_width" class="form-control" value="300" />
          <input type="number" name="${fieldname}_height" id="${fieldname}_height" class="form-control" value="300" />
        </div>
      `;
      if (field_type == "Rectangle") {
        additionalRectangles.push(fieldname);
      } else {
        additionalCircles.push(fieldname);
      }
    } else if (field_type == "Upload Image") {
      html = `
      <div class="col-md-4 form-group ${fieldname}">
        <label>Upload Image</label>
        <input type="file" class="form-control-file" name="${fieldname}" data-show-preview="false">
        <input type="hidden" name="${fieldname}_saved" id="${fieldname}_saved" />
      </div>
      <div class="upload_image_offset d-none ${fieldname}">
        <input type="number" name="${fieldname}_offset_x" id="${fieldname}_offset_x" class="form-control" />
        <input type="number" name="${fieldname}_offset_y" id="${fieldname}_offset_y" class="form-control" />
        <input type="number" name="${fieldname}_angle" id="${fieldname}_angle" class="form-control" />
        <input type="number" name="${fieldname}_scale" id="${fieldname}_scale" class="form-control" />
        <input type="number" name="${fieldname}_width" id="${fieldname}_width" class="form-control" />
        <input type="number" name="${fieldname}_height" id="${fieldname}_height" class="form-control" />
      </div>
      <div id="${fieldname}_loading" class="mt-4 pt-2 d-none">
        <div class="d-flex align-items-center">
          <div class="spinner-border ml-auto text-primary" role="status" aria-hidden="true"></div>
          <span>&nbsp;Saving...</span>
        </div>
      </div>
      `;
    }
    $(".template-components").append(html);
    $(".form-control-file").fileinput({
      showUpload: false,
      previewFileType: "any",
    });
    $(".font-select").select2({
      templateResult: formatOutput,
      templateSelection: formatOutput,
    });
    if (field_type == "Text") {
      drawAdditionalText();
    } else if (field_type == "Rectangle") {
      drawAdditionalRectangle();
    } else if (field_type == "Circle") {
      drawAdditionalCircle();
    }
  });

  $("#x_value, #y_value, #w_value, #h_value").on("change", function () {
    var obj = canvas.getActiveObject();
    let x = parseFloat($("#x_value").val());
    let y = parseFloat($("#y_value").val());
    let w = obj.width * obj.scaleX,
      h = obj.height * obj.scaleY;
    let w_ori = parseFloat($("#w_value").val()),
      h_ori = parseFloat($("#h_value").val());

    if (obj.originX == "middle") {
      x += w / 2;
    }
    if (obj.originY == "middle") {
      y += h / 2;
    }

    if (obj.get("type") == "textbox") {
      obj.set({
        left: x,
        top: y,
        width: w_ori,
        height: h_ori,
      });
    } else {
      obj.set({
        left: x,
        top: y,
        scaleX: w_ori / obj.width,
        scaleY: h_ori / obj.height,
      });
    }
    canvas.renderAll();
    updateControls();
  });

  function canvasModifiedCallback(e) {
    e.target.left += dimension["left"];
    e.target.top += dimension["top"];
    // drawSmartObject();
  }
});
