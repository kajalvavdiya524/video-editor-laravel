require("../../bootstrap");
require("bootstrap-fileinput");
require("select2");
import { font_list } from "../../fonts.js";

const getNewRow = (tbody, fieldType, cols) => {
  axios({
    method: "GET",
    url: `/admin/auth/template/field_types`,
  }).then(({ data }) => {
    const tr = $("<tr></tr>");
    tr.append(`
            <th>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn px-1 btn-move-up-row"><span class="c-icon cil-arrow-top"></span></button>
                    <button type="button" class="btn px-1 btn-move-down-row"><span class="c-icon cil-arrow-bottom"></span></button>
                    <button type="button" class="btn px-1 btn-delete-row"><span class="c-icon cil-x"></span></button>
                </div>
            </th>
        `);
    cols.map((col) => {
      if (col == "Field Type") {
        const th = $(`<th data-col-name="${col}"></th>`);
        const select = $('<select class="form-control"></select>');
        data.map((type) => {
          select.append(`<option value="${type}">${type}</option>`);
        });
        th.append(select);
        tr.append(th);
      } else if (col == "Name") {
        tr.append(
          `<th data-col-name="Name"><input class="form-control" value="${data[0]}"></th>`
        );
      } else if (
        col == "Font Selector" ||
        col == "Color Selector" ||
        col == "Moveable"
      ) {
        tr.append(`
                    <td data-col-name="${col}">
                        <select class="form-control">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </td>
                `);
      } else if (col == "Font") {
        let html =
          '<td data-col-name="Font"><select class="form-control"><option value=""></option>';
        html += Object.keys(font_list).map((key) => {
          return `<option value="${key}" style="font-family: '${key}'">${fonts[key]}</option>`;
        });
        html += "</select></td>";
        tr.append(html);
      } else if (col == "Alignment") {
        tr.append(`
                    <td data-col-name="${col}">
                      <div class="d-flex justify-content-center align-items-center">
                        <select class="form-control">
                            <option value="left">left</option>
                            <option value="center">center</option>
                            <option value="right">right</option>
                        </select>
                        <input type="checkbox" class="ml-1" {{ isset($options['ShowAlignment']) && $options['ShowAlignment'] ? "checked" : "" }} />
                      </div>
                    </td>
                `);
      } else if (col == "Kerning") {
        tr.append(`
                    <td data-col-name="${col}">
                        <select class="form-control">
                            <option value="none">none</option>
                            <option value="optical">optical</option>
                            <option value="metric">metric</option>
                        </select>
                    </td>
                `);
      } else if (col == "Filename") {
        tr.append(`
                    <td data-col-name="${col}">
                        <input type="file" class="form-control-file" data-show-preview="false" />
                        <span class="${col}_saved" id="${col}_saved"></span>
                    </td>
                `);
        tr.find(".form-control-file").fileinput({
          showUpload: false,
          previewFileType: "any",
        });
      } else if (
        col == "Font Color" ||
        ((fieldType == "Rectangle" ||
          fieldType == "Circle" ||
          fieldType == "Safe Zone") &&
          (col == "Option1" || col == "Option3"))
      ) {
        tr.append(`
                    <td data-col-name="${col}">
                        <div class="form-row">
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="text" class="form-control color-hex" placeholder="Color Hex Code" >
                            </div>
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="color" class="form-control" >
                            </div>
                        </div>
                    </td>
                `);
      } else if (
        (col == "Option1" && fieldType == "HTML") ||
        (col == "Option5" && fieldType == "Smart Object")
      ) {
        tr.append(
          `<td data-col-name="${col}"><textarea class="form-control"></textarea></td>`
        );
      } else {
        tr.append(
          `<td data-col-name="${col}"><input class="form-control" value=""></td>`
        );
      }
    });
    tbody.append(tr);
  });
};

const getFieldType = (row) => {
  return row.find('th[data-col-name="Field Type"] select').val();
};

