@extends('backend.layouts.app')

@section('title', __('Video Templates'))

@section('content')

<x-backend.card>
    <x-slot name="header">
        <h4>Templates</h4>
    </x-slot>
    <x-slot name="body">
        <table class="table table-bordered table-sm">
            <thead>
                <th>Name</th>
                <th>File Name</th>
                <th>Readonly</th>
                <th>Visibility</th>
                <th>Actions</th>
            </thead>
            <tbody id="sortable-list">
                @foreach($templates as $template)
                    <tr data-id="{{ $template->id }}">
                        <td style="cursor: move;">{{ $template->name }}</td>
                        <td>
                            <a href="{{ $template->path }}" download>{{ $template->file_name }}</a>
                        </td>
                        <td>
                            <input class="form-control form-control-sm btn-readonly" type="checkbox" @if($template->readonly) checked @endif attr="{{ $template->id }}">
                        </td>
                        <td>
                            @if($template->visibility)
                                <span class="badge badge-pill badge-success">Visible</span>
                            @else
                                <span class="badge badge-pill badge-secondary">Invisible</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-edit" data-companies-id=""  attr="{{ $template->id }}">
                                <i class="cil-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-delete" attr="{{ $template->id }}">
                                <i class="cil-trash"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btn-visibility" attr="{{ $template->id }}" visibility="{{ $template->visibility ? 'visible' : 'invisible' }}">
                                @if($template->visibility)
                                    Hide
                                @else
                                    Show
                                @endif
                            </button>
                            @include('backend.auth.video.common.select-company', [
                                'entityId' => $template->id,
                                'entityAll' => $template->all_companies,
                                'entityCompanies' => $template->companies,
                                'companies' => $companies
                            ])
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
                    <h5 class="modal-title">Edit Template</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="errors">
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="template-name">Name</label>
                            <input class="form-control" id="template-name" placeholder="Name">
                        </div>
                        <div class="form-group">
                            <label for="template-file-name">File Name</label>
                            <input class="form-control" id="template-file-name" placeholder="File Name">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="template-readonly">
                            <label class="form-check-label" for="template-readonly">Readonly</label>
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
                    <p>This will discard existing template. Are you sure?</p>
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
<script type="text/javascript" src="{{ asset('js/video-template.js') }}"></script>
@endpush