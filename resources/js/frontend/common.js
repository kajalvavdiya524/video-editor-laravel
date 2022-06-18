require("../bootstrap");
require("slick-carousel");
require("ekko-lightbox");
import Cropper from "cropperjs";

var jQueryBridget = require("jquery-bridget");
var Masonry = require("masonry-layout");
jQueryBridget("masonry", Masonry, $);

var cropper;

var indexCheckedFiles = [];
var nameCheckedFiles = [];

var indexCheckedFiles2 = [];
var nameCheckedFiles2 = [];

$(document).ready(function () {
  /* this line was making file_ids empty when selected_files was not set
     for what ive seen file_ids can be in a cookie or in this localStorage key
     this needs to be reviewed as not every template has the same behaviour */

  if (
    $('input[name="file_ids"]').length > 0 &&
    localStorage.getItem("selected_files") !== null
  ) {
    $('input[name="file_ids"]').val(localStorage.getItem("selected_files"));
  }

  function formatOutput(optionElement) {
    return $(
      `<div style="font-family: ${optionElement.id}">${optionElement.text}</div>`
    );
  }
  $(".font-select").select2({
    templateResult: formatOutput,
    templateSelection: formatOutput,
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

  function showInfo(message) {
    $(".alert.success").empty();
    $(".alert.success").append(
      $(`<div class="success-message">${message}</div>`)
    );
    $(".alert.success").show();
    setTimeout(function () {
      $(".alert.success").hide();
    }, 4000);
  }

  function sendNotificationEmail(email, url, projectname) {
    if (email) {
      var link = url.match(/outputs\/([a-zA-Z0-9_\.]*)\?/)[1];
      var filename = url.match(/outputs\/([a-zA-Z0-9_]*)\./)[1];
      var subject = (projectname ? projectname : filename) + " Banner";
      var mailto = document.createElement("a");
      var base_url = window.location.origin;
      mailto.href = `mailto:${email}?subject=${subject}&body=Here is the banner. ${base_url}/share?file=outputs/${link}`;
      mailto.target = "_blank";
      mailto.click();
    }
  }

  function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    } else {
      var date = new Date();
      date.setTime(date.getTime() + 1);
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  $(".slide-popup").on("click", function (e) {
    e.stopPropagation();
  });
  $("body").on("click", function () {
    $(".slide-popup").fadeOut();
  });

  $(".selected-customer").on("click", function (e) {
    e.stopPropagation();
    $(".customers").fadeIn();
    $(".templates").fadeOut();
    if (!$(".customers-carousel").hasClass("slick-initialized")) {
      $(".customers-carousel").slick({
        infinite: true,
        speed: 500,
        slidesToShow: 6,
        swipeToSlide: true,
        touchThreshold : 69,
      });
    }
  });

    
  $(".customers .slide-item img").on("mousedown", function (e) {
   
    $(this).data("position",$(this).offset().left)  ;
   
  });

     
  $(".customers .slide-item img").on("mouseup", function (e) {
   
    if ( $(this).offset().left == $(this).data("position")){

      $(".slide-item img").removeClass("selected");
      $(this).addClass("selected");
      $(".customers").fadeOut();
      var src = $(this).attr("src");
      var title = $(this).attr("title");
      var value = $(this).data("value");
      $(".selected-customer img").attr("src", src);
      $(".selected-customer img").attr("title", title);
      $(".selected-customer img").addClass("selected");
      $(".selected-customer").show();
      $("input[name=customer]").val(value);
      var file_ids = $('input[name="file_ids"]').val();
      setCookie("file_ids", file_ids, 1);
      window.location.href = "/banner/" + value;

    }
  
   
  });


  /*
  $(".customers .slide-item img").on("click", function (e) {
    
  });
*/




  $("input[name=customer]").on("change", function (e) {
    var file_ids = $('input[name="file_ids"]').val();
    setCookie("file_ids", file_ids, 1);
    window.location.href = "/banner/" + $(this).val();
  });

  $(".selected-template").on("click", function (e) {
    e.stopPropagation();
    $(".templates").fadeIn();
    $(".templates-carousel").remove();
    $(".templates-carousel-hidden")
      .clone()
      .removeClass("templates-carousel-hidden")
      .removeClass("d-none")
      .addClass("templates-carousel")
      .appendTo(".templates");

    $(".templates-carousel").slick({
      slidesToShow: 4,
    });
  });

  $(".templates").on(
    "click",
    ".templates-carousel .slide-item img, .templates-carousel .slide-item p",
    function (e) {
      $(
        ".templates-carousel .slide-item img, .templates-carousel .slide-item p"
      ).removeClass("selected");
      $(
        ".templates-carousel-hidden .slide-item img, .templates-carousel-hidden .slide-item p"
      ).removeClass("selected");
      $(this).addClass("selected");
      $(".templates").fadeOut();

      var src = $(this).attr("src");
      var title = $(this).attr("title");
      var value = $(this).data("value");
      var selected_index = $(this).closest(".slick-slide").index();

      $(
        ".templates-carousel-hidden .slide-item img, .templates-carousel-hidden .slide-item p"
      ).each((i, obj) => {
        if (value == $(obj).data("value")) {
          selected_index = i;
        }
      });
      $(".selected-template img").attr("src", src);
      $(".selected-template img").attr("title", title);
      $(".selected-template img").addClass("selected");
      $(".selected-template").show();
      $("input[name=output_dimensions]").val(value);
      $(".templates-carousel-hidden")
        .find(".slide-item img, .slide-item p")
        .each((index, element) => {
          if (index < selected_index) {
            $(".templates-carousel-hidden").append($(element).parent());
          } else if (index == selected_index) {
            $(element).addClass("selected");
          }
        });
    }
  );

  $(".inline-template-selector .templates").on(
    "click",
    ".templates-carousel .slide-item img, .templates-carousel .slide-item p",
    function (e) {
      var value = $(this).data("value");
      var file_ids = $('input[name="file_ids"]').val();
      var customer = $("input[name=customer]").val();
      setCookie("file_ids", file_ids, 1);

      rememberTemplateSettings();

      window.location.href = "/banner/" + customer + "/" + value;
    }
  );

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

  $("#generate-ads").on("click", function (e) {
    e.preventDefault();

    rememberTemplateSettings();

    var button = $(this);
    var formData = new FormData(document.getElementById("adForm"));
    button.prop("disabled", true);
    axios({
      method: "post",
      url: "/banner/isExistDraft",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    }).then(function (response) {
      if (response.data) {
        Swal.fire({
          title:
            "Draft with the same name already exists. Do you want to overwrite?",
          showCancelButton: true,
          confirmButtonText: "Confirm",
          cancelButtonText: "Cancel",
          icon: "warning",
        }).then(function (result) {
          if (result.value) {
            saveDraft();
          }
        });
      } else {
        saveDraft();
      }
      button.prop("disabled", false);
    });
  });

  $("#download-ads").on("click", function (e) {
    download(e, $(this), 0);
  });

  $("#proof-sheet").on("click", function (e) {
    download(e, $(this), 1);
  });

  $("#preview-ads").on("click", function (e) {
    e.preventDefault();

    rememberTemplateSettings();

    var button = $(this);
    button.prop("disabled", true);
    var file_ids = $("input[name=file_ids]").val();
    if (!file_ids) {
      file_ids = "";
    }
    file_ids = file_ids.replace(/  +/g, " ");
    var formData = new FormData(document.getElementById("adForm"));
    formData.set("file_ids", file_ids);
    var customer = formData.get("customer");
    if (customer == "walmart") {
      var GTINs = JSON.parse(localStorage.getItem("walmart_GTINs"));
      if (GTINs == null) {
        GTINs = {
          0: file_ids,
          1: file_ids,
          2: file_ids,
          3: file_ids,
          4: file_ids,
        };
      }
      formData.set("file_ids", Object.values(GTINs));
    }

    if ($("#show_text").length == 0) {
      formData.set("show_text", "on");
    }

    $(".generate-alert").css("display", "flex");
    axios({
      method: "post",
      url: "/banner/preview",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
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
          $("#preview-images").empty();
          var anchors = [];
          for (var file of data.files) {
            var anchor = $(
              `<a href="/${file}" class="preview-image" data-gallery="preview-image-gallery"></a>`
            );
            anchors.push(anchor);
            $("#preview-images").append(anchor);
          }
          anchors[0].ekkoLightbox({ alwaysShowClose: true });
        }
        button.prop("disabled", false);
        $(".generate-alert").css("display", "none");
      })
      .catch(function (response) {
        showError([response]);
        button.prop("disabled", false);
        $(".generate-alert").css("display", "none");
      });
  });

  $("#publish-team-ads").on("click", function (e) {
    e.preventDefault();

    rememberTemplateSettings();

    var button = $(this);
    var file_ids = $("input[name=file_ids]").val();
    if (!file_ids) {
      file_ids = "";
    }
    file_ids = file_ids.replace(/  +/g, " ");
    var formData = new FormData(document.getElementById("adForm"));
    formData.set("file_ids", file_ids);
    button.prop("disabled", true);
    axios({
      method: "post",
      url: "/banner/isExistProject",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    }).then(function (response) {
      if (response.data) {
        Swal.fire({
          title:
            "Project with the same name already exists. Do you want to overwrite?",
          showCancelButton: true,
          confirmButtonText: "Confirm",
          cancelButtonText: "Cancel",
          icon: "warning",
        }).then(function (result) {
          if (result.value) {
            saveProject();
          }
        });
      } else {
        saveProject();
      }
      button.prop("disabled", false);
    });
  });

  function appendBlobToForm(formData, imagepath, fieldname, value) {
    $.ajax({
      cache: false,
      method: "POST",
      type: "POST", // For jQuery < 1.9
      url: "/banner/getbase64image",
      dataType: "JSON",
      data: { image: imagepath },
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
      },
      success: function (response) {
        const file = DataURIToBlob(response.image);
        formData.append(fieldname, file, value);
      },
      fail: function (response) {
        console.log(response);
      },
      async: false,
    });

    return formData;
  }

  function check_download_all() {
    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";

    if (localStorage.getItem(key)) {
      const current_template_id = $("#template_id").val();
      const settings = JSON.parse(localStorage.getItem(key));
      const current_template_edited =
        settings["template_" + current_template_id + "_edited"];
      if (current_template_edited) {
        var current_settings = JSON.parse(current_template_edited);
        if (current_settings["download_all"] == "on") {
          return true;
        }
      }
    }
    return false;
  }

  function DataURIToBlob(dataURI) {
    const splitDataURI = dataURI.split(",");
    const byteString =
      splitDataURI[0].indexOf("base64") >= 0
        ? atob(splitDataURI[1])
        : decodeURI(splitDataURI[1]);
    const mimeString = splitDataURI[0].split(":")[1].split(";")[0];

    const ia = new Uint8Array(byteString.length);
    for (let i = 0; i < byteString.length; i++)
      ia[i] = byteString.charCodeAt(i);

    return new Blob([ia], {
      type: mimeString,
    });
  }

  function download_all(e, dom, proof_sheet) {
    // https://stackoverflow.com/questions/70418724/how-to-append-a-file-to-a-formdata-from-a-url-or-file-on-server-rather-than-a

    const customer_id = $('input[name="customer_id"]').val();
    const key = "customer_" + customer_id + "_settings";
    if (localStorage.getItem(key)) {
      // for each template que has download all, do the same as download
      const settings = JSON.parse(localStorage.getItem(key));

      Object.entries(settings).forEach((setting) => {
        const [template_id, fields] = setting;
        if (
          template_id.indexOf("template_") >= 0 &&
          template_id.indexOf("_edited") < 0
        ) {
          const current_template_edited = settings[template_id + "_edited"];
          if (current_template_edited) {
            var current_settings_edited = JSON.parse(current_template_edited);
            if (current_settings_edited["download_all"] == "on") {
              const current_template = settings[template_id];
              if (current_template) {
                var current_settings = JSON.parse(current_template);

                var formData = new FormData();
                var file_ids = "";
                for (var i = 0; i < current_settings.length; i++) {
                  formData.append(
                    current_settings[i].name,
                    current_settings[i].value
                  );
                  if (current_settings[i].name == "file_ids") {
                    file_ids = current_settings[i].value.replace(/  +/g, " ");
                  }
                }
                formData.set("file_ids", file_ids);

                const current_template_files = settings[template_id + "_files"];
                if (current_template_files) {
                  var current_files = JSON.parse(current_template_files);
                }

                Object.entries(current_files).forEach((current_file) => {
                  const [fieldname, value] = current_file;
                  var imagepath = current_settings_edited[fieldname + "_saved"];
                  imagepath = imagepath.substring(imagepath.indexOf("=") + 1);
                  if (imagepath) {
                    appendBlobToForm(formData, imagepath, fieldname, value);
                  }
                });

                formData.append("proof_sheet", proof_sheet);
                dom.prop("disabled", true);

                var customer = formData.get("customer");
                if (customer == "walmart") {
                  var GTINs = JSON.parse(localStorage.getItem("walmart_GTINs"));
                  if (GTINs == null) {
                    GTINs = {
                      0: file_ids,
                      1: file_ids,
                      2: file_ids,
                      3: file_ids,
                      4: file_ids,
                    };
                  }
                  formData.set("file_ids", Object.values(GTINs));
                }
                if ($("#show_text").length == 0) {
                  formData.set("show_text", "on");
                }

                $(".generate-alert").css("display", "flex");

                // this cant be async or else the S3 server will block the requests if they're sent all at the same time.
                // good ole ajax
                $.ajax({
                  cache: false,
                  contentType: false,
                  processData: false,
                  method: "POST",
                  type: "POST", // For jQuery < 1.9
                  url: "/banner/download",
                  dataType: "JSON",
                  data: formData,
                  headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                      "content"
                    ),
                  },
                  success: function (response) {
                    download_file(response);
                    dom.prop("disabled", false);
                    $(".generate-alert").css("display", "none");
                  },
                  fail: function (response) {
                    showError([response]);
                    dom.prop("disabled", false);
                    $(".generate-alert").css("display", "none");
                  },
                  async: false,
                });
              }
            }
          }
        }
      });
    }
  }

  function download(e, dom, proof_sheet) {
    e.preventDefault();

    rememberTemplateSettings();

    if (check_download_all()) {
      download_all(e, dom, proof_sheet);
    } else {
      var file_ids = $("input[name=file_ids]").val();
      if (!file_ids) {
        file_ids = "";
      }
      file_ids = file_ids.replace(/  +/g, " ");
      var formData = new FormData(document.getElementById("adForm"));
      formData.set("file_ids", file_ids);
      formData.append("proof_sheet", proof_sheet);
      dom.prop("disabled", true);
      var customer = formData.get("customer");
      if (customer == "walmart") {
        var GTINs = JSON.parse(localStorage.getItem("walmart_GTINs"));
        if (GTINs == null) {
          GTINs = {
            0: file_ids,
            1: file_ids,
            2: file_ids,
            3: file_ids,
            4: file_ids,
          };
        }
        formData.set("file_ids", Object.values(GTINs));
      }

      if ($("#show_text").length == 0) {
        formData.set("show_text", "on");
      }

      $(".generate-alert").css("display", "flex");
      axios({
        method: "post",
        url: "/banner/download",
        data: formData,
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
        .then(function (response) {
          download_file(response.data);
          dom.prop("disabled", false);
          $(".generate-alert").css("display", "none");
        })
        .catch(function (response) {
          showError([response]);
          dom.prop("disabled", false);
          $(".generate-alert").css("display", "none");
        });
    }
  }

  function saveDraft() {
    rememberTemplateSettings();

    var file_ids = $("input[name=file_ids]").val();
    if (!file_ids) {
      file_ids = "";
    }
    file_ids = file_ids.replace(/  +/g, " ");
    var formData = new FormData(document.getElementById("adForm"));
    formData.set("file_ids", file_ids);
    var is_download_draft = $("#is_download_draft").val();
    formData.append("product_texts", JSON.stringify(productTexts));

    if ($("#show_text").length == 0) {
      formData.set("show_text", "on");
    }

    if (is_download_draft == "1") {
      $(".generate-alert").html("Generating...");
    } else {
      $(".generate-alert").html("Saving...");
    }
    $(".generate-alert").css("display", "flex");

    axios({
      method: "post",
      url: "/banner/generate",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
      .then((response) => {
        if (is_download_draft == "1") {
          download_file(response.data);
        }
        $("#saved-draft").val(response.data.url);
        $("#saved-draft").data("projectname", response.data.projectname);
        if (
          $("#published-project").data("projectname") !=
          response.data.projectname
        ) {
          $("#published-project").val("");
        }
        $(".generate-alert").html("Saved!");
        setTimeout(() => {
          $(".generate-alert").css("display", "none");
        }, 1500);
      })
      .catch((response) => {
        showError([response]);
        $(".generate-alert").css("display", "none");
      });
  }

  function saveProject() {
    rememberTemplateSettings();

    var type = $('select[name="type"]').val();
    var file_ids = $("input[name=file_ids]").val();
    if (!file_ids) {
      file_ids = "";
    }
    file_ids = file_ids.replace(/  +/g, " ");
    var formData = new FormData(document.getElementById("adForm"));
    formData.set("file_ids", file_ids);
    var is_download_project = $("#is_download_project").val();
    formData.append("product_texts", JSON.stringify(productTexts));

    if ($("#show_text").length == 0) {
      formData.set("show_text", "on");
    }

    if (is_download_project == "1") {
      $(".generate-alert").html("Generating...");
    } else {
      $(".generate-alert").html("Saving...");
    }
    $(".generate-alert").css("display", "flex");

    axios({
      method: "post",
      url: "/banner/publish",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
      .then(function (response) {
        if (type != 1) {
          if (is_download_project == "1") {
            download_file(response.data);
          }
          $("#published-project").val(response.data.url);
          $("#published-project").data(
            "projectname",
            response.data.projectname
          );
          if (
            $("#saved-draft").data("projectname") != response.data.projectname
          ) {
            $("#saved-draft").val("");
          }
          $("#publish-team-ads").prop("disabled", false);
        } else {
          showInfo("Parent Saved.");
        }
        $(".generate-alert").html("Saved!");
        setTimeout(() => {
          $(".generate-alert").css("display", "none");
        }, 1500);
      })
      .catch(function (response) {
        showError([response]);
        $(".generate-alert").css("display", "none");
        $("#publish-team-ads").prop("disabled", false);
      });
  }

  $("#share-ads").on("click", function (e) {
    rememberTemplateSettings();

    e.preventDefault();
    var draft_url = $("#saved-draft").val();
    var project_url = $("#published-project").val();
    if (draft_url && project_url) {
      $("#shareModal #select-choice").show();
      $("#shareModal #select-choice p").text("Share draft or project:");
      $('#shareModal #select-choice label[for="save_draft"]').text("Draft");
      $('#shareModal #select-choice label[for="publish_to_team"]').text(
        "Project"
      );
      $("#shareModal").modal("show");
    } else if (draft_url || project_url) {
      $("#shareModal #select-choice").hide();
      $("#shareModal").modal("show");
    } else {
      var file_ids = $("input[name=file_ids]").val();
      if (!file_ids) {
        file_ids = "";
      }
      file_ids = file_ids.replace(/  +/g, " ");
      var formData = new FormData(document.getElementById("adForm"));
      formData.set("file_ids", file_ids);
      axios({
        method: "post",
        url: "/banner/can_share",
        data: formData,
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
        .then(function (response) {
          var data = response.data;
          if (data.status == "error") {
            showError(data.messages);
          } else {
            $("#shareModal").modal("show");
          }
        })
        .catch(function (response) {
          showError([response]);
        });
    }
  });

  $(document)
    .on("mouseover", ".grid-item", function (e) {
      $(this).children(".overlay").fadeIn();
    })
    .on("mouseleave", ".grid-item", function (e) {
      $(this).children(".overlay").fadeOut();
    });

  $(document).on(
    "click",
    "#selectImgModal .grid-item .overlay a",
    function (e) {
      e.preventDefault();
      e.stopPropagation();
      var base_url = window.location.origin;
      var path = $(this).data("path");
      var name = $(this).data("name");
      var width = $(this).data("width");
      var height = $(this).data("height");
      var index = $("#selectImgModal .grid-item .overlay a").index(this);

      $(".available-image-grid").hide();
      $("#selectImgModal .full-size-image").empty();
      $("#selectImgModal .full-size-image").append(
        $(`<a href="#" class="btn-back-grid">Back</a>`)
      );
      $("#selectImgModal .full-size-image").append(
        $(
          `<img src="${base_url}/share?file=${path}" class="product-image" id="full-size-image" />`
        )
      );
      $("#selectImgModal #full-size-image").data("name", name);
      $("#selectImgModal #full-size-image").data("type", "");
      $("#selectImgModal #full-size-image").data(
        "company_id",
        $(this).data("company_id")
      );
      $("#selectImgModal #full-size-image").data("path", path);
      $("#selectImgModal #full-size-image").data("file-index", index);
      // crop tool
      $("#selectImgModal .full-size-image").append(
        $(`
            <div class="overflow-hidden">
                <div class="cropped-image-size float-left">
                    <input type="number" id="crop_width" style="width: 64px; margin-top: 2px;">
                    x
                    <input type="number" id="crop_height" style="width: 64px; margin-top: 2px;">
                </div>
                <span class="product-image-description float-right">${name} [${width.toFixed(
          2
        )} x ${height.toFixed(2)} WxH]</span>
            </div>
        `)
      );
      $("#selectImgModal .full-size-image").append(
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
            </div>
            <div class="button-group text-right mt-2">
                <a href="#" id="save_edited_image">Save</a>
                <a href="#" id="cancel_edited_image">Cancel</a>
            </div>
        `)
      );
      $("#selectImgModal .full-size-image").show();

      // Crop, Resize, Rotate
      const image = $("#selectImgModal #full-size-image")[0];
      cropper = new Cropper(image, {
        autoCropArea: 1,
        zoomable: false,
        ready() {
          var origin_width = $("#selectImgModal #full-size-image")[0].width;
          var origin_height = $("#selectImgModal #full-size-image")[0].height;
          $("#resize_width").val(origin_width);
          $("#resize_height").val(origin_height);
        },
        crop(event) {
          $("#selectImgModal .cropped-image-size #crop_width").val(
            Math.round(event.detail.width)
          );
          $("#selectImgModal .cropped-image-size #crop_height").val(
            Math.round(event.detail.height)
          );
        },
      });
    }
  );

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
  });

  $("#selectImgModal #submit").on("click", function () {
    $("input[name=file_ids]").val(nameCheckedFiles2.join(" "));
    $("input[name=file_ids]").trigger("change");
    axios({
      method: "post",
      url: "/banner/update_product_selections",
      data: {
        file_ids: indexCheckedFiles2.join(" "),
      },
    })
      .then(function (response) {
        var data = response.data;
      })
      .catch(function (response) {
        showError([response]);
      });
  });

  $("#view-img").on("click", function () {
    if (!$(this).hasClass("disabled")) {
      var customer = $("input[name=customer]").val();
      var file_ids = $("input[name=file_ids]").val();
      if (!file_ids) {
        file_ids = "";
      }
      file_ids = file_ids.replace(/  +/g, " ");
      axios({
        method: "post",
        url: "/banner/view",
        data: {
          file_ids: file_ids,
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
                $("#selectImgModal #full-size-image").data("name", file.name);
                $("#selectImgModal #full-size-image").data("type", "");
                $("#selectImgModal #full-size-image").data(
                  "company_id",
                  file["company_id"]
                );
                $("#selectImgModal #full-size-image").data("path", file.path);
                $("#selectImgModal .full-size-image").append(
                  $(`
                                <div class="overflow-hidden">
                                    <div class="cropped-image-size float-left">
                                        <input type="number" id="crop_width" style="width: 64px; margin-top: 2px;">
                                        x
                                        <input type="number" id="crop_height" style="width: 64px; margin-top: 2px;">
                                    </div>
                                    <span class="product-image-description float-right">${
                                      file.name
                                    } [${file.width.toFixed(
                    2
                  )} x ${file.height.toFixed(2)} WxH]</span>
                                </div>
                            `)
                );
                $("#selectImgModal .full-size-image").append(
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
                      </div>
                      <div class="button-group text-right mt-2">
                          <a href="#" id="save_edited_image">Save</a>
                          <a href="#" id="cancel_edited_image">Cancel</a>
                      </div>
                  `)
                );
              }

              $("#selectImgModal .full-size-image").show();
              $("#selectImgModal #submit").hide();
              $("#selectImgModal").modal();

              // Crop, Resize, Rotate
              const image = $("#selectImgModal #full-size-image")[0];
              cropper = new Cropper(image, {
                autoCropArea: 1,
                zoomable: false,
                ready() {
                  var origin_width = $("#selectImgModal #full-size-image")[0]
                    .width;
                  var origin_height = $("#selectImgModal #full-size-image")[0]
                    .height;
                  $("#resize_width").val(origin_width);
                  $("#resize_height").val(origin_height);
                },
                crop(event) {
                  $("#selectImgModal .cropped-image-size #crop_width").val(
                    Math.round(event.detail.width)
                  );
                  $("#selectImgModal .cropped-image-size #crop_height").val(
                    Math.round(event.detail.height)
                  );
                },
              });
            }
          }
        })
        .catch(function (response) {
          showError([response]);
        });
    }
  });

  $(document).on(
    "change",
    "#selectImgModal #crop_width, #selectImgModal #crop_height",
    function () {
      var cropBoxData = cropper.getCropBoxData();
      var cropCanvasData = cropper.getCanvasData();
      var crop_width = $("#selectImgModal #crop_width").val();
      var crop_height = $("#selectImgModal #crop_height").val();
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

  $(document).on("change", "#selectImgModal #rotate_angle", function () {
    var angle = parseInt($(this).val());
    cropper.rotateTo(angle);
  });

  $(document).on(
    "change",
    "#selectImgModal #resize_width, #selectImgModal #resize_height",
    function () {
      var origin_width = $("#selectImgModal #full-size-image")[0].width;
      var origin_height = $("#selectImgModal #full-size-image")[0].height;
      var w = parseInt($("#selectImgModal #resize_width").val());
      var h = parseInt($("#selectImgModal #resize_height").val());
      cropper.scale(w / origin_width, h / origin_height);
    }
  );

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

  $("#shareModal #submit").on("click", function (e) {
    e.preventDefault();
    var draft_url = $("#saved-draft").val();
    var project_url = $("#published-project").val();
    var val = $("#shareModal input[name=share_ads]:checked").val();
    var email = $("#shareModal #share_email").val();
    if (draft_url && project_url) {
      var url, projectname;
      if (val == "save") {
        url = draft_url;
        projectname = $("#saved-draft").data("projectname");
      } else if (val == "publish") {
        url = project_url;
        projectname = $("#published-project").data("projectname");
      }
      sendNotificationEmail(email, url, projectname);
    } else if (draft_url || project_url) {
      var url, projectname;
      if (draft_url) {
        url = draft_url;
        projectname = $("#saved-draft").data("projectname");
      } else {
        url = project_url;
        projectname = $("#published-project").data("projectname");
      }
      sendNotificationEmail(email, url, projectname);
    } else {
      if (val == "save") {
        var formData = new FormData(document.getElementById("adForm"));
        $(".generate-alert").css("display", "flex");
        axios({
          method: "post",
          url: "/banner/generate",
          data: formData,
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
          .then((response) => {
            download_file(response.data);
            $("#generate-ads").prop("disabled", false);
            sendNotificationEmail(
              email,
              response.data.url,
              response.data.projectname
            );
            $(".generate-alert").html("Saved!");
            setTimeout(() => {
              $(".generate-alert").css("display", "none");
              $(".generate-alert").html("Generating...");
            }, 1500);
          })
          .catch((response) => {
            showError([response]);
            $(".generate-alert").css("display", "none");
            $("#generate-ads").prop("disabled", false);
          });
      } else if (val == "publish") {
        var formData = new FormData(document.getElementById("adForm"));
        $(".generate-alert").css("display", "flex");
        axios({
          method: "post",
          url: "/banner/publish",
          data: formData,
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
          .then(function (response) {
            download_file(response.data);
            $("#publish-team-ads").prop("disabled", false);
            sendNotificationEmail(
              email,
              response.data.url,
              response.data.projectname
            );
            $(".generate-alert").html("Saved!");
            setTimeout(() => {
              $(".generate-alert").css("display", "none");
              $(".generate-alert").html("Generating...");
            }, 1500);
          })
          .catch(function (response) {
            showError([response]);
            $(".generate-alert").css("display", "none");
            $("#publish-team-ads").prop("disabled", false);
          });
      }
    }
  });

  function download_file(data) {
    if (data.status == "error") {
      showError(data.messages);
    } else {
      if (data.status == "warning") {
        showError(data.messages);
      }
      var url = data.url;
      var log = data.log;

      $("#logModal .log-block").text(log);
      var link = document.createElement("a");
      link.href = url;
      document.body.appendChild(link);
      link.click();
      link.remove();
    }
  }

  $(document).on("click", "#selectImgModal #cancel_edited_image", function (e) {
    e.preventDefault();
    cropper.reset();
  });

  $(document).on("click", "#selectImgModal #save_edited_image", function (e) {
    e.preventDefault();
    $("#selectImgModal #save_edited_image").text("Saving");
    $("#selectImgModal #save_edited_image").removeAttr("href");
    var srcUrl = $("#selectImgModal #full-size-image").attr("src");
    srcUrl = srcUrl.split("=");
    var path = srcUrl[1];
    var filename = path.split("/").slice(-1)[0];
    path = path.split("/");
    path.pop();
    var ext = filename.split(".").slice(-1)[0];
    var name = filename.split(".")[0];
    var company_id = path[path.length - 1];
    cropper.getCroppedCanvas().toBlob(
      (blob) => {
        const formData = new FormData();

        // Pass the image file name as the third parameter if necessary.
        formData.append("croppedImage", blob);
        formData.append("filename", name + "_cropped." + ext);
        formData.append(
          "path",
          path.join("/") + "/" + name + "_cropped." + ext
        );
        formData.append("company_id", company_id);

        // Use `jQuery.ajax` method for example
        axios({
          method: "POST",
          url: "/banner/upload_cropped_product_image",
          data: formData,
          headers: {
            "Content-Type": "multipart/form-data",
          },
        }).then(function (response) {
          $("#selectImgModal #submit").show();
          var index = $("#selectImgModal #full-size-image").data("file-index");
          var filename = $("#selectImgModal #full-size-image").data("name");
          filename = filename.split(".")[0] + "_cropped";
          if (nameCheckedFiles2.length) {
            nameCheckedFiles2[index] = filename;
          } else {
            nameCheckedFiles2.push(filename);
          }
          $("#selectImgModal #save_edited_image").text("Save");
          $("#selectImgModal #save_edited_image").prop("href", "#");
          $(".notification").show();
          setTimeout(() => {
            $(".notification").hide();
          }, 3000);
        });
      } /*, 'image/png' */
    );
  });

  function disableLink(link) {
    // 1. Add isDisabled class to parent span
    link.parentElement.classList.add("isDisabled");
    // 2. Store href so we can add it later
    link.setAttribute("data-href", link.href);
    // 3. Remove href
    link.href = "";
    // 4. Set aria-disabled to 'true'
    link.setAttribute("aria-disabled", "true");
  }
  function enableLink(link) {
    // 1. Remove 'isDisabled' class from parent span
    link.parentElement.classList.remove("isDisabled");
    // 2. Set href
    link.href = link.getAttribute("data-href");
    // 3. Remove 'aria-disabled', better than setting to false
    link.removeAttribute("aria-disabled");
  }

  document.body.addEventListener("click", function (event) {
    // filter out clicks on any other elements
    if (
      event.target.nodeName == "A" &&
      event.target.getAttribute("aria-disabled") == "true"
    ) {
      event.preventDefault();
    }
  });

  $('input[name="file_ids"]').on("keyup", function () {
    if ($(this).val() == "") {
      disableLink($("#view-img")[0]);
    } else {
      enableLink($("#view-img")[0]);
    }
  });

  $('input[name="file_ids"]').on("change", function () {
    if ($(this).val() == "") {
      disableLink($("#view-img")[0]);
    } else {
      enableLink($("#view-img")[0]);
    }
  });

  $('input[name="file_ids"]').on("click", function () {
    if ($(this).val() == "") {
      disableLink($("#view-img")[0]);
    } else {
      enableLink($("#view-img")[0]);
    }
  });

  $("#reset_to_defaults").on("click", function (e) {
    /**
     * Add an 'are you sure' for reseting the form to default values
     */
    e.preventDefault();

    Swal.fire({
      title:
        "Reset templates for the current customer to their default settings?            ",
      showCancelButton: true,
      confirmButtonText: "Reset to defaults",
      cancelButtonText: "Cancel",
      icon: "info",
    }).then((result) => {
      if (result.value) {
        forgetTemplateSettings();
        location.reload();
      }
    });
  });

  $("#adForm").on("keypress", function (event) {
    if (event.key == "Enter") {
      event.preventDefault();
      $(event.target).trigger("change");
    }
  });

  window.onunload = function () {
    rememberTemplateSettings();
  };
});
