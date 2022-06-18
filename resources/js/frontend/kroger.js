$(document).ready(function () {
  change_theme();
  changeMessageOptions();

  $("#theme").on("change", change_theme);

  $("#circle_text_color").on("change", chagne_circle);

  function chagne_circle() {
    var colors = $("#circle_text_color").val().split(",");
    $("#circle_color").val(colors[0]);
    $("#text1_color").val(colors[1]);
    $("#text2_color").val(colors[2]);
    $("#text3_color").val(colors[3]);
    $(".circle-text-color-picker .circle-color").css("background", colors[0]);
    $("#text_color1").css("background", colors[1]);
    $("#text_color2").css("background", colors[2]);
    $("#text_color3").css("background", colors[3]);
  }

  $("#message_options").on("change", changeMessageOptions);

  function change_theme() {
    axios({
      method: "post",
      url: "/banner/kroger_template_settings",
      data: {
        customer: "kroger",
        color_scheme: $("#theme").val(),
      },
    }).then(function (response) {
      var colors = response.data.circle_text_color;
      var selected = $("#circle_text_color").data("value");
      $("#circle_text_color").empty();
      $(".theme-color").empty();
      $("#solid_preset").empty();
      colors.forEach((c) => {
        var cc = c.list.map((x) => x.value).join(",");
        var option = `<option value="${cc}">${c.name}</option>`;
        if (selected == `${cc}`) {
          option = `<option value="${cc}" selected>${c.name}</option>`;
        }
        $("#circle_text_color").append(option);
        if (c.list[0].value != "#ffffff") {
          option = `<i class="circle-color" style="background: ${c.list[0].value}"></i>`;
          $(".theme-color").append(option);
        }
      });

      chagne_circle();

      selected = $("#burst_color").data("value");
      $("#burst_color").empty();
      var burst_colors = response.data.burst_color;
      burst_colors.forEach((c) => {
        var cc = c.list.map((x) => x.value).join(",");
        var option = `<option value="${cc}">${c.name}</option>`;
        if (selected == `${cc}`) {
          option = `<option value="${cc}" selected>${c.name}</option>`;
        }
        $("#burst_color").append(option);
      });
      $("#burst_color").trigger("change");
    });
  }

  function changeMessageOptions() {
    var message_options = $("#message_options").val();
    $(".value2").show();
    if (message_options == 0) {
      $(".value1 label").text("X");
      $(".value2 label").text("$XX");
      $(".value1 input").attr("placeholder", 3);
      $(".value2 input").attr("placeholder", 5);
    } else if (message_options == 1) {
      $(".value1 label").text("$X");
      $(".value2 label").text("xx¢");
      $(".value1 input").attr("placeholder", 5);
      $(".value2 input").attr("placeholder", 99);
    } else if (message_options == 2) {
      $(".value1 label").text("X");
      $(".value2 label").text("$X");
      $(".value1 input").attr("placeholder", 3);
      $(".value2 input").attr("placeholder", 5);
    } else if (message_options == 3) {
      $(".value1 label").text("$X");
      $(".value2 label").text("xx¢");
      $(".value1 input").attr("placeholder", 3);
      $(".value2 input").attr("placeholder", 75);
    } else if (message_options == 4) {
      $(".value1 label").text("xx¢");
      $(".value2").hide();
      $(".value1 input").attr("placeholder", 50);
    }
  }

  $("#solid_preset").on("change", function () {
    var color = $(this).val();
    $("#background_solid_color_hex").val(color);
    $("#background_solid_color").val(color);
  });

  $("#burst_color").on("change", function () {
    var colors = $(this).val().split(",");
    $(".burst-circle-color").css("background", colors[0]);
    $(".burst-text-color").css("background", colors[1]);
    $("#burst_circle_color").val(colors[0]);
    $("#burst_text_color").val(colors[1]);
  });

  $(".select-bkimg").on("click", function (e) {
    e.preventDefault();
    axios({
      method: "post",
      url: "/banner/background",
      data: {
        customer: "kroger",
        theme: $("select[name='theme']").val(),
        template: $("input[name='output_dimensions']").val(),
        get_only: 'background',
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

  $("#toggleOffsetAngle").on("click", function () {
    var text = $(this).text();
    if (text == "+ Product Offsets/Angles") {
      $(this).text("- Product Offsets/Angles");
      $(".offsetAngle-wrapper").show();
    } else {
      $(this).text("+ Product Offsets/Angles");
      $(".offsetAngle-wrapper").hide();
    }
  });

  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img",
    function () {
      var template = $("input[name='output_dimensions']").val();
      $(".offsetAngle-wrapper").addClass("d-none");
      $(".offsetAngle-wrapper").eq(template).removeClass("d-none");
      if (template == 0) {
        $(".burst-row").hide();
        $(".button-show-row").hide();
      } else {
        $(".burst-row").show();
        $(".button-show-row").show();
      }
    }
  );
});
