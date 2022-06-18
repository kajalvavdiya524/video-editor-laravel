$(document).ready(function () {
    $('.btn-layout-assign').on('click', function () {
        const layout_id = $(this).attr('data-layout-id');
        $('#companyModal input[name="layout_id"]').val(layout_id);

        const selectedCompanies = $(this).attr('data-layout-companies').split(" ");
        selectedCompanies.forEach((id) => {
            $(`#companyModal #company_${id}`).prop('checked', true);
        })
        
        $('#companyModal').modal('show');
    })
});