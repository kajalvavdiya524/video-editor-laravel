@extends('backend.layouts.app')

@section('title', __('User Management'))

@section('breadcrumb-links')
    @include('backend.auth.user.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('User Management')
        </x-slot>

        @if (!$logged_in_user->isMember())
            <x-slot name="headerActions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.auth.user.create')"
                    :text="__('Create User')"
                />
            </x-slot>
        @endif

        <x-slot name="body">
            @if (!$logged_in_user->isMasterAdmin())    
                <livewire:users-table status="active" company-id="{{ $logged_in_user->company_id }}" />
            @else
                <livewire:users-table status="active" company-id="" />
            @endif
            
        </x-slot>
    </x-backend.card>
@endsection
