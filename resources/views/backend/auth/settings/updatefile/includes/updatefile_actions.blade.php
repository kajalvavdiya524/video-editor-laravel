@php
    $user = auth()->user();
@endphp
@if ($user->isMasterAdmin() || $user->company_id == $loading_history->company_id)
<x-utils.form-button
    :action="route('admin.auth.settings.loading.download_file', $loading_history)"
    method="get"
    button-class="btn btn-primary btn-sm"
    icon="fas fa-cloud-download-alt"
>
    @lang('Download')
</x-utils.form-button>
<x-utils.delete-button 
    :href="route('admin.auth.settings.loading.destroy', $loading_history)" 
    :text="__('Delete')" />
@endif