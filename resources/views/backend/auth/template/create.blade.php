@extends('backend.layouts.app')

@section('title', __('Create Template'))

@section('content')
    <x-forms.post :action="route('admin.auth.template.store', ['customer_id' => $customer_id])" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Template')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.template.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <input type="hidden" name="customer_id" value="{{ $customer_id }}" />
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="width" class="col-md-2 col-form-label">@lang('Width')</label>

                    <div class="col-md-10">
                        <input type="text" name="width" class="form-control" placeholder="{{ __('Width') }}" value="{{ old('width') }}" required />
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="height" class="col-md-2 col-form-label">@lang('Height')</label>

                    <div class="col-md-10">
                        <input type="text" name="height" class="form-control" placeholder="{{ __('Height') }}" value="{{ old('height') }}" required />
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Logo')</label>
                    <div class="col-md-10 file-input-group">
                        <input type="file" class="form-control-file" name="logo" data-show-preview="false">
                    </div>
                </div><!--form-group-->
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Create Template')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/template.js") }}"></script>
@endpush