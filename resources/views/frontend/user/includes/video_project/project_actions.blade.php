<a download class="btn btn-primary btn-sm" href="{{url('storage/projects').'/'.$project->file_name}}">
    <i class="fas fa-download"></i> Download 
</a>

@if(file_exists(public_path().'/video_creation/mp4/'.$project->name.'.mp4'))
<a download class="btn btn-primary btn-sm" href="{{url('video_creation/mp4').'/'.$project->name.'.mp4'}}">
    <i class="fas fa-download"></i> Download Video
</a>
@endif

<input type="hidden" id="project-id" value="{{$project->id}}" />
<input type="hidden" id="project-name" value="{{$project->name}}" />
