@extends('backend.layouts.app')

@section('title', __('Company Management'))

@section('breadcrumb-links')
    @include('backend.auth.company.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Company Management')
        </x-slot>

        @if ($logged_in_user->isAdmin())
            <x-slot name="headerActions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.auth.company.create')"
                    :text="__('Create Company')"
                />
            </x-slot>
        @endif

        <x-slot name="body">
            <livewire:company-table />
        </x-slot>
    </x-backend.card>
@endsection
