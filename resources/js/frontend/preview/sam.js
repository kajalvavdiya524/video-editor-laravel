import { fabric } from "fabric";

fabric.perfLimitSizeTotal = 16777216;

$(document).ready(function () {
    var product = [{ width: 180, height: 40, left: 600, top: 0, baseline: 40 }];
    var max_height = 0;
    var canvas;
    var base_url = window.location.origin;
    var originCords = [];

    function onLoad() {
        $(".canvas-container").remove();

        if ($(".edit-button").hasClass("save")) {
            $(".edit-button").removeClass("save");
            $(".edit-button").addClass("edit");
            $(".edit-button").html('<i class="cil-pencil"></i>');
        }

        $("#preview-popup").append(
            `<canvas id="canvas" width="1140" height="40"></canvas>`
        );
        canvas = new fabric.Canvas("canvas", {
            uniScaleTransform: false,
            uniScaleKey: null,
        });
        canvas.setDimensions(
            { width: "1140px", height: "40px" },
            { cssOnly: true }
        );

        canvas.on({
            "object:moving": updateControls,
        });

        $("#preview-popup").show();
        fabric.Object.prototype.transparentCorners = false;
        fabric.Object.prototype.cornerColor = "#ffffff";
        fabric.Object.prototype.cornerStyle = "circle";
        fabric.Object.prototype.cornerStrokeColor = "#000000";
        fabric.Object.prototype.cornerSize = 6;
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
    }

    function updateControls() {
        var image1, image2;
        canvas.getObjects().forEach(function (o) {
            if (o.id == "image1") {
                image1 = o;
            } else if (o.id == "image2") {
                image2 = o;
            }
        });
        var x1 = image1.oCoords.tl.x;
        var y1 = image1.oCoords.tl.y;
        $("input[name='x_offset[]']")
            .eq(0)
            .val((x1 - originCords[0]["x"]).toFixed(2));
        $("input[name='y_offset[]']")
            .eq(0)
            .val((y1 - originCords[0]["y"]).toFixed(2));
        if (image2) {
            var x2 = image2.oCoords.tl.x;
            var y2 = image2.oCoords.tl.y;
            $("input[name='x_offset[]']")
                .eq(1)
                .val((x2 - originCords[1]["x"]).toFixed(2));
            $("input[name='y_offset[]']")
                .eq(1)
                .val((y2 - originCords[1]["y"]).toFixed(2));
        }
    }

    onLoad();
    drawForLoading();
    drawProductImage();
    drawLogoImage();
    setTimeout(function () {
        drawPreHeader();
        drawCTA();
        drawHeader();
        drawSubhead();
        drawDisclaimer();
    }, 2000);

    function drawForLoading() {
        var text1 = new fabric.Text("A", {
            id: "header",
            top: -270,
            left: -105,
            fontSize: 20,
            fill: "#535353",
            textAlign: "center",
            width: 100,
            fontFamily: "Gibson-Regular",
        });
        canvas.add(text1);
        var text2 = new fabric.Text("A", {
            id: "header",
            top: -270,
            left: -105,
            fontSize: 20,
            fill: "#535353",
            textAlign: "center",
            width: 100,
            fontFamily: "Gibson-SemiBold",
        });
        canvas.add(text2);
        canvas.backgroundColor = "#f6f6f6";
    }

    function loadFabricImage(file, sum_width_dimension) {
        var product_width = product[0]["width"];
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
            var product_width = product[0]["width"];
            var product_height = product[0]["height"];
            var left = product[0]["left"];
            var files = response.data.files;
            if (!files) return;
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
            var r =
                max_height > product_height ? product_height / max_height : 1;
            var total_width = 0;
            res.forEach((item) => {
                item.width *= r;
                item.height *= r;
                total_width += item.width;
            });
            left += (product_width - total_width) / 2;
            res.forEach((item, index) => {
                var x_offset = parseFloat($("input[name='x_offset[]']").eq(index).val());
                var y_offset = parseFloat($("input[name='y_offset[]']").eq(index).val());
                item.image.set({ left: left + item.width / 2 + x_offset });
                item.image.scaleToWidth(item.width);
                item.image.set({
                    originX: "middle",
                    originY: "middle",
                    lockUniScaling: true
                });
                item.image.set({ top: product_height - item.height / 2 + y_offset });
                item.image.set({ id: "image" + (index + 1) });
                item.image.set({ scaleX: item.image.scaleX });
                item.image.set({ scaleY: item.image.scaleY });
                left = left + item.width + 10;
                canvas.add(item.image);
                originCords.push({
                    x: item.image.oCoords.tl.x - x_offset,
                    y: item.image.oCoords.tl.y - y_offset,
                });
            });
        });
    }

    function drawPreHeader() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == "pre-header") {
                canvas.remove(o);
            }
        });
        var preheader_text = $("select[name='pre_header']").val();
        var ht = new fabric.Text(preheader_text, {
            id: "pre-header",
            top: 13,
            left: 42,
            fontSize: 14,
            lineHeight: 1,
            fill: "#0067A0",
            textAlign: "left",
            width: 95,
            fontFamily: "Gibson-SemiBold",
            selectable: false,
            evented: false,
        });
        canvas.add(ht);
    }

    function drawHeader() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == "header") {
                canvas.remove(o);
            }
        });
        var header_text = $("input[name='header']").val();
        var ht = new fabric.Text(header_text, {
            id: "header",
            top: 13,
            left: 140,
            fontSize: 14,
            lineHeight: 1,
            fill: "#424242",
            textAlign: "left",
            width: 165,
            fontFamily: "Gibson-SemiBold",
            selectable: false,
            evented: false,
        });
        canvas.add(ht);
    }

    function drawSubhead() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == "subhead") {
                canvas.remove(o);
            }
        });
        var subhead_text = $("input[name='subhead']").val();
        var st = new fabric.Text(subhead_text, {
            id: "subhead",
            top: 13,
            left: 310,
            fontSize: 14,
            lineHeight: 1,
            fill: "#686868",
            textAlign: "left",
            width: 80,
            fontFamily: "Gibson-Regular",
            selectable: false,
            evented: false,
        });
        canvas.add(st);
    }

    function drawDisclaimer() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == "disclaimer") {
                canvas.remove(o);
            }
        });
        var disclaimer_text = $("input[name='disclaimer']").val();
        var dt = new fabric.Text(disclaimer_text, {
            id: "disclaimer",
            top: 15,
            left: 850,
            fontSize: 10,
            lineHeight: 1,
            fill: "#686868",
            textAlign: "left",
            width: 65,
            fontFamily: "Gibson-Regular",
            selectable: false,
            evented: false,
        });
        canvas.add(dt);
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
        }
        fabric.Image.fromURL(url, function (oImg) {
            var r = oImg.height / 28;
            oImg.set({
                id: "logo",
                left: 475,
                top: 6,
                selectable: false,
                evented: false,
            });
            oImg.scaleToWidth(oImg.width / r);
            oImg.scaleToHeight(oImg.height / r);
            canvas.add(oImg);
        });
    }

    function drawCTA() {
        canvas.getObjects().forEach(function (o) {
            if (o.id == "cta" || o.id == "arrow") {
                canvas.remove(o);
            }
        });
        var cta_text = $('select[name="cta"]').val();
        var shop_text = new fabric.Text(cta_text, {
            id: "cta",
            left: 1000,
            top: 13,
            width: 75,
            fontSize: 14,
            fill: "#424242",
            textAlign: "left",
            fontFamily: "Gibson-SemiBold",
            selectable: false,
            evented: false,
        });
        canvas.add(shop_text);
        var url = base_url + "/img/backgrounds/Sam/arrow.png";
        fabric.Image.fromURL(url, function (oImg) {
            oImg.scaleToWidth(15);
            oImg.scaleToHeight(12);
            oImg.set({
                id: "arrow",
                top: 14,
                left: 1083,
                selectable: false,
                evented: false,
            });
            canvas.add(oImg);
        });
    }

    $("select[name='pre_header']").on("change", drawPreHeader);
    $("input[name='custom_pre_header']").on("change", drawPreHeader);
    $("input[name='header']").on("change", drawHeader);
    $("input[name='subhead']").on("change", drawSubhead);
    $("input[name='disclaimer']").on("change", drawDisclaimer);
    $("select[name='cta']").on("change", drawCTA);
    $("input[name='logo']").on("change", drawLogoImage);

    $("input[name='file_ids']").on("change", function () {
        $("input[name='x_offset[]']").val(0);
        $("input[name='y_offset[]']").val(0);
        originCords = [];
        drawProductImage();
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
            canvas.setDimensions(
                { width: "1140px", height: "40px" },
                { cssOnly: true }
            );
        } else {
            $(this).removeClass("save");
            $(this).addClass("edit");
            $(this).html('<i class="cil-pencil"></i>');
            canvas.setDimensions(
                { width: "1140px", height: "40px" },
                { cssOnly: true }
            );
        }
        $("#preview-popup").css({ right: 0, left: "auto" });
        canvas.renderAll();
    });
});
