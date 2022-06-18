<x-utils.form-button
    :action="route('frontend.file.uploadimg.download_image', $upload_image)"
    method="get"
    button-class="btn btn-info btn-sm"
    icon="fas fa-cloud-download-alt"
>
    @lang('Download')
</x-utils.form-button>

<x-utils.link
    href="javascript: void();"
    class="btn btn-primary btn-sm edit-upload-file"
    icon="fas fa-pencil-alt"
    :text="__('Edit')" />

<x-utils.delete-button 
    :href="route('frontend.file.uploadimg.destroy', $upload_image)" 
    :text="__('Delete')" />
@if ($upload_image->company_id)
<input type="hidden" id="company_id" value="{{$upload_image->company_id}}"/>
@else
<input type="hidden" id="company_id" value="0"/>
@endif
<input type="hidden" id="id" value="{{$upload_image->id}}"/>
<input type="hidden" id="upload_image" value="{{$upload_image->id}}"/>

