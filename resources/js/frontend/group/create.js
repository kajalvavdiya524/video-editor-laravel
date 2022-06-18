import { GridStack } from "gridstack";
import { v4 as uuidv4 } from "uuid";
import "gridstack/dist/h5/gridstack-dd-native";

$(document).ready(function() {
  const grid = GridStack.init(
    {
      alwaysShowResizeHandle: true
    },
    ".grid-stack"
  );

  $(".add-grid-item").on("click", function() {
    const template_id = $("#grid-view-template").val();
    const selected = $("#grid-view-template option:selected");
    const template_name = selected.text();
    const width = selected.attr("data-width");
    const height = selected.attr("data-height");
    grid.addWidget({
      w: 12,
      content: `<div><span class="grid-stack-item-remove">&times;</span><div class="grid-stack-item-template d-flex align-items-center" data-id="${uuidv4()}" data-template="${template_id}" data-width="${width}" data-height="${height}">${template_name}</div></div>`
    });
  });

  $(document).on("click", ".grid-stack-item-remove", function() {
    grid.removeWidget($(this).closest(".grid-stack-item")[0]);
  });

  $(".create-layout-btn").on("click", function(e) {
    const layout = grid.save();
    layout.forEach(item => {
      var dom_item = $(item.content).find(".grid-stack-item-template");
      item.instance_id = dom_item.attr("data-id");
      item.template_id = parseInt(dom_item.attr("data-template"));
      item.width = parseInt(dom_item.attr("data-width"));
      item.height = parseInt(dom_item.attr("data-height"));
      delete item.content;
    });
    $('input[name="settings"]').val(JSON.stringify(layout));
  });
});
