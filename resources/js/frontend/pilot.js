$(document).ready(function () {
  $(".form-control-file").fileinput({
    showUpload: false,
    previewFileType: "any",
  });

  if ($("#logo_saved").val() != "") {
    $(".file-caption-name").val("logo.png");
  }

  $("input[name='logo']").on("change", function () {
    $("#logo_saved").val("");
  });

  $(".select-bkimg").on("click", function (e) {
    e.preventDefault();
    $(".selected-image").empty();
    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: "pilot",
        theme: $("select[name='theme']").val(),
        template: $("input[name='output_dimensions']").val(),
      },
    })
      .then(function (response) {
        var files = response.data.background;
        $(".full-size-image").hide();
        $(".background-image-grid").show();
        $("#selectBkImgModal #submit").hide();

        var html = "";
        var base_url = window.location.origin;
        for (var file of files) {
          html += "<div class='grid-item'>";
          html += "<input type='checkbox' class='select-check' checked />";
          html +=
            "<input class='d-none' data-name='" +
            file.name.split(".")[0] +
            "'/>";
          html += `<img src='${base_url}/share?file=${file.thumbnail}' loading='lazy'/>`;
          html += "<div class='overlay' style='display: none'>";
          html += `<a href="javascript: void(0);" data-name="${file.name}" data-path="${file.path}">`;
          html += "<i class='cil-search'></i> View Image</a>";
          html += "</div></div>";
        }
        $(".background-image-grid").empty();
        $(".background-image-grid").append(html);
        $("#selectBkImgModal").modal();
      })
      .catch(function (response) {
        showError([response]);
      });
  });

  $(document).on("click", "#selectBkImgModal .grid-item a", function (e) {
    e.preventDefault();
    e.stopPropagation();
    var base_url = window.location.origin;
    var path = $(this).data("path");
    var name = $(this).data("name");

    $(".background-image-grid").hide();
    $(".full-size-image").empty();
    $(".full-size-image").append(
      $(`<a href="#" class="btn-back-grid">Back</a>`)
    );
    $(".full-size-image").append(
      $(
        `<img src="${base_url}/share?file=${path}" class="product-image" id="full-size-image" />`
      )
    );
    $("#full-size-image").data("name", name);
    $("#full-size-image").data("type", "");
    $("#full-size-image").data("path", path);
    $(".full-size-image").append(
      $(`<span class="product-image-description float-right">${name}</span>`)
    );
    $(".full-size-image").show();
  });

  $(document).on("click", "#selectBkImgModal .grid-item", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#selectBkImgModal .grid-item").removeClass("selected");
    $(this).addClass("selected");
    $("#selectBkImgModal #submit").show();
  });

  $(document).on("click", "#selectBkImgModal .btn-back-grid", function () {
    $(".full-size-image").hide();
    $(".background-image-grid").show();
  });

  $("#selectBkImgModal #submit").on("click", function () {
    var base_url = window.location.origin;
    var name = $("#selectBkImgModal .grid-item.selected .overlay a").data(
      "name"
    );
    var path = $("#selectBkImgModal .grid-item.selected .overlay a").data(
      "path"
    );
    var html = "";
    html += `<img class="background-preview" src="${base_url}/share?file=${path}" />`;
    // html += `<p class="background-name">${name}</p>`;
    html += `<input type="hidden" name="background" value="${base_url}/share?file=${path}" />`;
    $(".selected-image").empty();
    $(".selected-image").append(html);
  });

  $("#background_color").on("change", function () {
    var background_color = $(this).val();
    $(".background-color-preview").css("background", background_color);
  });

  $("#background_type").on("change", function () {
    var background_type = $(this).val();
    if (background_type != "product_image") {
      $(".background-image-select").show();
      $(".product-image-select").hide();
    } else {
      $(".background-image-select").hide();
      $(".product-image-select").show();
    }
  });

  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img",
    function (e) {
      var template = $("input[name='output_dimensions']").val();
      template = parseInt(template);
      if (isNaN(template)) {
        template = 0;
      }
      if (template == 0) {
        $(".text2-row").show();
      } else if (template == 1) {
        $(".text2-row").hide();
      } else if (template == 2) {
        $(".text2-row").hide();
      }
    }
  );
});
