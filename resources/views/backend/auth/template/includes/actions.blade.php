@if ($logged_in_user->isMasterAdmin() || $logged_in_user->company->id == $template->company_id)
<x-utils.edit-button :href="route('admin.auth.template.edit', ['customer_id' => $customer_id, 'template' => $template])" />
@endif
<x-utils.form-button :action="route('admin.auth.template.copy', ['customer_id' => $customer_id, 'template' => $template])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-copy">
    @lang('Copy')
</x-utils.form-button>
@if ($logged_in_user->isMasterAdmin() || $logged_in_user->company->id == $template->company_id)
<x-utils.form-button :action="route('admin.auth.template.toggle', ['customer_id' => $customer_id, 'template' => $template])" method="post" button-class="btn btn-info btn-sm" icon="fas fa-lock">
    @lang($template->status ? 'Hide' : 'Show')
</x-utils.form-button>
<x-utils.delete-button :href="route('admin.auth.template.destroy', ['customer_id' => $customer_id, 'template' => $template])" />
<x-utils.form-button :action="route('admin.auth.template.moveup', ['customer_id' => $customer_id, 'template' => $template])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-arrow-up">
</x-utils.form-button>
<x-utils.form-button :action="route('admin.auth.template.movedown', ['customer_id' => $customer_id, 'template' => $template])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-arrow-down">
</x-utils.form-button>
@endif