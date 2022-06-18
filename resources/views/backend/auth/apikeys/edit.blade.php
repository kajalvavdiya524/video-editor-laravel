@extends('backend.layouts.app')

@section('title', __('Update API Key'))

@section('content')
    <x-forms.patch :action="route('admin.auth.apikeys.update', $apiKeys)">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update API Key')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.apikeys.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">


                    <label for="key" class="col-md-2 col-form-label">@lang('Key')</label>

                    <div class="col-md-9">
                        <input type="text" name="key" class="form-control" placeholder="{{ __('Key') }}" value="{{ $apiKeys->key }}" required />
                        <small id="emailHelp" class="form-text text-muted">Letters and numbers only, minimum length 40 characters</small>
                    </div>
                </div><!--form-group-->

                
                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">@lang('Company')</label>

                        <div class="col-md-9">
                            <select class="form-control" id="company" name="company">
                                <option value=""> Global - All companies </option>
                                @foreach ($companies as $company)
                                    @if ($company->id == $apiKeys->company_id)
                                        <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                    @else
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div><!--form-group-->
                
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update API Key')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/multicheckbox.js") }}"></script>
@endpush