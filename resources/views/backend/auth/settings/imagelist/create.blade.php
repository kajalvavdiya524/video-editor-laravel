@extends('backend.layouts.app')

@section('title', __('Create Image List'))

@section('content')
    <x-forms.post :action="route('admin.auth.settings.imagelist.store')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Image List')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.settings.imagelist.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="image_name" class="col-md-2 col-form-label">@lang('Name')</label>
                    <div class="col-md-10">
                        <input type="text" name="image_list_name" class="form-control" placeholder="{{ __('Image List Name') }}" required />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="submit" class="btn btn-sm btn-primary float-right">@lang('Create')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/images.js") }}"></script>
@endpush