$(document).ready(function () {
    $('select[name=product_layouts]').change(function (e) {
        var val = $(this).val();
        changeTemplateLayouts(val);
    });

    function changeTemplateLayouts(index) {
        $('.textlines input[name="headline[]"]').val("");  // empty input fields
        $('.textlines input[name=subheadline]').val("");
        if (index == 0) {
            $('.product-ids').show();
            $('.col-product-spacing').show();
            $('.textlines').show();
            $('.logo-button').show();
            $('.text-tracking').show();
            $('.product-layering').show();
            $('.border-stroke').show();
            $('.mirror-fade').show();
            $('.drop-shadow').show();
            $('.save1-layout').addClass("d-none");
        } else if (index == 1) {
            $('.product-ids').show();
            $('.col-product-spacing').hide();
            $('.textlines').hide();
            $('.logo-button').hide();
            $('.text-tracking').show();
            $('.product-layering').hide();
            $('.border-stroke').hide();
            $('.mirror-fade').show();
            $('.drop-shadow').show();
            $('.save1-layout').show();
            $('.save1-layout').removeClass("d-none");
        } else if (index == 2) {
            $('.product-ids').hide();
            $('.textlines').hide();
            $('.textlines.headlines').show();
            $('.logo-button').hide();
            $('.text-tracking').hide();
            $('.product-layering').hide();
            $('.border-stroke').hide();
            $('.mirror-fade').hide();
            $('.drop-shadow').hide();
            $('.save1-layout').addClass("d-none");
        } else if (index == 3) {
            $('.product-ids').show();
            $('.col-product-spacing').hide();
            $('.textlines').hide();
            $('.logo-button').hide();
            $('.text-tracking').hide();
            $('.product-layering').hide();
            $('.border-stroke').hide();
            $('.mirror-fade').show();
            $('.drop-shadow').show();
            $('.save1-layout').addClass("d-none");
        }
    }
});
