require("../../bootstrap");
import { fabric } from "fabric";
import { font_list } from '../../fonts.js';
var fonts = Object.keys(font_list);

fabric.perfLimitSizeTotal = 16777216;

var shadows = [];

const getFieldType = (row) => {
  return row.find('th[data-col-name="Field Type"] select').val();
};

const getFieldName = (row) => {
  return row.find('th[data-col-name="Name"] input').val();
};

const getFieldElementId = (row) => {
  const fieldName = getFieldName(row);
  return fieldName.replace(" ", "_").toLowerCase();
};

const getByColName = (row, col) => {
  const inputs = row.find(`td[data-col-name="${col}"] input`);
  if (inputs.length > 0) {
    return inputs.val();
  } else {
    return row.find(`td[data-col-name="${col}"] select`).val();
  }
};

const getByColNames = (row, cols) => {
  let options = {};
  cols.map((col) => {
    options[col] = getByColName(row, col);
  });
  return options;
};

const getOptions = (row) => {
  let options = {};
  let field_type = getFieldType(row);
  row.find("td").each(function (j, el) {
    const td = $(el);
    const col = td.attr("data-col-name");
    let value = null;
    if (col == "Filename") {
      value = td.find(".Filename_saved").text();
    } else if (
      (field_type == "Rectangle" ||
        field_type == "Circle" ||
        field_type == "Safe Zone") &&
      (col == "Option1" || col == "Option3")
    ) {
      value = td.find(".color-hex").val();
      if (value && value.length < 8) {
        value = td.find("input[type='color']").val();
      }
    } else if (col == "Font Color") {
      value = td.find("input[type='color']").val();
    } else if (col == "Alignment") {
      // value = td.find("input").is(':checked') ? td.find("select").val() : false;
      value = td.find("select").val();
    } else {
      if (td.find("input").length > 0) {
        value = td.find("input").val();
      } else {
        value = td.find("select").val();
      }
    }
    options[col] = value;
  });

  return options;
};

