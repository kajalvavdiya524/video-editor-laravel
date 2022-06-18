<x-utils.form-button :action="route('admin.auth.settings.imagelist.copy', ['imagelist' => $imagelist])" method="post" button-class="btn btn-success btn-sm" icon="fas fa-copy">
    @lang('Copy')
</x-utils.form-button>
@if ($logged_in_user->isMasterAdmin() || $logged_in_user->company_id == $imagelist->company_id)
    <x-utils.edit-button :href="route('admin.auth.settings.imagelist.edit', $imagelist)" />
    <x-utils.delete-button :href="route('admin.auth.settings.imagelist.destroy', $imagelist)" />
@endif