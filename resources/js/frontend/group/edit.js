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

    $('.grid-stack').on('change', (e) => {
        window.onbeforeunload = function () {
            return true;
        };
    });

    $('input[name="name"]').on('input', () => {
        window.onbeforeunload = function () {
            return true;
        };
    })

    const customer_id = $("#customer_id").val();
    const layout_id = $("#layout_id").val();
    $(".grid-stack-item-template").each(function() {
        const id = $(this).attr("data-id");
        const template_id = $(this).attr("data-template");
        $(this).prepend(`
            <a href="/banner/${customer_id}/group/${layout_id}/template/${id}/${template_id}"><i class="c-icon cil-pencil"></i></a>
        `);
    });

    $(document).on("click", ".grid-stack-item-remove", function() {
        grid.removeWidget($(this).closest(".grid-stack-item")[0]);
        window.onbeforeunload = function () {
            return true;
        };
    });

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
        window.onbeforeunload = function () {
            return true;
        };
    });

    $(".update-layout-btn").on("click", function(e) {
        window.onbeforeunload = null;
        const layout = grid.save();
        layout.forEach(item => {
            var dom_item = $(item.content).find(".grid-stack-item-template");
            item.instance_id = dom_item.attr('data-id');
            item.template_id = parseInt(dom_item.attr('data-template'));
            item.width = parseInt(dom_item.attr('data-width'));
            item.height = parseInt(dom_item.attr('data-height'));
            delete item.content;
        });
        $('input[name="settings"]').val(JSON.stringify(layout));
    });
});
