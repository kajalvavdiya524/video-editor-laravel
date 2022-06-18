require("../../bootstrap");
require("bootstrap-fileinput");
require("multiple-select");
import { font_list } from '../../fonts.js';
var fonts = Object.keys(font_list);

import { fabric } from "fabric";
import JSZip from "jszip";
// import imageCompression from 'browser-image-compression';

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
  const download_file = (data) => {
    if (data.status != "error") {
      var link = document.createElement("a");
      link.href = data.url;
      document.body.appendChild(link);
      link.click();
      link.remove();
    }
  };
  const countrySelect = $('select[name="country_id"]');
  const languageSelect = $('select[name="language_id"]');
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

  const base_url = window.location.origin;
  const canvas_data = {};
  let layout_options = null;
  let layout_title = null;
  let layout_group = null;

  let selectedTextObj = null;
  let layoutChanges = [];

  let downloadable_templates = {};

  const isInGroup = (groupFields, fieldName) => {
    const fieldNames = groupFields.split(",");
    for (const name of fieldNames) {
      if (name.trim() === fieldName) {
        return true;
      }
    }

    return false;
  };

  function drawForLoading(canvas) {

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

  async function setBackgroundImage({
    positioning_options,
    data,
    canvas,
    id,
    group_fields,
    fields,
    dimension,
  }) {
    const background = data["background"];
    if (!background) {
      canvas.backgroundImage = null;
      canvas.renderAll();
      return;
    }

    let urls = [];
    if (Array.isArray(background)) {
      urls = background;
    } else {
      urls.push(background);
    }

    canvas.getObjects().forEach(function (o) {
      if (o.id == "bk_theme_img") {
        canvas.remove(o);
      }
    });

    canvas_data[id].bkImgCoords = [];
    canvas_data[id].background_theme_image.forEach((b, i) => {
      if (!group_fields || !group_fields || isInGroup(group_fields, b.name)) {
        var dimension_width = b["width"];
        var dimension_height = b["height"];
        var offset_x = parseFloat(data.bk_img_offset_x[i]);
        var offset_y = parseFloat(data.bk_img_offset_y[i]);
        var positioningOption = getPositioningOption(
          positioning_options,
          data,
          fields,
          b
        );
        var left = b["left"] + dimension[4].x;
        var top = b["top"] + dimension[4].y;
        if (positioningOption) {
          left = positioningOption.x == null ? left : positioningOption.x;
          top = positioningOption.y == null ? top : positioningOption.y;
          dimension_width =
            positioningOption.width == null
              ? dimension_width
              : positioningOption.width;
        }
        fabric.Image.fromURL(urls[i], function (oImg) {
          var config = {
            id: "bk_theme_img",
            order: b["order"],
            left: canvas_data[id].spacingFieldPosition[b.name]
              ? canvas_data[id].spacingFieldPosition[b.name].x
              : left + offset_x - dimension[2],
            top: top + offset_y - dimension[3],
            originX: "left",
            originY: "top",
            scaleX: dimension_width / oImg.width,
            scaleY: dimension_height / oImg.height,
            selectable: false,
            evented: false,
          };
          if (b["crop"]) {
            config["cropX"] = 0;
            config["cropY"] = 0;
            config["scaleX"] = 1;
            config["scaleY"] = 1;
            config["width"] = dimension_width;
            config["height"] = dimension_height;
          }
          oImg.set(config);
          canvas.add(oImg);
          // oImg.scaleToWidth(dimension_width);
          // oImg.scaleToHeight(dimension_height);
          canvas_data[id].bkImgCoords.push({
            x: oImg.left - offset_x,
            y: oImg.top - offset_y,
          });
          setOrder(canvas);
        });
      }
    });
  }

  function setBackgroundColor({
    fields,
    data,
    canvas,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "pixel_background") {
        canvas.remove(o);
      }
    });

    const background_color = data["background_color"];
    let i = 0;
    fields.forEach((field) => {
      if (
        field.type == "Background Theme Color" &&
        (!group_fields || !group_fields || isInGroup(group_fields, field.name))
      ) {
        let options = JSON.parse(field.options);
        let fill = null;
        if (
          Array.isArray(background_color) &&
          background_color.length > 0 &&
          background_color[i] != null
        ) {
          const colors = background_color[i].split(",");
          if (colors[0] == "solid") {
            fill = colors[1];
          } else if (colors[0] == "gradient") {
            fill = new fabric.Gradient({
              coords: { x1: 0, y1: 0, x2: 0, y2: 1 },
              gradientUnits: "percentage",
              colorStops: [
                { offset: "0", color: colors[1] },
                { offset: "1", color: colors[2] },
              ],
            });
          }
          i++;
        } else {
          fill = background_color;
        }
        var rect = new fabric.Rect({
          id: "pixel_background",
          top: parseInt(options["Y"]) - dimension[3] + dimension[4].y,
          left: parseInt(options["X"]) - dimension[2] + dimension[4].x,
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]) + 1,
          fill,
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
        var width = (product_width * file.width) / sum_width_dimension;
        var r = width / oImg.width;
        var height = oImg.height * r;
        resolve({ image: oImg, width, height });
      });
    });
  }

  function getPositioningOption(positioning_options, data, fields, field) {
    if (typeof positioning_options !== "undefined") {
      const textFields = fields.filter(
        (field) =>
          field.type == "Text" ||
          field.type == "Text Options" ||
          field.type == "Text from Spreadsheet"
      );
      const fieldFlags = {};
      Object.keys(data)
        .filter(
          (key) =>
            key.startsWith("text_") &&
            !key.endsWith("_offset_x") &&
            !key.endsWith("_offset_y") &&
            !key.endsWith("_angle")
        )
        .map((key) => {
          const field = textFields.find((field) => field.element_id === key);
          if (field) {
            fieldFlags[field.name] = !!data[key];
          }
        });

      const parsedOptions = positioning_options.map((option) => {
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

  function drawUploadedBackgroundImage(fields, id) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("upload_bk_img_")) {
        canvas.remove(o);
      }
    });
    fields.forEach((field, index) => {
      if (field.type == "Background Image Upload") {
        var options = JSON.parse(field.options);
        canvas_data[id].uploaded_image[field.element_id] = options;
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
          setOrder(canvas);
        });
      }
    });
  }

  function drawProductImage({
    data,
    canvas,
    id,
    shadows,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("image")) {
        canvas.remove(o);
      }
    });
    let file_ids = (data["file_ids"] || "").split(" ");
    if (data.product_texts) {
      const country = countrySelect.val();
      const language = languageSelect.val();
      const product_texts = JSON.parse(data.product_texts);
      const text_data = product_texts[country + "_" + language];
      if (text_data && text_data.productIDs) {
        file_ids = text_data.productIDs.split(" ");
      }
    }
    file_ids = canvas_data[id].product_image_settings
      .slice(0, file_ids.length)
      .map((options, i) => {
        if (!group_fields || isInGroup(group_fields, options["Name"])) {
          return file_ids[i];
        }
        return null;
      })
      .filter((file_id) => file_id !== null);
    axios({
      method: "post",
      url: "/banner/view",
      data: {
        file_ids: file_ids.join(" "),
        show_warning: true,
      },
    }).then(async function (response) {
      var product_width = canvas_data[id].product["width"];
      var product_height = canvas_data[id].product["height"];
      // var left = canvas_data[id].product["left"];
      var left = 0;
      var top = canvas_data[id].product["top"];
      var margin = parseInt(data["product_space_product_space"]);
      if (isNaN(margin)) {
        margin = 0;
      }
      var files = response.data.files;
      if (!files) return;
      if (files.length > canvas_data[id].product_image_settings.length) {
        files = files.slice(0, canvas_data[id].product_image_settings.length);
      }
      var sum_width_dimension = 0;
      var gname;
      files.forEach((file, index) => {
        if (
          canvas_data[id].product_image_settings[index]["Option1"] != "Hero"
        ) {
          sum_width_dimension += file.related_files[0].width;
        }
        if (canvas_data[id].product_image_settings[index]["Group Name"]) {
          gname = canvas_data[id].product_image_settings[index]["Group Name"];
        }
      });
      if (
        canvas_data[id].smartObjCoords[gname] &&
        canvas_data[id].smartObjCoords[gname]["Width"] &&
        canvas_data[id].smartObjCoords[gname]["Height"]
      ) {
        product_width = parseInt(
          canvas_data[id].smartObjCoords[gname]["Width"]
        );
        product_height = parseInt(
          canvas_data[id].smartObjCoords[gname]["Height"]
        );
      }
      product_width -= margin * (files.length - 1);
      var max_height = 0;
      var total_width = 0;
      var res = await Promise.all(
        files.map((file) =>
          loadFabricImage(
            file.related_files[0],
            sum_width_dimension,
            product_width
          )
        )
      );
      res.forEach(({ height }) => {
        if (max_height < height) {
          max_height = height;
        }
      });
      var r = max_height > product_height ? product_height / max_height : 1;
      res.forEach((item) => {
        item.width *= r;
        item.height *= r;
        total_width += item.width;
      });

      if (canvas_data[id].product["alignment"] == "center") {
        left += (product_width - total_width) / 2;
      } else if (canvas_data[id].product["alignment"] == "right") {
        left += product_width - total_width;
      }
      const angles = data["angle"];
      const x_offsets = data["x_offset"];
      const y_offsets = data["y_offset"];
      const scales = data["scale"];
      const moveables = data["moveable"];
      res.forEach((item, index) => {
        const angle = parseFloat(angles[index]);
        const x_offset = parseFloat(x_offsets[index]);
        const y_offset = parseFloat(y_offsets[index]);
        const scale = parseFloat(scales[index]);
        const moveable = moveables[index];

        if (shadows.length) {
          var sh = shadows[0].list;
          var shadow = new fabric.Shadow({
            color: "#000000" + parseInt(2.5 * sh[0].value).toString(16),
            blur: Math.ceil(sh[4].value * 4),
            offsetX: -sh[2].value * 5 * Math.cos((sh[1].value * Math.PI) / 180),
            offsetY: sh[2].value * 5 * Math.sin((sh[1].value * Math.PI) / 180),
          });
        }

        var w, h;
        if (
          canvas_data[id].product_image_settings[index]["Option1"] == "Hero"
        ) {
          var ratio =
            parseInt(canvas_data[id].product_image_settings[index]["Width"]) /
            item.width;
          var option2 =
            canvas_data[id].product_image_settings[index]["Option2"];
          var hero_left =
            parseInt(canvas_data[id].product_image_settings[index]["X"]) +
            (parseInt(canvas_data[id].product_image_settings[index]["Width"]) *
              ratio *
              scale) /
              2 +
            x_offset;
          if (option2.startsWith("W-")) {
            var w =
              canvas_data[id].product_image_settings[index]["Width"] *
              ratio *
              scale;
            hero_left =
              canvas_data[id].dimension["width"] -
              w -
              parseInt(option2.split("-")[1]);
            hero_left += w / 2;
          }
          item.image.set({ left: hero_left + dimension[4].x });
          item.image.set({
            top:
              parseInt(canvas_data[id].product_image_settings[index]["Y"]) +
              (item.height * ratio * scale) / 2 +
              y_offset +
              dimension[4].y,
          });
          item.image.scaleToWidth(
            parseInt(canvas_data[id].product_image_settings[index]["Width"])
          );
          // left += margin;
        } else {
          var wr = item.width * scale;
          var hr = item.height * scale;
          w = item.width;
          h = item.height;
          var x =
            left + canvas_data[id].product["left"] + item.width / 2 + x_offset;
          var y = top + (product_height - hr) / 2 + y_offset + hr / 2;
          item.image.set({ left: x });
          item.image.set({ top: y });
          item.image.scaleToWidth(item.width);
          left += item.width + margin; // margin = 20;
        }
        item.image.set({
          originX: "middle",
          originY: "middle",
          lockUniScaling: true,
          selectable: moveable == "Yes",
          evented: moveable == "Yes",
        });
        item.image.set({ angle });
        item.image.set({ id: "image" + index });
        item.image.set({
          order: parseInt(
            canvas_data[id].product_image_settings[index]["Order"]
          ),
        });
        if (shadows.length) {
          item.image.set({ shadow: shadow });
        }
        item.image.set({ scaleX: item.image.scaleX * scale });
        item.image.set({ scaleY: item.image.scaleY * scale });

        var group_x = 0,
          group_y = 0;
        var groupName =
          canvas_data[id].product_image_settings[index]["Group Name"];
        if (
          canvas_data[id].product_image_settings[index]["Group Name"] &&
          canvas_data[id].smartObjCoords[groupName]
        ) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        item.image.set({
          groupName: groupName,
          left: item.image.left + group_x - dimension[2] + dimension[4].x,
          top: item.image.top + group_y - dimension[3] + dimension[4].y,
        });
        canvas.add(item.image);

        canvas_data[id].originCoords.push({
          x: item.image.left - x_offset,
          y: item.image.top - y_offset,
          scaleX: item.image.scaleX / scale,
        });
      });
      setOrder(canvas);
    });
  }

  function drawStaticImage({
    fields,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("st_img_")) {
        canvas.remove(o);
      }
    });
    canvas_data[id].stImgCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Static Image" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var fid = field.element_id;
        var url = base_url + "/share?file=" + options["Filename"];
        var offset_x = data[`${fid}_offset_x`];
        var offset_y = data[`${fid}_offset_y`];
        var angle = data[`${fid}_angle`];
        var scale = parseFloat(data[`${fid}_scale`]);

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
              left: canvas_data[id].spacingFieldPosition[field.name]
                ? canvas_data[id].spacingFieldPosition[field.name].x
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
            if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
              group_x = parseInt(
                canvas_data[id].smartObjCoords[groupName]["X"]
              );
              group_y = parseInt(
                canvas_data[id].smartObjCoords[groupName]["Y"]
              );
            }
            oImg.set({
              left: oImg.left + group_x - dimension[2] + dimension[4].x,
            });
            oImg.set({
              top: oImg.top + group_y - dimension[3] + dimension[4].y,
            });

            canvas.add(oImg);
            canvas_data[id].stImgCoords.push({
              x:
                parseInt(options["X"]) +
                group_x -
                dimension[2] +
                dimension[4].x,
              y:
                parseInt(options["Y"]) +
                group_y -
                dimension[3] +
                dimension[4].y,
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder(canvas);
          });
        } else {
          fabric.Image.fromURL(url, function (oImg) {
            var r, r1;
            r = oImg.width / parseInt(options.Width);
            r1 = oImg.height / r / parseInt(options.Height);
            oImg.set({
              id: "st_img_" + index,
              groupName: groupName,
              element_id: fid,
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
            if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
              group_x = parseInt(
                canvas_data[id].smartObjCoords[groupName]["X"]
              );
              group_y = parseInt(
                canvas_data[id].smartObjCoords[groupName]["Y"]
              );
            }
            oImg.set({
              left: oImg.left + group_x - dimension[2] + dimension[4].x,
            });
            oImg.set({
              top: oImg.top + group_y - dimension[3] + dimension[4].y,
            });

            canvas.add(oImg);
            canvas_data[id].stImgCoords.push({
              x: parseFloat(options.X),
              y: parseFloat(options.Y),
              scaleX: oImg.scaleX / scale,
            });
            index++;
            setOrder(canvas);
          });
        }
      }
    });
  }

  function drawBackgroundMockup({
    fields,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    if (layout_options && layout_options.show_mockup) {
      fields.forEach((field) => {
        var options = JSON.parse(field.options);
        if (field.type == "Background Mockup") {
          var url = base_url + "/share?file=" + options["Filename"];
          fabric.Image.fromURL(url, function (img) {
            // add background image
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
              scaleX: canvas.width / img.width,
              scaleY: canvas.height / img.height,
            });
          });
        }
      });
    }
  }

  function drawImageFromBackground({
    positioning_options,
    fields,
    data,
    canvas,
    id,
  }) {
    canvas_data[id].imgFromBkCoords = [];
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("img_from_bk_")) {
        canvas.remove(o);
      }
    });
    return Promise.all(
      canvas_data[id].img_from_bk.map(
        (b, i) =>
          new Promise((resolve, reject) => {
            if (!data.img_from_bk || data.img_from_bk.length == 0) {
              resolve();
              return;
            }
            var url = data.img_from_bk[i];
            if (!url) {
              canvas_data[id].imgFromBkCoords.push({ x: 0, y: 0 });
              resolve();
              return;
            }
            var dimension_width = b["width"];
            var offset_x = parseFloat(data.img_from_bk_offset_x[i]);
            var offset_y = parseFloat(data.img_from_bk_offset_y[i]);
            var scale = parseFloat(data.img_from_bk_scale[i]);
            var positioningOption = getPositioningOption(
              positioning_options,
              data,
              fields,
              b
            );
            var left = b["left"] + dimension[4].x;
            var top = b["top"] + dimension[4].y;
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
                left: canvas_data[id].spacingFieldPosition[b.name]
                  ? canvas_data[id].spacingFieldPosition[b.name].x
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

              canvas_data[id].imgFromBkCoords.push({
                x: oImg.left - offset_x,
                y: oImg.top - offset_y,
                scaleX: oImg.scaleX / scale,
              });
              setOrder(canvas);

              resolve();
            });
          })
      )
    );
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

  function drawText({
    fields,
    positioning_options,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("text")) {
        canvas.remove(o);
      }
    });
    canvas_data[id].textCoords = [];
    let index = 0;
    let i = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        (field.type == "Text" || field.type == "Text Options") &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        const positioningOption = getPositioningOption(
          positioning_options,
          data,
          fields,
          field
        );
        var text_val = data[field.element_id];
        if (data.product_texts) {
          const country = countrySelect.val();
          const language = languageSelect.val();
          const product_texts = JSON.parse(data.product_texts);
          const text_data = product_texts[country + "_" + language];
          if (text_data) {
            text_val = text_data.field_texts[i];
          }
        }
        var color = data[`${field.element_id}_color`];
        var font = data[`${field.element_id}_font`];
        var font_size = data[`${field.element_id}_fontsize`];
        var offset_x = data[`${field.element_id}_offset_x`];
        var offset_y = data[`${field.element_id}_offset_y`];
        var angle = data[`${field.element_id}_angle`];
        var alignment = data[`${field.element_id}_alignment`];
        var x = parseFloat(options["X"]) + dimension[4].x;
        var y = parseFloat(options["Y"]) + dimension[4].y;
        var width = +data[`${field.element_id}_width`] || +options["Width"];
        if (positioningOption) {
          x = positioningOption.x == null ? x : positioningOption.x;
          y = positioningOption.y == null ? y : positioningOption.y;
          width =
            positioningOption.width == null ? width : positioningOption.width;
        }
        if (text_val) {
          var { text, styles } = parseText(
            text_val,
            color ? color : options["Font Color"]
          );
          var textBox = new fabric.Textbox(text, {
            id: "text" + index,
            groupName: groupName,
            element_id: field.element_id,
            order: parseInt(options["Order"]),
            top: y + parseFloat(offset_y),
            left: canvas_data[id].spacingFieldPosition[field.name]
              ? canvas_data[id].spacingFieldPosition[field.name].x
              : x + parseFloat(offset_x),
            width: width,
            textAlign: alignment
              ? alignment
              : options["Alignment"]
              ? options["Alignment"]
              : "left",
            fontSize: font_size ? font_size : parseInt(options["Font Size"]),
            fontColor: color ? color : options["Font Color"],
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
            fontSelector: options["Font Selector"] == "Yes",
            colorSelector: options["Color Selector"] == "Yes",
          });

          var group_x = 0,
            group_y = 0;
          if (groupName && canvas_data[id].smartObjCoords[groupName]) {
            group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
            group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
          }
          textBox.set({ left: textBox.left + group_x - dimension[2] });
          textBox.set({ top: textBox.top + group_y - dimension[3] });

          canvas.add(textBox);
          var overflow_width =
            textBox.width > width ? (textBox.width - width) / 2 : 0;
          textBox.set({ left: textBox.left - overflow_width });

          index++;
          canvas_data[id].textCoords.push({ x, y });
        }
        i++;
      }
    });
    setOrder(canvas);
  }

  function drawStaticText({
    fields,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("static_txt")) {
        canvas.remove(o);
      }
    });
    canvas_data[id].stTextCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Static Text" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var text_val = options["Option1"];
        var offset_x = data[`${field.element_id}_offset_x`];
        var offset_y = data[`${field.element_id}_offset_y`];
        if (text_val != "") {
          var text = new fabric.Textbox(text_val, {
            id: "static_txt" + index,
            groupName: groupName,
            element_id: field.element_id,
            order: parseInt(options["Order"]),
            top: parseFloat(options["Y"]) + parseFloat(offset_y),
            left: canvas_data[id].spacingFieldPosition[field.name]
              ? canvas_data[id].spacingFieldPosition[field.name].x
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
          });

          var group_x = 0,
            group_y = 0;
          if (groupName && canvas_data[id].smartObjCoords[groupName]) {
            group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
            group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
          }
          text.set({
            left: text.left + group_x - dimension[2] + dimension[4].x,
          });
          text.set({ top: text.top + group_y - dimension[3] + dimension[4].y });

          canvas.add(text);
          canvas_data[id].stTextCoords.push({
            x: parseFloat(options["X"]),
            y: parseFloat(options["Y"]),
          });
          index++;
        }
      }
    });
    setOrder(canvas);
  }

  function drawRectangle({
    fields,
    positioning_options,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("rectangle_")) {
        canvas.remove(o);
      }
    });

    canvas_data[id].rectCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Rectangle" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var fill_color = data[`${field.element_id}_fill_color`];
        var stroke_color = data[`${field.element_id}_stroke_color`];
        var offset_x = parseInt(data[`${field.element_id}_offset_x`]);
        var offset_y = parseInt(data[`${field.element_id}_offset_y`]);
        var scaleX = parseFloat(data[`${field.element_id}_scaleX`]);
        var scaleY = parseFloat(data[`${field.element_id}_scaleY`]);
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
        var visible =
          options["Color Selector"] == "No" ||
          (data[`${field.element_id}_toggle_shape`] &&
            data[`${field.element_id}_toggle_shape`] == "on");
        if (!visible) {
          return;
        }
        var x = parseInt(options["X"]);
        var y = parseInt(options["Y"]);
        var width = parseInt(options["Width"]);
        const positioningOption = getPositioningOption(
          positioning_options,
          data,
          fields,
          field
        );
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
          top: y + offset_y,
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : x + offset_x,
          width: width * scaleX,
          height: parseInt(options["Height"]) * scaleY,
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
          rx: radius,
          ry: radius,
          corners: corners,
          selectable: options["Moveable"] === "Yes",
          evented: options["Moveable"] === "Yes",
        });

        var group_x = 0,
          group_y = 0;
        if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        rect.set({ left: rect.left + group_x - dimension[2] + dimension[4].x });
        rect.set({ top: rect.top + group_y - dimension[3] + dimension[4].y });

        canvas.add(rect);
        canvas_data[id].rectCoords.push({
          x: x + group_x,
          y: y + group_y,
          scaleX: rect.scaleX / scaleX,
          scaleY: rect.scaleY / scaleY,
        });
        index++;
      }
    });
    setOrder(canvas);
  }

  function drawCircle({ fields, canvas, id, group_fields, dimension }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("circle_")) {
        canvas.remove(o);
      }
    });

    canvas_data[id].circleCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Circle" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var fill_color = $("#" + field.element_id + "_fill_color").val();
        var stroke_color = $("#" + field.element_id + "_stroke_color").val();
        var visible = $("#" + field.element_id + "_toggle_shape").prop(
          "checked"
        );
        if (!visible) {
          return;
        }
        var circle = new fabric.Circle({
          id: "circle_" + index,
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          radius: parseInt(options["Width"]),
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
        });

        var group_x = 0,
          group_y = 0;
        if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        circle.set({
          left: circle.left + group_x - dimension[2] + dimension[4].x,
        });
        circle.set({
          top: circle.top + group_y - dimension[3] + dimension[4].y,
        });

        canvas.add(circle);
        canvas_data[id].circleCoords.push({
          x: parseInt(options["X"]) + group_x,
          y: parseInt(options["Y"]) + group_y,
        });
        index++;
      }
    });
    setOrder(canvas);
  }

  function drawOverlayArea({ fields, canvas, id, group_fields, dimension }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("overlay_area_")) {
        canvas.remove(o);
      }
    });
    fields.forEach((field, index) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Overlay Area" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        const left = parseInt(options["X"]) + dimension[4].x;
        const top = parseInt(options["Y"]) + dimension[4].y;
        const width = parseInt(options["Width"]);
        const height = parseInt(options["Height"]);
        const order = parseInt(options["Order"]);
        if (layout_options && layout_options.show_overlay) {
          var overlay = new fabric.Rect({
            id: "overlay_area_" + index,
            groupName: groupName,
            order,
            top,
            left: canvas_data[id].spacingFieldPosition[field.name]
              ? canvas_data[id].spacingFieldPosition[field.name].x
              : left,
            width,
            height,
            fill: options["Option1"] ? options["Option1"] : "#ffffff00",
            selectable: false,
            evented: false,
          });

          var group_x = 0,
            group_y = 0;
          if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
            group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
            group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
          }
          overlay.set({ left: overlay.left + group_x - dimension[2] });
          overlay.set({ top: overlay.top + group_y - dimension[3] });

          canvas.add(overlay);
        } else {
          const objects = canvas.getObjects();
          objects.forEach(function (o) {
            if (
              o.id != "layout_stroke" &&
              o.order <= order &&
              (o.left < 0 || (o.left >= left && o.left <= left + width)) &&
              (o.top < 0 || (o.top >= top && o.top <= top + height))
            ) {
              canvas.remove(o);
            }
          });
        }
      }
    });
    setOrder(canvas);
  }

  function drawImageList({
    fields,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("icon")) {
        canvas.remove(o);
      }
    });

    canvas_data[id].iconCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Image List" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var image_name = data[field.element_id];
        if (image_name && image_name != "none") {
          var url = base_url + "/share?file=" + image_name;
          var offset_x = data[`${field.element_id}_offset_x`];
          var offset_y = data[`${field.element_id}_offset_y`];
          var angle = data[`${field.element_id}_angle`];
          var scale = parseFloat(data[`${field.element_id}_scale`]);
          var extension = image_name.split(".").slice(-1)[0];
          if (extension.toLowerCase() == "svg") {
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
                id: "icon" + index,
                groupName: groupName,
                element_id: field.element_id,
                order: parseInt(options["Order"]),
                left: canvas_data[id].spacingFieldPosition[field.name]
                  ? canvas_data[id].spacingFieldPosition[field.name].x
                  : parseInt(options["X"]) +
                    parseFloat(offset_x) -
                    dimension[2] +
                    dimension[4].x,
                top:
                  parseInt(options["Y"]) +
                  parseFloat(offset_y) -
                  dimension[3] +
                  dimension[4].y,
                angle: parseInt(angle),
                selectable: options["Moveable"] == "Yes",
                evented: options["Moveable"] == "Yes",
              });
              oImg.scaleToWidth(oImg.width / r / r1);
              oImg.scaleToHeight(oImg.height / r / r1);
              oImg.set({ scaleX: oImg.scaleX * scale });
              oImg.set({ scaleY: oImg.scaleY * scale });
              canvas.add(oImg);
              canvas_data[id].iconCoords.push({
                x: parseInt(options["X"]),
                y: parseInt(options["Y"]),
                scaleX: oImg.scaleX / scale,
              });
              index++;
              setOrder(canvas);
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
                id: "icon" + index,
                groupName: groupName,
                element_id: field.element_id,
                order: parseInt(options["Order"]),
                left: canvas_data[id].spacingFieldPosition[field.name]
                  ? canvas_data[id].spacingFieldPosition[field.name].x
                  : parseInt(options["X"]) +
                    parseFloat(offset_x) -
                    dimension[2] +
                    dimension[4].x,
                top:
                  parseInt(options["Y"]) +
                  parseFloat(offset_y) -
                  dimension[3] +
                  dimension[4].y,
                angle: parseInt(angle),
                selectable: options["Moveable"] == "Yes",
                evented: options["Moveable"] == "Yes",
              });
              oImg.scaleToWidth(oImg.width / r / r1);
              oImg.scaleToHeight(oImg.height / r / r1);
              oImg.set({ scaleX: oImg.scaleX * scale });
              oImg.set({ scaleY: oImg.scaleY * scale });
              canvas.add(oImg);
              canvas_data[id].iconCoords.push({
                x: parseInt(options["X"]),
                y: parseInt(options["Y"]),
                scaleX: oImg.scaleX / scale,
              });
              index++;
              setOrder(canvas);
            });
          }
        }
      }
    });
  }

  function drawUploadedImage({
    fields,
    data,
    canvas,
    id,
    group_fields,
    dimension,
  }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("upload_img_")) {
        canvas.remove(o);
      }
    });
    canvas_data[id].imgCoords = [];
    var index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Upload Image" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        canvas_data[id].uploaded_image[field.element_id] = options;
        var element_id = field.element_id;

        var url = data[`${element_id}_saved`];
        var offset_x = parseFloat(data[`${element_id}_offset_x`]);
        var offset_y = parseFloat(data[`${element_id}_offset_y`]);
        var angle = parseFloat(data[`${element_id}_angle`]);
        var scale = parseFloat(data[`${element_id}_scale`]);
        fabric.Image.fromURL(url, function (oImg) {
          var w = parseInt(options.Width);
          var h = parseInt(options.Height);
          var r;
          h = (w * oImg.height) / oImg.width;
          r = h / parseInt(options.Height);
          r = r > 1 ? r : 1;
          w /= r;
          h /= r;
          if (options.Option1) {
            if (options.Option1.includes("auto_height")) {
              w = canvas_data[id].dimension.width;
              h = (canvas_data[id].dimension.width / oImg.width) * oImg.height;
            }
            if (options.Option1.includes("fix_y")) {
              oImg.set({ lockMovementX: false, lockMovementY: true });
            }
            if (options.Option1.includes("fix_x")) {
              oImg.set({ lockMovementX: true, lockMovementY: false });
            }
          }
          oImg.set({
            id: "upload_img_" + index,
            groupName: groupName,
            element_id,
            order: parseInt(options["Order"]),
            left: canvas_data[id].spacingFieldPosition[field.name]
              ? canvas_data[id].spacingFieldPosition[field.name].x
              : parseInt(options.X) + w / 2 + parseFloat(offset_x),
            top: parseInt(options.Y) + h / 2 + parseFloat(offset_y),
            // angle: parseInt(angle),
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
          if (groupName != "" && canvas_data[id].smartObjCoords[groupName]) {
            group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
            group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
          }
          oImg.set({
            left: oImg.left + group_x - dimension[2] + dimension[4].x,
          });
          oImg.set({ top: oImg.top + group_y - dimension[3] + dimension[4].y });
          canvas.add(oImg);

          canvas_data[id].imgCoords.push({
            x: parseFloat(options.X) + group_x - dimension[2] + dimension[4].x,
            y: parseFloat(options.Y) + group_y - dimension[3] + dimension[4].y,
            scaleX: oImg.scaleX / scale,
          });
          index++;
          setOrder(canvas);
        });
      }
    });
  }

  function drawMarker({ fields, data, canvas, group_fields, dimension }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "circle" || o.id == "square" || o.id == "list") {
        canvas.remove(o);
      }
    });
    var list_type = data["list_type"];
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "List Numbered Circle" ||
        field.type == "List Checkmark" ||
        field.type == "List Star" ||
        ((list_type == "circle" ||
          list_type == "checkmark" ||
          list_type == "star") &&
          field.type == "List All" &&
          (!group_fields || isInGroup(group_fields, field.name)))
      ) {
        var circle = new fabric.Circle({
          id: "circle",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          radius: parseInt(options["Width"]) / 2,
          stroke: "#ffffff",
          strokeWidth: 10,
          fill: "rgba(0,0,0,0)",
          selectable: false,
          evented: false,
        });
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
        }
        circle.set({
          left: circle.left + group_x - dimension[2] + dimension[4].x,
        });
        circle.set({
          top: circle.top + group_y - dimension[3] + dimension[4].y,
        });
        canvas.add(circle);
      } else if (
        field.type == "List Numbered Square" ||
        (list_type == "square" &&
          field.type == "List All" &&
          (!group_fields || isInGroup(group_fields, field.name)))
      ) {
        var rect = new fabric.Rect({
          id: "square",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]),
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]),
          width: parseInt(options["Width"]),
          height: parseInt(options["Height"]),
          stroke: "#ffffff",
          strokeWidth: 10,
          fill: "rgba(0,0,0,0)",
          selectable: false,
          evented: false,
        });
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        rect.set({ left: rect.left + group_x - dimension[2] + dimension[4].x });
        rect.set({ top: rect.top + group_y - dimension[3] + dimension[4].y });
        canvas.add(rect);
      }
      if (
        field.type == "List Numbered Square" ||
        field.type == "List Numbered Circle" ||
        ((list_type == "circle" || list_type == "square") &&
          field.type == "List All" &&
          (!group_fields || isInGroup(group_fields, field.name)))
      ) {
        var text = new fabric.Textbox(options["Option1"], {
          id: "list",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + 5,
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + 5,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: "#ffffff",
          fontFamily: "Proxima-Nova-Semibold",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x - dimension[2] + dimension[4].x });
        text.set({ top: text.top + group_y - dimension[3] + dimension[4].y });
        canvas.add(text);
      } else if (
        field.type == "List Checkmark" ||
        (list_type == "checkmark" &&
          field.type == "List All" &&
          (!group_fields || isInGroup(group_fields, field.name)))
      ) {
        var text = new fabric.Textbox("✓", {
          id: "list",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + 10,
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + 5,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: "#ffffff",
          fontFamily: "ARIALUNI",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x - dimension[2] + dimension[4].x });
        text.set({ top: text.top + group_y - dimension[3] + dimension[4].y });
        canvas.add(text);
      } else if (
        field.type == "List Star" ||
        (list_type == "star" &&
          field.type == "List All" &&
          (!group_fields || isInGroup(group_fields, field.name)))
      ) {
        var text = new fabric.Textbox("★", {
          id: "list",
          groupName: groupName,
          order: parseInt(options["Order"]),
          top: parseInt(options["Y"]) + 5,
          left: canvas_data[id].spacingFieldPosition[field.name]
            ? canvas_data[id].spacingFieldPosition[field.name].x
            : parseInt(options["X"]) + 5,
          width: parseInt(options["Width"]),
          fontSize: parseInt(options["Font Size"]),
          fill: "#ffffff",
          fontFamily: "ARIALUNI",
          textAlign: "center",
          selectable: false,
          evented: false,
        });
        text.top += (parseInt(options["Height"]) - text.height) / 2;
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        text.set({ left: text.left + group_x - dimension[2] + dimension[4].x });
        text.set({ top: text.top + group_y - dimension[3] + dimension[4].y });
        canvas.add(text);
      }
    });
    setOrder(canvas);
  }

  function drawLine({ fields, canvas, id, group_fields, dimension }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("shape_")) {
        canvas.remove(o);
      }
    });
    canvas_data[id].lineCoords = [];
    let index = 0;
    fields.forEach((field) => {
      var options = JSON.parse(field.options);
      var groupName = options["Group Name"];
      if (
        field.type == "Line" &&
        (!group_fields || isInGroup(group_fields, field.name))
      ) {
        var width = parseInt(options["Width"]);
        var height = parseInt(options["Height"]);
        var coords = [
          parseInt(options["X"]),
          parseInt(options["Y"]),
          parseInt(options["X"]) + (width > height ? width : 0),
          parseInt(options["Y"]) + (width < height ? height : 0),
        ];
        var line = new fabric.Line(coords, {
          id: "shape_" + index,
          groupName: groupName,
          element_id: field.element_id,
          order: parseInt(options["Order"]),
          fill: options["Option1"],
          stroke: options["Option1"],
          strokeWidth: Math.min(width, height),
          selectable: options["Moveable"] == "Yes",
          evented: options["Moveable"] == "Yes",
        });
        var group_x = 0,
          group_y = 0;
        if (groupName && canvas_data[id].smartObjCoords[groupName]) {
          group_x = parseInt(canvas_data[id].smartObjCoords[groupName]["X"]);
          group_y = parseInt(canvas_data[id].smartObjCoords[groupName]["Y"]);
        }
        line.set({ left: line.left + group_x - dimension[2] + dimension[4].x });
        line.set({ top: line.top + group_y - dimension[3] + dimension[4].y });
        canvas.add(line);
        canvas_data[id].lineCoords.push({
          x: parseFloat(options["X"]),
          y: parseFloat(options["Y"]),
        });
        index++;
      }
    });
    setOrder(canvas);
  }

  function drawStroke({ fields, canvas, id }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "stroke") {
        canvas.remove(o);
      }
    });

    const strokeField = fields.find((f) => f.type == "Stroke");
    if (strokeField) {
      const options = JSON.parse(strokeField.options);
      const stroke_color = options["Option1"] || "#6d6d6d";
      const stroke_width = options["Option2"] || 1;
      const rect = new fabric.Rect({
        id: "stroke",
        top: 0,
        left: 0,
        width: canvas_data[id].dimension.width - stroke_width,
        height: canvas_data[id].dimension.height - stroke_width,
        fill: "#00000000",
        stroke: stroke_color,
        strokeWidth: stroke_width,
        selectable: false,
        evented: false,
      });
      canvas.add(rect);
      canvas.bringToFront(rect);
    }
    setOrder(canvas);
  }

  function drawSmartObject({ fields, canvas, group_fields, dimension }) {
    canvas.getObjects().forEach(function (o) {
      if (o.id.includes("mask")) {
        canvas.remove(o);
      }
    });
    fields.forEach((field) => {
      if (field.type == "Smart Object") {
        var options = JSON.parse(field.options);
        var groupName = options["Group Name"];
        if (options["Option5"]) {
          var width = parseInt(options["Width"]);
          var height = parseInt(options["Height"]);
          var left = parseInt(options["X"]) + dimension[4].x;
          var top = parseInt(options["Y"]) + dimension[4].y;
          var option5 = JSON.parse(options["Option5"]);
          var radius = parseInt(option5.mask.radius);
          var shadow = option5.shadow;
          var order = parseInt(options["Order"]);

          var rect = new fabric.Rect({
            id: "mask",
            order: order,
            left: left - dimension[2],
            top: top - dimension[3],
            width: width,
            height: height,
            absolutePositioned: true,
          });

          if (!group_fields || isInGroup(group_fields, field.name)) {
            rect = new fabric.Rect({
              id: "mask",
              order: order,
              left: left - dimension[2],
              top: top - dimension[3],
              width: width,
              height: height,
              fill: "red",
              rx: radius,
              ry: radius,
              absolutePositioned: true,
            });
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
              id: "mask_frame",
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
            });
            canvas.add(frame);
          }

          canvas.getObjects().forEach(function (o) {
            if (o.groupName == groupName) {
              o.clipPath = rect;
            }
          });
        }
      }
    });
    setOrder(canvas);
  }

  function setOrder(canvas) {
    var objects = canvas.getObjects();
    objects.sort((a, b) => {
      return b.order - a.order;
    });
    objects.forEach((element) => {
      canvas.bringToFront(element);
    });
  }

  const drawPreviews = async (previews) => {
    for (const preview of previews) {
      const { id, canvas, dimension, data, template, shadows } = preview;
      const [width, height, group_x, group_y] = dimension;
      const { fields, positioning_options } = template;
      canvas_data[id] = {
        dimension: {},
        product: {},
        background_theme_image: [],
        img_from_bk: [],
        uploaded_image: {},
        product_image_settings: [],
        originCoords: [],
        textCoords: [],
        stTextCoords: [],
        imgCoords: [],
        stImgCoords: [],
        lineCoords: [],
        iconCoords: [],
        imgFromBkCoords: [],
        bkImgCoords: [],
        rectCoords: [],
        circleCoords: [],
        cirtypeCoords: [],
        smartObjCoords: {},
        spacingFieldPosition: {},
        _data: data,
      };
      let group_fields = "";
      fields.forEach((field) => {
        if (field.type == "Product Dimensions") {
          var options = JSON.parse(field.options);
          canvas_data[id].product["left"] = parseInt(options.X);
          canvas_data[id].product["top"] = parseInt(options.Y);
          canvas_data[id].product["width"] = parseInt(options.Width);
          canvas_data[id].product["height"] = parseInt(options.Height);
          canvas_data[id].product["alignment"] = options.Alignment;
        } else if (field.type == "Product Image") {
          canvas_data[id].product_image_settings.push(
            JSON.parse(field.options)
          );
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
          canvas_data[id].background_theme_image.push(bt_image);
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
          canvas_data[id].img_from_bk.push(bt_image);
        } else if (field.type == "Canvas") {
          var options = JSON.parse(field.options);
          canvas_data[id].dimension["width"] = parseInt(options.Width);
          canvas_data[id].dimension["height"] = parseInt(options.Height);
          canvas_data[id].dimension["left"] = options.X
            ? parseInt(options.X)
            : 0;
          canvas_data[id].dimension["top"] = options.Y
            ? parseInt(options.Y)
            : 0;
        } else if (field.type == "Smart Object") {
          var options = JSON.parse(field.options);
          var groupName = options["Group Name"];
          canvas_data[id].smartObjCoords[groupName] = options;
        } else if (field.type == "Group" && field.name == layout_group) {
          var options = JSON.parse(field.options);
          group_fields = options["Option1"];
        } else if (field.type == "Field Spacing") {
          const options = JSON.parse(field.options);
          const field_spacing_names = options["Option1"].split(",");
          const spacingFieldValues = options["Option2"].split(",");
          const spacingFieldX = +options["X"];
          let spacingFieldWidth = +options["Width"];
          if (spacingFieldWidth == 0) {
            spacingFieldWidth = width;
          }
          const spacingFields = [];
          const spacingFieldAlignment = options["Alignment"];
          for (let i = 0; i < field_spacing_names.length; i++) {
            const spacing_field = fields.find(
              (f) => f.name === field_spacing_names[i].trim()
            );
            if (spacing_field) {
              const spacing_field_options = JSON.parse(spacing_field.options);
              let spacing_field_width = +spacing_field_options["Width"];
              if (
                spacing_field.type.includes("Text") &&
                data[spacing_field.element_id]
              ) {
                var { text, styles } = parseText(
                  data[spacing_field.element_id],
                  "#000000"
                );
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

          canvas_data[id].spacingFieldPosition[spacingFields[0].name] = {
            x,
            width: spacingFields[0].width,
          };
          for (let i = 1; i < spacingFields.length; i++) {
            x += spacingFields[i - 1].width;
            canvas_data[id].spacingFieldPosition[spacingFields[i].name] = {
              x,
              width: spacingFields[i].width,
            };
          }
        }
      });
      if (
        !canvas_data[id].dimension["width"] ||
        !canvas_data[id].dimension["height"]
      ) {
        canvas_data[id].dimension["width"] = width;
        canvas_data[id].dimension["height"] = height;
        canvas_data[id].dimension["left"] = 0;
        canvas_data[id].dimension["top"] = 0;
      }

      drawForLoading(canvas);
      const params = {
        fields,
        positioning_options,
        data,
        canvas,
        id,
        group_fields,
        shadows,
        dimension,
      };
      setBackgroundColor(params);
      setBackgroundImage(params);
      drawUploadedImage(params);
      drawStaticImage(params);
      drawBackgroundMockup(params);
      // drawUploadedBackgroundImage(params);
      drawImageFromBackground(params).then(() => drawSmartObject(params));
      drawRectangle(params);
      drawCircle(params);
      setTimeout(() => {
        drawText(params);
        drawStaticText(params);
        drawProductImage(params);
        drawStroke(params);
        drawMarker(params);
        drawLine(params);
        drawImageList(params);
        drawOverlayArea(params);

        if (layout_options && layout_options.show_stroke) {
          const layout_stroke_width = layout_options
            ? parseInt(layout_options.stroke_width)
            : 1;
          const layout_stroke_color = layout_options
            ? layout_options.stroke_color
            : "#A9A9A9";
          const rect = new fabric.Rect({
            id: "layout_stroke",
            top: 0,
            left: 0,
            order: 0,
            width: width - layout_stroke_width,
            height: height - layout_stroke_width,
            fill: "#00000000",
            stroke: layout_stroke_color,
            strokeWidth: layout_stroke_width,
            selectable: false,
            evented: false,
          });
          canvas.add(rect);
        }
      }, 9000);

      setTimeout(() => {
        drawSmartObject(params);
      }, 18000);

      // canvas.on({
      //   'object:added': function (e) {
      //     drawSmartObject(params);
      //   },
      // });

      fabric.Object.prototype.transparentCorners = false;
      fabric.Object.prototype.cornerColor = "#ffffff";
      fabric.Object.prototype.cornerStyle = "circle";
      fabric.Object.prototype.cornerSize =
        (canvas_data[id].dimension["width"] >
        canvas_data[id].dimension["height"]
          ? canvas_data[id].dimension["width"]
          : canvas_data[id].dimension["height"]) / 70;
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
          (canvas_data[id].dimension["width"] >
          canvas_data[id].dimension["height"]
            ? canvas_data[id].dimension["width"]
            : canvas_data[id].dimension["height"]) / 40,
      });
    }
  };

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

  const grid = $("#template-group-preview");
  const customer_id = grid.attr("data-customer-id");
  const layout_id = grid.attr("data-layout-id");
  let instances = [];
  const previews = [];

  const LT_HEIGHT = 48;
  const LT_FONT_SIZE = 30;
  const TNS_HEIGHT = 27;
  const TNS_FONT_SIZE = 18;
  let SEPARATOR_HEIGHT = 20;

  const createLayoutTitleCanvas = function (grid) {
    if (layout_title) {
      grid.append(
        $('<canvas class="layout-title-canvas" width="0" height="0"></canvas>')
      );
    }
  };

  const createTemplateNameCanvas = function (grid) {
    if (layout_options && layout_options.show_separator) {
      grid.append(
        $('<canvas class="template-name-canvas" width="0" height="0"></canvas>')
      );
    }
  };

  const createSeparatorCanvas = function (grid) {
    if (layout_options && layout_options.show_separator) {
      grid.append(
        $('<canvas class="separator-canvas" width="0" height="0"></canvas>')
      );
    }
  };

  const drawSeparator = function () {
    if (layout_options && layout_options.show_separator) {
      grid.find(".separator-canvas").each(function (i, el) {
        const template_width = $(this).prev().width();
        const template_height = $(this).prev().height();

        if (template_width > 0 && template_height > 0) {
          el.width = template_width;
          el.height = SEPARATOR_HEIGHT;
          const context = el.getContext("2d");
          context.fillStyle = layout_options.separator_color;
          context.fillRect(0, 0, template_width, SEPARATOR_HEIGHT);
        }
      });
    }
  };

  const getTemplateNameText = function (template) {
    const segments = [];
    if (layout_options && layout_options.include_template_name) {
      segments.push(template.name);
    }
    if (layout_options && layout_options.include_template_size) {
      segments.push(`${template.width} x ${template.height}`);
    }

    if (segments.length == 0) return null;

    return segments.join(" ");
  };

  const drawTemplateName = function (templates) {
    if (
      layout_options &&
      (layout_options.include_template_name ||
        layout_options.include_template_size)
    ) {
      grid.find(".template-name-canvas").each(function (i, el) {
        const template_width = $(el).next().width();
        const template_height = $(el).next().height();
        if (template_width > 0 && template_height > 0) {
          const template_name_text = getTemplateNameText(templates[i]);

          if (template_name_text != null) {
            el.width = template_width;
            el.height = TNS_HEIGHT;
            const context = el.getContext("2d");
            context.font = `${TNS_FONT_SIZE}px Arial`;
            context.fillText(template_name_text, 0, TNS_FONT_SIZE);
          }
        }
      });
    }
  };

  const drawLayoutTitle = function () {
    if (layout_title) {
      const titleCanvas = grid.find(".layout-title-canvas")[0];
      let max_width = 0;
      grid.find(".grid-stack-row").each((index, el) => {
        max_width = Math.max(max_width, $(el).width());
      });
      titleCanvas.width = max_width;
      titleCanvas.height = LT_HEIGHT;
      const context = titleCanvas.getContext("2d");
      context.font = `${LT_FONT_SIZE}px Arial`;
      context.fillText(layout_title, 0, LT_FONT_SIZE);
    }
  };

  const handleObjectMoving = function ({ target }) {
    const { canvas, element_id, id } = target;
    const { instance_id } = canvas;

    if (!element_id || !id) return;

    const index = parseInt(id.split("_").pop());
    let x = target.oCoords.tl.x;
    let y = target.oCoords.tl.y;

    if (id.includes("image")) {
      x -= canvas_data[instance_id].originCoords[index]["x"];
      y -= canvas_data[instance_id].originCoords[index]["y"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
    } else if (id.includes("text")) {
      x -= canvas_data[instance_id].textCoords[index]["x"];
      y -= canvas_data[instance_id].textCoords[index]["y"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
    } else if (id.includes("static_txt")) {
      x -= canvas_data[instance_id].stTextCoords[index]["x"];
      y -= canvas_data[instance_id].stTextCoords[index]["y"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
    } else if (id.includes("shape_")) {
      x -= canvas_data[instance_id].lineCoords[index]["x"];
      y -= canvas_data[instance_id].lineCoords[index]["y"];
      const scale =
        target.scaleX / canvas_data[instance_id].lineCoords[index]["scaleX"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_scale`, scale.toFixed(2));
    } else if (id.includes("circle_")) {
      x -= canvas_data[instance_id].circleCoords[index]["x"];
      y -= canvas_data[instance_id].circleCoords[index]["y"];
      const scale =
        target.scaleX / canvas_data[instance_id].circleCoords[index]["scaleX"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_scale`, scale.toFixed(2));
    } else if (id.includes("rectangle_")) {
      x -= canvas_data[instance_id].rectCoords[index]["x"];
      y -= canvas_data[instance_id].rectCoords[index]["y"];
      const scaleX =
        target.scaleX / canvas_data[instance_id].rectCoords[index]["scaleX"];
      const scaleY =
        target.scaleY / canvas_data[instance_id].rectCoords[index]["scaleY"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(
        instance_id,
        `${element_id}_scaleX`,
        scaleX.toFixed(2)
      );
      updateLayoutChanges(
        instance_id,
        `${element_id}_scaleY`,
        scaleY.toFixed(2)
      );
    } else if (id.includes("st_img_")) {
      x -= canvas_data[instance_id].stImgCoords[index]["x"];
      y -= canvas_data[instance_id].stImgCoords[index]["y"];
      const scale =
        target.scaleX / canvas_data[instance_id].stImgCoords[index]["scaleX"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_scale`, scale.toFixed(2));
    } else if (id.includes("icon_")) {
      x -= canvas_data[instance_id].iconCoords[index]["x"];
      y -= canvas_data[instance_id].iconCoords[index]["y"];
      const scale =
        target.scaleX / canvas_data[instance_id].iconCoords[index]["scaleX"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_scale`, scale.toFixed(2));
    } else if (id.includes("bk_theme_img_")) {
      x -= canvas_data[instance_id].bkImgCoords[index]["x"];
      y -= canvas_data[instance_id].bkImgCoords[index]["y"];
      const { bk_img_offset_x, bk_img_offset_y } =
        canvas_data[instance_id]._data;
      bk_img_offset_x[index] = x.toFixed(2);
      bk_img_offset_y[index] = y.toFixed(2);
      updateLayoutChanges(instance_id, `bk_img_offset_x`, bk_img_offset_x);
      updateLayoutChanges(instance_id, `bk_img_offset_y`, bk_img_offset_y);
    } else if (id.includes("img_from_bk_")) {
      x -= canvas_data[instance_id].imgFromBkCoords[index]["x"];
      y -= canvas_data[instance_id].imgFromBkCoords[index]["y"];
      const scale =
        target.scaleX /
        canvas_data[instance_id].imgFromBkCoords[index]["scaleX"];
      const {
        img_from_bk_offset_x,
        img_from_bk_offset_y,
        img_from_bk_offset_scale,
      } = canvas_data[instance_id]._data;
      img_from_bk_offset_x[index] = x.toFixed(2);
      img_from_bk_offset_y[index] = y.toFixed(2);
      img_from_bk_offset_scale[index] = scale.toFixed(2);
      updateLayoutChanges(
        instance_id,
        `img_from_bk_offset_x`,
        img_from_bk_offset_x
      );
      updateLayoutChanges(
        instance_id,
        `img_from_bk_offset_y`,
        img_from_bk_offset_y
      );
      updateLayoutChanges(
        instance_id,
        `img_from_bk_scale`,
        img_from_bk_offset_scale
      );
    } else {
      x -= canvas_data[instance_id].imgCoords[index]["x"];
      y -= canvas_data[instance_id].imgCoords[index]["y"];
      const scale =
        target.scaleX / canvas_data[instance_id].imgCoords[index]["scaleX"];
      updateLayoutChanges(instance_id, `${element_id}_offset_x`, x.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_offset_y`, y.toFixed(2));
      updateLayoutChanges(instance_id, `${element_id}_scale`, scale.toFixed(2));
    }
  };

  const handleSelectionCreated = function ({ e, target }) {
    if (
      target &&
      target.element_id &&
      target.element_id.startsWith("text") &&
      (target.fontSelector || target.colorSelector)
    ) {
      if (!target.fontSelector) {
        $("#text-editor-control font-selector").hide();
      } else {
        $("#text-editor-font-family").val(target.fontFamily);
        $("#text-editor-font-size").val(target.fontSize);
        $("#text-editor-control font-selector").show();
      }
      if (!target.colorSelector) {
        $("#text-editor-control color-selector").hide();
      } else {
        $("#text-editor-font-color").val(target.fontColor);
        $("#text-editor-control color-selector").show();
      }
      $("#text-editor-control").css("top", e.pageY);
      $("#text-editor-control").css("left", e.pageX);
      $("#text-editor-control").show();

      selectedTextObj = target;
    }
  };

  const handleSelectionCleared = function (e) {
    $("#text-editor-control").hide();

    selectedTextObj = null;
  };

  const handleTextChange = function (e) {
    console.log(e);
  };

  let row_y = 0;
  let row = $('<div class="grid-stack-row"></div>');
  let dom_content = $('<div class="grid-stack-col"></div>');
  axios
    .get(`/banner/${customer_id}/group/${layout_id}/preview`)
    .then(async ({ data }) => {
      layout_options = data.options;
      layout_title = layout_options.title || data.name;
      layout_group = layout_options.group || "All";

      for (const setting of data.settings) {
        const instance = data.instances.find(
          (x) => x.instance_id == setting.instance_id
        );
        if (instance) {
          instances.push(instance);
          let selected = false;
          downloadable_templates = layout_options.downloadable_templates;
          if (downloadable_templates && downloadable_templates[layout_group]) {
            selected = downloadable_templates[layout_group].includes(
              setting.instance_id
            );
          }
          $("#downloadable_templates").append(
            $(
              `<option value="${setting.instance_id}" ${
                selected ? "selected" : ""
              }>${instance.template.name}</option>`
            )
          );
        }
      }
      $("#downloadable_templates").multipleSelect();

      if (layout_options.separator_height) {
        SEPARATOR_HEIGHT = parseInt(layout_options.separator_height);
        SEPARATOR_HEIGHT = Number.isNaN(SEPARATOR_HEIGHT)
          ? 20
          : SEPARATOR_HEIGHT;
      }

      if (layout_options.resolution_size_suffix_text) {
        const json = JSON.parse(layout_options.resolution_size_suffix_text);
        const size = $("select[name=resolution_size]").val();
        $("input[name=resolution_size_suffix]").val(json[size]);
      }

      createLayoutTitleCanvas(grid);
      const layouts = data.settings;
      let layout_dimensions = {};
      if (data.alignment == 1) {
        grid.addClass("align-left");
      }
      for (const layout of layouts) {
        const { y, instance_id } = layout;
        if (row_y != y) {
          createTemplateNameCanvas(grid);
          grid.append(row.clone());
          createSeparatorCanvas(grid);

          row = $('<div class="grid-stack-row"></div>');
          dom_content = $('<div class="grid-stack-col"></div>');
          row_y = y;
        }
        row.append(dom_content);

        const instance = data.instances.find(
          (x) => x.instance_id == instance_id
        );
        if (instance) {
          const { template } = instance;
          let { fields, width, height } = template;
          const canvasField = fields.find((x) => x.type == "Canvas");
          const groupField = fields.find((x) => x.type == "Group");
          const bgMockupField = fields.find(
            (x) => x.type == "Background Mockup"
          );

          let group_x = 0;
          let group_y = 0;

          if (
            canvasField &&
            (!layout_options ||
              layout_options.show_canvas == undefined ||
              layout_options.show_canvas)
          ) {
            const options = JSON.parse(canvasField.options);
            width = options["Width"];
            height = options["Height"];
          }

          if (groupField && groupField.name == layout_group) {
            var groupFieldOptions = JSON.parse(groupField.options);
            width = parseInt(groupFieldOptions["Width"]);
            height = parseInt(groupFieldOptions["Height"]);
            group_x = parseInt(groupFieldOptions["X"]);
            group_y = parseInt(groupFieldOptions["Y"]);
          }

          let bg_mockup = { x: 0, y: 0 };
          if (bgMockupField && layout_options.show_mockup) {
            var options = JSON.parse(bgMockupField.options);
            var url = base_url + "/share?file=" + options["Filename"];
            try {
              var img = await new Promise((resolve, reject) => {
                fabric.Image.fromURL(url, function (img) {
                  resolve(img);
                });
              });
              width = img.width;
              height = img.height;
            } catch (err) {}
            bg_mockup.x = +options["X"];
            bg_mockup.y = +options["Y"];
          }

          layout_dimensions[instance_id] = [
            width,
            height,
            group_x,
            group_y,
            bg_mockup,
          ];
          let child_content = `<div class="grid-stack-item"><canvas class='template-canvas' width="${width}" height="${height}"></canvas>`;
          if (
            template.fields.find((field) => field.type === "Editable Template")
          ) {
            child_content +=
              '<span draggable="false" ondragstart="return false;" class="template-resizer"><i class="cil-resize-both"></i></span>';
          }
          child_content += "</div>";
          dom_content.append($(child_content));
        }
      }
      createTemplateNameCanvas(grid);
      grid.append(row.clone());
      createSeparatorCanvas(grid);

      const total_width = $("#template-group-preview").width();
      let min_ratio = 1;

      layouts.forEach(({ instance_id, y, w }, i) => {
        const instance_index = data.instances.findIndex(
          (x) => x.instance_id == instance_id
        );
        if (instance_index >= 0) {
          const [width] = layout_dimensions[instance_id];
          min_ratio = Math.min(total_width / (12 / w) / width, min_ratio);
        }
      });

      let top = {};
      let iy = 0;
      top[0] = 0;
      layouts.forEach(({ instance_id, y, w }, i) => {
        iy = y;
        const instance_index = data.instances.findIndex(
          (x) => x.instance_id == instance_id
        );
        if (instance_index >= 0) {
          const instance = data.instances[instance_index];
          const shadows = data.shadows[instance_index];
          const template_data = JSON.parse(instance.settings);
          const { template } = instance;
          const [width, height] = layout_dimensions[instance_id];

          const dom_width = width * min_ratio;
          const dom_height = height * min_ratio;

          const item = $("#template-group-preview .grid-stack-item").eq(i);
          // item.css("top", `${top[y]}px`)
          item.css("height", `${dom_height}px`);
          top[i + 1] = top[i] + dom_height;

          const canvas = new fabric.Canvas(item.find(".template-canvas")[0], {
            instance_id,
            preserveObjectStacking: true,
            uniScaleTransform: false,
            uniScaleKey: null,
          });
          canvas.setDimensions(
            {
              width: dom_width + "px",
              height: dom_height + "px",
            },
            { cssOnly: true }
          );
          canvas.on({
            "object:moving": handleObjectMoving,
            "object:scaling": handleObjectMoving,
            "object:resizing": handleObjectMoving,
            "selection:created": handleSelectionCreated,
            "selection:updated": handleSelectionCreated,
            "selection:cleared": handleSelectionCleared,
            "text:changed": handleTextChange,
          });
          previews.push({
            id: instance_id,
            canvas,
            dimension: layout_dimensions[instance_id],
            data: template_data,
            template,
            shadows,
          });
        } else {
          top[i + 1] = top[i] + 0;
        }
      });
      // $("#template-group-preview").height(top[iy + 1]);

      let countries = [];
      fetch("/projects/countries")
        .then((res) => res.json())
        .then((json) => {
          countries = json;
          return fetch(`/projects/languages?c=United%20States`);
        })
        .then((res) => res.json())
        .then((languages) => {
          countrySelect.empty();
          countries.map((c) => {
            countrySelect.append(
              `<option value="${c}" ${
                "United States" == c ? "selected" : ""
              }>${c}</option>`
            );
          });
          languageSelect.empty();
          languages.map((l) => {
            languageSelect.append(`<option value="${l}">${l}</option>`);
          });
          drawPreviews(previews);
          drawSeparator(grid);
          drawTemplateName(previews.map((p) => p.template));
          drawLayoutTitle();
        });
    });

  countrySelect.change(function () {
    var country = $(this).val();
    fetch(`/projects/languages?c=${country}`)
      .then((res) => res.json())
      .then((languages) => {
        languageSelect.empty();
        languages.map((l) => {
          languageSelect.append(`<option value="${l}">${l}</option>`);
        });
        drawPreviews(previews);
      });
  });

  languageSelect.change(function () {
    drawPreviews(previews);
  });

  $(".form-control-file").each(function (index, el) {
    var options = {
      showUpload: false,
      previewFileType: "any",
    };
    var name = $(el)
      .closest(".col")
      .find('input[name="web_page_file_name"]')
      .val();
    if (name) {
      options["initialCaption"] = name;
    }
    $(el).fileinput(options);
  });

  $("input[name=resolution_size_suffix]").on("input", function () {
    const suffix = $(this).val();
    const size = $("select[name=resolution_size]").val();
    const suffix_text = $("input[name=resolution_size_suffix_text]").val();
    let suffix_json = { 100: "", 50: "" };
    if (suffix_text) {
      suffix_json = JSON.parse(suffix_text);
    }
    suffix_json[size] = suffix;
    $("input[name=resolution_size_suffix_text]").val(
      JSON.stringify(suffix_json)
    );
  });

  $("select[name=resolution_size]").on("change", function () {
    const size = $(this).val();
    const suffix_text = $("input[name=resolution_size_suffix_text]").val();
    let suffix_json = { 100: "", 50: "" };
    if (suffix_text) {
      suffix_json = JSON.parse(suffix_text);
    }
    $("input[name=resolution_size_suffix]").val(suffix_json[size]);
  });

  $(".btn-download-assets-one-file").on("click", async function () {
    const token = $('meta[name="csrf-token"]').attr("content");
    const layout_id = $("#template-group-preview").attr("data-layout-id");
    const formData = new FormData();
    formData.set("_token", token);
    formData.set("layout_id", layout_id);
    if (layout_options && layout_options.include_psd) {
      formData.set("include_psd", "on");
    }

    const button = $(this).closest(".btn-group").find("button");
    
    button.prop("disabled", true);
    button.hide();
    $("#downloading_spinner").addClass('d-flex');
    $("#downloading_spinner").removeClass('d-none');
    
    try {
    const { data } = await axios({
      method: "post",
      url: "/banner/download_layout_assets",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    
    download_file(data);

    } catch (err) {
      
      console.log('Unable to download: '+err);
      
    }
   
      
    button.prop("disabled", false);
    button.show();
    $("#downloading_spinner").addClass('d-none');
    $("#downloading_spinner").removeClass('d-flex');
    

  });

  $(".btn-download-assets").on("click", async function () {
    const button = $(this).closest(".btn-group").find("button");
    button.prop("disabled", true);

    const token = $('meta[name="csrf-token"]').attr("content");
    for (const instance of instances) {
      const formObj = JSON.parse(instance.settings);
      const formData = new FormData();
      for (const key in formObj) {
        formData.set(key, formObj[key]);
      }
      formData.set("show_text", "on");
      formData.delete("_method");
      formData.set("_token", token);
      if (layout_options && layout_options.include_psd) {
        formData.set("include_psd", "on");
      }
      const { data } = await axios({
        method: "post",
        url: "/banner/download",
        data: formData,
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      download_file(data);
    }

    button.prop("disabled", false);
  });

  $(".btn-download-each-image").on("click", async function () {
    let zip_file = new JSZip();
    let promises = [];

    $("#template-group-preview .grid-stack-col").each(function (index) {
      const self = $(this);
      if (self.width() > 0 && self.height() > 0) {
        let row_width = 0;
        let row_height = 0;
        const canvas_cols = [];
        $(this)
          .find(".canvas-container")
          .each(function (i, el) {
            const lower_canvas = $(el).find(".lower-canvas")[0];
            const canvas_col = document.createElement("canvas");
            const canvas_col_ctx = canvas_col.getContext("2d");
            canvas_col.width = lower_canvas.width;
            canvas_col.height = lower_canvas.height;
            canvas_col_ctx.drawImage(lower_canvas, 0, 0);

            canvas_cols.push(canvas_col);
            row_width += canvas_col.width;
            row_height = canvas_col.height;
          });

        const canvas_row = document.createElement("canvas");
        const r = layout_options.resolution_size == "50" ? 0.5 : 1;
        canvas_row.width = row_width * r;
        canvas_row.height = row_height * r;
        const canvas_row_ctx = canvas_row.getContext("2d");
        canvas_row_ctx.fillStyle = "#fff";
        canvas_row_ctx.fillRect(0, 0, row_width * r, row_height * r);
        let left = 0;
        canvas_cols.forEach((canvas_col) => {
          canvas_row_ctx.drawImage(
            canvas_col,
            left,
            0,
            canvas_col.width * r,
            canvas_col.height * r
          );
          left += canvas_col.width;
        });

        promises.push(
          new Promise((resolve, reject) => {
            canvas_row.toBlob(async function (blob) {
              const { template } = previews[index];
              const {
                custom_name,
                use_custom_naming,
                brand,
                title,
                resolution_size_suffix,
                prepend_to_filename,
              } = layout_options;
              const layout_name = $("#template-group-preview").attr(
                "data-layout-name"
              );
              const project_name = previews[index].data.project_name || "";
              const { max_file_size } = previews[index].data;

              let output_filename = "";
              if (use_custom_naming) {
                output_filename = custom_name;
                output_filename = output_filename.replaceAll("%Brand%", brand);
                output_filename = output_filename.replaceAll(
                  "%LayoutName%",
                  layout_name
                );
                output_filename = output_filename.replaceAll(
                  "%TemplateName%",
                  template.name
                );
                output_filename = output_filename.replaceAll(
                  "%ProjectName%",
                  project_name
                );
                output_filename = output_filename.replaceAll(
                  "%TemplateWidth%",
                  template.width
                );
                output_filename = output_filename.replaceAll(
                  "%TemplateHeight%",
                  template.height
                );
                output_filename = output_filename.replaceAll(
                  "%LayoutTitle%",
                  title
                );
                if (output_filename.includes("%SpaceToUnderscore%")) {
                  output_filename = output_filename.replaceAll(
                    "%SpaceToUnderscore%",
                    ""
                  );
                  output_filename = output_filename.replaceAll(" ", "_");
                }
              } else {
                output_filename = template.name;
                if (brand && prepend_to_filename) {
                  output_filename = brand + "_" + output_filename;
                }
              }

              if (resolution_size_suffix) {
                output_filename = output_filename + resolution_size_suffix;
              }

              // if (max_file_size) {
              //   const compressed_image = await imageCompression(blob, { maxSizeMB: max_file_size / 1024 });
              //   zip_file.file(`${output_filename}.jpg`, compressed_image);
              // } else {
              //   zip_file.file(`${output_filename}.jpg`, blob);
              // }
              zip_file.file(`${output_filename}.jpg`, blob);
              resolve();
            }, "image/jpeg");
          })
        );
      }
    });

    Promise.all(promises).then((res) => {
      let zip_filename = $("#template-group-preview").attr("data-layout-name");
      if (layout_options.brand && layout_options.prepend_to_filename) {
        zip_filename = layout_options.brand + "_" + zip_filename;
      }

      zip_file.generateAsync({ type: "blob" }).then((blob) => {
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `${zip_filename}.zip`;
        document.body.appendChild(link);
        link.click();
        link.remove();
      });
    });
  });

  $(".btn-download-proof").on("click", async function () {
    const align_left = $("#template-group-preview").hasClass("align-left");
    const group = $('select[name="group"]').val();

    const canvas_rows = [];
    const template_texts = [];
    let total_height = 0;
    let max_width = 0;
    $("#template-group-preview .grid-stack-col").each(function (i) {
      const self = $(this);
      if (self.width() > 0 && self.height() > 0) {
        template_texts.push(getTemplateNameText(previews[i].template));

        let row_width = 0;
        let row_height = 0;
        const canvas_cols = [];
        $(this)
          .find(".canvas-container")
          .each(function (i, el) {
            const lower_canvas = $(el).find(".lower-canvas")[0];
            const canvas_col = document.createElement("canvas");
            const canvas_col_ctx = canvas_col.getContext("2d");
            canvas_col.width = lower_canvas.width;
            canvas_col.height = lower_canvas.height;
            canvas_col_ctx.drawImage(lower_canvas, 0, 0);

            canvas_cols.push(canvas_col);
            row_width += canvas_col.width;
            row_height = canvas_col.height;
          });

        const canvas_row = document.createElement("canvas");
        canvas_row.width = row_width;
        canvas_row.height = row_height;
        const canvas_row_ctx = canvas_row.getContext("2d");
        canvas_row_ctx.fillStyle = "#fff";
        canvas_row_ctx.fillRect(0, 0, row_width, row_height);
        let left = 0;
        canvas_cols.forEach((canvas_col) => {
          canvas_row_ctx.drawImage(
            canvas_col,
            left,
            0,
            canvas_col.width,
            canvas_col.height
          );
          left += canvas_col.width;
        });

        canvas_rows.push(canvas_row);
        max_width = Math.max(canvas_row.width, max_width);
        total_height += canvas_row.height;
      }
    });

    const ratio = max_width / 1500;
    const tns_height = TNS_HEIGHT * ratio;
    const tns_font_size = TNS_FONT_SIZE * ratio;
    const lt_height = LT_HEIGHT * ratio;
    const lt_font_size = LT_FONT_SIZE * ratio;

    if (layout_title) {
      total_height += lt_height;
    }

    if (
      layout_options &&
      (layout_options.include_template_name ||
        layout_options.include_template_size)
    ) {
      total_height += tns_height * canvas_rows.length;
    }

    if (layout_options && layout_options.show_separator) {
      total_height += SEPARATOR_HEIGHT * canvas_rows.length;
    }

    const final_canvas = document.createElement("canvas");
    const final_context = final_canvas.getContext("2d");
    final_canvas.width = max_width;
    final_canvas.height = total_height;
    final_context.fillStyle = "#fff";
    final_context.fillRect(0, 0, max_width, total_height);
    let top = 0;

    if (layout_title) {
      final_context.fillStyle = "#000000";
      final_context.font = `${lt_font_size}px Arial`;
      final_context.fillText(layout_title, 0, top + lt_font_size);
      top += lt_height;
    }

    canvas_rows.forEach((canvas, i) => {
      if (
        layout_options &&
        (layout_options.include_template_name ||
          layout_options.include_template_size)
      ) {
        final_context.fillStyle = "#ffffff";
        final_context.fillRect(0, top, max_width, tns_height);
        final_context.fillStyle = "#000000";
        final_context.font = `${tns_font_size}px Arial`;
        final_context.fillText(template_texts[i], 0, top + tns_font_size);
        top += tns_height;
      }
      final_context.drawImage(
        canvas,
        align_left ? 0 : (max_width - canvas.width) / 2,
        top
      );
      top += canvas.height;
      if (layout_options && layout_options.show_separator) {
        final_context.fillStyle = layout_options.separator_color;
        final_context.fillRect(0, top, max_width, SEPARATOR_HEIGHT);
        top += SEPARATOR_HEIGHT;
      }
    });

    final_canvas.toBlob(function (blob) {
      const layout_name = $("#template-group-preview").attr("data-layout-name");
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `${layout_name}.jpg`;
      document.body.appendChild(link);
      link.click();
      link.remove();
    }, "image/jpeg");
  });

  $(".btn-download-overlay-proof").on("click", async function () {
    const align_left = $("#template-group-preview").hasClass("align-left");
    const group = $('select[name="group"]').val();

    const canvas_rows = [];
    const template_texts = [];
    let total_height = 0;
    let max_width = 0;
    $("#template-group-preview .grid-stack-col").each(function (i) {
      const self = $(this);
      if (self.width() > 0 && self.height() > 0) {
        template_texts.push(getTemplateNameText(previews[i].template));

        let row_width = 0;
        let row_height = 0;
        const canvas_cols = [];
        $(this)
          .find(".canvas-container")
          .each(function (i, el) {
            const lower_canvas = $(el).find(".lower-canvas")[0];
            const canvas_col = document.createElement("canvas");
            const canvas_col_ctx = canvas_col.getContext("2d");
            canvas_col.width = lower_canvas.width;
            canvas_col.height = lower_canvas.height;
            canvas_col_ctx.drawImage(lower_canvas, 0, 0);

            canvas_cols.push(canvas_col);
            row_width += canvas_col.width;
            row_height = canvas_col.height;
          });

        const canvas_row = document.createElement("canvas");
        canvas_row.width = row_width;
        canvas_row.height = row_height;
        const canvas_row_ctx = canvas_row.getContext("2d");
        canvas_row_ctx.fillStyle = "#fff";
        canvas_row_ctx.fillRect(0, 0, row_width, row_height);
        let left = 0;
        canvas_cols.forEach((canvas_col) => {
          canvas_row_ctx.drawImage(
            canvas_col,
            left,
            0,
            canvas_col.width,
            canvas_col.height
          );
          left += canvas_col.width;
        });

        canvas_rows.push(canvas_row);
        max_width = Math.max(canvas_row.width, max_width);
        total_height += canvas_row.height;
      }
    });

    const ratio = max_width / 1500;
    const tns_height = TNS_HEIGHT * ratio;
    const tns_font_size = TNS_FONT_SIZE * ratio;
    const lt_height = LT_HEIGHT * ratio;
    const lt_font_size = LT_FONT_SIZE * ratio;

    if (layout_title) {
      total_height += lt_height;
    }

    if (
      layout_options &&
      (layout_options.include_template_name ||
        layout_options.include_template_size)
    ) {
      total_height += tns_height * canvas_rows.length;
    }

    if (layout_options && layout_options.show_separator) {
      total_height += SEPARATOR_HEIGHT * canvas_rows.length;
    }

    const final_canvas = document.createElement("canvas");
    const final_context = final_canvas.getContext("2d");
    final_canvas.width = max_width;
    final_canvas.height = total_height;
    final_context.fillStyle = "#fff";
    final_context.fillRect(0, 0, max_width, total_height);
    let top = 0;

    if (layout_title) {
      final_context.fillStyle = "#000000";
      final_context.font = `${lt_font_size}px Arial`;
      final_context.fillText(layout_title, 0, top + lt_font_size);
      top += lt_height;
    }

    canvas_rows.forEach((canvas, i) => {
      if (
        layout_options &&
        (layout_options.include_template_name ||
          layout_options.include_template_size)
      ) {
        final_context.fillStyle = "#ffffff";
        final_context.fillRect(0, top, max_width, tns_height);
        final_context.fillStyle = "#000000";
        final_context.font = `${tns_font_size}px Arial`;
        final_context.fillText(template_texts[i], 0, top + tns_font_size);
        top += tns_height;
      }
      final_context.drawImage(
        canvas,
        align_left ? 0 : (max_width - canvas.width) / 2,
        top
      );
      top += canvas.height;
      if (layout_options && layout_options.show_separator) {
        final_context.fillStyle = layout_options.separator_color;
        final_context.fillRect(0, top, max_width, SEPARATOR_HEIGHT);
        top += SEPARATOR_HEIGHT;
      }
    });

    final_canvas.toBlob(function (blob) {
      const layout_name = $("#template-group-preview").attr("data-layout-name");
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `${layout_name}.jpg`;
      document.body.appendChild(link);
      link.click();
      link.remove();
    }, "image/jpeg");
  });

  $(".btn-download-logos").on("click", async function () {
    const token = $('meta[name="csrf-token"]').attr("content");
    const layout_id = $("#template-group-preview").attr("data-layout-id");
    const formData = new FormData();
    formData.set("_token", token);
    formData.set("layout_id", layout_id);

    const button = $(this).closest(".btn-group").find("button");
    button.prop("disabled", true);

    const { data } = await axios({
      method: "post",
      url: "/banner/download_layout_logos",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    download_file(data);

    button.prop("disabled", false);
  });

  $(".btn-download-web").on("click", async function () {
    const token = $('meta[name="csrf-token"]').attr("content");
    const layout_id = $("#template-group-preview").attr("data-layout-id");
    const formData = new FormData();
    formData.set("_token", token);
    formData.set("layout_id", layout_id);

    const button = $(this).closest(".btn-group").find("button");
    button.prop("disabled", true);

    const { data } = await axios({
      method: "post",
      url: "/banner/download_layout_web",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    download_file(data);

    button.prop("disabled", false);
  });

  $(".btn-download-spreadsheet").on("click", function () {
    console.log(instances);
    const text_data = {};
    for (const instance of instances) {
      const template = "template_" + instance.template.id;
      if (!text_data[template]) {
        text_data[template] = [];
      }
      const obj = {};
      const template_settings = JSON.parse(instance.settings);
      for (const k in template_settings) {
        // if (k == 'background') {
        //   obj[k] = { text: template_settings[k], cell: template_settings['cell_' + k] };
        // } else if (!k.endsWith('offset_x') && !k.endsWith('offset_y') && !k.endsWith('angle') && !k.endsWith('scale')) {
        //   if (k.startsWith('text_') || k.startsWith('static_text_') || k.startsWith('image_list_')) {
        //     obj[k] = { text: template_settings[k], cell: template_settings['cell_' + k] }
        //   }
        // }
        if (
          !k.endsWith("offset_x") &&
          !k.endsWith("offset_y") &&
          !k.endsWith("angle") &&
          !k.endsWith("scale")
        ) {
          if (k.startsWith("text_") || k.startsWith("static_text_")) {
            const { text } = parseText(template_settings[k]);
            obj[k] = { text, cell: template_settings["cell_" + k] };
          }
        }
      }
      // instance.template.fields
      //   .filter(f => f.type == 'Static Image')
      //   .forEach(f => {
      //     const options = JSON.parse(f.options);
      //     obj[f.element_id] = { text: options['Filename'], cell: template_settings['cell_' + f.element_id] }
      //   });
      const fileNameField = instance.template.fields.find(
        (f) => f.type == "Filename Cell"
      );
      if (fileNameField) {
        const filename =
          template_settings["project_name"] || instance.template.name;
        obj["filename_cell"] = {
          text: `${filename}.jpg`,
          cell: template_settings["filename_cell"],
        };
      }
      text_data[template].push(obj);
    }
    $('input[name="template_settings"]').val(JSON.stringify(text_data));
    $("#download-xlsx-form").submit();
  });

  function updateLayoutChanges(instance_id, key, value) {
    const index = layoutChanges.findIndex(
      (x) => x.instance_id === instance_id && x.key === key
    );
    if (index < 0) {
      layoutChanges.push({ instance_id, key, value });
    } else {
      layoutChanges[index].value = value;
    }

    $(".btn-save-changes").prop("disabled", false);
  }

  $("#text-editor-font-family").on("change", function () {
    const fontFamily = $(this).val();
    selectedTextObj.fontFamily = fontFamily;

    const { canvas } = selectedTextObj;
    canvas.renderAll();

    updateLayoutChanges(
      canvas.instance_id,
      `${selectedTextObj.element_id}_font`,
      fontFamily
    );
  });

  $("#text-editor-font-size").on("change", function () {
    const fontSize = $(this).val();
    selectedTextObj.fontSize = fontSize;

    const { canvas } = selectedTextObj;
    canvas.renderAll();

    updateLayoutChanges(
      canvas.instance_id,
      `${selectedTextObj.element_id}_fontsize`,
      fontSize
    );
  });

  $("#text-editor-font-color").on("change", function () {
    const fontColor = $(this).val();
    const { styles } = parseText(selectedTextObj.text, fontColor);
    selectedTextObj.styles = styles;

    const { canvas } = selectedTextObj;
    canvas.renderAll();

    updateLayoutChanges(
      canvas.instance_id,
      `${selectedTextObj.element_id}_color`,
      fontColor
    );
  });

  $(".btn-save-changes").on("click", function () {
    $(".btn-save-changes").text("Saving...");
    axios
      .put(
        `/banner/${customer_id}/group/${layout_id}/save_changes`,
        layoutChanges
      )
      .then(() => {
        $(".btn-save-changes").text("Save");
        $(".btn-save-changes").prop("disabled", true);
      });
  });

  let m_xpos;
  let m_ypos;
  function resizeCanvas(e) {
    const dx = m_xpos - e.x;
    const dy = m_ypos - e.y;

    console.log(dx, dy);

    m_xpos = e.x;
    m_ypos = e.y;
  }

  $(document).on("mousedown", ".template-resizer", function (e) {
    m_xpos = e.x;
    m_ypos = e.y;
    document.addEventListener("mousemove", resizeCanvas, false);
  });

  $(document).on("mouseup", function () {
    document.removeEventListener("mousemove", resizeCanvas, false);
  });

  $("#layoutSettingsModal select[name=group]").on("change", function () {
    const layout_group = $(this).val();
    let templates = [];
    if (downloadable_templates[layout_group]) {
      templates = downloadable_templates[layout_group];
    }
    $("#downloadable_templates").empty();
    for (const instance of instances) {
      let selected = templates.includes(instance.instance_id);
      $("#downloadable_templates").append(
        $(
          `<option value="${instance.instance_id}" ${
            selected ? "selected" : ""
          }>${instance.template.name}</option>`
        )
      );
    }
    $("#downloadable_templates").multipleSelect("refresh");
  });
});
