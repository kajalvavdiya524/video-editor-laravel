@if ($urlsfile->status)
    <x-utils.link
        class="btn btn-info btn-sm download-list-btn"
        icon="fas fa-list-alt"
        :text="__('Download List')" 
        data-toggle="tooltip" 
        title="Download list of new/changed items."
    />
@endif

@if (!$urlsfile->status)
<x-utils.link
        class="btn btn-primary btn-sm get-btn"
        icon="fas fa-database"
        :text="__('Get')" />
@endif

@if ($urlsfile->status)
    <x-utils.link
        class="btn btn-success btn-sm download-new-prod-file-btn"
        icon="fas fa-cloud-download-alt"
        :text="__('Download New Prod Images')" 
        data-toggle="tooltip" 
        title="Download New Product Images"
    />
    <x-utils.link
        class="btn btn-success btn-sm download-prod-file-btn"
        icon="fas fa-cloud-download-alt"
        :text="__('Download Prod Images')" 
        data-toggle="tooltip" 
        title="Download Product Images"
    />
    <x-utils.link
        class="btn btn-success btn-sm download-nfi-file-btn"
        icon="fas fa-cloud-download-alt"
        :text="__('Download NF+I Images')" 
        data-toggle="tooltip" 
        title="Download Nutrition Facts + Ingredients Images"
    />
@endif

<x-utils.delete-button 
    :href="route('admin.auth.settings.updatefile.destroy', $urlsfile)" 
    :text="__('Delete')" />

<input type="hidden" class="urlsfile_id" value="{{$urlsfile->id}}" />
