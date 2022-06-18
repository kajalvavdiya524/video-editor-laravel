@extends('frontend.layouts.app')

@section('title', __('Sub-Projects'))

@section('meta')
<meta name="siteUrl" content="{{ siteUrl() }}">
<meta name="userName" content="{{ auth()->user()->name }}">
<meta name="userId" content="{{ auth()->user()->id }}">
@endsection

@php
$columns = Config::get('columns.project');
$columns_url = 'projects/columns';
@endphp

@section('content')
    <div class="row justify-content-center" id="project-section">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    <x-utils.link class="btn btn-link" :href="route('frontend.projects.index')" icon="fas fa-arrow-left" />
                    <span>@lang('Sub-Projects')</span>
                </x-slot>

                <x-slot name="headerActions">
                    <button type="button" class="btn btn-sm" data-toggle="modal" data-target="#columnsModal">
                        <i class="cil-grid"></i>
                    </button>
                </x-slot>

                <x-slot name="body">
                    <livewire:sub-project-table parentId="{{ $parent_id }}" />
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->
@endsection

@section('modals')
    <!-- Share Modal -->
    @include('frontend.includes.modals.share')

    <!-- Column Options Modal -->
    @include('frontend.includes.modals.project_columns')
@endsection

@push("after-scripts")
    <script type="text/javascript" src="{{ asset("js/project.js") }}"></script>
@endpush