@extends('backend.layouts.app')

@section('title', __('Create Team'))

@section('content')
    <x-forms.post :action="route('admin.auth.team.store')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Team')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.team.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-9">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
                    </div>
                </div><!--form-group-->

                @if ($logged_in_user->isMasterAdmin())
                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">@lang('Company')</label>

                        <div class="col-md-9">
                            <select class="form-control" id="company" name="company">
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div><!--form-group-->
                @else
                    <div class="form-group row d-none">
                        <label for="name" class="col-md-2 col-form-label">@lang('Company')</label>

                        <div class="col-md-9">
                            <select class="form-control" id="company" name="company">
                                <option value="{{ $logged_in_user->company_id }}"></option>
                            </select>
                        </div>
                    </div><!--form-group-->
                @endif

                @if (! $logged_in_user->isMasterAdmin())
                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">@lang('Members')</label>

                        <div class="col-md-9">
                            <select id="member">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div><!--form-group-->
                @endif

                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Customers')</label>

                    <div class="col-md-9">
                        <select id="customer">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div><!--form-group-->
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Create Team')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/multicheckbox.js") }}"></script>
@endpush