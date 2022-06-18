@extends('backend.layouts.app')

@section('title', __('Edit Image List'))

@section('content')
    <x-forms.post :action="route('admin.auth.settings.imagelist.update', $imagelist)" >
        <x-backend.card>
            <x-slot name="header">
                @lang('Edit Image List')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.settings.imagelist.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="image_list_name" class="col-md-2 col-form-label">@lang('Image List Name')</label>
                    <div class="col-md-10">
                        <input type="text" name="image_list_name" class="form-control" placeholder="{{ __('Image List Name') }}" value="{{ $imagelist->name }}" required />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="submit" class="btn btn-sm btn-primary float-right">@lang('Save')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    <x-forms.post :action="route('admin.auth.settings.images.store')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Upload Image')
            </x-slot>

            <x-slot name="body">
                <input type="hidden" name="list_id" value="{{ $imagelist->id }}" />
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

    <x-backend.card>
        <x-slot name="header">
            @lang('Images')
        </x-slot>

        <div class="d-none" id="preview-images"></div>
        <x-slot name="body">
            <livewire:images-table listId="{{ $imagelist->id }}" />
        </x-slot>
    </x-backend.card>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/images.js") }}"></script>
@endpush