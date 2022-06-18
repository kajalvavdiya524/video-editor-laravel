require("../../bootstrap");
require("slick-carousel");
require("ekko-lightbox");
require("bootstrap-fileinput");

var jQueryBridget = require("jquery-bridget");
var Masonry = require("masonry-layout");
jQueryBridget("masonry", Masonry, $);

var indexCheckedFiles = [];
var nameCheckedFiles = [];

var indexCheckedFiles2 = [];
var nameCheckedFiles2 = [];

$(document).ready(function () {
  $(".form-control-file").each(function (index, el) {
    var url_field = $(el).next();
    var name_field = url_field.next();
    var options = {
      showUpload: false,
      previewFileType: "any",
    };
    var url = url_field.val();
    if (url) {
      var caption = name_field.val();
      caption = caption.split("/share?file=").pop();
      options["initialCaption"] = caption;
      options["initialPreview"] = [url];
    }
    $(el).fileinput(options);
  });

  // Make the DIV element draggable:
  var previewPopup = document.getElementById("preview-popup");
  if (previewPopup) {
    dragElement(previewPopup);
  }

  function dragElement(elmnt) {
    var pos1 = 0,
      pos2 = 0,
      pos3 = 0,
      pos4 = 0;
    if (document.getElementById("drag-handler")) {
      // if present, the header is where you move the DIV from:
      document.getElementById("drag-handler").onmousedown = dragMouseDown;
    } else {
      // otherwise, move the DIV from anywhere inside the DIV:
      elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
      e = e || window.event;
      e.preventDefault();
      // get the mouse cursor position at startup:
      pos3 = e.clientX;
      pos4 = e.clientY;
      document.onmouseup = closeDragElement;
      // call a function whenever the cursor moves:
      document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
      e = e || window.event;
      e.preventDefault();
      // calculate the new cursor position:
      const width = $("#preview-popup").width();
      const height = $("#preview-popup").height();
      pos1 = pos3 - e.clientX;
      pos2 = pos4 - e.clientY;
      pos3 = e.clientX;
      pos4 = e.clientY;
      // set the element's new position:
      let top = Math.min(elmnt.offsetTop - pos2, window.innerHeight - height);
      top = Math.max(56, top);
      elmnt.style.top = top + "px";
      let left = Math.min(elmnt.offsetLeft - pos1, window.innerWidth - width);
      left = Math.max(0, left);
      elmnt.style.left = left + "px";
      elmnt.style.right = "auto";
    }

    function closeDragElement() {
      // stop moving when mouse button is released:
      document.onmouseup = null;
      document.onmousemove = null;
    }
  }

  $(".grid").masonry({
    itemSelector: ".grid-item",
    columnWidth: 220,
  });

  function showError(messages) {
    $(".alert.errors").empty();
    for (var msg of messages) {
      var alert = msg;
      if (msg.toString().includes("status code 419")) {
        alert =
          "Error: Your session has expired, please log out and log back in.";
      }
      $(".alert.errors").append($(`<div class="error-message">${alert}</div>`));
    }
    $(".alert.errors").show();
    setTimeout(function () {
      $(".alert.errors").hide();
    }, 4000);
  }

  $("select[name=product_layering]").on("change", function (e) {
    $(".product_custom_layering").removeClass("d-none");
    if ($(this).val() == "Custom") {
      $(".product_custom_layering").show();
    } else {
      $(".product_custom_layering").hide();
    }
  });

  $(document).on("click", ".grid-item", function (e) {
    var id = Number($(this).find("input.info").val());
    var name = $(this).find("input.info").data("name");
    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
      indexCheckedFiles = _.pull(indexCheckedFiles, id);
      nameCheckedFiles = _.pull(nameCheckedFiles, name);
    } else {
      $(this).addClass("selected");
      indexCheckedFiles.push(id);
      nameCheckedFiles.push(name);
    }
  });

  $(document)
    .on("mouseover", ".grid-item", function (e) {
      $(this).children(".overlay").fadeIn();
    })
    .on("mouseleave", ".grid-item", function (e) {
      $(this).children(".overlay").fadeOut();
    });

  $(document).on("click", "#selectImgModal .grid-item a", function (e) {
    e.preventDefault();
    e.stopPropagation();
    var base_url = window.location.origin;
    var path = $(this).data("path");
    var name = $(this).data("name");
    var width = $(this).data("width");
    var height = $(this).data("height");

    $(".available-image-grid").hide();
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
    $("#full-size-image").data("company_id", $(this).data("company_id"));
    $("#full-size-image").data("path", path);
    $(".full-size-image").append(
      $(
        `<span class="product-image-description float-right">${name} [${width.toFixed(
          2
        )} x ${height.toFixed(2)} WxH]</span>`
      )
    );
    $(".full-size-image").show();
    // crop tool
    $("#image-crop-button").show();
    $(".image-crop .button-group").hide();
    $(".image-edit-tools").show();
  });

  $(document).on("click", "#selectImgModal .grid-item", function (e) {
    e.preventDefault();
    e.stopPropagation();

    $(this).parent().find(".grid-item").removeClass("selected");
    $(this).addClass("selected");
    indexCheckedFiles2 = [];
    nameCheckedFiles2 = [];
    $("#selectImgModal .grid-item.selected").each((i, element) => {
      var id = Number($(element).find("input.info").val());
      var name = $(element).find("input.info").data("name");
      indexCheckedFiles2.push(id);
      var child_id = $(element).parent().siblings("p").text().split(".")[0];
      var isParent = $(element).parent().siblings("p").data("parent");
      console.log(isParent);
      if (isParent) {
        if (child_id != name) {
          nameCheckedFiles2.push(name);
        } else {
          nameCheckedFiles2.push(name + "_p");
        }
      } else {
        if (child_id == name) {
          nameCheckedFiles2.push(name);
        } else {
          nameCheckedFiles2.push(name + "_p");
        }
      }
    });
  });

  $(document).on("click", "#selectImgModal .btn-back-grid", function () {
    $(".full-size-image").hide();
    $(".available-image-grid").show();
    $(".image-edit-tools").hide();
  });

  $("#selectImgModal #submit").on("click", function () {
    $("input[name=file_ids]").val(nameCheckedFiles2.join(" "));
    axios({
      method: "post",
      url: "/banner/update_product_selections",
      data: {
        file_ids: indexCheckedFiles2.join(" "),
      },
    })
      .then(function (response) {
        var data = response.data;
        console.log(data);
      })
      .catch(function (response) {
        showError([response]);
      });
  });

  $("#view-img").click(function () {
    if (!$(this).hasClass("disabled")) {
      var customer = $("input[name=customer]").val();
      axios({
        method: "post",
        url: "/banner/view",
        data: {
          file_ids: $("input[name=file_ids]").val(),
          show_warning: customer == "mrhi" || customer == "instagram",
        },
      })
        .then(function (response) {
          var data = response.data;
          if (data.status == "error") {
            showError(data.messages);
          } else {
            if (data.status == "warning") {
              showError(data.messages);
            }
            $(".full-size-image").hide();
            $(".image-edit-tools").hide();
            $(".image-crop .button-group").hide();
            $(".available-image-grid").show();
            $("#product-images").empty();
            indexCheckedFiles2 = [];
            nameCheckedFiles2 = [];

            if (
              data.files.length >= 2 ||
              data.files[0].related_files.length >= 2
            ) {
              var html = "";
              var base_url = window.location.origin;
              for (var file of data.files) {
                if (file.popular_file) {
                  indexCheckedFiles2.push(file.popular_file.id);
                  if (file.name == file.popular_file.name.split(".")[0]) {
                    nameCheckedFiles2.push(
                      file.popular_file.name.split(".")[0]
                    );
                  } else {
                    nameCheckedFiles2.push(
                      file.popular_file.name.split(".")[0] + "_p"
                    );
                  }
                } else {
                  indexCheckedFiles2.push(file.related_files[0].id);
                  if (file.name == file.related_files[0].name.split(".")[0]) {
                    nameCheckedFiles2.push(
                      file.related_files[0].name.split(".")[0]
                    );
                  } else {
                    nameCheckedFiles2.push(
                      file.related_files[0].name.split(".")[0] + "_p"
                    );
                  }
                }
                html += "<div class='image-grid-responsive'>";
                html +=
                  "<p data-parent='" +
                  file.isParent +
                  "' class='font-weight-bold'>" +
                  file.name +
                  "</p>";
                html += "<div class='grid'>";

                for (var rfile of file.related_files) {
                  if (indexCheckedFiles2.includes(rfile.id)) {
                    html += "<div class='grid-item selected'>";
                    nameCheckedFiles.push(rfile.name.split(".")[0]);
                  } else {
                    html += "<div class='grid-item'>";
                  }
                  html +=
                    "<input type='checkbox' class='select-check' checked />";
                  html +=
                    "<input class='info d-none' data-name='" +
                    rfile.name.split(".")[0] +
                    "' value='" +
                    rfile.id +
                    "'/>";
                  html += `<img src='${base_url}/share?file=${rfile.thumbnail}' loading='lazy'/>`;
                  html += "<p>" + rfile.name + "</p>";
                  html += "<div class='overlay' style='display: none'>";
                  html += `<a href="javascript: void(0);" data-name="${rfile.name}" data-path="${rfile.path}" data-width="${rfile.width}" data-height="${rfile.height}" data-company_id="${rfile.company_id}">`;
                  html += "<i class='cil-search'></i> View Image</a>";
                  html += "</div></div>";
                }
                html += "</div></div>";
              }
              $(".available-image-grid").empty();
              $(".available-image-grid").append(html);
              $("#selectImgModal #submit").show();
              $("#selectImgModal").modal();
            } else {
              var base_url = window.location.origin;
              for (var file of data.files[0].related_files) {
                $(".available-image-grid").hide();
                $(".full-size-image").empty();
                $(".full-size-image").append(
                  $(
                    `<img src="${base_url}/share?file=${file.path}" class="product-image" id="full-size-image" />`
                  )
                );
                $("#full-size-image").data("name", file.name);
                $("#full-size-image").data("type", "");
                $("#full-size-image").data("company_id", file["company_id"]);
                $("#full-size-image").data("path", file.path);
                $(".full-size-image").append(
                  $(
                    `<span class="product-image-description float-right">${
                      file.name
                    } [${file.width.toFixed(2)} x ${file.height.toFixed(
                      2
                    )} WxH]</span>`
                  )
                );
              }
              $(".full-size-image").show();
              $(".image-edit-tools").show();
              $("#image-crop-button").show();
              $("#selectImgModal #submit").hide();
              $("#selectImgModal").modal();
            }
          }
        })
        .catch(function (response) {
          showError([response]);
        });
    }
  });

  $("input[name=file_ids]").keyup(function () {
    var elem = $(this);
    var file_ids = elem.val();
    if (file_ids.length > 0) {
      $("#view-img").removeClass("disabled");
    } else {
      $("#view-img").addClass("disabled");
    }
  });

  $('[data-toggle="tooltip"]').tooltip();

  function save_group_template() {
    rememberTemplateSettings();
    let logo_urls = [];
    $(".logo-image").each(function () {
      logo_urls.push($(this).val());
    });
    $('input[name="logos"]').val(JSON.stringify(logo_urls));
    $('input[name="product_texts"]').val(JSON.stringify(productTexts));
  }

  $("#save-group-template").on("click", function (e) {
    e.preventDefault();
    save_group_template();
    $(this).parents("form:first").submit();
  });

  $(".navigate_templates").on("click", function (e) {
    e.preventDefault();
    $("#next_template").val($(this).data("next-template"));
    save_group_template();
    $(this).parents("form:first").submit();
  });

  $("#show_cells").on("change", function () {
    if ($(this).prop("checked")) {
      $(".cell-fields").removeClass("d-none");
    } else {
      $(".cell-fields").addClass("d-none");
    }
  });

  $('input[type="color"]').on("change", function (e) {
    var hex = $(this).closest(".form-row").find(".color-hex");
    var color = $(this).val();
    hex.val(color);
  });

  $(".color-hex").on("keyup", function (e) {
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

  function updateBulkUpdateButton() {
    const checkedFieldsCount =
      $(".field-checkboxes").find("input:checked").length;
    const checkedTemplatesCount = $(".template-checkboxes").find(
      "input:checked"
    ).length;
    if (checkedFieldsCount > 0 && checkedTemplatesCount > 0) {
      $("#bulk-update").prop("disabled", false);
    } else {
      $("#bulk-update").prop("disabled", true);
    }
  }

  $("#bulk-update").on("click", async function () {
    const instanceIds = [];
    const fieldValues = {};
    $(".field-checkboxes")
      .find("input:checked")
      .each(function () {
        const elementId = $(this).attr("data-element-id");
        
        if (elementId.match("^upload_image_")) {
          
          fieldValues[elementId+"_saved"] = $(`input[name="${elementId+"_saved"}"]`).val();
          fieldValues[elementId+"_saved_name"] = $(`input[name="${elementId+"_saved_name"}"]`).val();
          fieldValues[elementId+"_offset_x"] = $(`input[name="${elementId+"_offset_x"}"]`).val();
          fieldValues[elementId+"_offset_y"] = $(`input[name="${elementId+"_offset_y"}"]`).val();
          fieldValues[elementId+"_angle"] = $(`input[name="${elementId+"_angle"}"]`).val();
          fieldValues[elementId+"_scale"] = $(`input[name="${elementId+"_scale"}"]`).val();
          fieldValues[elementId+"_width"] = $(`input[name="${elementId+"_width"}"]`).val();
          fieldValues[elementId+"_height"] = $(`input[name="${elementId+"_height"}"]`).val();
        
        }
        
        fieldValues[elementId] = $(`input[name="${elementId}"]`).val();


      });
    $(".template-checkboxes")
      .find("input:checked")
      .each(function () {
        const instanceId = $(this).attr("data-instance-id");
        instanceIds.push(instanceId);
      });
    if (Object.keys(fieldValues).length > 0 && instanceIds.length > 0) {
      const instanceId = $('input[name="instance_id"]').val();
      const customerId = $('input[name="customer_id"]').val();
      const layoutId = $('input[name="layout_id"]').val();
      await axios.post(`/banner/${customerId}/group/${layoutId}/bulk_update`, {
        currentInstanceId: instanceId,
        fieldValues,
        instanceIds,
        productTexts,
      });
      alert("Bulk update finished.");
    }
  });

  $("#bulkUpdateModal").on("show.bs.modal", function () {
    updateBulkUpdateButton();
  });

  $("#bulkUpdateModal input").on("change", function () {
    updateBulkUpdateButton();
  });

  $("#chk_all_fields").on("change", function () {
    const selected = $(this).is(":checked");
    $(".field-checkboxes").find("input").prop("checked", selected);
  });

  $("#chk_all_templates").on("change", function () {
    const selected = $(this).is(":checked");
    $(".template-checkboxes").find("input").prop("checked", selected);
  });
});
