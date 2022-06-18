@extends('backend.layouts.app')

@section('title', __('Video Projects'))

@push("after-styles")
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css" rel="stylesheet">
@endpush

@section('content')
<x-backend.card>
    <x-slot name="header">
        <h4>Shares</h4>
    </x-slot>
    <x-slot name="body">
        <div class="form-group row">
            <label for="emails" class="col-sm-2 col-form-label col-form-label-sm">Email address</label>
            <div class="col-sm-10">
                <select id="emails" class="form-control form-control-sm" name="emails[]" multiple="multiple"></select>
            </div>
        </div>
        <div class="form-group row">
            <label for="subject" class="col-sm-2 col-form-label col-form-label-sm">Subject</label>
            <div class="col-sm-10">
                <input type="text" class="form-control form-control-sm" id="subject" value="Please review this video">
            </div>
        </div>
        <div class="form-group row">
            <label for="message" class="col-sm-2 col-form-label col-form-label-sm">Message</label>
            <div class="col-sm-10">
                <textarea id="message" class="form-control form-control-sm">Please review this video <link></textarea>
            </div>
        </div>

        <table class="table table-bordered table-sm mt-4">
            <thead>
                <th>Create Date</th>
                <th>Name</th>
                <th>FileName</th>
                <th>Comments</th>
                <th>Actions</th>
            </thead>
            <tbody id="sortable-list">
                @foreach($shares as $key => $share)
                    <tr>
                        <td>{{ $share->created_at }}</td>
                        <td>{{ $share->name }}</td>
                        <td>
                            <a href="{{ $share->path }}" download>{{ $share->file_name }}</a>
                        </td>
                        <td>
                            <a href="{{ 'video-review/' . $share->uuid }}" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="left" title="See comments">
                                {{ $share->comments }}
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-edit" data-id="{{ $share->id }}">
                                <i class="cil-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $share->id }}">
                                <i class="cil-trash"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btn-copy" data-uuid="{{ $share->uuid }}">
                                <i class="cil-copy"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btn-share" data-uuid="{{ $share->uuid }}" data-id="{{ $share->id }}">Share</button>
                            <button class="btn btn-primary btn-sm btn-preview" path-mp4="{{ $share->path_mp4 }}">Preview</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @section('modals')

        <div class="modal fade" id="preview-modal" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <video id="video-mp4" style="width: 100%;" controls>
                                    <source id="source-mp4" src="" type="video/mp4">
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <label for="share-name">Name</label>
                                <input class="form-control" id="share-name" placeholder="Name">
                            </div>
                            <div class="form-group">
                                <label for="share-file-name">File Name</label>
                                <input class="form-control" id="share-file-name" placeholder="File Name">
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
                        <p>This will discard existing one. Are you sure?</p>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary mr-2" data-dismiss="modal" id="delete-btn">Yes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endsection
    </x-slot>
</x-backend.card>

@endsection

@push("after-scripts")
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="{{ asset('js/video-share.js') }}"></script>
@endpush