$(document).ready(function () {
  var GTINs = {
    0: "",
    1: "",
    2: "",
    3: "",
    4: "",
  };

  changeTemplate(false);
  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img",
    function () {
      changeTemplate(true);
    }
  );

  function changeTemplate(remember) {
    var template = $("input[name=output_dimensions]").val();
    if (template == 3) {
      $(".subheadline").hide();
    } else if (template == 4) {
      $(".subheadline").show();
      $(".subheadline").eq(1).hide();
    } else {
      $(".subheadline").show();
    }

    if (remember) {
      if (localStorage.getItem("walmart_GTINs") == null) {
        var file_ids = $("input[name=file_ids]").val();
        if (file_ids) {
          for (const key in GTINs) {
            if (!GTINs[key]) {
              GTINs[key] = file_ids;
            }
          }
        }

        localStorage.setItem("walmart_GTINs", JSON.stringify(GTINs));
      }

      if (GTINs[template]) {
        $("input[name=file_ids]").val(GTINs[template]);
      }
    } else {
      localStorage.removeItem("walmart_GTINs");
    }
  }

  $("input[name=file_ids]").on("input", function () {
    var template = $("input[name=output_dimensions]").val();
    var file_ids = $(this).val();
    GTINs[template] = file_ids;

    if (localStorage.getItem("walmart_GTINs") != null) {
      localStorage.setItem("walmart_GTINs", JSON.stringify(GTINs));
    }
  });

  $("input[name='headline1'], input[name='headline2']").on(
    "change",
    function () {
      var template = $("input[name=output_dimensions]").val();
      var headline1 = $("input[name='headline1']").val();
      var headline2 = $("input[name='headline2']").val();
      if (
        (template == 4 && headline1 != "" && headline2 != "") ||
        template == 3
      ) {
        $(".subheadline").hide();
      } else {
        $(".subheadline").eq(0).show();
      }
    }
  );

  $(".select-bkimg").on("click", function (e) {
    e.preventDefault();
    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: "walmart",
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
});
