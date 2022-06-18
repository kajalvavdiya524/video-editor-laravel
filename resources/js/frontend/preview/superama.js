import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
  var dimension = [{ width: 1680, height: 320 }];
  var product = [
    {
      width: [450, 310],
      height: [320, 320],
      left: [490, 1120],
      baseline: [320, 320],
    },
  ];
  var template = 0;
  var max_height = [0, 0];

  var canvas = new fabric.Canvas("canvas");
  canvas.setDimensions({ width: "420px", height: "80px" }, { cssOnly: true });

  $("#preview-popup").show();

  $(".toggle-button").on("click", function () {
    if ($(this).text() == "-") {
      $(".canvas-container").fadeOut();
      $(this).text("+");
    } else {
      $(".canvas-container").fadeIn();
      $(this).text("-");
    }
  });

  drawForLoading();
  setBackgroundImage("/img/backgrounds/Superama/Superama_background.png");
  setTimeout(function () {
    drawHeadline();
    drawSubheadline();
    drawDescription();
    drawMulti1();
    drawPrice1();
    drawCPU1();
    drawWeight1();
    drawMulti2();
    drawPrice2();
    drawCPU2();
    drawWeight2();
  }, 1000);

  function drawForLoading() {
    var text1 = new fabric.Textbox("A", {
      id: "headline",
      top: -270,
      left: -105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 100,
      fontFamily: "Amazon-Ember",
    });
    canvas.add(text1);

    var text1 = new fabric.Textbox("A", {
      id: "headline",
      top: -270,
      left: -105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 100,
      fontFamily: "Amazon-Ember",
    });
    canvas.add(text1);

    var text2 = new fabric.Textbox("A", {
      id: "headline",
      top: -270,
      left: -105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 100,
      fontFamily: "Proxima-Nova-Bold",
    });
    canvas.add(text2);

    var text3 = new fabric.Textbox("A", {
      id: "headline",
      top: -270,
      left: -105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 100,
      fontFamily: "Proxima-Nova-Semibold",
    });
    canvas.add(text3);
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

  function loadFabricImage(file, sum_width_dimension, index) {
    var product_width = product[template]["width"][index];
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL("/share?file=" + file.path, function (oImg) {
        var width = (product_width * file.width) / sum_width_dimension;
        var r = width / oImg.width;
        var height = oImg.height * r;
        max_height[index] =
          max_height[index] < height ? height : max_height[index];
        resolve({ image: oImg, width, height });
      });
    });
  }

  function drawProductImage1() {
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
      var product_space = parseInt($("input[name='product_space1']").val());
      var product_width = product[template]["width"][0];
      var product_height = product[template]["height"][0];
      var left = product[template]["left"][0];
      var files = response.data.files;
      if (!files) return;
      var sum_width_dimension = 0;
      files.forEach((file) => {
        sum_width_dimension += file.related_files[0].width;
      });
      max_height[0] = 0;
      var res = await Promise.all(
        files.map((file) =>
          loadFabricImage(file.related_files[0], sum_width_dimension, 0)
        )
      );
      var r =
        max_height[0] > product_height ? product_height / max_height[0] : 1;
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
        item.image.set({ left: left });
        item.image.scaleToWidth(item.width);
        item.image.set({ top: product[template]["baseline"][0] - item.height });
        item.image.set({ id: "image" });
        item.image.set({
          selectable: false,
          evented: false,
        });
        left += item.width;
        canvas.add(item.image);
      });
    });
  }

  function drawProductImage2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "image2") {
        canvas.remove(o);
      }
    });
    axios({
      method: "post",
      url: "/banner/view",
      data: {
        file_ids: $("input[name=file_ids2]").val(),
        show_warning: true,
      },
    }).then(async function (response) {
      var product_space = parseInt($("input[name='product_space2']").val());
      var product_width = product[template]["width"][1];
      var product_height = product[template]["height"][1];
      var left = product[template]["left"][1];
      var files = response.data.files;
      if (!files) return;
      var sum_width_dimension = 0;
      files.forEach((file) => {
        sum_width_dimension += file.related_files[0].width;
      });

      max_height[1] = 0;
      var res = await Promise.all(
        files.map((file) =>
          loadFabricImage(file.related_files[0], sum_width_dimension, 1)
        )
      );
      var r =
        max_height[1] > product_height ? product_height / max_height[1] : 1;
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
        item.image.set({ left: left });
        item.image.scaleToWidth(item.width);
        item.image.set({ top: product[template]["baseline"][1] - item.height });
        item.image.set({ id: "image2" });
        item.image.set({
          selectable: false,
          evented: false,
        });
        left += item.width;
        canvas.add(item.image);
      });
    });
  }

  function drawDescription() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "description") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='description']").val();
    if (text == "") {
      text = $("input[name='description']").attr("placeholder");
    }
    var description_text = new fabric.Textbox(text, {
      id: "description",
      top: 270,
      left: 105,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 405,
      fontFamily: "Amazon-Ember",
      selectable: false,
      evented: false,
    });
    canvas.add(description_text);
  }

  function drawHeadline() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "headline") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='headline']").val();
    if (text == "") {
      text = $("input[name='headline']").attr("placeholder");
    }
    var headline_text = new fabric.Textbox(text, {
      id: "headline",
      top: 80,
      left: 120,
      fontSize: 80,
      fill: "#ff0000",
      textAlign: "center",
      width: 370,
      fontFamily: "Proxima-Nova-Bold",
      selectable: false,
      evented: false,
    });
    canvas.add(headline_text);
  }

  function drawSubheadline() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "subheadline") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='subheadline']").val();
    if (text == "") {
      text = $("input[name='subheadline']").attr("placeholder");
    }
    var subheadline_text = new fabric.Textbox(text, {
      id: "subheadline",
      top: 150,
      left: 120,
      fontSize: 65,
      fill: "#ff0000",
      textAlign: "center",
      width: 370,
      fontFamily: "Amazon-Ember",
      selectable: false,
      evented: false,
    });
    canvas.add(subheadline_text);
  }

  function drawMulti1() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "multi1") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='multi1']").val();
    if (text == "") {
      text = $("input[name='multi1']").attr("placeholder");
    }
    var multi1_text = new fabric.Textbox(text + "x", {
      id: "multi1",
      top: 40,
      left: 940,
      fontSize: 55,
      fill: "#ff0000",
      textAlign: "center",
      width: 180,
      fontFamily: "Proxima-Nova-Bold",
      selectable: false,
      evented: false,
    });
    canvas.add(multi1_text);
  }

  function drawPrice1() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "price1") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='price1']").val();
    if (text == "") {
      text = $("input[name='price1']").attr("placeholder");
    }
    var price1_text = new fabric.Textbox("$" + text, {
      id: "price1",
      top: 90,
      left: 940,
      fontSize: 55,
      fill: "#ff0000",
      textAlign: "center",
      width: 180,
      fontFamily: "Proxima-Nova-Bold",
      selectable: false,
      evented: false,
    });
    canvas.add(price1_text);
  }

  function drawCPU1() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "unit_cost1") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='unit_cost1']").val();
    if (text == "") {
      text = $("input[name='unit_cost1']").attr("placeholder");
    }
    var price_text = new fabric.Textbox("Precio:", {
      id: "unit_cost1",
      top: 145,
      left: 940,
      fontSize: 25,
      fill: "#535353",
      textAlign: "center",
      width: 180,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    var cpu1_text = new fabric.Textbox("$" + text + " c/u", {
      id: "unit_cost1",
      top: 175,
      left: 940,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 180,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    canvas.add(price_text);
    canvas.add(cpu1_text);
  }

  function drawWeight1() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "weight1") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='weight1']").val();
    if (text == "") {
      text = $("input[name='weight1']").attr("placeholder");
    }
    var weight1_text = new fabric.Textbox(text + " g", {
      id: "weight1",
      top: 225,
      left: 940,
      fontSize: 25,
      fill: "#535353",
      textAlign: "center",
      width: 180,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    canvas.add(weight1_text);
  }

  function drawMulti2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "multi2") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='multi2']").val();
    if (text == "") {
      text = $("input[name='multi2']").attr("placeholder");
    }
    var multi2_text = new fabric.Textbox(text + "x", {
      id: "multi2",
      top: 40,
      left: 1430,
      fontSize: 55,
      fill: "#ff0000",
      textAlign: "center",
      width: 120,
      fontFamily: "Proxima-Nova-Bold",
      selectable: false,
      evented: false,
    });
    canvas.add(multi2_text);
  }

  function drawPrice2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "price2") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='price2']").val();
    if (text == "") {
      text = $("input[name='price2']").attr("placeholder");
    }
    var price2_text = new fabric.Textbox("$" + text, {
      id: "price2",
      top: 90,
      left: 1430,
      fontSize: 55,
      fill: "#ff0000",
      textAlign: "center",
      width: 120,
      fontFamily: "Proxima-Nova-Bold",
      selectable: false,
      evented: false,
    });
    canvas.add(price2_text);
  }

  function drawCPU2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "unit_cost2") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='unit_cost2']").val();
    if (text == "") {
      text = $("input[name='unit_cost2']").attr("placeholder");
    }
    var price_text = new fabric.Textbox("Precio:", {
      id: "unit_cost2",
      top: 145,
      left: 1430,
      fontSize: 25,
      fill: "#535353",
      textAlign: "center",
      width: 120,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    var cpu2_text = new fabric.Textbox("$" + text + " c/u", {
      id: "unit_cost2",
      top: 175,
      left: 1430,
      fontSize: 20,
      fill: "#535353",
      textAlign: "center",
      width: 120,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    canvas.add(price_text);
    canvas.add(cpu2_text);
  }

  function drawWeight2() {
    canvas.getObjects().forEach(function (o) {
      if (o.id == "weight2") {
        canvas.remove(o);
      }
    });
    var text = $("input[name='weight2']").val();
    if (text == "") {
      text = $("input[name='weight2']").attr("placeholder");
    }
    var weight2_text = new fabric.Textbox(text + " g", {
      id: "weight2",
      top: 225,
      left: 1430,
      fontSize: 25,
      fill: "#535353",
      textAlign: "center",
      width: 130,
      fontFamily: "Proxima-Nova-Semibold",
      selectable: false,
      evented: false,
    });
    canvas.add(weight2_text);
  }

  $("input[name='headline']").on("change", function () {
    drawHeadline();
  });

  $("input[name='subheadline']").on("change", function () {
    drawSubheadline();
  });

  $("input[name='description']").on("change", function () {
    drawDescription();
  });

  $("input[name='multi1']").on("change", function () {
    drawMulti1();
  });

  $("input[name='price1']").on("change", function () {
    drawPrice1();
  });

  $("input[name='unit_cost1']").on("change", function () {
    drawCPU1();
  });

  $("input[name='weight1']").on("change", function () {
    drawWeight1();
  });

  $("input[name='multi2']").on("change", function () {
    drawMulti2();
  });

  $("input[name='price2']").on("change", function () {
    drawPrice2();
  });

  $("input[name='unit_cost2']").on("change", function () {
    drawCPU2();
  });

  $("input[name='weight2']").on("change", function () {
    drawWeight2();
  });

  $("input[name='file_ids']").on("change", function () {
    drawProductImage1();
  });

  $("input[name='file_ids2']").on("change", function () {
    drawProductImage2();
  });

  $("#selectImgModal #submit").on("click", function () {
    drawProductImage1();
    drawProductImage2();
  });
});
