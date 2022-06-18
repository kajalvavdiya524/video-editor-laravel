require("select2");

$(document).ready(function() {
    const projectTypeSelect = $('select[name="type"]');
    const parentIdSelect = $('select[name="parent_id"]');
    const countrySelect = $('select[name="country_id"]');
    const languageSelect = $('select[name="language_id"]');
    var previous_country, previous_language;

    function drawText() {
        
        const { template_data, previewCanvas } = window;
        if (template_data && previewCanvas) {
            previewCanvas.getObjects().forEach(function(o) {
                if (o.id.includes("text")) {
                    previewCanvas.remove(o);
                }
            });
            template_data.fields.forEach((field, index) => {
                if (field.type == "Text") {
                    var options = JSON.parse(field.options);
                    var text_val = $(`input[name="${field.element_id}"]`).val();
                    var color = $(`#${field.element_id}_color`).val();
                    var font = $(`#${field.element_id}_font`).val();
                    var font_size = $(`#${field.element_id}_fontsize`).val();
                    var offset_x = $(`#${field.element_id}_offset_x`).val();
                    var offset_y = $(`#${field.element_id}_offset_y`).val();


                    //ward
                    if (text_val != "") {
                        var text = new fabric.Textbox(text_val, {
                            id: "text" + index,
                            element_id: field.element_id,
                            order: parseInt(options["Order"]),
                            top:
                                parseFloat(options["Y"]) + parseFloat(offset_y),
                            left:
                                parseFloat(options["X"]) + parseFloat(offset_x),
                            width: parseInt(options["Width"]),
                            textAlign: options["Alignment"]
                                ? options["Alignment"]
                                : "left",
                            fontSize: font_size
                                ? font_size
                                : parseInt(options["Font Size"]),
                            fill: color ? color : options["Font Color"],
                            fontFamily: font
                                ? font
                                : options["Font"]
                                ? options["Font"]
                                : "Proxima-Nova-Semibold",
                            selectable: options["Moveable"] == "Yes",
                            evented: options["Moveable"] == "Yes",
                            charSpacing: options["Text Tracking"]
                                ? parseInt(options["Text Tracking"])
                                : 0
                        });
                        previewCanvas.add(text);
                    }
                }
            });

            var objects = previewCanvas.getObjects();
            objects.sort((a, b) => {
                return b.order - a.order;
            });
            objects.forEach(element => {
                previewCanvas.bringToFront(element);
            });
        }
    }

    parentIdSelect.select2({
        theme: "bootstrap4",
        ajax: {
            url: "/projects/master_projects",
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data, params) {
                console.log(data);
                return {
                    results: data.items
                };
            }
        }
    });

    projectTypeSelect.change(function(e) {
        var type = $(this).val();
        if (type == 0 || type == 1) {
            $(".parent-select").addClass("d-none");
            $(".country-select").addClass("d-none");
            $(".language-select").addClass("d-none");
            $(".project-name").removeClass("col-md-8 col-md-6");
            $(".project-name").addClass("col-md-10");
            $(".project-name").removeClass("d-none");
            if (type == 1) {
                $(".d-none-parent").hide();
            } else {
                $(".d-none-parent").show();
            }
        } else {
            if (type == 2) {
                $(".country-select").addClass("d-none");
                $(".language-select").addClass("d-none");
                $(".project-name").removeClass("col-md-10 col-md-6");
                $(".project-name").addClass("col-md-8");
                $(".project-name").removeClass("d-none");
                $(".parent-select").removeClass("d-none");
                $(".d-none-parent").show();
            } else {
                $(".country-select").removeClass("d-none");
                $(".language-select").removeClass("d-none");
                $(".project-name").removeClass("col-md-10 col-md-8");
                $(".project-name").addClass("col-md-6");
                $(".project-name").removeClass("d-none");
                $(".parent-select").addClass("d-none");
                $(".d-none-parent").show();
            }
        }
    });

    var countries = [];
    fetch("/projects/countries")
        .then(res => res.json())
        .then(json => {
            countries = json;
            return fetch(`/projects/languages?c=United%20States`);
        })
        .then(res => res.json())
        .then(languages => {
            countrySelect.empty();
            countries.map(c => {
                countrySelect.append(
                    `<option value="${c}" ${
                        "United States" == c ? "selected" : ""
                    }>${c}</option>`
                );
            });
            languageSelect.empty();
            languages.map(l => {
                languageSelect.append(`<option value="${l}">${l}</option>`);
            });
            updateLanguageTextUI();
        });

    countrySelect.change(function() {
        $('input[name="headline[]"').val("");
        $('input[name="subheadline[]"').val("");

        // save previous values;
        updateLanguageText() ;

        var country = $(this).val();
        fetch(`/projects/languages?c=${country}`)
            .then(res => res.json())
            .then(languages => {
                languageSelect.empty();
                languages.map(l => {
                    languageSelect.append(`<option value="${l}">${l}</option>`);
                });
                updateLanguageTextUI();
            });
    });

    languageSelect.on('change', function () {
        // save previous values;
        updateLanguageText() ;
        // restore previously saved values;
        updateLanguageTextUI();
    });
        
    $(languageSelect).on('focus', function () {
        // Store the current value on focus 
        previous_language = this.value;
    });
    
    $(countrySelect).on('focus', function () {
        // Store the current value on focus 
        previous_country = this.value;
    });
    
    function updateLanguageText() {
       
       // var country = countrySelect.val();
       // var language = languageSelect.val();
        
        var country = previous_country;
        var language = previous_language;

        /*alert (country);
        alert (language);*/

        var headlines = [];
        $('input[name="headline[]"]').each(function() {
            headlines.push($(this).val());
        });
        var subheadlines = [];
        $('input[name="subheadline[]"]').each(function() {
            subheadlines.push($(this).val());
        });
        var text1 = $('input[name="text1"]').val();
        var text2 = $('input[name="text2"]').val();
        var text3 = $('input[name="text3"]').val();
        var productIDs = $('input[name="file_ids"]').val();

        field_texts = [];
        field_texts_dimensions = [];
       
        // store/save current values
        $(".template-text-field").each(function() {
            field_texts.push($(this).val());
            var text_dimentions= [];
            var input_id = $(this).attr('name');
            text_dimentions['offset_x'] = $("#"+input_id+"_offset_x").val();
            text_dimentions['offset_y'] = $("#"+input_id+"_offset_y").val();
            text_dimentions['width'] = $("#"+input_id+"_width").val();
            text_dimentions['angle'] = $("#"+input_id+"_angle").val();
            field_texts_dimensions.push(text_dimentions);
        });

        var key = country + "_" + language;
        productTexts[key] = {
            headlines,
            subheadlines,
            text1,
            text2,
            text3,
            productIDs,
            field_texts,
            field_texts_dimensions
        };
    }

    function updateLanguageTextUI() {
        $('input[name="headline[]"').val("");
        $('input[name="subheadline[]"').val("");
        var country = countrySelect.val();
        var language = languageSelect.val();
        var key = country + "_" + language;
        //console.log(productTexts, key);
       
        /*alert (country);
        alert (language);*/

        if (productTexts[key]) {
            $('input[name="headline[]"]').each(function(i) {
                $(this).val(productTexts[key].headlines[i]);
            });
            $('input[name="subheadline[]"]').each(function(i) {
                $(this).val(productTexts[key].subheadlines[i]);
            });

            for (let i = 1; i <= 3; i++) {
                const name = "text" + i;
                $(`input[name="${name}"]`).val("productTexts[key][name]");
            }
            $('input[name="file_ids"]').val(productTexts[key].productIDs);
            $(".template-text-field").each(function(i) {
               
                $(this).val(productTexts[key].field_texts[i]);
                if (productTexts[key].field_texts_dimensions[i]){
                    // console.log ( productTexts[key].field_texts_dimensions[i]['offset_x']);
                    var input_id = $(this).attr('name');
                    $("#"+input_id+"_offset_x").val(productTexts[key].field_texts_dimensions[i]['offset_x'] );
                    $("#"+input_id+"_offset_y").val(productTexts[key].field_texts_dimensions[i]['offset_y'] );
                    $("#"+input_id+"_width").val(productTexts[key].field_texts_dimensions[i]['width'] );
                    $("#"+input_id+"_angle").val(productTexts[key].field_texts_dimensions[i]['angle'] );
                    
                }
            });
        }

        window.drawTextNewTemplate();
        if (country && language) {
            //updateLanguageText();
            previous_country = country;
            previous_language = language;
        }
    }

    $(
        'input[name="file_ids"], input[name="headline[]"], input[name="subheadline[]"], input[name="text1"], input[name="text2"], input[name="text3"]'
    ).on("input", function() {
        updateLanguageText();
    });

    $(".template-text-field").on("input", function() {
        updateLanguageText();

        var max_chars = parseInt($(this).attr('data-max-chars'));
        if (max_chars > 0) {
            var cur_chars = $(this).val().length;
            var badge = $(this).prev().find('.badge');
            badge.removeClass('badge-success');
            badge.removeClass('badge-danger');
            badge.addClass(max_chars >= cur_chars ? 'badge-success' : 'badge-danger');
            badge.text(`${cur_chars}/${max_chars}`);
        }
    });

    previous_country = countrySelect.val();
    previous_language = languageSelect.val();

});
