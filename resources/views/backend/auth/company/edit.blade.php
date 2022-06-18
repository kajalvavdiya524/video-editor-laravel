@extends('backend.layouts.app')

@section('title', __('Update Company'))

@section('content')
    <x-forms.patch :action="route('admin.auth.company.update', $company)">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update Company')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route($logged_in_user->isMasterAdmin() ? 'admin.auth.company.index' : 'admin.dashboard')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ $company->name }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="address" class="col-md-2 col-form-label">@lang('Address')</label>

                    <div class="col-md-10">
                        <input type="text" name="address" id="address" class="form-control" placeholder="{{ __('Address') }}" value="{{ $company->address }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="address" class="col-md-2 col-form-label">@lang('Storage Service')</label>

                    <div class="col-md-10">
                        <select class="form-control" name="use_azure">
                            <option value="0" {{ $company->use_azure ? '' : 'selected' }}>S3</option>
                            <option value="1" {{ $company->use_azure ? 'selected' : '' }}>Azure</option>
                        </select>
                    </div>
                </div><!--form-group-->

                @if ($logged_in_user->isCompanyAdmin() || $logged_in_user->isMasterAdmin())
                    <div class="form-group row">
                        <label for="address" class="col-md-2 col-form-label">@lang('MRHI template')</label>
                        <div class="col-md-10">
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="has_mrhi_hidden" name="has_mrhi" />
                                <input type="checkbox" class="custom-control-input" value="on" id="has_mrhi" name="has_mrhi" {{ $company->has_mrhi ? "checked" : "" }}/>
                                <label class="custom-control-label" for="has_mrhi"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="address" class="col-md-2 col-form-label">@lang('Pilot template')</label>
                        <div class="col-md-10">
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="has_pilot_hidden" name="has_pilot" />
                                <input type="checkbox" class="custom-control-input" value="on" id="has_pilot" name="has_pilot" {{ $company->has_pilot ? "checked" : "" }}/>
                                <label class="custom-control-label" for="has_pilot"></label>
                            </div>
                        </div>
                    </div>
                @endif

            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Company')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection
