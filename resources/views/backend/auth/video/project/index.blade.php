@extends('backend.layouts.app')

@section('title', __('Video Projects'))

@push("after-styles")
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css">
@endpush

@section('content')

<x-backend.card>
    <x-slot name="header">
        <h4>Projects</h4>
    </x-slot>
    <x-slot name="body">
        <table class="table table-bordered table-sm">
            <thead>
                <th>Name</th>
                <th>File Name</th>
                <th>User</th>
                <th>Last Update</th>
                <th>Visibility</th>
                <th>Actions</th>
            </thead>
            <tbody id="sortable-list">
                @foreach($projects as $key => $project)
                    <tr data-id="{{ $project->id }}">
                        <td style="cursor: move;">{{ $project->name }}</td>
                        <td>
                            <a href="{{ $project->path }}" download>{{ $project->file_name }}</a>
                        </td>
                        <td>{{ $project->user }}</td>
                        <td>{{ $project->updated_at }}</td>
                        <td>
                           	@if($project->visibility)
                            	<span class="badge badge-pill badge-success">Visible</span>
                            @else
                            	<span class="badge badge-pill badge-secondary">Invisible</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-edit" attr="{{ $project->id }}">
                                <i class="cil-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-delete" attr="{{ $project->id }}">
                                <i class="cil-trash"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btn-show" attr="{{ $project->id }}" visibility="{{ $project->visibility ? 'visible' : 'invisible' }}">
                                @if($project->visibility)
                                	Hide
                                @else
                                	Show
                                @endif
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot>
    
    @section('modals')

    <div class="modal fade" id="edit-modal" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="errors">
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="project-name">Name</label>
                            <input class="form-control" id="project-name" placeholder="Name">
                        </div>
                        <div class="form-group">
                            <label for="project-file-name">File Name</label>
                            <input class="form-control" id="project-file-name" placeholder="File Name">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="project-visibility">
                            <label class="form-check-label" for="project-visibility">Visibility</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary mr-2" id="update-btn">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-confirm-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Heads up!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>This will discard existing project. Are you sure?</p>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary mr-2" data-dismiss="modal" id="delete-btn">Yes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection
</x-backend.card>

@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/video-project.js') }}"></script>
@endpush