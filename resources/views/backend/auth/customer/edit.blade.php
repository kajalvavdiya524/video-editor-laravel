@extends('backend.layouts.app')

@section('title', __('Update Customer'))

@section('content')
    <x-forms.patch :action="route('admin.auth.customer.update', $customer)" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update Customer')
            </x-slot>

            <x-slot name="headerActions">
                @if (!empty($customer->xlsx_template_url))
                <x-utils.link class="card-header-action" :href="route('admin.auth.customer.download_xlsx_template', $customer)" :text="__('Download XLSX Template')" />
                @endif
                <x-utils.link class="card-header-action" :href="route(!$logged_in_user->isMember() ? 'admin.auth.customer.index' : 'admin.dashboard')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ $customer->name }}" required/>
                    </div>
                </div><!--form-group-->
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Logo')</label>
                    <div class="col-md-10 file-input-group">
                        <input type="file" class="form-control-file" name="logo" data-show-preview="false">
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
                                @php 
                                    $t = TRUE; 
                                @endphp
                                @foreach ($customer->companies as $customer_company)
                                    @if ($company->id == $customer_company->id)
                                        <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                        @php 
                                            $t = FALSE; 
                                        @endphp
                                    @endif
                                @endforeach
                                @if ($t)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div><!--form-group-->
                @endif
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Customer')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/multicheckbox.js") }}"></script>
	<script type="text/javascript" src="{{ asset("js/customer.js") }}"></script>
@endpush