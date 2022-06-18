@extends('backend.layouts.app')

@section('title', __('API Keys Management'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('API Keys Management')
        </x-slot>

        @if (! $logged_in_user->isMember())
            <x-slot name="headerActions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.auth.apikeys.create')"
                    :text="__('Create Api Key')"
                />
            </x-slot>
        @endif

        <x-slot name="body">
            <livewire:api-keys-table />
        </x-slot>
    </x-backend.card>
@endsection