const getRowsByFieldType = (fieldType) => {
  const result = [];
  $("tr").each(function (i, el) {
    if (i > 2 && getFieldType($(el)) == fieldType) {
      result.push($(el));
    }
  });

  return result;
};

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

  var dimension = {};
  var product = {};
  var background_theme_image = [];
  var uploaded_image = {};
  var product_image_settings = [];
  var originCoords = [];
  var textCoords = [];
  var stTextCoords = [];
  var iconCoords = [];
  var imgCoords = [];
  var stImgCoords = [];
  var lineCoords = [];
  var circleCoords = [];
  var cirtypeCoords = [];
  var rectCoords = [];
  var max_height = 0;
  var smartObjCoords = {};
  var canvas;
  var base_url = window.location.origin;
  var grid_density = 0;

  function onLoad() {
    originCoords = [];

    localStorage.clear();
    $(".canvas-container").remove();
    if ($(".edit-button").hasClass("save")) {
      $(".edit-button").removeClass("save");
      $(".edit-button").addClass("edit");
      $(".edit-button").html('<i class="cil-pencil"></i>');
    }

    dimension["width"] = parseInt($('input[name="width"]').val());
    dimension["height"] = parseInt($('input[name="height"]').val());

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
    canvas.on({
      "object:moving": updateControls,
      "object:scaling": updateControls,
      "object:resizing": updateControls,
      "object:rotating": updateControls,
      "selection:created": selectionUpdated,
      "selection:cleared": selectionUpdated,
      "selection:updated": selectionUpdated,
    });
    canvas.on("text:changed", function (e) {
      let field_id = e.target.field_id;
      let text = e.target.text;
      $(".template-table tbody tr").each(function (i, el) {
        const row = $(el);
        const fieldId = row.data("field-id");
        if (fieldId == field_id) {
          row.find("td").eq(2).find("input").val(text);
        }
      });
    });
    $("#preview-popup").show();

    $(".template-table tbody tr").each(function (i, el) {
      if (i > 1) {
        const row = $(el);
        const fieldType = getFieldType(row);
        switch (fieldType) {
          case "Product Dimensions":
            product["left"] = parseInt(getByColName(row, "X"));
            product["top"] = parseInt(getByColName(row, "Y"));
            product["width"] = parseInt(getByColName(row, "Width"));
            product["height"] = parseInt(getByColName(row, "Height"));
            product["alignment"] = getByColName(row, "Alignment");
            break;
          case "Background Theme Image":
            var bt_image = {};
            bt_image["left"] = parseInt(getByColName(row, "X"));
            bt_image["top"] = parseInt(getByColName(row, "Y"));
            bt_image["width"] = parseInt(getByColName(row, "Width"));
            bt_image["height"] = parseInt(getByColName(row, "Height"));
            bt_image["order"] = parseInt(getByColName(row, "Order"));
            bt_image["crop"] = getByColName(row, "Option5") == "crop";
            background_theme_image.push(bt_image);
            break;
          case "Canvas":
            dimension["width"] = parseInt(getByColName(row, "Width"));
            dimension["height"] = parseInt(getByColName(row, "Height"));
            dimension["left"] = parseInt(getByColName(row, "X")) || 0;
            dimension["top"] = parseInt(getByColName(row, "Y")) || 0;
            break;
          case "Smart Object":
            var groupName = getByColName(row, "Group Name");
            smartObjCoords[groupName] = getOptions(row);
            break;
          case "Upload Image":
            const element_id = getFieldElementId(row);
            const cols = ["X", "Y", "Width", "Height"];
            const options = getByColNames(row, cols);
            options["Moveable"] = getByColName(row, "Moveable");
            uploaded_image[element_id] = options;
            // drawUploadedImage(
            //     element_id,
            //     options.X,
            //     options.Y,
            //     options.Width,
            //     options.Height,
            //     options.Moveable == "Yes"
            // );
            break;
        }
      }
    });

    // init
    drawForLoading();
    setBackgroundColor();
    // setBackgroundImage();
    drawUploadedImage();
    drawStaticImage();
    // drawUploadedBackgroundImage();
    drawRectangle();
    drawCircle();
    drawCircleType();
    drawOverlayArea();
    setTimeout(() => {
      drawText();
      drawStaticText();
      drawProductImage();
      drawMarker();
      drawLine();
      drawImageList();
      drawStroke();
      drawProductDimension();
      // drawGrid(grid_density);
    }, 3000);

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
  }

  function setDraggable() {
    var elmnt = document.getElementById("preview-popup");

    var pos1 = 0,
      pos2 = 0,
      pos3 = 0,
      pos4 = 0;
    if (document.getElementById("drag-handler")) {
      // if present, the header is where you move the DIV from:
      document.getElementById("drag-handler").onmousedown = dragMouseDown;
    } else {
      // otherwise, move the DIV from anywhere inside the DIV:
      elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
      e = e || window.event;
      e.preventDefault();
      // get the mouse cursor position at startup:
      pos3 = e.clientX;
      pos4 = e.clientY;
      document.onmouseup = closeDragElement;
      // call a function whenever the cursor moves:
      document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
      e = e || window.event;
      e.preventDefault();
      // calculate the new cursor position:
      const width = $("#preview-popup").width();
      const height = $("#preview-popup").height();
      pos1 = pos3 - e.clientX;
      pos2 = pos4 - e.clientY;
      pos3 = e.clientX;
      pos4 = e.clientY;
      // set the element's new position:
      let top = Math.min(elmnt.offsetTop - pos2, window.innerHeight - height);
      top = Math.max(56, top);
      elmnt.style.top = top + "px";
      let left = Math.min(elmnt.offsetLeft - pos1, window.innerWidth - width);
      left = Math.max(0, left);
      elmnt.style.left = left + "px";
      elmnt.style.right = "auto";
    }

    function closeDragElement() {
      // stop moving when mouse button is released:
      document.onmouseup = null;
      document.onmousemove = null;
    }
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

  function updateControls() {
    var obj = canvas.getActiveObject();
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

    if (obj.id) {
      if (obj.id.includes("product_image")) {
        var obj_id = parseInt(obj.id.replace("product_image", ""));
        var x = obj.left - originCoords[obj_id].x;
        var y = obj.top - originCoords[obj_id].y;
        const rows = getRowsByFieldType("Product Image");
        if (rows[obj_id]) {
          rows[obj_id].find('td[data-col-name="X"] input').val(parseInt(x));
          rows[obj_id].find('td[data-col-name="Y"] input').val(parseInt(y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("text")) {
        var obj_id = parseInt(obj.id.replace("text", ""));
        const rows = getRowsByFieldType("Text");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("static_txt")) {
        var obj_id = parseInt(obj.id.replace("static_txt", ""));
        const rows = getRowsByFieldType("Static Text");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("shape_")) {
        var obj_id = parseInt(obj.id.replace("shape_", ""));
        const rows = getRowsByFieldType("Line");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("circle_")) {
        var obj_id = parseInt(obj.id.replace("circle_", ""));
        const rows = getRowsByFieldType("Circle");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("circletype_")) {
        var obj_id = parseInt(obj.id.replace("circletype_", ""));
        const rows = getRowsByFieldType("Circle Type");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("rectangle_")) {
        var obj_id = parseInt(obj.id.replace("rectangle_", ""));
        const rows = getRowsByFieldType("Rectangle");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("st_img_")) {
        var obj_id = parseInt(obj.id.replace("st_img_", ""));
        const rows = getRowsByFieldType("Static Image");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("icon_")) {
        var obj_id = parseInt(obj.id.replace("icon_", ""));
        const rows = getRowsByFieldType("Image List");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("overlay_area_")) {
        var obj_id = parseInt(obj.id.replace("overlay_area_", ""));
        const rows = getRowsByFieldType("Overlay Area");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("uploaded_img_")) {
        var obj_id = parseInt(obj.id.replace("uploaded_img_", ""));
        const rows = getRowsByFieldType("Upload Image");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else if (obj.id.includes("dimension")) {
        var obj_id = 0;
        const rows = getRowsByFieldType("Product Dimensions");
        if (rows[obj_id]) {
          rows[obj_id]
            .find('td[data-col-name="X"] input')
            .val(parseInt(obj.oCoords.tl.x));
          rows[obj_id]
            .find('td[data-col-name="Y"] input')
            .val(parseInt(obj.oCoords.tl.y));
          rows[obj_id]
            .find('td[data-col-name="Width"] input')
            .val(parseInt(obj.scaleX * obj.width));
          rows[obj_id]
            .find('td[data-col-name="Height"] input')
            .val(parseInt(obj.scaleY * obj.height));
        }
      } else {
        console.log(obj);
      }
    }
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

  function getMeta(url) {
    return new Promise((resolve, reject) => {
      let img = new Image();
      img.onload = () => resolve(img);
      img.onerror = () => reject();
      img.src = url;
    });
  }

  async function setBackgroundImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "bk_theme_img") {
        canvas.remove(o);
      }
    });

    background_theme_image.forEach(async (b, i) => {
      var url = $('input[name="background[]"]').val();
      if (url == "" || url === undefined) {
        canvas.backgroundImage = null;
        canvas.renderAll();
        return;
      }

      var img = await getMeta(url);
      var dimension_width = b["width"];
      var dimension_height = b["height"];
      var canvasAspect = dimension_width / dimension_height;
      var imgAspect = img.width / img.height;
      var left, top, scaleFactor;

      if (canvasAspect < imgAspect) {
        var scaleFactor = dimension_width / img.width;
        top = b["top"] - (img.height * scaleFactor - dimension_height) / 2;
      } else {
        var scaleFactor = dimension_height / img.height;
        top = b["top"];
      }
      left = b["left"];

      canvas.setBackgroundImage(url, canvas.renderAll.bind(canvas), {
        top: top,
        left: left,
        originX: "left",
        originY: "top",
        scaleX: scaleFactor,
        scaleY: scaleFactor,
      });
    });
  }

  function setBackgroundColor() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "pixel_background") {
        canvas.remove(o);
      }
    });

    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Background Theme Color") {
        const cols = ["X", "Y", "Width", "Height"];
        const options = getByColNames(row, cols);
        const colors = ["gradient", "#2e3394", "#1f67b4"];
        let isGradient = false;
        let gradient;
        let color = null;
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

  function loadFabricImage(width) {
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL("/img/sample/product.png", function (oImg) {
        var r = width / oImg.width;
        var height = oImg.height * r;
        max_height = max_height < height ? height : max_height;
        resolve({ image: oImg, width, height });
      });
    });
  }

  function drawUploadedBackgroundImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("upload_bk_img_")) {
        canvas.remove(o);
      }
    });
    var index = 0;
    template_data.fields.forEach((field) => {
      if (field.type == "Background Image Upload") {
        var options = JSON.parse(field.options);
        uploaded_image[field.element_id] = options;
        var id = field.element_id;

        var url;
        var img = document.getElementsByName(id)[0];
        if (img.files.length) {
          url = URL.createObjectURL(img.files[0]);
        } else {
          url = $(`#${id}_saved`).val();
        }
        fabric.Image.fromURL(url, function (oImg) {
          var r, r1;
          r = oImg.width / parseInt(options.Width);
          r1 = oImg.height / r / parseInt(options.Height);
          oImg.set({
            id: "upload_bk_img_" + index,
            element_id: id,
            order: 1001,
            left: parseInt(options.X),
            top: parseInt(options.Y),
          });
          oImg.scaleToWidth(oImg.width / r / r1);
          oImg.scaleToHeight(oImg.height / r / r1);
          canvas.add(oImg);
          index++;
          setOrder();
        });
      }
    });
  }

  async function drawProductImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("product_image")) {
        canvas.remove(o);
      }
    });
    var product_width = product["width"];
    var product_height = product["height"];
    var product_left = product["left"];
    var product_top = product["top"];
    var margin = 0;

    let product_options = [];
    let product_count = 0;
    $(".template-table tbody tr").each(function (i, el) {
      if (i > 1) {
        const row = $(el);
        const fieldType = getFieldType(row);
        if (fieldType == "Product Image") {
          product_count++;
          product_options.push(getOptions(row));
        }
      }
    });
    max_height = 0;
    const files = [];
    for (let i = 0; i < product_count; i++) {
      files.push(await loadFabricImage(product_width / product_count));
    }
    var r = max_height > product_height ? product_height / max_height : 1;
    var total_width = 0;
    files.forEach((item) => {
      item.width *= r;
      item.height *= r;
      total_width += item.width;
    });
    // if (product['alignment'] == "center") {
    //     left += (product_width - total_width) / 2;
    // } else if (product['alignment'] == "right") {
    //     left += product_width - total_width;
    // }
    originCoords = [];
    for (let i = 0; i < product_count; i++) {
      if (shadows.length) {
        var sh = shadows[0].list;
        var shadow = new fabric.Shadow({
          color: "#000000" + parseInt(2.5 * sh[0].value).toString(16),
          blur: Math.ceil(sh[4].value * 4),
          offsetX: -sh[2].value * 5 * Math.cos((sh[1].value * Math.PI) / 180),
          offsetY: sh[2].value * 5 * Math.sin((sh[1].value * Math.PI) / 180),
        });
      }

      const { image, width, height } = files[i];
      const angle = parseFloat(product_options[i]["Angle"]);
      const x_offset = parseFloat(product_options[i]["X"]);
      const y_offset = parseFloat(product_options[i]["Y"]);
      const scale = 1;
      image.set({ left: product_left + (width * scale) / 2 + x_offset });
      image.set({
        top:
          product_top +
          (product_height - height) / 2 +
          y_offset +
          (height * scale) / 2,
      });
      image.scaleToWidth(width);
      image.set({
        originX: "middle",
        originY: "middle",
        lockUniScaling: true,
        selectable: true,
        evented: true,
      });
      image.set({ angle });
      image.set({ id: "product_image" + i });
      image.set({ order: parseInt(product_options[i]["Order"]) });
      if (shadows.length) {
        image.set({ shadow: shadow });
      }
      image.set({ scaleX: image.scaleX * scale });
      image.set({ scaleY: image.scaleY * scale });
      product_left += width + margin; // margin = 20;
      canvas.add(image);
      canvas.bringToFront(image);
      originCoords.push({
        x: image.left,
        y: image.top,
        scaleX: image.scaleX / scale,
      });
    }
    setOrder();
  }

  function drawStaticImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("st_img_")) {
        canvas.remove(o);
      }
    });
    stImgCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Static Image") {
        const options = getOptions(row);
        var url = base_url + "/share?file=" + options["Filename"];
        const angle = options["Angle"] ? parseFloat(options["Angle"]) : 0;
        const scale = options["Scale"] ? parseFloat(options["Scale"]) : 1;
        const x = parseFloat(options["X"]);
        const y = parseFloat(options["Y"]);

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
              element_id: getFieldElementId(row),
              order: parseInt(options["Order"]),
              left: x,
              top: y,
              angle: angle,
              selectable: true,
              evented: true,
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });

            var group_x = 0,
              group_y = 0;
            var groupName = options["Group Name"];
            if (groupName && smartObjCoords[groupName]) {
              group_x = parseInt(smartObjCoords[groupName]["X"]);
              group_y = parseInt(smartObjCoords[groupName]["Y"]);
            }
            oImg.set({ left: oImg.left + group_x });
            oImg.set({ top: oImg.top + group_y });

            canvas.add(oImg);
            stImgCoords.push({
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
            r1 = oImg.height / r / parseInt(options["Height"]);
            oImg.set({
              id: "st_img_" + index,
              element_id: getFieldElementId(row),
              left: x,
              top: y,
              angle: angle,
              order: parseInt(options["Order"]),
              selectable: true,
              evented: true,
            });
            oImg.scaleToWidth(oImg.width / r / r1);
            oImg.scaleToHeight(oImg.height / r / r1);
            oImg.set({ scaleX: oImg.scaleX * scale });
            oImg.set({ scaleY: oImg.scaleY * scale });

            var group_x = 0,
              group_y = 0;
            var groupName = options["Group Name"];
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

  function drawText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("text")) {
        canvas.remove(o);
      }
    });
    textCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldId = row.data("field-id");
      const fieldType = getFieldType(row);
      if (fieldType == "Text" || fieldType == "Text Options") {
        const options = getOptions(row);
        const x = options["X"] ? parseFloat(options["X"]) : 0;
        const y = options["Y"] ? parseFloat(options["Y"]) : 0;
        const width = options["Width"] ? parseInt(options["Width"]) : 100;
        const fontSize = options["Font Size"]
          ? parseInt(options["Font Size"])
          : 100;
        const fill = options["Font Color"] || "#000000";
        const textAlign = options["Alignment"] || "left";
        const fontFamily = options["Font"] || "Proxima-Nova-Semibold";
        const offset_x = 0;
        const offset_y = 0;
        const text_val = options["Placeholder"] || getFieldName(row);
        const spacing = options["Text Tracking"]
          ? parseInt(options["Text Tracking"])
          : 0;
        var groupName = options["Group Name"];
        if (text_val) {
          var { text, styles } = parseText(text_val, fill);
          var textBox = new fabric.Textbox(text, {
            id: "text" + index,
            element_id: getFieldElementId(row),
            field_id: fieldId,
            groupName: groupName,
            top: y + offset_y,
            left: x + offset_x,
            width,
            textAlign,
            fontSize,
            fontFamily,
            selectable: true,
            evented: true,
            charSpacing: spacing,
            styles,
          });

          var group_x = 0,
            group_y = 0;
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          textBox.set({ left: textBox.left + group_x });
          textBox.set({ top: textBox.top + group_y });
          canvas.add(textBox);
          var overflow_width =
            textBox.width > parseInt(options["Width"])
              ? (textBox.width - parseInt(options["Width"])) / 2
              : 0;
          textBox.set({ left: textBox.left - overflow_width });
          textCoords.push({ x: x + group_x, y: y + group_y });
          index++;

          textBox.on("scaling", function () {
            const controlPoint = textBox.__corner;
            let lastHeight = textBox.height;
            let lastWidth = textBox.width;
            if (controlPoint && controlPoint != "mr" && controlPoint != "ml") {
              lastHeight = textBox.height * textBox.scaleY;
              lastWidth = textBox.width * textBox.scaleX;
            }
            textBox.set({
              height: lastHeight || textBox.height,
              width: lastWidth || textBox.width,
              scaleX: 1,
              scaleY: 1,
            });

            canvas.renderAll();
          });
        }
      }
    });
    setOrder();
  }

  function drawStaticText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("static_txt")) {
        canvas.remove(o);
      }
    });
    stTextCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldId = row.data("field-id");
      const fieldType = getFieldType(row);
      if (fieldType == "Static Text") {
        const options = getOptions(row);
        const x = options["X"] ? parseFloat(options["X"]) : 0;
        const y = options["Y"] ? parseFloat(options["Y"]) : 0;
        const width = options["Width"] ? parseInt(options["Width"]) : 100;
        const fontSize = options["Font Size"]
          ? parseInt(options["Font Size"])
          : 100;
        const fill = options["Font Color"] || "#000000";
        const textAlign = options["Alignment"] || "left";
        const fontFamily = options["Font"] || "Proxima-Nova-Semibold";
        const spacing = options["Text Tracking"]
          ? parseInt(options["Text Tracking"])
          : 0;
        const text_val =
          options["Option1"] || options["Placeholder"] || options["Name"];
        if (text_val) {
          var { text, styles } = parseText(text_val, fill);
          var text = new fabric.Textbox(text, {
            id: "static_txt" + index,
            element_id: getFieldElementId(row),
            field_id: fieldId,
            order: parseInt(options["Order"]),
            top: y,
            left: x,
            width,
            textAlign,
            fontSize,
            ineHeight: options["Leading"] ? parseFloat(options["Leading"]) : 1,
            fill,
            fontFamily,
            selectable: true,
            evented: true,
            charSpacing: spacing,
            styles: styles,
          });

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
          stTextCoords.push({ x: x + group_x, y: y + group_y });

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
    rectCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Rectangle") {
        var options = getOptions(row);
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
        var rect = new fabric.CustomRect({
          id: "rectangle_" + index,
          element_id: getFieldElementId(row),
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          fill: options["Option3"] || "#ffffff",
          stroke: options["Option1"],
          strokeWidth: parseInt(options["Option2"]) || 0,
          rx: radius,
          ry: radius,
          corners: corners,
          selectable: true,
          evented: true,
          opacity: parseFloat(options["Option5"]) || 1,
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
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        rect.set({ left: rect.left + group_x });
        rect.set({ top: rect.top + group_y });
        canvas.add(rect);

        rectCoords.push({
          x: parseInt(options["X"]) + group_x,
          y: parseInt(options["Y"]) + group_y,
          scaleX: rect.scaleX,
          scaleY: rect.scaleY,
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
    circleCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Circle") {
        var options = getOptions(row);
        var circle = new fabric.Circle({
          id: "circle_" + index,
          element_id: getFieldElementId(row),
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          radius: parseInt(options["Width"]) / 2,
          fill: options["Option3"] || "#ffffff",
          stroke: options["Option1"] || "#ffffff",
          strokeWidth: parseInt(options["Option2"]) || 0,
          selectable: true,
          evented: true,
          opacity: parseFloat(options["Option5"]) || 1,
        });

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
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
          scaleX: circle.scaleX,
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
    cirtypeCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Circle Type") {
        var options = getOptions(row);
        var circle = new fabric.Circle({
          id: "circletype_" + index,
          element_id: getFieldElementId(row),
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          radius: parseInt(options["Width"]) / 2,
          fill: options["Option1"] || "#ffffff",
          selectable: true,
          evented: true,
        });

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
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
          scaleX: circle.scaleX,
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
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Overlay Area") {
        var options = getOptions(row);
        var overlay = new fabric.Rect({
          id: "overlay_area_" + index,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          fill: options["Option1"] || "#ffffff00",
          selectable: true,
          evented: true,
        });

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
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
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("icon_")) {
        canvas.remove(o);
      }
    });
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Image List") {
        let default_url;
        const options = getOptions(row);
        const items = row.find("td").eq(23).find("select option");
        items.each((i, item) => {
          if ($(item).prop("value") == options["Option1"]) {
            default_url = $(item).data("default-url");
          }
        });
        const url = base_url + "/share?file=" + default_url;
        const angle = options["Angle"] ? parseFloat(options["Angle"]) : 0;
        const scale = 1;
        fabric.Image.fromURL(url, function (oImg) {
          var r, r1;
          r = oImg.width / parseInt(options["Width"]);
          r1 = oImg.height / r / parseInt(options["Height"]);
          oImg.set({
            id: "icon_" + index,
            element_id: getFieldElementId(row),
            order: parseInt(options["Order"]),
            left: parseInt(options["X"]),
            top: parseInt(options["Y"]),
            angle,
            selectable: true,
            evented: true,
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
        });
      }
    });
    setOrder();
  }

  function drawUploadedImage() {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("uploaded_img_")) {
        canvas.remove(o);
      }
    });

    let index = 0;
    $(".template-table tbody tr").each(async function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Upload Image") {
        const options = getOptions(row);
        const left = parseInt(options["X"]);
        const top = parseInt(options["Y"]);
        const width = parseInt(options["Width"]);
        const height = parseInt(options["Height"]);

        let img_file = row.find("input[type='file']").prop("files")[0];
        let formData = new FormData();
        let isFile = false;
        let url;
        if (img_file) {
          formData.append("file", img_file);
          formData.append("url", URL.createObjectURL(img_file));

          const { data: u } = await axios({
            method: "post",
            url: "/banner/store_remote_image",
            data: formData,
            headers: {
              "Content-Type": "multipart/form-data",
            },
          });
          url = u;
          isFile = true;
        } else {
          url = row.find(".Filename_saved").text();
          if (url) {
            url = "share?file=" + url;
            isFile = true;
          }
        }

        if (isFile && url) {
          await new Promise((resolve, reject) => {
            fabric.Image.fromURL("/" + url, function (oImg) {
              var w = parseInt(options["Width"]);
              var h = parseInt(options["Height"]);
              var r;
              h = (w * oImg.height) / oImg.width;
              r = h / parseInt(options["Height"]);
              r = r > 1 ? r : 1;
              w /= r;
              h /= r;
              if (
                options["Option1"] &&
                options["Option1"].includes("auto_height")
              ) {
                w = dimension.width;
                h = (dimension.width / oImg.width) * oImg.height;
              }
              if (options["Option1"] && options["Option1"].includes("fix_y")) {
                oImg.set({
                  lockMovementX: false,
                  lockMovementY: true,
                });
              }
              if (options["Option1"] && options["Option1"].includes("fix_x")) {
                oImg.set({
                  lockMovementX: true,
                  lockMovementY: false,
                });
              }
              oImg.set({
                id: "uploaded_img_" + index,
                element_id: getFieldElementId(row),
                order: parseInt(options["Order"]),
                left: parseInt(options.X) + w / 2,
                top: parseInt(options.Y) + h / 2,
                originX: "middle",
                originY: "middle",
                selectable: true,
                evented: true,
              });

              oImg.scaleToWidth(w);

              var group_x = 0,
                group_y = 0;
              var groupName = options["Group Name"];
              if (groupName && smartObjCoords[groupName]) {
                group_x = parseInt(smartObjCoords[groupName]["X"]);
                group_y = parseInt(smartObjCoords[groupName]["Y"]);
              }
              oImg.set({ left: oImg.left + group_x });
              oImg.set({ top: oImg.top + group_y });
              canvas.add(oImg);
              index++;
              setOrder();
              resolve();
            });
          });
        } else {
          const rect = new fabric.Rect({
            id: "uploaded_img_rect_" + index,
            element_id: getFieldElementId(row),
            order: parseInt(options["Order"]),
            top: 0,
            left: 0,
            width: width - 10,
            height: height - 10,
            fill: "#ffffff",
            stroke: "#000000",
            strokeWidth: 10,
            selectable: false,
            evented: false,
          });

          const coords1 = [width - 10, 0, 0, height - 10];
          const line1 = new fabric.Line(coords1, {
            id: "uploaded_img_l1_" + index,
            element_id: getFieldElementId(row),
            order: parseInt(options["Order"]) - 1,
            stroke: "#000000",
            strokeWidth: 10,
            selectable: false,
            evented: false,
          });

          const coords2 = [0, 0, width - 10, height - 10];
          const line2 = new fabric.Line(coords2, {
            id: "uploaded_img_l2_" + index,
            element_id: getFieldElementId(row),
            order: parseInt(options["Order"]) - 1,
            stroke: "#000000",
            strokeWidth: 10,
            selectable: false,
            evented: false,
          });

          let group = new fabric.Group([rect, line1, line2], {
            id: "uploaded_img_" + index,
            left: parseInt(options["X"]),
            top: parseInt(options["Y"]),
            order: parseInt(options["Order"]),
          });

          var group_x = 0,
            group_y = 0;
          var groupName = options["Group Name"];
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          group.set({ left: group.left + group_x });
          group.set({ top: group.top + group_y });
          canvas.add(group);
          index++;
          setOrder();
        }
      }
    });
  }

  function drawMarker() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "circle" || o.id == "square" || o.id == "list") {
        canvas.remove(o);
      }
    });
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      const listTypes = [
        "List Numbered Circle",
        "List Numbered Square",
        "List Checkmark",
        "List Star",
        "List All",
      ];
      if (listTypes.includes(fieldType)) {
        const cols = ["X", "Y", "Width", "Height", "Font Size", "Option1"];
        const options = getByColNames(row, cols);
        if (
          fieldType == "List Numbered Circle" ||
          fieldType == "List Checkmark" ||
          fieldType == "List Star" ||
          fieldType == "List All"
        ) {
          var circle = new fabric.Circle({
            id: "circle",
            order: parseInt(options["Order"]),
            top: parseInt(options["Y"]),
            left: parseInt(options["X"]),
            radius: parseInt(options["Width"]) / 2,
            stroke: options["Option2"] || "#ffffff",
            strokeWidth: parseInt(options["Option3"]) || 10,
            fill: options["Option4"] || "rgba(0,0,0,0)",
            selectable: true,
            evented: true,
          });

          var group_x = 0,
            group_y = 0;
          var groupName = options["Group Name"];
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          circle.set({ left: circle.left + group_x });
          circle.set({ top: circle.top + group_y });

          canvas.add(circle);
        } else if (fieldType == "List Numbered Square") {
          var rect = new fabric.Rect({
            id: "square",
            order: parseInt(options["Order"]),
            top: parseInt(options["Y"]),
            left: parseInt(options["X"]),
            width: parseInt(options["Width"]),
            height: parseInt(options["Height"]),
            stroke: options["Option2"] || "#ffffff",
            strokeWidth: parseInt(options["Option3"]) || 10,
            fill: options["Option4"] || "rgba(0,0,0,0)",
            selectable: true,
            evented: true,
          });

          var group_x = 0,
            group_y = 0;
          var groupName = options["Group Name"];
          if (groupName && smartObjCoords[groupName]) {
            group_x = parseInt(smartObjCoords[groupName]["X"]);
            group_y = parseInt(smartObjCoords[groupName]["Y"]);
          }
          circle.set({ left: circle.left + group_x });
          circle.set({ top: circle.top + group_y });

          canvas.add(rect);
        }

        if (
          fieldType == "List Numbered Circle" ||
          fieldType == "List Numbered Square" ||
          fieldType == "List All"
        ) {
          if (options["Option1"]) {
            var text = new fabric.Textbox(options["Option1"], {
              id: "list",
              order: parseInt(options["Order"]),
              top: parseInt(options["Y"]) + 5,
              left: parseInt(options["X"]) + 5,
              width: parseInt(options["Width"]),
              fontSize: parseInt(options["Font Size"]),
              fill: "#ffffff",
              fontFamily: options["Font"] || "Proxima-Nova-Semibold",
              textAlign: "center",
              selectable: true,
              evented: true,
            });
            text.top += (parseInt(options["Height"]) - text.height) / 2;
            canvas.add(text);
          }
        } else if (fieldType == "List Checkmark") {
          var text = new fabric.Textbox("✓", {
            id: "list",
            top: parseInt(options["Y"]) + 10,
            left: parseInt(options["X"]) + 5,
            width: parseInt(options["Width"]),
            fontSize: parseInt(options["Font Size"]),
            fill: "#ffffff",
            fontFamily: options["Font"] || "Proxima-Nova-Semibold",
            textAlign: "center",
            selectable: true,
            evented: true,
          });
          text.top += (parseInt(options["Height"]) - text.height) / 2;
          canvas.add(text);
        } else if (fieldType == "List Star") {
          var text = new fabric.Textbox("★", {
            id: "list",
            top: parseInt(options["Y"]) + 5,
            left: parseInt(options["X"]) + 5,
            width: parseInt(options["Width"]),
            fontSize: parseInt(options["Font Size"]),
            fill: "#ffffff",
            fontFamily: options["Font"] || "Proxima-Nova-Semibold",
            textAlign: "center",
            selectable: true,
            evented: true,
          });
          text.top += (parseInt(options["Height"]) - text.height) / 2;
          canvas.add(text);
        }
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
    lineCoords = [];
    let index = 0;
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Line") {
        const cols = [
          "X",
          "Y",
          "Width",
          "Height",
          "Option1",
          "Option2",
          "Order",
        ];
        const options = getByColNames(row, cols);
        const width = parseInt(options["Width"]);
        const height = parseInt(options["Height"]);
        const coords = [
          parseInt(options["X"]),
          parseInt(options["Y"]),
          parseInt(options["X"]) + (width > height ? width : 0),
          parseInt(options["Y"]) + (width < height ? height : 0),
        ];
        const line = new fabric.Line(coords, {
          id: "shape_" + index,
          element_id: getFieldElementId(row),
          order: parseInt(options["Order"]),
          fill: options["Option1"],
          stroke: options["Option1"],
          strokeWidth: Math.min(width, height),
          selectable: true,
          evented: true,
        });

        var group_x = 0,
          group_y = 0;
        var groupName = options["Group Name"];
        if (groupName && smartObjCoords[groupName]) {
          group_x = parseInt(smartObjCoords[groupName]["X"]);
          group_y = parseInt(smartObjCoords[groupName]["Y"]);
        }
        line.set({ left: line.left + group_x });
        line.set({ top: line.top + group_y });

        lineCoords.push({
          x: parseFloat(options["X"]) + group_x,
          y: parseFloat(options["Y"]) + group_y,
          scaleX: line.scaleX,
        });
        canvas.add(line);
        index++;
      }
    });
    setOrder();
  }

  function drawStroke() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "stroke") {
        canvas.remove(o);
      }
    });
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Stroke") {
        const cols = ["Option1", "Option2"];
        const options = getByColNames(row, cols);
        const stroke_color = options["Option1"] || "#6d6d6d";
        const stroke_width = options["Option2"] || 1;
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
      }
    });
  }

  function drawProductDimension() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "dimension") {
        canvas.remove(o);
      }
    });
    $(".template-table tbody tr").each(function (i, el) {
      const row = $(el);
      const fieldType = getFieldType(row);
      if (fieldType == "Product Dimensions") {
        const cols = ["X", "Y", "Width", "Height", "Order"];
        const options = getByColNames(row, cols);
        var rect = new fabric.Rect({
          id: "dimension",
          top: parseInt(options["Y"]),
          left: parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          fill: "#00000000",
          stroke: "#ff0000",
          strokeWidth: 5,
          strokeDashArray: [5, 5],
          order: parseInt(options["Order"]),
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
        canvas.sendToBack(rect);
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
      var width, height;
      if (dimension["width"] > dimension["height"]) {
        width = 700;
        height = (width * dimension["height"]) / dimension["width"];
      } else {
        height = 600;
        width = (height * dimension["width"]) / dimension["height"];
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
    }
    $("#preview-popup").css({ right: 0, left: "auto" });
    canvas.renderAll();
  });

  $(".add-button").on("click", function () {
    $(".list-field-type").toggle();
  });

  $(".list-field-type li").on("click", function () {
    let field_type = $(this).text();
    $(".list-field-type").hide();
    $(".btn-add-row").trigger("click", field_type);
    setTimeout(() => {
      $('th[data-col-name="Field Type"] select').last().val(field_type);
    }, 500);
  });

  $(document).on("click", ".fileinput-remove-button", function () {
    $(this).closest("td").find(".Filename_saved").text("");
    $(this).closest("td").find(".Filename_saved_name").text("");
    drawUploadedImage();
  });

  $("#x_value, #y_value, #w_value, #h_value").on("change", function () {
    var obj = canvas.getActiveObject();
    let x = parseFloat($("#x_value").val());
    let y = parseFloat($("#y_value").val());
    let w = parseFloat($("#w_value").val());
    let h = parseFloat($("#h_value").val());
    if (obj.get("type") == "textbox") {
      obj.set({
        left: x,
        top: y,
        width: w,
        height: h,
        scaleX: 1,
        scaleY: 1,
      });
    } else {
      obj.set({
        left: x,
        top: y,
        scaleX: w / obj.width,
        scaleY: h / obj.height,
      });
    }
    canvas.renderAll();
    updateControls();
  });

  $(document).on("input", "th,td", function () {
    // init
    drawForLoading();
    setBackgroundColor();
    // setBackgroundImage();
    drawUploadedImage();
    drawStaticImage();
    // drawUploadedBackgroundImage();
    drawRectangle();
    drawCircle();
    drawCircleType();
    drawOverlayArea();
    setTimeout(() => {
      drawText();
      drawStaticText();
      drawProductImage();
      drawMarker();
      drawLine();
      drawImageList();
      drawStroke();
      // drawGrid(grid_density);
    }, 1000);
  });

  $(document).on("change", ".color-hex", function () {
    drawRectangle();
    drawCircle();
    drawText();
    drawStaticText();
  });

  onLoad();
  setDraggable();
});
