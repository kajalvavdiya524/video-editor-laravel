import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

var shadows;

$(document).ready(function () {
  var top = 0;

  var dimension = [
    { width: 160, height: 600 },
    { width: 300, height: 250 },
    { width: 300, height: 600 },
    { width: 320, height: 50 },
    { width: 728, height: 90 },
  ];

  var x = [
    [40, 100, -5],
    [230, 128, 180],
    [70, 0, 120],
    [145, 222, 245],
    [333, 480, 530],
  ];

  var y = [
    [210, 250, 300],
    [0, 105, 90],
    [220, 325, 250],
    [-20, 20, 0],
    [-15, 45, 0],
  ];

  var product = [
    { width: 160, height: 250, left: 0, top: 180, baseline: 480 },
    { width: 150, height: 250, left: 100, top: 0, baseline: 250 },
    { width: 200, height: 250, left: 40, top: 60, baseline: 555 },
    { width: 120, height: 90, left: 15, top: 0, baseline: 0 },
    { width: 300, height: 150, left: 27, top: 0, baseline: 0 },
  ];
  var originCords = [];
  var template = 0;
  var max_height = 0;
  var canvas;
  var base_url = window.location.origin;

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
        `<canvas id="canvas" width="160" height="600"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "160px", height: "600px" },
        { cssOnly: true }
      );
    } else if (template == 1) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="300" height="250"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "300px", height: "250px" },
        { cssOnly: true }
      );
    } else if (template == 2) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="300" height="600"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "150px", height: "300px" },
        { cssOnly: true }
      );
    } else if (template == 3) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="320" height="50"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "320px", height: "50px" },
        { cssOnly: true }
      );
    } else if (template == 4) {
      $("#preview-popup").append(
        `<canvas id="canvas" width="728" height="90"></canvas>`
      );
      canvas = new fabric.Canvas("canvas", {
        uniScaleTransform: false,
        uniScaleKey: null,
      });
      canvas.setDimensions(
        { width: "728px", height: "90px" },
        { cssOnly: true }
      );
    }

    canvas.on({
      "object:moving": updateControls,
      "object:scaling": updateControls,
      "object:resizing": updateControls,
      "object:rotating": updateControls,
    });

    drawProductImage();
    drawText();
    drawCTA();
    drawLogoImage();
    drawBackgroundImage();
  }

  function onLoad() {
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: "walmart",
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

  function updateControls() {
    var image1, image2, image3;
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image1") {
        image1 = o;
      } else if (o.id == "image2") {
        image2 = o;
      } else if (o.id == "image3") {
        image3 = o;
      }
    });
    var bound = image1.getBoundingRect();
    var x1 = image1.left - bound.width / 2;
    var y1 = image1.top - bound.height / 2;
    $("input[name='x_offset[]']")
      .eq(template * 3)
      .val((x1 - originCords[0]["x"]).toFixed(2));
    $("input[name='y_offset[]']")
      .eq(template * 3)
      .val((y1 - originCords[0]["y"]).toFixed(2));
    $("input[name='angle[]']")
      .eq(template * 3)
      .val(image1.angle.toFixed(2));
    $("input[name='scale[]']")
      .eq(template * 3)
      .val((image1.scaleX / originCords[0]["scaleX"]).toFixed(2));
    if (image2) {
      var bound = image2.getBoundingRect();
      var x2 = image2.left - bound.width / 2;
      var y2 = image2.top - bound.height / 2;
      $("input[name='x_offset[]']")
        .eq(template * 3 + 1)
        .val((x2 - originCords[1]["x"]).toFixed(2));
      $("input[name='y_offset[]']")
        .eq(template * 3 + 1)
        .val((y2 - originCords[1]["y"]).toFixed(2));
      $("input[name='angle[]']")
        .eq(template * 3 + 1)
        .val(image2.angle.toFixed(2));
      $("input[name='scale[]']")
        .eq(template * 3 + 1)
        .val((image2.scaleX / originCords[1]["scaleX"]).toFixed(2));
    }
    if (image3) {
      var bound = image3.getBoundingRect();
      var x3 = image3.left - bound.width / 2;
      var y3 = image3.top - bound.height / 2;
      $("input[name='x_offset[]']")
        .eq(template * 3 + 2)
        .val((x3 - originCords[2]["x"]).toFixed(2));
      $("input[name='y_offset[]']")
        .eq(template * 3 + 2)
        .val((y3 - originCords[2]["y"]).toFixed(2));
      $("input[name='angle[]']")
        .eq(template * 3 + 2)
        .val(image3.angle.toFixed(2));
      $("input[name='scale[]']")
        .eq(template * 3 + 2)
        .val((image3.scaleX / originCords[2]["scaleX"]).toFixed(2));
    }
  }

  onLoad();
  drawForLoading();
  setTimeout(function () {
    drawCTA();
  }, 1000);
  // changeTheme();

  function drawForLoading() {
    var text1 = new fabric.Textbox("A", {
      id: "headline",
      top: -270,
      left: -105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 100,
      fontFamily: "Bogle-Regular",
    });
    canvas.add(text1);
  }

  async function setBackgroundImage(url) {
    if (url == "") {
      canvas.backgroundImage = null;
      canvas.renderAll();
      return;
    }
    canvas.setBackgroundImage(url, canvas.renderAll.bind(canvas), {
      originX: "left",
      originY: "top",
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
      if (o.id == "image1" || o.id == "image2" || o.id == "image3") {
        canvas.remove(o);
      }
    });
    var file_ids = $("input[name=file_ids]").val();
    file_ids = file_ids.replace(/  +/g, " ");
    axios({
      method: "post",
      url: "/banner/view",
      data: {
        file_ids: file_ids,
        show_warning: true,
      },
    }).then(async function (response) {
      var product_width = product[template]["width"];
      var product_height = product[template]["height"];
      var left = product[template]["left"];
      var files = response.data.files;
      if (!files) return;
      if (files.length > 3) {
        files = files.slice(0, 3);
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
      res.forEach((item, index) => {
        var sh = shadows[0].list;
        var shadow = new fabric.Shadow({
          color: "#000000" + parseInt(2.5 * sh[0].value).toString(16),
          blur: Math.ceil(sh[4].value * 4),
          offsetX: -sh[2].value * 10 * Math.cos((sh[1].value * Math.PI) / 180),
          offsetY: sh[2].value * 10 * Math.sin((sh[1].value * Math.PI) / 180),
        });
        var angle = parseFloat(
          $("input[name='angle[]']")
            .eq(template * 3 + index)
            .val()
        );
        var x_offset = parseFloat(
          $("input[name='x_offset[]']")
            .eq(template * 3 + index)
            .val()
        );
        var y_offset = parseFloat(
          $("input[name='y_offset[]']")
            .eq(template * 3 + index)
            .val()
        );
        var scale = parseFloat(
          $("input[name='scale[]']")
            .eq(template * 3 + index)
            .val()
        );
        item.image.set({
          left: x[template][index] + x_offset + (item.width * scale) / 2,
        });
        item.image.scaleToWidth(item.width);
        item.image.set({
          originX: "middle",
          originY: "middle",
          lockUniScaling: true,
        });
        item.image.set({ angle: angle });
        item.image.set({
          top: y[template][index] + y_offset + (item.height * scale) / 2,
        });
        item.image.set({ id: "image" + (index + 1) });
        item.image.set({ shadow: shadow });
        item.image.set({ scaleX: item.image.scaleX * scale });
        item.image.set({ scaleY: item.image.scaleY * scale });
        canvas.add(item.image);

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
          customer: "walmart",
          theme: theme,
          template: template,
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

  function drawText() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "headline" || o.id == "subheadline") {
        canvas.remove(o);
      }
    });
    var headline_text1 = $("input[name='headline1']").val();
    var headline_text2 = $("input[name='headline2']").val();
    var subheadline_text1 = $("input[name='subheadline1']").val();
    var subheadline_text2 = $("input[name='subheadline2']").val();

    var template = $("input[name=output_dimensions]").val();
    if (template == 0) {
      top = 72;
      // if (subheadline_text1 == "" && subheadline_text2 == "") {
      //     top += 20;
      // }
      var headline_height = 0;
      if (headline_text1) {
        var ht = new fabric.Textbox(headline_text1, {
          id: "headline",
          top: top,
          left: 10,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (headline_text2) {
        var ht = new fabric.Textbox(headline_text2, {
          id: "headline",
          top: top + headline_height,
          left: 10,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (subheadline_text1) {
        var ht = new fabric.Textbox(subheadline_text1, {
          id: "subheadline",
          top: top + headline_height + 8,
          left: 10,
          fontSize: 14,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (subheadline_text2) {
        var ht = new fabric.Textbox(subheadline_text2, {
          id: "subheadline",
          top: top + headline_height + 8,
          left: 10,
          fontSize: 14,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
    } else if (template == 1) {
      var text_height = 0;
      var ht_box1 = null;
      var ht_box2 = null;
      var sht_box1 = null;
      var sht_box2 = null;
      if (headline_text1) {
        ht_box1 = new fabric.Textbox(headline_text1, {
          id: "headline",
          top: top,
          left: 18,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box1.height;
      }
      if (headline_text2) {
        ht_box2 = new fabric.Textbox(headline_text2, {
          id: "headline",
          top: top,
          left: 18,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box2.height;
      }
      if (subheadline_text1) {
        sht_box1 = new fabric.Textbox(subheadline_text1, {
          id: "subheadline",
          top: top,
          left: 19,
          fontSize: 14,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += 13;
        text_height += sht_box1.height;
      }
      if (subheadline_text2) {
        sht_box2 = new fabric.Textbox(subheadline_text2, {
          id: "subheadline",
          top: top,
          left: 19,
          fontSize: 14,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += sht_box2.height;
      }
      top = (dimension[template]["height"] - text_height - 64) / 2;
      var headline_height = 0;
      if (ht_box1) {
        ht_box1.top = top;
        headline_height += ht_box1.height;
        canvas.add(ht_box1);
      }
      if (ht_box2) {
        ht_box2.top = top + headline_height;
        canvas.add(ht_box2);
        headline_height += ht_box2.height;
      }
      if (sht_box1) {
        sht_box1.top = top + headline_height + 8;
        canvas.add(sht_box1);
        headline_height += sht_box1.height;
      }
      if (sht_box2) {
        sht_box2.top = top + headline_height + 8;
        canvas.add(sht_box2);
      }
    } else if (template == 2) {
      top = 72;
      // if (subheadline_text1 == "" && subheadline_text2 == "") {
      //     top += 20;
      // }
      var headline_height = 0;
      if (headline_text1) {
        var ht = new fabric.Textbox(headline_text1, {
          id: "headline",
          top: top,
          left: 40,
          fontSize: 36,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (headline_text2) {
        var ht = new fabric.Textbox(headline_text2, {
          id: "headline",
          top: top + headline_height,
          left: 40,
          fontSize: 36,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (subheadline_text1) {
        var ht = new fabric.Textbox(subheadline_text1, {
          id: "subheadline",
          top: top + headline_height + 10,
          left: 40,
          fontSize: 21,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
      if (subheadline_text2) {
        var ht = new fabric.Textbox(subheadline_text2, {
          id: "subheadline",
          top: top + headline_height + 10,
          left: 40,
          fontSize: 21,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "center",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        canvas.add(ht);
        headline_height += ht.height;
      }
    } else if (template == 3) {
      var text_height = 0;
      var ht_box1 = null;
      var ht_box2 = null;
      if (headline_text1) {
        ht_box1 = new fabric.Textbox(headline_text1, {
          id: "headline",
          top: 0,
          left: 65,
          fontSize: 17,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box1.height;
      }
      if (headline_text2 != "") {
        ht_box2 = new fabric.Textbox(headline_text2, {
          id: "headline",
          top: 0,
          left: 65,
          fontSize: 17,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box2.height;
      }
      top = (dimension[template]["height"] - text_height) / 2;
      var headline_height = 0;
      if (ht_box1) {
        ht_box1.top = top;
        headline_height += ht_box1.height;
        canvas.add(ht_box1);
      }
      if (ht_box2) {
        ht_box2.top = top + headline_height;
        headline_height += ht_box2.height;
        canvas.add(ht_box2);
      }
    } else if (template == 4) {
      var text_height = 0;
      var ht_box1 = null;
      var ht_box2 = null;
      var sht_box = null;
      if (headline_text1) {
        ht_box1 = new fabric.Textbox(headline_text1, {
          id: "headline",
          top: 0,
          left: 119,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 220,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box1.height;
      }
      if (headline_text2) {
        ht_box2 = new fabric.Textbox(headline_text2, {
          id: "headline",
          top: 0,
          left: 119,
          fontSize: 24,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += ht_box2.height;
      }
      if (subheadline_text1) {
        sht_box = new fabric.Textbox(subheadline_text1, {
          id: "subheadline",
          top: 0,
          left: 119,
          fontSize: 14,
          lineHeight: 1,
          fill: "#2f2f2f",
          textAlign: "left",
          width: 140,
          fontFamily: "Bogle-Regular",
          selectable: false,
          evented: false,
        });
        text_height += sht_box.height;
      }
      top = (dimension[template]["height"] - text_height) / 2;
      var headline_height = 0;
      if (ht_box1) {
        ht_box1.top = top;
        headline_height += ht_box1.height;
        canvas.add(ht_box1);
      }
      if (ht_box2) {
        ht_box2.top = top + headline_height;
        headline_height += ht_box2.height;
        canvas.add(ht_box2);
      }
      if (sht_box) {
        console.log(subheadline_text1, top + headline_height + 8);
        sht_box.top = top + headline_height + 8;
        canvas.add(sht_box);
      }
    }
  }

  function drawLogoImage() {
    var template = $('input[name="output_dimensions"]').val();
    canvas.getObjects().forEach(function (o) {
      if (o.id == "logo") {
        canvas.remove(o);
      }
    });
    var url = base_url + "/img/backgrounds/Walmart/walmart_burst.png";
    if (template == 0) {
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(80);
        oImg.scaleToHeight(19);
        oImg.set({
          id: "logo",
          top: 532,
          left: 42,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    } else if (template == 1) {
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(80);
        oImg.scaleToHeight(19);
        oImg.set({
          id: "logo",
          top: dimension[template]["height"] - top - 20,
          left: 25,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    } else if (template == 2) {
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(80);
        oImg.scaleToHeight(19);
        oImg.set({
          id: "logo",
          top: 532,
          left: (dimension[template]["width"] - 80) / 2 + 4,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    } else if (template == 3) {
      url = base_url + "/img/backgrounds/Walmart/burst.png";
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(35);
        oImg.scaleToHeight(40);
        oImg.set({
          id: "logo",
          top: (dimension[template]["height"] - 40) / 2,
          left: 15,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    } else if (template == 4) {
      url = base_url + "/img/backgrounds/Walmart/burst.png";
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(65);
        oImg.scaleToHeight(74);
        oImg.set({
          id: "logo",
          top: (dimension[template]["height"] - 74) / 2,
          left: 27,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    }
  }

  function drawCTA() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "cta") {
        canvas.remove(o);
      }
    });
    var template = $('input[name="output_dimensions"]').val();
    var cta_text = $('select[name="cta"]').val();
    if (template == 0) {
      var button_background = new fabric.Rect({
        id: "cta",
        left: 38,
        top: 505,
        fill: "#0070dc",
        width: 84,
        height: 20,
        rx: 10,
        ry: 10,
        selectable: false,
        evented: false,
      });
      canvas.add(button_background);
      var shop_text = new fabric.Textbox(cta_text, {
        id: "cta",
        left: 38,
        top: 508,
        width: 84,
        fontSize: 13,
        fill: "#ffffff",
        textAlign: "center",
        fontFamily: "Bogle-Bold",
        selectable: false,
        evented: false,
      });
      canvas.add(shop_text);
    } else if (template == 1) {
      var button_background = new fabric.Rect({
        id: "cta",
        left: 21,
        top: dimension[template]["height"] - top - 47,
        fill: "#0070dc",
        width: 84,
        height: 20,
        rx: 10,
        ry: 10,
        selectable: false,
        evented: false,
      });
      canvas.add(button_background);
      var shop_text = new fabric.Textbox(cta_text, {
        id: "cta",
        left: 21,
        top: dimension[template]["height"] - top - 44,
        width: 84,
        fontSize: 13,
        fill: "#ffffff",
        textAlign: "center",
        fontFamily: "Bogle-Bold",
        selectable: false,
        evented: false,
      });
      canvas.add(shop_text);
    } else if (template == 2) {
      var button_background = new fabric.Rect({
        id: "cta",
        left: (dimension[template]["width"] - 84) / 2,
        top: 505,
        fill: "#0070dc",
        width: 84,
        height: 20,
        rx: 10,
        ry: 10,
        selectable: false,
        evented: false,
      });
      canvas.add(button_background);
      var shop_text = new fabric.Textbox(cta_text, {
        id: "cta",
        left: (dimension[template]["width"] - 84) / 2,
        top: 508,
        width: 84,
        fontSize: 13,
        fill: "#ffffff",
        textAlign: "center",
        fontFamily: "Bogle-Bold",
        selectable: false,
        evented: false,
      });
      canvas.add(shop_text);
    } else if (template == 3) {
      var url = base_url + "/img/backgrounds/Walmart/arrow.png";
      fabric.Image.fromURL(url, function (oImg) {
        oImg.scaleToWidth(26);
        oImg.scaleToHeight(26);
        oImg.set({
          id: "logo",
          top: (dimension[template]["height"] - oImg.height) / 2,
          left: dimension[template]["width"] - oImg.width - 15,
          selectable: false,
          evented: false,
        });
        canvas.add(oImg);
      });
    } else if (template == 4) {
      var button_background = new fabric.Rect({
        id: "cta",
        left: dimension[template]["width"] - 117,
        top: (dimension[template]["height"] - 25) / 2,
        fill: "#0070dc",
        width: 90,
        height: 25,
        rx: 12,
        ry: 12,
        selectable: false,
        evented: false,
      });
      canvas.add(button_background);
      var shop_text = new fabric.Textbox(cta_text, {
        id: "cta",
        left: dimension[template]["width"] - 117,
        top: (dimension[template]["height"] - 25) / 2 + 5,
        width: 90,
        fontSize: 13,
        fill: "#ffffff",
        textAlign: "center",
        fontFamily: "Bogle-Bold",
        selectable: false,
        evented: false,
      });
      canvas.add(shop_text);
    }
  }

  $(
    "input[name='headline1'], input[name='headline2'], input[name='subheadline1'], input[name='subheadline2']"
  ).on("change", function () {
    drawText();
    drawCTA();
    drawLogoImage();
  });

  $("select[name='cta']").on("change", function () {
    drawCTA();
  });

  $("input[name='file_ids']").on("change", function () {
    $("input[name='angle[]']").each((i, obj) => {
      if (i >= template * 3 && i < (template + 1) * 3) {
        var v = $(obj).attr("placeholder");
        $(obj).val(v);
      }
    });
    $("input[name='x_offset[]']")
      .eq(template * 3)
      .val(0);
    $("input[name='x_offset[]']")
      .eq(template * 3 + 1)
      .val(0);
    $("input[name='x_offset[]']")
      .eq(template * 3 + 2)
      .val(0);
    $("input[name='y_offset[]']")
      .eq(template * 3)
      .val(0);
    $("input[name='y_offset[]']")
      .eq(template * 3 + 1)
      .val(0);
    $("input[name='y_offset[]']")
      .eq(template * 3 + 2)
      .val(0);
    $("input[name='scale[]']")
      .eq(template * 3)
      .val(1);
    $("input[name='scale[]']")
      .eq(template * 3 + 1)
      .val(1);
    $("input[name='scale[]']")
      .eq(template * 3 + 2)
      .val(1);
    originCords = [];
    drawProductImage();
  });

  $("input[name='show_stroke']").on("change", function () {
    if ($(this).prop("checked")) {
      var rect = new fabric.Rect({
        id: "stroke",
        top: 0,
        left: 0,
        width: dimension[template].width - 1,
        height: dimension[template].height - 1,
        fill: "#00000000",
        stroke: "#6d6d6d",
        strokeWidth: 1,
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

  $("#theme").on("change", changeTheme);

  function changeTheme() {
    drawBackgroundImage(true);
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: "walmart",
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      shadows = response.data.shadow;
      drawProductImage();
    });
  }

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
          { width: "160px", height: "600px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "600px", height: "500px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "300px", height: "600px" },
          { cssOnly: true }
        );
      } else if (template == 3) {
        canvas.setDimensions(
          { width: "640px", height: "80px" },
          { cssOnly: true }
        );
      } else if (template == 4) {
        canvas.setDimensions(
          { width: "728px", height: "90px" },
          { cssOnly: true }
        );
      }
    } else {
      $(this).removeClass("save");
      $(this).addClass("edit");
      $(this).html('<i class="cil-pencil"></i>');
      if (template == 0) {
        canvas.setDimensions(
          { width: "160px", height: "600px" },
          { cssOnly: true }
        );
      } else if (template == 1) {
        canvas.setDimensions(
          { width: "300px", height: "250px" },
          { cssOnly: true }
        );
      } else if (template == 2) {
        canvas.setDimensions(
          { width: "150px", height: "300px" },
          { cssOnly: true }
        );
      } else if (template == 3) {
        canvas.setDimensions(
          { width: "320px", height: "50px" },
          { cssOnly: true }
        );
      } else if (template == 4) {
        canvas.setDimensions(
          { width: "728px", height: "90px" },
          { cssOnly: true }
        );
      }
    }
    $("#preview-popup").css({ right: 0, left: "auto" });
    canvas.renderAll();
  });

  $("#selectBkImgModal #submit").on("click", function () {
    var background = $("input[name='background']").val();
    setBackgroundImage(background);
  });
});
