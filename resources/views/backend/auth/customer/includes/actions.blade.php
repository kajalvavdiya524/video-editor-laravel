@if (!$logged_in_user->isMember())
    <x-utils.view-button :href="route('admin.auth.customer.show', $customer)" />
    <x-utils.edit-button :href="route('admin.auth.customer.edit', $customer)" />
    <x-utils.form-button :action="route('admin.auth.customer.toggle', $customer)" method="post" button-class="btn btn-info btn-sm" icon="fas fa-lock">
    @lang($customer->status ? 'Hide' : 'Show')
    </x-utils.form-button>
    <x-utils.delete-button :href="route('admin.auth.customer.destroy', $customer)" />

@endif
