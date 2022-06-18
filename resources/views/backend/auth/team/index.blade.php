@extends('backend.layouts.app')

@section('title', __('Team Management'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Team Management')
        </x-slot>

        @if (! $logged_in_user->isMember())
            <x-slot name="headerActions">
                <x-utils.link
                    icon="c-icon cil-plus"
                    class="card-header-action"
                    :href="route('admin.auth.team.create')"
                    :text="__('Create Team')"
                />
            </x-slot>
        @endif

        <x-slot name="body">
            <livewire:team-table />
        </x-slot>
    </x-backend.card>
@endsection
