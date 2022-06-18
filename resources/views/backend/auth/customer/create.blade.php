@extends('backend.layouts.app')

@section('title', __('Create Customer'))

@section('content')
    <x-forms.post :action="route('admin.auth.customer.store')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Customer')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.customer.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Logo')</label>
                    <div class="col-md-10 file-input-group">
                        <input type="file" class="form-control-file" name="logo" data-show-preview="false" required>
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('XLSX Template')</label>
                    <div class="col-md-10 file-input-group">
                        <input type="file" class="form-control-file" name="xlsx_template" data-show-preview="false">
                    </div>
                </div><!--form-group-->
                @if ($logged_in_user->isMasterAdmin())
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Companies')</label>

                    <div class="col-md-10">
                        <select id="company">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div><!--form-group-->
                @endif
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Create Customer')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/multicheckbox.js") }}"></script>
	<script type="text/javascript" src="{{ asset("js/customer.js") }}"></script>
@endpush