@extends('backend.layouts.app')

@section('title', __('Update User'))

@section('content')
    <x-forms.patch :action="route('admin.auth.user.update', $user)">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update User')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.user.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-group row">
                    <label for="first_name" class="col-md-2 col-form-label">@lang('First Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="first_name" class="form-control" placeholder="{{ __('First Name') }}" value="{{ $user->first_name }}" required />
                    </div>
                </div><!--form-group-->
                
                <div class="form-group row">
                    <label for="last_name" class="col-md-2 col-form-label">@lang('Last Name')</label>

                    <div class="col-md-10">
                        <input type="text" name="last_name" class="form-control" placeholder="{{ __('Last Name') }}" value="{{ $user->last_name }}" required />
                    </div>
                </div><!--form-group-->

                <div class="form-group row">
                    <label for="email" class="col-md-2 col-form-label">@lang('E-mail Address')</label>

                    <div class="col-md-10">
                        <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('E-mail Address') }}" value="{{ $user->email }}" required />
                    </div>
                </div><!--form-group-->

                @if (!$user->isMasterAdmin())
                    @include('backend.auth.includes.roles')
                @endif
                @include('backend.auth.includes.companies')

                <div class="form-group row">
                    <label for="customer_id" class="col-md-2 col-form-label">@lang('Default Customer')</label>
                    <div class="col-md-10">
                        <select name="customer_id" id="customer_id" class="form-control">
                            @forelse($customers as $customer)
                                <option {{ $customer->id == $user->customer_id ? "selected" : "" }} value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @empty
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforelse
                        </select>
                    </div>
                </div><!--form-group-->
                
                <div class="form-group row {{ $user->isMember() ? '' : 'd-none' }} ">
                    <label for="can_upload_image" class="col-md-2 col-form-label">@lang('Can Upload Image')</label>
                    <div class="form-group ml-4 pl-3">
                        <input type="hidden" class="custom-control-input" value="off" id="can_upload_image_hidden" name="can_upload_image" />
                        <input type="checkbox" class="custom-control-input" value="on" id="can_upload_image" name="can_upload_image" {{ $user->can_upload_image ? "checked" : "" }}/>
                        <label class="custom-control-label" for="can_upload_image"></label>
                    </div>
                </div><!--form-group-->
                
                <div class="form-group row">
                    <label class="form-check-label col-md-3 text-md-right" for="is_download_draft">Save Draft - Download</label>
                    <div class="col-md-9">
                        <input type="checkbox"id="is_download_draft" name="is_download_draft" {{ (!empty($user->is_download_draft) && $user->is_download_draft) ? "checked" : "" }} />
                    </div>
                </div>

                <div class="form-group row">
                    <label class="form-check-label col-md-3 text-md-right" for="is_download_project">Save Project - Download</label>
                    <div class="col-md-9">
                        <input type="checkbox" id="is_download_project" name="is_download_project" {{ (!empty($user->is_download_project) && $user->is_download_project) ? "checked" : "" }} />
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update User')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection
