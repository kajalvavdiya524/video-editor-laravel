@extends('backend.layouts.app')

@section('title', __('Update Team'))

@section('content')
    <x-forms.patch :action="route('admin.auth.team.update', $team)">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update Team')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route(!$logged_in_user->isMember() ? 'admin.auth.team.index' : 'admin.dashboard')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>

                    <div class="col-md-9">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ $team->name }}" required />
                    </div>
                </div><!--form-group-->

                @if ($logged_in_user->isMasterAdmin())
                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">@lang('Company')</label>

                        <div class="col-md-9">
                            <select class="form-control" id="company" name="company">
                                @foreach ($companies as $company)
                                    @if ($company->id == $team->company_id)
                                        <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                    @else
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endif
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
                                @php 
                                    $t = TRUE; 
                                @endphp
                                @foreach ($members as $member)
                                    @if ($user->id == $member->id)
                                        <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                                        @php 
                                            $t = FALSE; 
                                        @endphp
                                    @endif
                                @endforeach
                                @if ($t)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
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
                                @php 
                                    $t = TRUE; 
                                @endphp
                                @foreach ($team_customers as $team_customer)
                                    @if ($customer->id == $team_customer->id)
                                        <option value="{{ $customer->id }}" selected>{{ $customer->name }}</option>
                                        @php 
                                            $t = FALSE; 
                                        @endphp
                                    @endif
                                @endforeach
                                @if ($t)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div><!--form-group-->
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Team')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/multicheckbox.js") }}"></script>
@endpush