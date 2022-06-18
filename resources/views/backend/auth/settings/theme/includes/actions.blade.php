@if ($logged_in_user->isMasterAdmin() || $logged_in_user->company->id == $theme->company_id)
<x-utils.edit-button :href="route('admin.auth.settings.theme.edit', ['customer_id' => $customer_id, 'theme' => $theme])" />
@endif
<x-utils.form-button :action="route('admin.auth.settings.theme.copy', ['customer_id' => $customer_id, 'theme' => $theme])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-copy">
    @lang('Copy')
</x-utils.form-button>
@if ($logged_in_user->isMasterAdmin() || $logged_in_user->company->id == $theme->company_id)
<x-utils.form-button :action="route('admin.auth.settings.theme.toggle', ['customer_id' => $customer_id, 'theme' => $theme])" method="post" button-class="btn btn-info btn-sm" icon="fas fa-lock">
    @lang($theme->status ? 'Hide' : 'Show')
</x-utils.form-button>
<x-utils.delete-button :href="route('admin.auth.settings.theme.destroy', ['customer_id' => $customer_id, 'theme' => $theme])" />
<x-utils.form-button :action="route('admin.auth.settings.theme.moveup', ['customer_id' => $customer_id, 'theme' => $theme])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-arrow-up">
    @lang('Move Up')
</x-utils.form-button>
<x-utils.form-button :action="route('admin.auth.settings.theme.movedown', ['customer_id' => $customer_id, 'theme' => $theme])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-arrow-down">
    @lang('Move Down')
</x-utils.form-button>
@endif