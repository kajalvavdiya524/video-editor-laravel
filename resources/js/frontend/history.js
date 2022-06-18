require("ekko-lightbox");

let selected_history = [];

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
    $("#share_email").val("");
    var subject = $(this).data("subject");
    $("#share_subject").val(subject);
    var body = $(this).data("body");
    $("#share_body").val(body);
    $("#copyTarget").val($(this).data("share-link"));
    $("#shareModal").modal("show");
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
      var body = $("#share_body").val();
      var mailto = document.createElement("a");
      mailto.href = `mailto:${email}?subject=${subject}&body=${body}`;
      mailto.target = "_blank";
      mailto.click();
    }
  });

  $(document).on(
    "click",
    "table tr td:not(:last-child):not(:first-child)",
    function () {

      var draft_type = get_type();
      if(draft_type == 'image') {

        var id = $(this).parent().find("#history-id").val();
        axios({
          method: "get",
          url: "/history/" + id + "/show",
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
  );

  $(document).on("click", "table tr td input[type='checkbox']", function () {
    let row = $(this).closest("tr");
    let history_id = row.find("#history-id").val();
    if ($(this).prop("checked")) {
      selected_history.push(history_id);
    } else {
      let index = selected_history.indexOf(history_id);
      if (index > -1) {
        selected_history.splice(index, 1);
      }
    }
    if (selected_history.length) {
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
        url: "/history/download_all",
        data: {
          history_ids: selected_history,
          download_name,
        },
      }).then(function (response) {
        selected_history = [];
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

 

  // $(document).on("click", ".btn-history-delete", function() {
  //     var id = $(this).data('history-id');
  //     axios({
  //         method: 'post',
  //         url: '/history/delete',
  //         data: {
  //             id
  //         }
  //     })
  //     .then( (response) => {
  //         $(this).closest("tr").remove();
  //     })
  //     .catch(function (response) {
  //         showError([response]);
  //     });
  // });


    $(document).on("click", ".btn-history-edit", function() {
   
      forgetTemplateSettings($(this).data('customer-id'));
      var id = $(this).data('history-id');
      window.location.href = "/history/" + id + "/edit";

    });

    $("#togle_list").on("click", function() {
      toggle_viewmode();
    });

    show_results();

});
