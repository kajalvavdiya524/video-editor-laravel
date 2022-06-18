$(document).ready(function () {
    $("#member").CreateMultiCheckBox({ defaultText : 'Please select team members', height:'250px' });
    $("#customer").CreateMultiCheckBox({ defaultText : 'Please select customers', height:'250px' });

    $(document).on("click", ".MultiCheckBox", function () {
        var detail = $(this).next();
        detail.show();
    });

    $(document).on("click", ".MultiCheckBoxDetailHeader input", function (e) {
        e.stopPropagation();
        var hc = $(this).prop("checked");
        $(this).closest(".MultiCheckBoxDetail").find(".MultiCheckBoxDetailBody input").prop("checked", hc);
        $(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();
    });

    $(document).on("click", ".MultiCheckBoxDetailHeader", function (e) {
        var inp = $(this).find("input");
        var chk = inp.prop("checked");
        inp.prop("checked", !chk);
        $(this).closest(".MultiCheckBoxDetail").find(".MultiCheckBoxDetailBody input").prop("checked", !chk);
        $(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();
    });

    $(document).on("click", ".MultiCheckBoxDetail .cont input", function (e) {
        e.stopPropagation();
        $(this).closest(".MultiCheckBoxDetail").next().UpdateSelect();

        var val = ($(".MultiCheckBoxDetailBody input:checked").length == $(".MultiCheckBoxDetailBody input").length)
        $(".MultiCheckBoxDetailHeader input").prop("checked", val);
    });

    $(document).on("click", ".MultiCheckBoxDetail .cont", function (e) {
        var inp = $(this).find("input");
        var chk = inp.prop("checked");
        inp.prop("checked", !chk);

        var multiCheckBoxDetail = $(this).closest(".MultiCheckBoxDetail");
        multiCheckBoxDetail.next().UpdateSelect();

        var val = ($(".MultiCheckBoxDetailBody input:checked").length == $(".MultiCheckBoxDetailBody input").length)
        $(".MultiCheckBoxDetailHeader input").prop("checked", val);
    });

    $(document).mouseup(function (e) {
        var container = $(".MultiCheckBoxDetail");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });
});

var defaultMultiCheckBoxOption = { defaultText: 'Select Below', height: '200px' };

jQuery.fn.extend({
    CreateMultiCheckBox: function (options) {

        var localOption = {};
        
        var selectedText = '';

        $(this).find('option').each(function () {
            if ($(this).attr('selected') == 'selected') {
                selectedText += ($(this).text() + ' ');
            }
        });

        localOption.defaultText = selectedText ? selectedText : ((options != null && options.defaultText != null && options.defaultText != undefined) ? options.defaultText : defaultMultiCheckBoxOption.defaultText);
        localOption.height = (options != null && options.height != null && options.height != undefined) ? options.height : defaultMultiCheckBoxOption.height;

        this.hide();
        this.attr("multiple", "multiple");
        var divSel = $("<div class='MultiCheckBox'><span class='SelectedContent'>" + localOption.defaultText + "</span><span class='k-icon k-i-arrow-60-down'><svg aria-hidden='true' focusable='false' data-prefix='fas' data-icon='sort-down' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512' class='svg-inline--fa fa-sort-down fa-w-10 fa-2x'><path fill='currentColor' d='M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z' class=''></path></svg></span></div>").insertBefore(this);
        

        var detail = $("<div class='MultiCheckBoxDetail'><div class='MultiCheckBoxDetailBody'></div></div>").insertAfter(divSel);
        detail.css({ "max-height": localOption.height });
        var multiCheckBoxDetailBody = detail.find(".MultiCheckBoxDetailBody");
        
        var id = $(this).attr('id');
        this.find("option").each(function () {
            var val = $(this).attr("value");

            if (val == undefined)
                val = '';

            if (id == 'customer' && $(this).text() == 'Generic') {
                multiCheckBoxDetailBody.append("<div class='cont disabled'><div><input type='checkbox' class='mulinput' name='" + id + val + "' value='" + val + "' checked disabled /></div><div>" + $(this).text() + "</div></div>");
                multiCheckBoxDetailBody.append("<div class='cont d-none'><div><input type='checkbox' class='mulinput' name='" + id + val + "' value='" + val + "' checked  /></div><div>" + $(this).text() + "</div></div>");
            }
            else {
                if ($(this).attr("selected"))
                    multiCheckBoxDetailBody.append("<div class='cont'><div><input type='checkbox' class='mulinput' name='" + id + val + "' value='" + val + "' checked /></div><div>" + $(this).text() + "</div></div>");
                else
                    multiCheckBoxDetailBody.append("<div class='cont'><div><input type='checkbox' class='mulinput' name='" + id + val + "' value='" + val + "' /></div><div>" + $(this).text() + "</div></div>");
            }
        });

        multiCheckBoxDetailBody.css("max-height", (parseInt($(".MultiCheckBoxDetail").css("max-height")) - 28) + "px");

    },
    UpdateSelect: function () {
        var arr = [];
        var label = "";

        this.prev().find(".mulinput:checked").each(function () {
            if (! $(this).attr("disabled")) {
                arr.push($(this).val());
                label = label + " " + $(this).parent().next().text();
                
            }
        });
        this.parent().find(".SelectedContent").text(label);
        this.val(arr);
    },
});