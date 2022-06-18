require("bootstrap-fileinput");
require("select2");

$(document).ready(function () {
  $(".form-control-file").fileinput({
    showUpload: false,
    previewFileType: "any",
  });

  $(document).on("change", 'input[type="color"]', function (e) {
    var hex = $(this).closest(".form-row").find(".color-hex");
    var color = $(this).val();
    hex.val(color);
    hex.trigger("change");
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

  $("select[name=output_dimensions]").on("change", function (e) {
    var defaults = {
      headline_font_size: [44, 40, 70, 24, 52],
      subheadline_font_size: [18, 16, 28, 0, 0],
      CTA_font_size: [19, 19, 16, 11, 19],
      compress_size: [40, 30, 80, 10, 25],
    };
    var index = parseInt($(this).val());
    Object.keys(defaults).forEach(function (key) {
      $(`input.${key}`).val(index >= 0 ? defaults[key][index] : defaults[key]);
    });

    var customer = $("select[name=customer]").val();
    var template = $(this).val();

    var file_ids = $('input[name="file_ids"]').val();
    var product_format = $('input[name="product_format"]').val();
    var sub_text = $('input[name="sub_text"]').val();
    var quantity = $('input[name="quantity"]').val();
    var unit = $('input[name="unit"]').val();

    setCookie("file_ids", file_ids, 1);
    setCookie("product_format", product_format, 1);
    setCookie("sub_text", sub_text, 1);
    setCookie("quantity", quantity, 1);
    setCookie("unit", unit, 1);

    window.location.href = "/banner/" + customer + "/" + template;
  });

  $("#multi_headline").on("click", function (e) {
    $(".multi-headline").toggleClass("d-none");
  });
});
