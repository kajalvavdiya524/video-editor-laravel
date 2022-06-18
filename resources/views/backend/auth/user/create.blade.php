@extends('backend.layouts.app')

@section('title', __('Create User'))

@section('content')
    <x-forms.post :action="route('admin.auth.user.store')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create User')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.user.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="first_name" class="col-md-2 col-form-label">@lang('First Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="first_name" class="form-control" placeholder="{{ __('First Name') }}" value="{{ old('first_name') }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="last_name" class="col-md-2 col-form-label">@lang('Last Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="last_name" class="form-control" placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="email" class="col-md-2 col-form-label">@lang('E-mail Address')</label>

                    <div class="col-md-10">
                        <input type="email" name="email" class="form-control" placeholder="{{ __('E-mail Address') }}" value="{{ old('email') }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="password" class="col-md-2 col-form-label">@lang('Password')</label>

                    <div class="col-md-10">
                        <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('Password') }}" required autocomplete="new-password" />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="password_confirmation" class="col-md-2 col-form-label">@lang('Password Confirmation')</label>

                    <div class="col-md-10">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('Password Confirmation') }}" required autocomplete="new-password" />
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

                <div x-data="{ emailVerified : false }">
                    <div class="form-group row">
                        <label for="email_verified" class="col-md-2 col-form-label">@lang('E-mail Verified')</label>

                        <div class="col-md-10">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="email_verified"
                                    id="email_verified"
                                    value="1"
                                    class="form-check-input"
                                    @click="emailVerified = !emailVerified"
                                    {{ old('email_verified') ? 'checked' : '' }} />
                            </div><!--form-check-->
                        </div>
                    </div><!--form-group-->

                    <div x-show="!emailVerified">
                        <div class="form-group row">
                            <label for="send_confirmation_email" class="col-md-2 col-form-label">@lang('Send Confirmation E-mail')</label>

                            <div class="col-md-10">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="send_confirmation_email"
                                        id="send_confirmation_email"
                                        value="1"
                                        class="form-check-input"
                                        {{ old('send_confirmation_email') ? 'checked' : '' }} />
                                </div><!--form-check-->
                            </div>
                        </div><!--form-group-->
                    </div>
                </div>

                @include('backend.auth.includes.roles')
                @include('backend.auth.includes.companies')

                <div class="form-group row">
                    <label for="customer_id" class="col-md-2 col-form-label">@lang('Default Customer')</label>
                    <div class="col-md-10">
                        <select name="customer_id" id="customer_id" class="form-control">
                            @forelse($customers as $customer)
                                <option {{ $customer->id == $logged_in_user->customer_id ? "selected" : "" }} value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @empty
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforelse
                        </select>
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label class="form-check-label col-md-3 text-md-right" for="is_download_draft">Save Draft - Download</label>
                    <div class="col-md-9">
                        <input type="checkbox"id="is_download_draft" name="is_download_draft" />
                    </div>
                </div>

                <div class="form-group row">
                    <label class="form-check-label col-md-3 text-md-right" for="is_download_project">Save Project - Download</label>
                    <div class="col-md-9">
                        <input type="checkbox" id="is_download_project" name="is_download_project" />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Create User')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
@endsection
