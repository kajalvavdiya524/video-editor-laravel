@extends('frontend.layouts.app')

@section('title', __('Projects'))

@section('meta')
<meta name="requesterId" content="{{ $requester_id }}">
<meta name="requestTimestamp" content="{{ $requeste_timestamp }}">
<meta name="userId" content="{{ auth()->user()->id }}">
@endsection

@php
$columns = Config::get('columns.project');
@endphp

@section('content')
    <div class="row justify-content-center" id="project-section">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    @lang('Project Approval')
                </x-slot>

                <x-slot name="body">
                    <div class="form-row">
                        <label>Project:</label>&nbsp;&nbsp;<a class="project-preview" data-id="{{ $project->id }}" href="#">{{ $project->name }}</a>
                    </div>
                    <div class="form-row">
                        <label>Comment</label>
                        <textarea class="form-control comment" rows="5"></textarea>
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <button data-id="{{ $project->id }}" class="btn btn-reject btn-danger float-right" type="button">@lang('Reject')</button>
                    <button data-id="{{ $project->id }}" class="btn btn-approve btn-success float-right mr-2" type="button">@lang('Approve')</button>
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->
@endsection

@push("after-scripts")
    <script type="text/javascript" src="{{ asset("js/request_approve.js") }}"></script>
@endpush