@if (! $logged_in_user->isMember())
    <x-utils.view-button :href="route('admin.auth.apikeys.show', $apiKey)" />
    <x-utils.edit-button :href="route('admin.auth.apikeys.edit', $apiKey)" />
    <x-utils.form-button :action="route('admin.auth.apikeys.toggle', $apiKey)" method="get" button-class="btn btn-info btn-sm" icon="fas fa-lock">
    @lang($apiKey->status ? 'Deactivate' : 'Activate')
    </x-utils.form-button>
    <x-utils.delete-button :href="route('admin.auth.apikeys.destroy', $apiKey)" />
@endif
