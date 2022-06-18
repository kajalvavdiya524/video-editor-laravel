@extends('backend.layouts.app')

@section('title', __('Upload Image'))

@section('content')
    <x-forms.post :action="route('admin.auth.settings.images.store')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Upload Image')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.settings.images.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="image_name" class="col-md-2 col-form-label">@lang('Image Name')</label>
                    <div class="col-md-10">
                        <input type="text" name="image_name" class="form-control" placeholder="{{ __('Image Name') }}" />
                    </div>
                </div>
                <div class="form-group row">
                    <label for="image" class="col-md-2 col-form-label">@lang('Image')</label>
                    <div class="col-md-10">
                        <input type="file" class="form-control-file" name="image" data-show-preview="false" required />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="submit" class="btn btn-sm btn-primary float-right">@lang('Upload')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/images.js") }}"></script>
@endpush