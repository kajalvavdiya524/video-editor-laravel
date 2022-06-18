const ACTION_SELECT = 'select';
const ACTION_EXCLUDE = 'exclude';

$(document).ready(() => {
    function addOrRemoveCompany(event) {
        event.preventDefault()
        $(this).parent().dropdown('show')
        const entityId = $(this).data('entity-id')
        const companyId = $(this).data('company-id')
        const action = $(this).data('action')
        axios.post(ENTITY_URL, {
            _token: $('meta[name="csrf-token"]').attr('content'),
            entityId,
            companyId,
            action
        }).then(({ data }) => {
            if ([ACTION_SELECT, ACTION_EXCLUDE].includes(data.action)) {
                if (data.action === ACTION_SELECT) {
                    $(this).data('action', ACTION_EXCLUDE)
                    $(this).parent().find('i').each(function () {
                        $(this).addClass('cil-check-alt')
                    })
                } else {
                    $(this).data('action', ACTION_SELECT)
                    $(this).parent().find('i').each(function () {
                        $(this).removeClass('cil-check-alt')
                    })
                }
            } else {
                if (data.isExists) {
                    $(this).find('i').addClass('cil-check-alt')
                } else {
                    $(this).find('i').removeClass('cil-check-alt')
                }

                if (data.isSelectAll) {
                    $(this).parent().find('.all-companies a i').addClass('cil-check-alt')
                } else {
                    $(this).parent().find('.all-companies a i').removeClass('cil-check-alt')
                }
                const action = data.isSelectAll ? ACTION_EXCLUDE : ACTION_SELECT
                $(this).parent().find('.all-companies').data('action', action)
            }
        })
    }

    $('.dropdown-menu li').each(function () {
        $(this).on('click', addOrRemoveCompany)
    });
})