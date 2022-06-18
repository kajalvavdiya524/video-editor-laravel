require("ekko-lightbox");

let selected_project = [];
$(document).ready(function () {
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
    $(".alert").show();
    setTimeout(function () {
      $(".alert").hide();
    }, 4000);
  }

  $(document).on("click", "a.share-action", function () {
    var project_name = $(this).data("project-name");
    var project_url = $(this).data("project-url");
    var project_id = $(this).data("project-id");
    var site_url = $('meta[name="siteUrl"]').attr("content");
    
    localStorage.setItem("project_name", project_name);
    localStorage.setItem("project_url", project_url);
    localStorage.setItem("project_id", project_id);

    $("#share_email").val("");
    $("#copyTarget").val($(this).data("share-link"));
    $("#share_subject").val(`${project_name} Banner`);
    $("#share_body").val(
      `Here is the banner. ${site_url}/share?file=${project_url}`
    );
    $("#request_approval").val("");
    $("#shareModal").modal("show");
  });

  $(document).on("change", "#request_approval", function () {
    var checked = $(this).is(":checked");
    var project_name = localStorage.getItem("project_name");
    var project_url = localStorage.getItem("project_url");
    var project_id = localStorage.getItem("project_id");
    var site_url = $('meta[name="siteUrl"]').attr("content");
    var user_name = $('meta[name="userName"]').attr("content");
    var user_id = $('meta[name="userId"]').attr("content");

    if (checked) {
      $("#share_subject").val(`Approval Request for ${project_name} Project`);
      $("#share_body").val(
        `${user_name} is requesting approval to publish the following creative project:\n` +
          `Project Name: ${project_name}\n` +
          `Review the Project: ${site_url}/projects/${project_id}/request_approve?requester_id=${user_id}&request_timestamp=${Date.now()}\n`
      );
    } else {
      $("#share_subject").val(`${project_name} Banner`);
      $("#share_body").val(
        `Here is the banner. ${site_url}/share?file=${project_url}`
      );
    }
  });

  $(document).on("click", "#shareModal #submit", function () {
    var email = $("#share_email").val();
    email = email
      .split(/[ ,]+/)
      .filter(function (v) {
        return v !== "";
      })
      .join(",");
    if (email) {
      var subject = $("#share_subject").val();
      var body = encodeURIComponent($("#share_body").val());
      var mailto = document.createElement("a");
      mailto.href = `mailto:${email}?subject=${subject}&body=${body}`;
      mailto.target = "_blank";
      mailto.click();
    }
  });


  $("table tr td:not(:last-child):not(:first-child)").on("click", function () {
  
    var project_type = get_type();
    if(project_type == 'image') {
      var id = $(this).parent().find("#project-id").val();
      var type = $(this).parent().find("#project-type").val();
      if (type != 1) {
        axios({
          method: "get",
          url: "/projects/" + id + "/show",
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
          .then(function (response) {
            var data = response.data;
            var files = data.jpg_files.split(" ");
            $("#preview-images").empty();
            var anchors = [];
            for (var file of files) {
              var anchor = $(
                `<a href="/share?file=outputs/jpg/${file}" class="preview-image" data-gallery="preview-image-gallery"></a>`
              );
              anchors.push(anchor);
              $("#preview-images").append(anchor);
            }
            anchors[0].ekkoLightbox({ alwaysShowClose: true });
          })
          .catch(function (response) {
            showError([response]);
          });
      }
    }
  });

  $(document).on("click", "table tr td input[type='checkbox']", function () {
    let row = $(this).closest("tr");
    let project_id = row.find("#project-id").val();
    if ($(this).prop("checked")) {
      selected_project.push(project_id);
    } else {
      let index = selected_project.indexOf(project_id);
      if (index > -1) {
        selected_project.splice(index, 1);
      }
    }
    if (selected_project.length) {
      $("#download_all").show();
    } else {
      $("#download_all").hide();
    }
  });

  $(document).on("click", "#download_all", async function () {
    const { value: download_name } = await Swal.fire({
      title: "Please input download name.",
      input: "text",
      inputLabel: "Filename",
      inputPlaceholder: "Enter the file name",
    });
    if (download_name) {
      axios({
        method: "post",
        url: "/projects/download_all",
        data: {
          project_ids: selected_project,
          download_name,
        },
      }).then(function (response) {
        selected_project = [];
        $("table tr td input[type='checkbox']").prop("checked", false);
        $("#download_all").hide();
        var link = document.createElement("a");
        link.href = "/" + response.data;
        document.body.appendChild(link);
        link.click();
        link.remove();
      });
    }
  });

  $(document).on("click", ".btn-project-edit", function() {
   
    forgetTemplateSettings($(this).data('customer-id'));
    var id = $(this).data('project-id');
    window.location.href = "/projects/" + id + "/edit";

  });

  $("#togle_list").on("click", function() {
    toggle_viewmode();
  });

  show_results();

});
