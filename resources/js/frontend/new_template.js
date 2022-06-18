import Cropper from "cropperjs";

$(document).ready(function () {
  var selectedBackground;
  var cropper;
  var base_url = window.location.origin;

  custom_select();
  change_theme();

  function draw_background_images(response, background_url = "", page = 1) {
    var files = response.data.background;
    var html = "";
    var html_cropped = "";
    var background_pagination = response.data.background_pagination;
    $(".background-image-grid").empty();
    $(".background-cropped-image-grid").empty();
    for (var file of files) {
      let isSelected = "";
      if (`${base_url}/share?file=${file.path}` == background_url) {
        isSelected = "selected";
      }
      if (file.cropped) {
        html_cropped += `<div class='grid-item ${isSelected}' data-locked='${file.locked}'>`;
        html_cropped +=
          "<input type='checkbox' class='select-check' checked />";
        html_cropped +=
          "<input class='d-none' data-name='" + file.name.split(".")[0] + "'/>";
        html_cropped += `<img src='${base_url}/share?file=${file.thumbnail}' loading='lazy'/>`;
        html_cropped += `<a class="item-filename" href='${base_url}/share?file=${
          file.path
        }'>${file.name.split(".")[0]}</a>`;
        html_cropped += "<div class='overlay' style='display: none'>";
        html_cropped += `<a class="view-image" href="javascript: void(0);" data-name="${file.name}" data-path="${file.path}">`;
        html_cropped += "<i class='cil-search'></i> View Image</a>";
        html_cropped += "</div></div>";
      } else {
        html += `<div class='grid-item ${isSelected}' data-locked='${file.locked}'>`;
        html += "<input type='checkbox' class='select-check' checked />";
        html +=
          "<input class='d-none' data-name='" + file.name.split(".")[0] + "'/>";
        html += `<img src='${base_url}/share?file=${file.thumbnail}' loading='lazy'/>`;
        html += `<a class="item-filename" href='${base_url}/share?file=${
          file.path
        }'>${file.name.split(".")[0]}</a>`;
        html += "<div class='overlay' style='display: none'>";
        html += `<a class="view-image" href="javascript: void(0);" data-name="${file.name}" data-path="${file.path}">`;
        html += "<i class='cil-search'></i> View Image</a>";
        html += "</div></div>";
      }
    }

    var str = "";
    str += "<div class='grid-item image-none'>";
    str += "<input type='checkbox' class='select-check' checked />";
    str += "<input class='d-none' data-name='none'/>";
    str += `<div class="grey-background"></div>`;
    str += "</div>";

    html_cropped += str;
    html += str;

    var pagination = "";
    pagination += `<div class="row" style="clear: both">`;
    pagination += `<div class="mx-auto pt-3">`;
    pagination += `<nav aria-label="pagination">`;
    pagination += `  <ul class="pagination">`;

    if (page > 1)
      pagination +=
        `    <li class="page-item"><a class="page-link background_page_link" data-page="` +
        (parseInt(page) - 1) +
        `" href="#">Previous</a></li>`;

    for (var j = 1; j <= background_pagination.background_total_pages; j++) {
      pagination +=
        `    <li class="page-item` +
        (background_pagination.background_page == j ? " active" : "") +
        `"><a class="page-link background_page_link" data-page="` +
        j +
        `" href="#">` +
        j +
        `</a></li>`;
    }

    if (page < background_pagination.background_total_pages)
      pagination +=
        `    <li class="page-item"><a class="page-link background_page_link" data-page="` +
        (parseInt(page) + 1) +
        `"href="#">Next</a></li>`;

    pagination += `  </ul>`;
    pagination += `</nav>`;
    pagination += `</div>`;
    pagination += `</div>`;

    html += pagination;

    return [html, html_cropped];
  }

  function draw_stock_images(response, background_url = "", page = 1) {
    var html_stock = "";
    var files = response.data.stock;
    var stock_pagination = response.data.stock_pagination;

    $(".stock-image-grid").empty();

    for (var file of files) {
      let isSelected = "";
      if (`${base_url}/share?file=${file.path}` == background_url) {
        isSelected = "selected";
      }
      html_stock += `<div class='grid-item ${isSelected}'>`;
      html_stock += "<input type='checkbox' class='select-check' checked />";
      html_stock +=
        "<input class='d-none' data-name='" + file.name.split(".")[0] + "'/>";
      html_stock += `<img src='${base_url}/share?file=${file.thumbnail}' loading='lazy'/>`;
      html_stock += `<a class="item-filename" href='${base_url}/share?file=${
        file.path
      }'>${file.name.split(".")[0]}</a>`;
      html_stock += "<div class='overlay' style='display: none'>";
      html_stock += `<a class="view-image" href="javascript: void(0);" data-name="${file.name}" data-path="${file.path}">`;
      html_stock += "<i class='cil-search'></i> View Image</a>";
      html_stock += "</div></div>";
    }
    var str = "";
    str += "<div class='grid-item image-none'>";
    str += "<input type='checkbox' class='select-check' checked />";
    str += "<input class='d-none' data-name='none'/>";
    str += `<div class="grey-background"></div>`;
    str += "</div>";

    html_stock += str;

    var pagination = "";
    pagination += `<div class="row" style="clear: both">`;
    pagination += `<div class="mx-auto pt-3">`;
    pagination += `<nav aria-label="pagination">`;
    pagination += `  <ul class="pagination">`;

    if (page > 1)
      pagination +=
        `    <li class="page-item"><a class="page-link stock_page_link-link" data-page="` +
        (parseInt(page) - 1) +
        `" href="#">Previous</a></li>`;

    for (var j = 1; j <= stock_pagination.stock_total_pages; j++) {
      pagination +=
        `    <li class="page-item` +
        (stock_pagination.stock_page == j ? " active" : "") +
        `"><a class="page-link stock_page_link" data-page="` +
        j +
        `" href="#">` +
        j +
        `</a></li>`;
    }

    if (page < stock_pagination.stock_total_pages)
      pagination +=
        `    <li class="page-item"><a class="page-link stock_page_link" data-page="` +
        (parseInt(page) + 1) +
        `"href="#">Next</a></li>`;

    pagination += `  </ul>`;
    pagination += `</nav>`;
    pagination += `</div>`;
    pagination += `</div>`;

    html_stock += pagination;

    return html_stock;
  }

  function update_stock_images(page = 1) {
    let template_id = $("input[name='output_dimensions']").val();
    if (!template_id) {
      template_id = $("input[name='template_id']").val();
    }
    $(".stock-image-grid .grid-item ").each(function (index) {
      $(this).empty();
      $(this).html(`
        <div class="spinner-grow text-light" style="width: 7rem; height: 7rem;" role="status">
        <span class="sr-only">Loading...</span>
        </div>`);
    });
    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: $('input[name="customer"]').val(),
        theme: $("select[name='theme']").val(),
        template: template_id,
        stock_page: page,
        get_only: "stock",
      },
    })
      .then(function (response) {
        var html_stock = draw_stock_images(response, "", page);
        $(".stock-image-grid").append(html_stock);
      })
      .catch(function (response) {
        //showError([response]);
        console.log(response);
      });
  }

  function update_background_images(page = 1) {
    let template_id = $("input[name='output_dimensions']").val();
    if (!template_id) {
      template_id = $("input[name='template_id']").val();
    }
    $(".background-image-grid .grid-item ").each(function (index) {
      $(this).empty();
      $(this).html(`
        <div class="spinner-grow text-light" style="width: 7rem; height: 7rem;" role="status">
        <span class="sr-only">Loading...</span>
        </div>`);
    });
    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: $('input[name="customer"]').val(),
        theme: $("select[name='theme']").val(),
        template: template_id,
        stock_page: page,
        get_only: "background",
      },
    })
      .then(function (response) {
        var html_background = draw_background_images(response, "", page);
        $(".background-image-grid").append(html_background[0]);
        $(".background-cropped-image-grid").append(html_background[1]);
      })
      .catch(function (response) {
        //showError([response]);
        console.log(response);
      });
  }

  $("#nav-stock").on("click", ".stock_page_link", function (e) {
    e.preventDefault();
    update_stock_images($(this).data("page"));
  });

  $(".background-image-grid").on(
    "click",
    ".background_page_link",
    function (e) {
      e.preventDefault();
      update_background_images($(this).data("page"));
    }
  );

  $(".select-bkimg").on("click", function (e) {
    selectedBackground = $(this).parent();
    let background_url = selectedBackground
      .find(".background-preview")
      .attr("src");

    let template_id = $("input[name='output_dimensions']").val();
    if (!template_id) {
      template_id = $("input[name='template_id']").val();
    }

    $("#selectBkImgModal").data("type", $(this).data("type"));
    let _this = this;
    $(_this).prop("disabled", true).html($(_this).data("loading-text"));

    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: $('input[name="customer"]').val(),
        theme: $("select[name='theme']").val(),
        template: template_id,
        stock_page: 1,
        background_page: 1,
      },
    })
      .then(function (response) {
        $(".full-size-image").hide();
        $(".background-wrapper").show();
        $("#selectBkImgModal #submit").hide();

        var background = draw_background_images(response, background_url);
        var html = background[0];
        var html_cropped = background[1];

        var html_stock = draw_stock_images(response, background_url);

        $(".background-image-grid").empty();
        $(".stock-image-grid").empty();
        $(".background-cropped-image-grid").empty();
        $(".background-image-grid").append(html);
        $(".stock-image-grid").append(html_stock);
        $(".background-cropped-image-grid").append(html_cropped);
        $("#selectBkImgModal").modal();
        $(_this).prop("disabled", false).html($(_this).data("text"));
      })
      .catch(function (response) {
        // showError([response]);
        console.log(response);
      });
  });

  $(document).on(
    "click",
    "#selectBkImgModal .grid-item a.view-image",
    function (e) {
      e.preventDefault();
      e.stopPropagation();
      var base_url = window.location.origin;
      var path = $(this).data("path");
      var name = $(this).data("name");

      $(".background-wrapper").hide();
      $("#selectBkImgModal .full-size-image").empty();
      $("#selectBkImgModal .full-size-image").append(
        $(`<p class="notification" style="display: none;">Image Save!</p>`)
      );
      $("#selectBkImgModal .full-size-image").append(
        $(`<a href="#" class="btn-back-grid">Back</a>`)
      );
      $("#selectBkImgModal .full-size-image").append(
        $(
          `<img src="${base_url}/share?file=${path}" class="product-image" id="full-size-image" />`
        )
      );
      $("#selectBkImgModal #full-size-image").data("name", name);
      $("#selectBkImgModal #full-size-image").data("type", "");
      $("#selectBkImgModal #full-size-image").data("path", path);
      $("#selectBkImgModal .full-size-image").append(
        $(`
                <div class="overflow-hidden">
                    <div class="cropped-image-size float-left">
                        <input type="number" id="crop_width" style="width: 64px; margin-top: 2px;" />
                        x
                        <input type="number" id="crop_height" style="width: 64px; margin-top: 2px;" />
                    </div>
                    <span class="product-image-description float-right">${name}</span>
                </div>`)
      );

      $("#selectBkImgModal .full-size-image").append(
        $(`
          <div class="editing-tool row mt-2">
            <div class="form-group col-md-4">
              <label>Width</label>
              <input type="number" id="resize_width" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-4">
              <label>Height</label>
              <input type="number" id="resize_height" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-4">
              <label>Rotate</label>
              <input type="number" id="rotate_angle" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-4">
              <input type="checkbox" id="fix_ratio" />
              <label for="fix_ratio">Maintain aspect ratio</label>
            </div>
          </div>
          <div class="button-group text-right mt-2">
              <a href="#" id="save_edited_image">Save</a>
              <a href="#" id="cancel_edited_image">Cancel</a>
          </div>
        `)
      );
      $("#selectBkImgModal .full-size-image").show();

      // Crop, Resize, Rotate
      const image = $("#selectBkImgModal #full-size-image")[0];
      cropper = new Cropper(image, {
        autoCropArea: 1,
        zoomable: false,
        ready() {
          var origin_width = $("#selectBkImgModal #full-size-image")[0].width;
          var origin_height = $("#selectBkImgModal #full-size-image")[0].height;
          $("#resize_width").val(origin_width);
          $("#resize_height").val(origin_height);
        },
        crop(event) {
          $("#selectBkImgModal .cropped-image-size #crop_width").val(
            Math.round(event.detail.width)
          );
          $("#selectBkImgModal .cropped-image-size #crop_height").val(
            Math.round(event.detail.height)
          );
        },
      });
    }
  );

  $(document).on("change", "#fix_ratio", function () {
    if ($(this).prop("checked")) {
      let w = parseInt($("#resize_width").val());
      let h = parseInt($("#resize_height").val());
      let ratio = w / h;
      cropper.setAspectRatio(ratio);
    } else {
      cropper.setAspectRatio(NaN);
    }
  });

  $(document).on(
    "change",
    "#selectBkImgModal #crop_width, #selectBkImgModal #crop_height",
    function () {
      var cropBoxData = cropper.getCropBoxData();
      var cropCanvasData = cropper.getCanvasData();
      var crop_width = $("#selectBkImgModal #crop_width").val();
      var crop_height = $("#selectBkImgModal #crop_height").val();
      cropper.setCropBoxData({
        left: cropBoxData.left,
        top: cropBoxData.top,
        width:
          (cropCanvasData.width * crop_width) / cropCanvasData.naturalWidth,
        height:
          (cropCanvasData.height * crop_height) / cropCanvasData.naturalHeight,
      });
    }
  );

  $(document).on("click", "#selectBkImgModal #save_edited_image", function (e) {
    e.preventDefault();
    $("#selectBkImgModal #save_edited_image").text("Saving");
    $("#selectBkImgModal #save_edited_image").removeAttr("href");
    var srcUrl = $("#selectBkImgModal #full-size-image").attr("src");
    srcUrl = srcUrl.split("=");
    var path = srcUrl[1];
    var filename = path.split("/").slice(-1)[0];
    path = path.split("/");
    path.pop();
    var ext = filename.split(".").slice(-1)[0];
    var name = filename.split(".")[0];
    cropper.getCroppedCanvas().toBlob(
      (blob) => {
        const formData = new FormData();

        // Pass the image file name as the third parameter if necessary.
        formData.append("croppedImage", blob);
        formData.append(
          "filename",
          path.join("/") + "/" + name + "_cropped." + ext
        );

        // Use `jQuery.ajax` method for example
        axios({
          method: "POST",
          url: "/banner/upload_cropped_bk_image",
          data: formData,
          headers: {
            "Content-Type": "multipart/form-data",
          },
        }).then(function (response) {
          $("#selectBkImgModal #submit").show();
          $("#selectBkImgModal #save_edited_image").text("Save");
          $("#selectBkImgModal #save_edited_image").prop("href", "#");
          $(".notification").show();
          setTimeout(() => {
            $(".notification").hide();
          }, 3000);
        });
      } /*, 'image/png' */
    );
  });

  $(document).on(
    "click",
    "#selectBkImgModal #cancel_edited_image",
    function (e) {
      e.preventDefault();
      cropper.reset();
    }
  );

  $(document).on("change", "#selectBkImgModal #rotate_angle", function () {
    var angle = parseInt($(this).val());
    cropper.rotateTo(angle);
  });

  $(document).on(
    "change",
    "#selectBkImgModal #resize_width, #selectBkImgModal #resize_height",
    function () {
      var origin_width = $("#selectBkImgModal #full-size-image")[0].width;
      var origin_height = $("#selectBkImgModal #full-size-image")[0].height;
      var w = parseInt($("#selectBkImgModal #resize_width").val());
      var h = parseInt($("#selectBkImgModal #resize_height").val());
      var ratio = origin_width / origin_height;
      if ($("#fix_ratio").prop("checked")) {
        if ($(this).attr("id") == "resize_width") {
          h = w / ratio;
          $("#selectBkImgModal #resize_height").val(h.toFixed(1));
        } else {
          w = h * ratio;
          $("#selectBkImgModal #resize_width").val(w.toFixed(1));
        }
      }
      cropper.scale(w / origin_width, h / origin_height);
    }
  );

  $(document).on("click", "#selectBkImgModal .grid-item", function (e) {
    $("#selectBkImgModal .grid-item").removeClass("selected");
    $(this).addClass("selected");
    $("#selectBkImgModal #submit").show();
    if ($("#nav-profile-tab").hasClass("active")) {
      if (!$(this).hasClass("image-none")) {
        $("#selectBkImgModal #delete_cropped").show();
      } else {
        $("#selectBkImgModal #delete_cropped").hide();
      }
    } else {
      var locked = $(this).data("locked");
      if (!$(this).hasClass("image-none") && locked == 0) {
        $("#selectBkImgModal #delete").show();
      } else {
        $("#selectBkImgModal #delete").hide();
      }
    }
  });

  $(document).on("click", "#selectBkImgModal .btn-back-grid", function () {
    $(".full-size-image").hide();
    $(".background-wrapper").show();
  });

  $("#theme").on("change", change_theme);

  function change_theme() {
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: $('input[name="customer"]').val(),
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      var colors = response.data.colors;
      $("select[name='background_color[]']").empty();
      $(".select-items").empty();
      colors.forEach((c) => {
        var cc = c.list.map((x) => x.value).join(",");
        var option = `<option value="${cc}">${c.name}</option>`;
        var c_arr = cc.split(",");
        $("select[name='background_color[]']").append(option);
        if (c_arr[0] == "solid") {
          option = `<div class="option-item">
                                    <span>${c.name}</span>
                                    <span class="color-pane" style="background: ${c_arr[1]}"></span>
                                </div>
                        `;
        } else {
          option = `<div class="option-item">
                                    <span>${c.name}</span>
                                    <span class="color-pane" style="background: ${c_arr[1]}; background: linear-gradient(90deg, ${c_arr[1]} 0%, ${c_arr[2]} 100%);"></span>
                                </div>
                        `;
        }
        $(".select-items").append(option);
      });
      if (colors.length) {
        $(".select-selected").text(colors[0].name);
      }
    });
  }

  $("#selectBkImgModal #submit").on("click", function () {
    var base_url = window.location.origin;
    var path = $("#selectBkImgModal .grid-item.selected .overlay a").data(
      "path"
    );
    path = `${base_url}/share?file=${path}`;
    if ($("#selectBkImgModal .full-size-image").is(":visible")) {
      var img = $("#full-size-image").attr("src");
      var arr = img.split("/");
      var filename = arr[arr.length - 1];
      filename = filename.split(".");
      filename[filename.length - 2] =
        filename[filename.length - 2] + "_cropped";
      filename = filename.join(".");
      arr[arr.length - 1] = filename;
      path = arr.join("/");
    }
    if (
      path &&
      !$("#selectBkImgModal .grid-item.selected").hasClass("image-none")
    ) {
      var html = "";
      html += `<img class="background-preview" src="${path}" />`;
      if ($("#selectBkImgModal").data("type") == "Image From Background") {
        html += `<input type="hidden" name="img_from_bk[]" value="${path}" />`;
      } else {
        html += `<input type="hidden" name="background[]" value="${path}" />`;
      }
      selectedBackground.find(".selected-image").empty();
      selectedBackground.find(".selected-image").append(html);
    } else {
      var html = "";
      if ($("#selectBkImgModal").data("type") == "Image From Background") {
        html = `<input type="hidden" name="img_from_bk[]" value="" />`;
      } else {
        html = `<input type="hidden" name="background[]" value="" />`;
      }
      selectedBackground.find(".selected-image").empty();
      selectedBackground.find(".selected-image").append(html);
    }
  });

  $("#selectBkImgModal #delete_cropped").on("click", function () {
    var path = $("#selectBkImgModal .grid-item.selected")
      .find(".view-image")
      .data("path");
    const formData = new FormData();
    formData.append("path", path);
    axios({
      method: "POST",
      url: "/banner/delete_cropped_bk_image",
      data: formData,
    }).then(function (response) {
      $("#selectBkImgModal .grid-item.selected").remove();
      $("#selectBkImgModal #delete_cropped").hide();
    });
  });

  $("#selectBkImgModal #delete").on("click", function () {
    var path = $("#selectBkImgModal .grid-item.selected")
      .find(".view-image")
      .data("path");
    var name = $("#selectBkImgModal .grid-item.selected")
      .find(".view-image")
      .data("name");
    const formData = new FormData();
    formData.append("name", name);
    formData.append("path", path);
    axios({
      method: "POST",
      url: "/banner/delete_bk_image",
      data: formData,
    }).then(function (response) {
      $("#selectBkImgModal .grid-item.selected").remove();
      $("#selectBkImgModal #delete").hide();
    });
  });

  $("input[name='new-background-image']").on("change", function () {
    var files = $(this).prop("files");
    if (files.length) {
      $("#selectBkImgModal #upload").show();
    } else {
      $("#selectBkImgModal #upload").hide();
    }
  });

  $("#selectBkImgModal .fileinput-remove-button").on("click", function () {
    $("#selectBkImgModal #upload").hide();
  });

  $("#selectBkImgModal #upload").on("click", function () {
    var img = document.getElementsByName("new-background-image")[0];
    const formData = new FormData();
    var customer_id = $("input[name='customer_id']").val();
    var theme_id = $("#theme").val();
    var template_id = $("input[name='output_dimensions']").val();
    var uploadBtn = $(this);
    uploadBtn.prop("disabled", true);
    for (var i = 0; i < img.files.length; i++) {
      formData.append("file" + i, img.files[i]);
    }
    formData.append("customer_id", customer_id);
    formData.append("theme_id", theme_id);
    formData.append("template_id", template_id);
    axios({
      method: "POST",
      url: "/banner/upload_bk_image",
      data: formData,
      dataType: "JSON",
    }).then(function (response) {
      var { data } = response;
      data.forEach((p) => {
        var html = "";
        html += `<div class='grid-item' data-locked='${p.locked}'>`;
        html += "<input type='checkbox' class='select-check' checked />";
        html +=
          "<input class='d-none' data-name='" + p.name.split(".")[0] + "'/>";
        html += `<img src='${base_url}/share?file=${p.thumbnail}' loading='lazy'/>`;
        html += `<a class="item-filename" href='${base_url}/share?file=${
          p.path
        }'>${p.name.split(".")[0]}</a>`;
        html += "<div class='overlay' style='display: none'>";
        html += `<a class="view-image" href="javascript: void(0);" data-name="${p.name}" data-path="${p.path}">`;
        html += "<i class='cil-search'></i> View Image</a>";
        html += "</div></div>";
        $(".background-image-grid .image-none").before(html);
        $("#selectBkImgModal .fileinput-remove-button").trigger("click");
      });
      uploadBtn.prop("disabled", false);
    });
  });

  $("#download-xlsx").on("click", function () {
    rememberTemplateSettings();
    $("#downloadXlsxModal").modal("show");
  });

  $("#download-xlsx-output").on("click", function () {
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";
    const text_data = {};
    if (localStorage.getItem(key)) {
      const template_settings = JSON.parse(localStorage.getItem(key));
      for (const template in template_settings) {
        if ($("#" + template).is(":checked")) {
          text_data[template] = {};
          const params = new URLSearchParams(template_settings[template]);
          params.forEach(function (v, k) {
            if (
              (k.startsWith("text_") || k.startsWith("static_text_")) &&
              !k.endsWith("offset_x") &&
              !k.endsWith("offset_y")
            ) {
              text_data[template][k] = v;
            }
          });
        }
      }
      $('input[name="template_settings"]').val(JSON.stringify(text_data));
    }
    $("#download-xlsx-form").submit();
  });

  $("#upload-from-web").on("click", function (e) {
    e.preventDefault();
    $("#webUploadModal").modal();
  });

  $("#download_from_web").on("click", function (e) {
    e.preventDefault();
    axios({
      method: "post",
      url: "/file/uploadimg/image_from_web",
      data: {
        background_remove: $("#background_remove").prop("checked"),
        upload_images_url: $('textarea[name="upload_images_url"]').val(),
      },
    }).then(function ({ data }) {
      $("textarea[name='upload_images_url']").val("");
      let filenames = data.map((fn) => {
        let arr = fn.split(".");
        arr.pop();
        return arr.join(".");
      });
      $("input[name='file_ids']").val(filenames.join(" "));
      $("input[name='file_ids']").trigger("change");
    });
  });

  var waitForEl = function (name, callback, count) {
    if (
      jQuery(
        $(`[name='${name}']`)
          .parent()
          .parent()
          .parent()
          .find(".file-caption-name")
      ).length
    ) {
      callback();
    } else {
      setTimeout(function () {
        if (!count) {
          count = 0;
        }
        count++;
        // console.log("count: " + count + name);
        if (count < 10) {
          waitForEl(name, callback, count);
        } else {
          return;
        }
      }, 1000);
    }
  };

  function syncRememberedSettingWithEdited(cts) {
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";
    const settings = JSON.parse(localStorage.getItem(key));
    const current_template_id = $("#template_id").val();
    var current_template_edited =
      settings["template_" + current_template_id + "_edited"];
    var found = false;
    if (current_template_edited) {
      var current_settings = JSON.parse(current_template_edited);
      Object.entries(current_settings).forEach((input) => {
        found = false;
        const [index, value] = input;
        cts.forEach((current_element) => {
          if (!found && current_element.name == index) {
            found = true;
            // store the value
            current_element.value = value;
          }
        });
        if (!found) {
          var data = {};
          data["name"] = index;
          data["value"] = value;
          cts.push(data);
        }
      });
    }
    return cts;
  }

  const readRememberedSettings = () => {
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";

    // new logic to this behaviour
    /* 
     Use Placeholders, if any
     If field is edited, remember it for that template; carry it over to next, overriding Placeholder, if any
     Repeat, carrying over edits that are in the 'edit list'; overriding default (if any) on next template
     Edit list maintains most current values only. So if Field1 was edited in T1, and then edited in T2, only T2 value will be carried over to T3.
    */

    if (!localStorage.getItem(key)) {
      const json = {};
      $(".templates-carousel-hidden .slide-item img").each(function () {
        const template_id = $(this).attr("data-value");
        json["template_" + template_id] = "";
      });
      localStorage.setItem(key, JSON.stringify(json));
    } else {
      var settings = JSON.parse(localStorage.getItem(key));
      const last_template_id = settings["last_template"];
      const current_template_id = $("#template_id").val();
      const current_template_settings =
        settings["template_" + current_template_id];

      // copy the values alreadystored for the current template
      if (current_template_settings) {
        var cts = JSON.parse(current_template_settings);
        cts = syncRememberedSettingWithEdited(cts);
        settings["template_" + current_template_id] = JSON.stringify(cts);
        cts.forEach((current_element) => {
          // check for checkboxes
          if (
            $(`[name='${current_element.name}']`).attr("type") == "checkbox"
          ) {
            if (current_element.value == "on")
              $(`[name='${current_element.name}']`).prop("checked", true);
            else $(`[name='${current_element.name}']`).prop("checked", false);
          }

          if ($(`[name='${current_element.name}']`).attr("type") == "file") {
            waitForEl(current_element.name, function () {
              jQuery(
                $(`[name='${current_element.name}']`)
                  .parent()
                  .parent()
                  .parent()
                  .find(".file-caption-name")
              ).attr("value", current_element.value);
              jQuery(
                $(`[name='${current_element.name}']`)
                  .parent()
                  .parent()
                  .parent()
                  .find(".file-caption-name")
              ).addClass("is-valid");
            });
          } else {
            // check for arrays of items (for background template image for example)
            var input_name = current_element.name;
            if (input_name.match(/(.+)\[(.+)\]/)) {
              var name = input_name.match(/(.+)\[(.+)\]/)[1] + "[]";
              var idx = input_name.match(/(.+)\[(.+)\]/)[2];
              $(`input[name='${name}']`).eq(idx).val(current_element.value);

              if (name == "background[]") {
                var selectedBackground = $(`input[name='${name}']`).eq(idx);
                var container = selectedBackground.parent();
                container.empty();
                var html = "";
                html += `<img class="background-preview" src="${current_element.value}" /></img>`;
                html += `<input type="hidden" name="background[]" value="${current_element.value}" />`;
                container.append(html);
              }
            } else {
              // store the value in the input
              $(`[name='${current_element.name}']`).val(current_element.value);
            }
          }
        });
      }

      if (last_template_id != current_template_id) {
        // check if last template has edited values that are not already edited in this template

        const last_template_edited =
          settings["template_" + last_template_id + "_edited"];
        var current_template_edited =
          settings["template_" + current_template_id + "_edited"];
        var current_template_files =
          settings["template_" + current_template_id + "_files"];

        if (last_template_edited) {
          var last_settings = JSON.parse(last_template_edited);

          // if nothing has been altered yet.. initialize

          if (!current_template_edited) {
            var data = {};
            settings["template_" + current_template_id + "_edited"] =
              JSON.stringify(data);
            current_template_edited =
              settings["template_" + current_template_id + "_edited"];
          }

          if (!current_template_files) {
            var data_files = {};
            settings["template_" + current_template_id + "_files"] =
              JSON.stringify(data_files);
            current_template_files =
              settings["template_" + current_template_id + "_files"];
          }

          var current_settings = JSON.parse(current_template_edited);
          var current_files = JSON.parse(current_template_files);

          Object.entries(last_settings).forEach((input) => {
            const [index, value] = input;
            var save_this_value = value;

            // if the input exists and it was not edited already for the current template
            if (
              $(`[name='${index.replace(/\[.*?\]/, "[]")}']`).length !== 0 &&
              current_settings[index] === undefined
            ) {
              // check for checkboxes
              if ($(`[name='${index}']`).attr("type") == "checkbox") {
                if (value == "on") $(`[name='${index}']`).prop("checked", true);
                else $(`[name='${index}']`).prop("checked", false);
              }

              if ($(`[name='${index}']`).attr("type") == "file") {
                waitForEl(index, function () {
                  jQuery(
                    $(`[name='${index}']`)
                      .parent()
                      .parent()
                      .parent()
                      .find(".file-caption-name")
                  ).attr("value", value);
                  jQuery(
                    $(`[name='${index}']`)
                      .parent()
                      .parent()
                      .parent()
                      .find(".file-caption-name")
                  ).addClass("is-valid");
                });

                current_files[index] = value;
              } else {
                var input_name = index;
                if (input_name.match(/(.+)\[(.+)\]/)) {
                  var name = input_name.match(/(.+)\[(.+)\]/)[1] + "[]";
                  var idx = input_name.match(/(.+)\[(.+)\]/)[2];
                  $(`input[name='${name}']`).eq(idx).val(value);

                  if (name == "background[]") {
                    var selectedBackground = $(`input[name='${name}']`).eq(idx);
                    var container = selectedBackground.parent();
                    container.empty();
                    var html = "";
                    html += `<img class="background-preview" src="${value}" /></img>`;
                    html += `<input type="hidden" name="background[]" value="${value}" />`;
                    container.append(html);
                  }
                } else {
                  // check if should carry over font size
                  if (index.indexOf("_fontsize") >= 0) {
                    var text_inputname = index.replace("_fontsize", "");
                    var text_carryover_input =
                      text_inputname + "_carryfontsize";

                    if (
                      !last_settings[text_carryover_input] ||
                      last_settings[text_carryover_input] == "off"
                    ) {
                      save_this_value = $(`[name='${index}']`).val();
                      $(`[name='${index}']`).val(save_this_value);
                    } else {
                      //set the value
                      $(`[name='${index}']`).val(value);
                    }
                  } else {
                    //set the value
                    $(`[name='${index}']`).val(value);
                  }
                }
              }

              //mark as edited
              current_settings[index] = save_this_value;
            }
          });

          // save the changes
          settings["template_" + current_template_id + "_edited"] =
            JSON.stringify(current_settings);
          settings["template_" + current_template_id + "_files"] =
            JSON.stringify(current_files);
        }
      }

      /* special treatment for the file_ids input when they selected one 
      from the file browser */

      if (
        $('input[name="file_ids"]').length > 0 &&
        localStorage.getItem("selected_files") !== null
      ) {
        var selected_files = localStorage.getItem("selected_files");
        $('input[name="file_ids"]').val(selected_files);

        current_template_edited =
          settings["template_" + current_template_id + "_edited"];

        //  initialize in case this was empty
        if (!current_template_edited) {
          var data = {};
          settings["template_" + current_template_id + "_edited"] =
            JSON.stringify(data);
          current_template_edited =
            settings["template_" + current_template_id + "_edited"];
        }

        current_settings = JSON.parse(current_template_edited);

        current_settings["file_ids"] = selected_files;

        // save the changes
        settings["template_" + current_template_id + "_edited"] =
          JSON.stringify(current_settings);

        // remove, we are just using this once.
        localStorage.removeItem("selected_files");
      }

      // save in case there were changes;
      localStorage.setItem(key, JSON.stringify(settings));

      // check if the last template had similar fields and if not set already on current template use that value
      // this mostly to initialize the dimensions of the images

      if (last_template_id != current_template_id) {
        const last_template_settings = settings["template_" + last_template_id];

        if (last_template_settings) {
          var ts = JSON.parse(last_template_settings);

          ts.forEach((element) => {
            if (
              $(`[name='${element.name}']`).length !== 0 &&
              element.name.includes("upload_image_") &&
              current_settings[element.name] === undefined &&
              $(`[name='${element.name}']`).val() ==
                $(`[name='${element.name}']`).data("default")
            ) {
              $(`[name='${element.name}']`).val(element.value);
            }
          });
        }
      }
    }
  };

  $("#adForm").on("change", "select", function () {
    var the_input = $(this);
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";

    if (!localStorage.getItem(key)) {
      const json = {};
      $(".templates-carousel-hidden .slide-item img").each(function () {
        const template_id = $(this).attr("data-value");
        json["template_" + template_id] = "";
      });
      localStorage.setItem(key, JSON.stringify(json));
    }
    // const template_id = $('input[name="template_id"]').val();
    const settings = JSON.parse(localStorage.getItem(key));
    const current_template_id = $("#template_id").val();
    const current_template_settings =
      settings["template_" + current_template_id + "_edited"];

    if (current_template_settings)
      var data = JSON.parse(current_template_settings);
    else var data = {};

    // check for checkboxes

    data[the_input.attr("name")] = the_input.val();

    settings["template_" + current_template_id + "_edited"] =
      JSON.stringify(data);
    localStorage.setItem(key, JSON.stringify(settings));
  });

  $("#adForm").on("change", "input", function () {
    var the_input = $(this);
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";

    if (!localStorage.getItem(key)) {
      const json = {};
      $(".templates-carousel-hidden .slide-item img").each(function () {
        const template_id = $(this).attr("data-value");
        json["template_" + template_id] = "";
      });
      localStorage.setItem(key, JSON.stringify(json));
    }
    // const template_id = $('input[name="template_id"]').val();
    const settings = JSON.parse(localStorage.getItem(key));
    const current_template_id = $("#template_id").val();
    const current_template_settings =
      settings["template_" + current_template_id + "_edited"];
    const current_template_files =
      settings["template_" + current_template_id + "_files"];

    if (current_template_settings)
      var data = JSON.parse(current_template_settings);
    else var data = {};

    if (current_template_files)
      var data_files = JSON.parse(current_template_files);
    else var data_files = {};

    // check for checkboxes
    if (the_input.attr("type") == "checkbox") {
      if (the_input.is(":checked")) data[the_input.attr("name")] = "on";
      else data[the_input.attr("name")] = "off";
    } else {
      if (the_input.attr("type") == "file") {
        //var file = the_input.prop('files');
        $("#" + the_input.attr("name") + "_loading").removeClass("d-none");
        var filename = the_input.val().split("\\").pop();
        data[the_input.attr("name")] = filename;
        data_files[the_input.attr("name")] = filename;
      } else {
        data[the_input.attr("name")] = the_input.val();
      }
    }
    settings["template_" + current_template_id + "_edited"] =
      JSON.stringify(data);
    settings["template_" + current_template_id + "_files"] =
      JSON.stringify(data_files);
    localStorage.setItem(key, JSON.stringify(settings));
  });

  function custom_select() {
    var x, i, l, selElmnt, a;

    x = document.getElementsByClassName("select-custom");
    l = x.length;
    for (i = 0; i < l; i++) {
      selElmnt = x[i].getElementsByTagName("select")[0];
      /*for each element, create a new DIV that will act as the selected item:*/
      a = document.createElement("DIV");
      a.setAttribute("class", "form-control select-selected");
      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
      x[i].appendChild(a);
      a.addEventListener("click", function (e) {
        e.stopPropagation();
        closeAllSelect(this);
        $(this).prev().toggleClass("select-hide");
        this.classList.toggle("select-arrow-active");
      });
    }

    /*if the user clicks anywhere outside the select box,
        then close all select boxes:*/
    document.addEventListener("click", closeAllSelect);
  }

  function closeAllSelect(elmnt) {
    /*a function that will close all select boxes in the document,
        except the current select box:*/
    var x,
      y,
      i,
      xl,
      yl,
      arrNo = [];
    x = document.getElementsByClassName("select-items");
    y = document.getElementsByClassName("select-selected");
    xl = x.length;
    yl = y.length;
    for (i = 0; i < yl; i++) {
      if (elmnt == y[i]) {
        arrNo.push(i);
      } else {
        y[i].classList.remove("select-arrow-active");
      }
    }
    for (i = 0; i < xl; i++) {
      if (arrNo.indexOf(i)) {
        x[i].classList.add("select-hide");
      }
    }
  }

  $(".select-items").on("click", ".option-item", function () {
    var name = $(this).text().trim();
    var select = $(this).closest(".select-custom");
    select.find(".select-selected").text(name);
    select.find(".select-items .option-item").removeClass("same-as-selected");
    $(this).addClass("same-as-selected");
    var index = select.find(".select-items .option-item").index($(this));
    select.find("select option").eq(index).prop("selected", true);
    $('select[name="background_color[]"]').trigger("change");
  });
  readRememberedSettings();
});
