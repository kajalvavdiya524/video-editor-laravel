@extends('frontend.layouts.app')

@section('title', __('Create Layout'))

@section('content')
    <x-forms.post :action="route('frontend.banner.group.store', $customer_id)" enctype="multipart/form-data">
        <x-frontend.card>
            <x-slot name="header">
                @lang('Create Layout')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('frontend.banner.group.index', $customer_id)" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Layout</label>

                    <div class="col-md-10">
                        <div class="template-list">
                            <div class="d-flex">
                                <div class="form-inline">
                                    <label class="col-form-label">Template</label>
                                    <select class="form-control ml-2" id="grid-view-template">
                                        @foreach($templates as $template)
                                        <option value="{{ $template['id'] }}" data-width="{{ $template['width'] }}" data-height="{{ $template['height'] }}">{{ $template['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ml-4">
                                    <button type="button" class="btn btn-primary btn-sm add-grid-item"><i class="c-icon cil-plus"></i>Add</button>
                                </div>
                            </div>
                        </div>
                        <div id="template-grid-layout" class="grid-stack">

                        </div>
                        <input type="hidden" value="" name="settings" />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right create-layout-btn" type="submit">@lang('Create Layout')</button>
            </x-slot>
        </x-frontend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/group/create.js') }}"></script>
@endpush