$(document).ready(function () {
  function formatOutput(optionElement) {
    return $(
      `<div style="font-family: ${optionElement.id}">${optionElement.text}</div>`
    );
  }
  $(".font-select").select2({
    templateResult: formatOutput,
    templateSelection: formatOutput,
  });
  $(".form-control-file").each(function (index, el) {
    var url_field = $(el).next();
    var name_field = url_field.next();
    var options = {
      showUpload: false,
      previewFileType: "any",
    };
    var url = url_field.text();
    if (url) {
      options["initialCaption"] = name_field.text();
      options["initialPreview"] = [url];
    }
    $(el).fileinput(options);
  });

  $(document).on("change", 'input[type="color"]', function (e) {
    var hex = $(this).closest(".form-row").find(".color-hex");
    var color = $(this).val();
    hex.val(color);
  });

  $(document).on("keyup", ".color-hex", function (e) {
    var hex = $(this).val();
    var color = $(this).closest(".form-row").find('input[type="color"]');
    if (hex.length == 7 && hex[0] == "#") {
      color.val(hex);
    } else {
      hex = hex.toLowerCase();
      switch (hex) {
        case "transparent":
          color.val("#00000000");
          break;
        case "black":
          color.val("#000000");
          break;
        case "red":
          color.val("#ff0000");
          break;
        case "blue":
          color.val("#0000ff");
          break;
        case "green":
          color.val("#00ff00");
          break;
        case "white":
          color.val("#ffffff");
          break;
        case "pink":
          color.val("#ff00ff");
          break;
        case "yellow":
          color.val("#ffff00");
          break;
        case "brown":
          color.val("#A52A2A");
          break;
        case "orange":
          color.val("#FFA500");
          break;
        case "purple":
          color.val("#800080");
          break;
      }
    }
  });

  $("#customer").on("change", function () {
    var customer = $(this).val();
    window.location.href = "/admin/auth/template/" + customer;
  });

  $("#btn_update_template").on("click", function () {
    window.onbeforeunload = null;
    var formData = new FormData(document.getElementById("templateForm"));
    var customerId = $("#customerId").val();
    var templateId = $("#templateId").val();
    var fields = [];
    $(".template-table tbody tr").each(function (i) {
      if (i >= 2) {
        var tr = $(this);
        var field_type = getFieldType(tr);
        var fieldId = tr.attr("data-field-id");
        var field = { id: fieldId || -1 };
        tr.children().each(function () {
          var th = $(this);
          var colName = th.attr("data-col-name");
          if (colName == "Filename") {
            var element = th.find(".form-control-file");
            if (element.length > 0) {
              if (element[0].files.length) {
                field[colName] = "uploads/0/" + element.val().slice(12);
                formData.append("static_files[]", element[0].files[0]);
              } else {
                field[colName] = th.find(".Filename_saved").text();
              }
            }
          } else if (
            (field_type == "Rectangle" ||
              field_type == "Circle" ||
              field_type == "Safe Zone") &&
            (colName == "Option1" || colName == "Option3")
          ) {
            var element = th.find("input[type='text']");
            field[colName] = element.val();
          } else if (colName == "Font Color") {
            var element = th.find("input[type='text']");
            field[colName] = element.val();
          } else if (colName == "Alignment") {
            field[colName] = th.find("select").val();
            field["ShowAlignment"] = th
              .find('input[type="checkbox"]')
              .is(":checked");
          } else if (colName == "Size To Fit") {
            field[colName] = th.find('input[type="checkbox"]').is(":checked");
          } else if (colName) {
            field[colName] = th.find(">:first-child").val();
          }
        });
        fields.push(field);
      }
    });

    formData.append("fields", JSON.stringify(fields));

    axios({
      method: "post",
      url: `/admin/auth/template/${customerId}/${templateId}/update`,
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    }).then(function (response) {
      location.reload();
      // var name = $('#templateForm input[name="name"]').val();
      // $(".breadcrumb-item.active").text('Editing ' + name);
      // $(".alert-info").prepend(
      //     `<div class="alert alert-success" role="alert">
      //         <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      //             <span aria-hidden="true">×</span>
      //         </button>
      //         The template was successfully saved.
      //     </div>`
      // );
      // setTimeout(() => {
      //     $(".alert-info").empty();
      // }, 2000);
      // window.scrollTo(0, 0);
    });
  });

  $("#btn_update_view_template").on("click", function () {
    window.onbeforeunload = null;
    var formData = new FormData(document.getElementById("templateForm"));
    var customerId = $("#customerId").val();
    var customerName = $("#customerName").val();
    var templateId = $("#templateId").val();

    var fields = [];

    $(".template-table tbody tr").each(function (i) {
      if (i >= 2) {
        var tr = $(this);
        var field_type = getFieldType(tr);
        var fieldId = tr.attr("data-field-id");
        var field = { id: fieldId || -1 };
        tr.children().each(function () {
          var th = $(this);
          var colName = th.attr("data-col-name");
          if (colName == "Filename") {
            var element = th.find(".form-control-file");
            if (element.length > 0) {
              if (element[0].files.length) {
                field[colName] = "uploads/0/" + element.val().slice(12);
                formData.append("static_files[]", element[0].files[0]);
              } else {
                field[colName] = th.find(".Filename_saved").text();
              }
            }
          } else if (
            (field_type == "Rectangle" ||
              field_type == "Circle" ||
              field_type == "Safe Zone") &&
            (colName == "Option1" || colName == "Option3")
          ) {
            var element = th.find("input[type='text']");
            field[colName] = element.val();
          } else if (colName == "Font Color") {
            var element = th.find("input[type='text']");
            field[colName] = element.val();
          } else if (colName == "Alignment") {
            field[colName] = th.find("select").val();
            field["ShowAlignment"] = th
              .find('input[type="checkbox"]')
              .is(":checked");
          } else if (colName == "Size To Fit") {
            field[colName] = th.find('input[type="checkbox"]').is(":checked");
          } else if (colName) {
            field[colName] = th.find(">:first-child").val();
          }
        });
        fields.push(field);
      }
    });

    formData.append("fields", JSON.stringify(fields));

    axios({
      method: "post",
      url: `/admin/auth/template/${customerId}/${templateId}/update`,
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    }).then(function (response) {
      window.location.href = "/banner/" + customerName + "/" + templateId;
    });
  });

  $(document).on("click", ".btn-add-row", function (e, fieldType) {
    const tbody = $(this).closest("table").find("tbody");
    const tr = tbody.find("tr");
    if (tr.length > 2) {
      const fieldId = parseInt($(tr[tr.length - 1]).attr("data-field-id")) + 1;
      const clone = $(tr[2]).clone();
      clone.find("#Filename_saved").text("");
      clone.find("#Filename_saved_name").text("");
      clone.attr("data-field-id", fieldId);

      if (fieldType) {
        clone.find('th[data-col-name="Field Type"] select').val(fieldType);
      } else {
        fieldType = clone.find('th[data-col-name="Field Type"] select').val();
      }
      let number = 0;
      tr.each(function () {
        const type = $(this)
          .find('th[data-col-name="Field Type"] select')
          .val();
        const name = $(this).find('th[data-col-name="Name"] input').val();
        if (type == fieldType) {
          if (name == fieldType) {
            number = 2;
          } else if (name.startsWith(fieldType)) {
            const segments = name.split(" ");
            const suffix = parseInt(segments[segments.length - 1]);
            if (!Number.isNaN(suffix)) {
              number = Math.max(number, suffix + 1);
            }
          }
        }
      });

      if (number) {
        clone
          .find('th[data-col-name="Name"] input')
          .val(fieldType + " " + number);
      }

      if (
        fieldType == "Rectangle" ||
        fieldType == "Circle" ||
        fieldType == "Safe Zone"
      ) {
        clone.find('td[data-col-name="Option1"], td[data-col-name="Option3"]')
          .html(`
                    <div class="form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" class="form-control color-hex" placeholder="Color Hex Code" >
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" >
                        </div>
                    </div>
                `);
      }
      tbody.append(clone);
    } else {
      const cols = [];
      $("thead th").each(function (i) {
        if (i > 0) {
          cols.push($(this).text());
        }
      });
      getNewRow(tbody, fieldType, cols);
    }
    window.onbeforeunload = function () {
      return true;
    };
  });

  $(document).on("click", ".btn-delete-row", function () {
    const tr = $(this).closest("tbody").find("tr");
    if (tr.length > 3) {
      $(this).closest("tr").remove();
    }
    window.onbeforeunload = function () {
      return true;
    };
  });

  $(document).on("click", ".btn-move-up-row", function () {
    const row = $(this).closest("tr");
    const fieldId = row.attr("data-field-id");
    if (row.prev().attr("data-field-id")) {
      const prevFieldId = row.prev().attr("data-field-id");
      row.prev().attr("data-field-id", fieldId);
      row.attr("data-field-id", prevFieldId);
      row.insertBefore(row.prev());
    }
    window.onbeforeunload = function () {
      return true;
    };
  });

  $(document).on("click", ".btn-move-down-row", function () {
    const row = $(this).closest("tr");
    const fieldId = row.attr("data-field-id");
    const nextFieldId = row.next().attr("data-field-id");
    row.next().attr("data-field-id", fieldId);
    row.attr("data-field-id", nextFieldId);
    row.insertAfter(row.next());
    window.onbeforeunload = function () {
      return true;
    };
  });

  $(document).on("input", 'th[data-col-name="Field Type"]', function () {
    const width = $('.template-table input[name="width"]').val();
    const height = $('.template-table input[name="height"]').val();
    const row = $(this).closest("tr");
    const select = $(this).find("select");
    const fieldType = select.val();
    if (fieldType.includes("Background")) {
      row.find('td[data-col-name="Width"] input').val(width);
      row.find('td[data-col-name="Height"] input').val(height);
      row.find('td[data-col-name="X"] input').val(0);
      row.find('td[data-col-name="Y"] input').val(0);
    } else if (fieldType == "Text") {
      row.find('td[data-col-name="X"] input').val(0);
      row.find('td[data-col-name="Y"] input').val(0);
      row.find('td[data-col-name="Width"] input').val(100);
      row.find('td[data-col-name="Height"] input').val(100);
      row.find('td[data-col-name="Order"] input').val(500);
      row.find('td[data-col-name="Font Size"] input').val(100);
      row.find('td[data-col-name="Font Color"] input').val("black");
      row.find('td[data-col-name="Font"] select').val("Avenir-Black");
    } else if (fieldType == "Filename Cell") {
      row.find('th[data-col-name="Name"] input').val("Filename Cell");
    } else if (fieldType == "Text Options") {
      row.find('td[data-col-name="Option1"] input').val("Option 1");
      row.find('td[data-col-name="Option2"] input').val("Option 2");
      row.find('td[data-col-name="Option3"] input').val("Option 3");
      row.find('td[data-col-name="Option4"] input').val("Option 4");
      row.find('td[data-col-name="Option5"] input').val("Option 5");
    } else if (fieldType == "Image List") {
      axios({
        method: "get",
        url: `/admin/auth/template/image_lists`,
        dataType: "JSON",
      }).then(function (response) {
        var image_lists = response.data;
        var html = "<select class='form-control'>";
        image_lists.forEach((list) => {
          html += `<option value="${list.id}">${list.name}</option>`;
        });
        html += "</select>";
        row.find('td[data-col-name="Option1"]').html(html);
      });
    } else if (
      fieldType == "Rectangle" ||
      fieldType == "Circle" ||
      fieldType == "Safe Zone"
    ) {
      var html = `
                <div class="form-row">
                    <div class="col-md-6 col-sm-6 form-group">
                        <input type="text" class="form-control color-hex" placeholder="Color Hex Code" >
                    </div>
                    <div class="col-md-6 col-sm-6 form-group">
                        <input type="color" class="form-control" >
                    </div>
                </div>
            `;
      row.find('td[data-col-name="Option1"]').html(html);
      row.find('td[data-col-name="Option3"]').html(html);
    } else if (fieldType == "HTML") {
      var html = '<textarea class="form-control"></textarea>';
      row.find('td[data-col-name="Option1"]').html(html);
    } else if (fieldType == "Smart Object") {
      var html = '<textarea class="form-control"></textarea>';
      row.find('td[data-col-name="Option5"]').html(html);
    } else if (fieldType == "Text Oversampling") {
      var html = '<input class="form-control" value="1">';
      row.find('td[data-col-name="Option1"]').html(html);
      html = '<input class="form-control" value="0">';
      row.find('td[data-col-name="Option2"]').html(html);
    }
  });

  $(document).on("input", "th,td", function () {
    window.onbeforeunload = function () {
      return true;
    };
  });

  $("#delete_template_image").on("click", function () {
    let id = $(this).data("id");
    Swal.fire({
      title: "Do you want to delete template image?",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
      icon: "warning",
    }).then(function (result) {
      if (result.value) {
        axios({
          method: "POST",
          url: "/admin/auth/template/delete_image",
          data: {
            template_id: id,
          },
        }).then(({ data }) => {
          if (data == "success") {
            $(".template-image .card-header-actions").remove();
            Swal.fire("Success!", "Template image is deleted!", "success");
          }
        });
      }
    });
  });
});
