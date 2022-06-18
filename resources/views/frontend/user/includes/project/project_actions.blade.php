<x-utils.link
    :text="__('Download')"
    class="btn btn-primary btn-sm"
    :href="route('frontend.projects.download', $project)"
    icon="fas fa-download" />
<x-utils.link
    :text="__('Share')"
    class="btn btn-success btn-sm share-action"
    data-project-name="{{ $project->name }}"
    data-project-url="{{ $project->url }}"
    data-project-id="{{ $project->id }}"
    data-share-link="{{ route('frontend.projects.share_project', $project->sharelink()) }}"
    icon="fas fa-share-alt" />
<!--<x-utils.edit-button :href="route('frontend.projects.edit', $project)" />-->
<button type="button" class="btn btn-primary btn-sm btn-project-edit" data-project-id="{{$project->id}}" data-customer-id="{{$project->customer_id()}}">
    <i class="fas fa-pencil-alt"></i> Edit
</button> 
<x-utils.delete-button :href="route('frontend.projects.destroy', $project)" />
@if ($project->type == 1)
<x-utils.link
    :text="__('Projects')"
    class="btn btn-dark btn-sm"
    :href="route('frontend.projects.subprojects', $project)"
    icon="fas fa-project-diagram" />
@endif
<input type="hidden" id="project-id" value="{{$project->id}}" />
<input type="hidden" id="project-type" value="{{$project->type}}" />
<input type="hidden" id="project-name" value="{{$project->name}}" />
