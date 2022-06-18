var Sortable = require("sortablejs").Sortable;

$(document).ready(function() {
    var el = document.querySelector(".columns-list");
    new Sortable(el, {
        handle: ".column-handler"
    });
});
