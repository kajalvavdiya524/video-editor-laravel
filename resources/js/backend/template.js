require("bootstrap-fileinput");

$(document).ready(function () {
  $(".form-control-file").fileinput({
    showUpload: false,
    previewFileType: "any",
  });

  $("select[name='customer']").on("change", function () {
    var customer = $(this).val();
    window.location.href = "/admin/auth/settings/template/" + customer;
  });

  $("#btn-reset").click(function (e) {
    var _form = $("#reset-form");
    _form.submit();
  });

  $("#btn-submit").click(function (e) {
    if ($("#show_text_tracking").is(":checked"))
      $("#show_text_tracking_hidden").prop("disabled", true);

    if ($("#show_3h").is(":checked"))
      $("#show_3h_hidden").prop("disabled", true);
    var _form = $("#template-form");
    _form.submit();
  });

  $("#AmazonFresh_Products_Area input").on("change", function () {
    var left = $("#AmazonFresh_Products_Left").val();
    var right = $("#AmazonFresh_Products_Right").val();
    var top = $("#AmazonFresh_Products_Top").val();
    var bottom = $("#AmazonFresh_Products_Bottom").val();

    var height = bottom - top;
    var width = right - left;

    $("#AmazonFresh_Products_Area #size").text(
      "WxH: " + width + " x " + height
    );
    $("#AmazonFresh_Products_Area #ratio").text(
      "W:H " + (width / height).toFixed(2)
    );
  });

  $(".attribute").on("change", 'input[type="color"]', function (e) {
    var hex = $(this).closest(".form-row").find(".color-hex");
    var color = $(this).val();
    hex.val(color);
  });

  $(".attribute").on("keyup", ".color-hex", function (e) {
    var hex = $(this).val();
    var color = $(this).closest(".form-row").find('input[type="color"]');
    if (hex.length == 7 && hex[0] == "#") {
      color.val(hex);
    }
  });

  $(".attribute").on("change", ".template", function () {
    // var template = $(this).val();
    // var url = $(this).closest('tr').find(".background-url").text().split('/');
    // url[url.length - 2] = template;
    // $(this).closest('tr').find(".background-url").text(url.join('/'));
  });

  $(".attribute").on("change", ".fill-type", function () {
    var fill_type = $(this).val();
    var html;
    if (fill_type == "solid") {
      html = `<div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control color-hex" value="#ffffff">
                        </div>
                        <div class="form-group col-md-6">
                            <input type="color" class="form-control" value="#ffffff">
                        </div>
                    </div>`;
    } else {
      html = `<div class="form-row">
                        <div class="form-row col-md-6">
                            <div class="form-group col-md-6">
                                <input type="text" class="form-control color-hex" value="#ffffff">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="color" class="form-control" value="#ffffff">
                            </div>
                        </div>
                        <div class="form-row col-md-6">
                            <div class="form-group col-md-6">
                                <input type="text" class="form-control color-hex" value="#ffffff">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="color" class="form-control" value="#ffffff">
                            </div>
                        </div>
                    </div>`;
    }
    $(this).closest("tr").find("td[data-type='color']").html(html);
  });

  $("#btn_update_theme").on("click", function () {
    var result = [];
    var customer_id = $("#customer_id").val();
    var isDuplicate = "";
    $(".attribute").each((index, element) => {
      var json_data = {};
      var attribute_name = $(element).find("p").text();
      var table = $(element).find("table");
      var attrs = table.find("thead th:not(:first-child):not(:last-child)");
      json_data["name"] = attribute_name;
      json_data["list"] = [];
      // if (attribute_name == "Background Images") {
      //   for (var i = 0; i < $("#mass_upload")[0].files.length; i++) {
      //     $(".btn-add-attr")
      // }
      table.find("tbody tr").each((i, obj) => {
        var option = {};
        var fillType = "solid";
        option["name"] = $(obj).find(".option-name").val();
        if (attribute_name == "Background Images" && option["name"] == "") {
          var fname = $(obj).find("input[type='file']").val();
          if (fname != "") {
            fname = fname.split("\\").reverse()[0];
            fname = fname.split(".")[0];
          } else {
            fname = $(obj).find(".background-url").text().trim();
            fname = fname.split("/").reverse()[0];
            fname = fname.split(".")[0];
          }
          option["name"] = fname;
        }
        option["list"] = [];
        $(obj)
          .find("td:not(:first-child):not(:last-child)")
          .each((j, obj2) => {
            var type = $(obj2).data("type");
            var value;
            if (type != "") {
              var old_value = "";
              if (type == "color") {
                if (fillType == "solid") {
                  value = $(obj2).find(".color-hex").val();
                } else {
                  var c1 = $(obj2).find(".color-hex").eq(0).val();
                  var c2 = $(obj2).find(".color-hex").eq(1).val();
                  value = `${c1},${c2}`;
                }
              } else if (type == "fill_type") {
                value = $(obj2).find("select").val();
                fillType = value;
              } else if (type == "font_color") {
                value = $(obj2).find("select").val();
                value = value.join(",");
              } else if (type == "background") {
                value = $(obj2).find("input[type='file']").val();
                if (value != "") {
                  value = value.split("\\").reverse()[0];
                }
                if (option["name"]) {
                  value = option["name"].toLowerCase() + ".png";
                }
                var template = $(obj).find(".template").val();
                value =
                  "/share?file=files/background/" +
                  customer_id +
                  "/0/" +
                  $("#theme_id").val() +
                  "/" +
                  template +
                  "/" +
                  value;
                old_value = $(obj2).find(".background-url").text();
              } else if (type == "background_template") {
                value = $(obj2).find(".template").val();
              } else if (type == "number") {
                value = $(obj2).find("input").val();
              }
              var item = {
                name: $(attrs[j]).text(),
                type: type,
                value: value,
                old_value: old_value,
              };
              option["list"].push(item);
            }
          });
        json_data["list"].push(option);
      });

      var valueArr = json_data["list"].map(function (item) {
        return item.name;
      });
      isDuplicate = valueArr.some(function (item, idx) {
        return valueArr.indexOf(item) != idx;
      });
      if (isDuplicate) {
        isDuplicate = attribute_name;
      }
      result.push(json_data);
    });
    if (isDuplicate && isDuplicate !== "Background Images") {
      alert(
        "There are duplicated item names in " + isDuplicate + " attributes."
      );
      return;
    }

    var form = $(
      '<form method="post" action="/admin/auth/settings/theme/' +
        customer_id +
        '/update" enctype="multipart/form-data" style="display:none"></form>'
    );
    form.append(
      $(
        '<input type="hidden" name="_token" id="csrf-token" value="' +
          $('meta[name="csrf-token"]').attr("content") +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="theme_id" id="theme_id" value="' +
          $("#theme_id").val() +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="theme_name" id="theme_name" value="' +
          $("input[name='theme_name']").val() +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="json_data" id="json_data" value=\'' +
          JSON.stringify(result) +
          "' />"
      )
    );
    var template_list = [];
    var filename_list = [];
    $("input[name='bk-image']").each((i, element) => {
      if ($(element)[0].files.length) {
        var template = $(element).closest("tr").find(".template").val();
        var filename = $(element).closest("tr").find(".option-name").val();
        template_list.push(template);
        filename_list.push(filename);
        var name = $(element).attr("name");
        $(element).attr("name", name + i);
        form.append($(element)[0]);
      }
    });
    if ($("#mass_upload")[0].files) {
      for (var i = 0; i < $("#mass_upload")[0].files.length; i++) {
        template_list.push(-1);
        filename_list.push("");
      }
      var elem = $("#mass_upload").clone();
      form.append(elem[0]);
    }
    form.append(
      $(
        '<input type="hidden" name="templates" id="templates" value=\'' +
          JSON.stringify(template_list) +
          "' />"
      )
    );
    form.append(
      $(
        '<input type="hidden" name="file_names" id="file_names" value=\'' +
          JSON.stringify(filename_list) +
          "' />"
      )
    );
    $(document.body).append(form);
    form.submit();
  });

  $('input[name="theme_name"]').on("input", function () {
    const name = $(this).val();
    $("#btn_create_theme").prop("disabled", name.length == 0);
  });

  $("input, select").on("input", function () {
    hasChanged = true;
  });

  $("#btn_create_theme").on("click", function () {
    var result = [];
    var customer_id = $("#customer_id").val();
    $(".attribute").each((index, element) => {
      var json_data = {};
      var attribute_name = $(element).find("p").text();
      var table = $(element).find("table");
      var attrs = table.find("thead th:not(:first-child):not(:last-child)");
      json_data["name"] = attribute_name;
      json_data["list"] = [];
      table.find("tbody tr").each((i, obj) => {
        var option = {};
        var fillType = "solid";
        option["name"] = $(obj).find(".option-name").val();
        if (attribute_name == "Background Images" && option["name"] == "") {
          var fname = $(obj).find("input[type='file']").val();
          if (fname != "") {
            fname = fname.split("\\").reverse()[0];
            fname = fname.split(".")[0];
          } else {
            fname = $(obj).find(".background-url").text().trim();
            fname = fname.split("/").reverse()[0];
            fname = fname.split(".")[0];
          }
          option["name"] = fname;
        }
        option["list"] = [];
        $(obj)
          .find("td:not(:first-child):not(:last-child)")
          .each((j, obj2) => {
            var type = $(obj2).data("type");
            var value;
            if (type != "") {
              if (type == "color") {
                if (fillType == "solid") {
                  value = $(obj2).find(".color-hex").val();
                } else {
                  var c1 = $(obj2).find(".color-hex").eq(0).val();
                  var c2 = $(obj2).find(".color-hex").eq(1).val();
                  value = `${c1},${c2}`;
                }
              } else if (type == "fill_type") {
                value = $(obj2).find("select").val();
                fillType = value;
              } else if (type == "background") {
                value = $(obj2).find("input[type='file']").val();
                if (value != "") {
                  value = value.split("\\").reverse()[0];
                }
                if (option["name"]) {
                  value = option["name"].toLowerCase() + ".png";
                }
                var template = $(obj).find(".template").val();
                value =
                  "/share?file=files/background/" +
                  customer_id +
                  "/0/" +
                  $("#theme_id").val() +
                  "/" +
                  template +
                  "/" +
                  value;
              } else if (type == "background_template") {
                value = $(obj2).find(".template").val();
              } else if (type == "number") {
                value = $(obj2).find("input").val();
              }
              var item = {
                name: $(attrs[j]).text(),
                type: type,
                value: value,
              };
              option["list"].push(item);
            }
          });
        json_data["list"].push(option);
      });
      result.push(json_data);
    });

    var form = $(
      '<form method="post" action="/admin/auth/settings/theme/' +
        customer_id +
        '/store" enctype="multipart/form-data" style="display:none"></form>'
    );
    form.append(
      $(
        '<input type="hidden" name="_token" id="csrf-token" value="' +
          $('meta[name="csrf-token"]').attr("content") +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="customer_id" id="customer_id" value="' +
          $("input[name='customer_id']").val() +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="theme_name" id="theme_name" value="' +
          $("input[name='theme_name']").val() +
          '" />'
      )
    );
    form.append(
      $(
        '<input type="hidden" name="json_data" id="json_data" value=\'' +
          JSON.stringify(result) +
          "' />"
      )
    );
    var template_list = [];
    var filename_list = [];
    $("input[type='file']").each((i, element) => {
      if ($(element)[0].files.length) {
        var template = $(element).closest("tr").find(".template").val();
        var filename = $(element).closest("tr").find(".option-name").val();
        template_list.push(template);
        filename_list.push(filename);
        var name = $(element).attr("name");
        $(element).attr("name", name + i);
        form.append($(element)[0]);
      }
    });
    form.append(
      $(
        '<input type="hidden" name="templates" id="templates" value=\'' +
          JSON.stringify(template_list) +
          "' />"
      )
    );
    form.append(
      $(
        '<input type="hidden" name="file_names" id="file_names" value=\'' +
          JSON.stringify(filename_list) +
          "' />"
      )
    );
    $(document.body).append(form);
    form.submit();
  });

  $(".btn-add-attr").on("click", function () {
    var attributeName = $(this).prev().text();
    if (attributeName != "Shadow Effects") {
      var table = $(this).parent().next();
      var tr = table.find("tr").last().clone();
      tr.find("input").val("");
      tr.find(".background-url-link").hide();
      tr.find(".background-url").text("");
      table.find("tbody").append(tr);
      table.find(".btn-delete-attr").prop("disabled", false);
      $("#btn_update_theme").prop("disabled", true);
      tr.find("input").on("change", function () {
        var row = $(this).closest("tr");
        if (
          row.find(".option-name").val() != "" ||
          (attributeName == "Background Images" &&
            row.find("input[type='file']").val())
        ) {
          $("#btn_update_theme").prop("disabled", false);
        } else {
          $("#btn_update_theme").prop("disabled", true);
        }
      });
    } else {
      var table = $(this).parent().next();
      table.find("tbody").append(
        $(`
                <tr>
                    <td><input type="text" class="form-control option-name" value=""></td>
                    <td data-type="number"><div class="form-group"><input type="number" class="form-control"></div></td>
                    <td data-type="number"><div class="form-group"><input type="number" class="form-control"></div></td>
                    <td data-type="number"><div class="form-group"><input type="number" class="form-control"></div></td>
                    <td data-type="number"><div class="form-group"><input type="number" class="form-control"></div></td>
                    <td data-type="number"><div class="form-group"><input type="number" class="form-control"></div></td>
                    <td><button type="button" class="btn btn-sm btn-danger btn-delete-attr">Delete</button></td>
                </tr>
            `)
      );
    }
  });

  $(".attribute").on("click", ".btn-delete-attr", function () {
    var tbody = $(this).closest("tbody");
    $(this).closest("tr").remove();
    const attributeName = $(this)
      .closest(".form-group.attribute")
      .find(".attribute-name")
      .text();
    if (attributeName != "Shadow Effects") {
      if (tbody.find(".btn-delete-attr").length == 1) {
        tbody.find(".btn-delete-attr").prop("disabled", true);
      }
      $("#btn_update_theme").prop("disabled", false);
      var inputs = tbody.find("input");

      inputs.each((index, input) => {
        if (
          ($(input).hasClass("option-name") && $(input).val() == "") ||
          ($(input).attr("name") == "bk-image" &&
            $(input).val() == "" &&
            $(input).closest(".form-row").find(".background-url").text() == "")
        ) {
          $("#btn_update_theme").prop("disabled", true);
        }
      });
    }
  });

  $(".attribute").on("click", ".btn-browse", function () {
    $(this).prev().trigger("click");
  });
  $(".attribute").on("change", 'input[type="file"]', function () {
    $(this).closest(".form-row").find("span").addClass("d-none");
    $(this).closest(".form-row").find(".background-url").removeClass("d-none");
    $(this)
      .closest(".form-row")
      .find(".background-url")
      .text("1 file selected");
    $(this).closest(".form-row").find(".background-url").attr("href", null);
  });
});
