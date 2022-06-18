<input type="hidden" value="{{ $image->url }}" />
<button class="btn btn-info btn-sm btn-view">
    <i class="fas fa-search"></i>
    @lang('View')
</button>
<x-utils.edit-button :href="route('admin.auth.settings.images.edit', $image)" />
<a class="btn btn-info btn-sm" id="download_image" href ="{{siteUrl().'/share?file='.$image->url}}">
    <i class="fas fa-download"></i>
    @lang('Download')
</a>
<x-utils.delete-button :href="route('admin.auth.settings.images.destroy', $image)" />