@extends('backend.layouts.app')

@section('title', __('Create Company'))

@section('content')
    <x-forms.post :action="route('admin.auth.company.store')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Company')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.company.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="address" class="col-md-2 col-form-label">@lang('Address')</label>

                    <div class="col-md-10">
                        <input type="text" name="address" class="form-control" placeholder="{{ __('Address') }}" value="{{ old('address') }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="address" class="col-md-2 col-form-label">@lang('Storage Service')</label>

                    <div class="col-md-10">
                        <select class="form-control" name="use_azure">
                            <option value="0">S3</option>
                            <option value="1">Azure</option>
                        </select>
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="active" class="col-md-2 col-form-label">@lang('Active')</label>

                    <div class="col-md-10">
                        <div class="form-check">
                            <input name="active" id="active" class="form-check-input" type="checkbox" value="1" {{ old('active', true) ? 'checked' : '' }} />
                        </div><!--form-check-->
                    </div>
                </div><!--form-group-->

            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Create Company')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection
