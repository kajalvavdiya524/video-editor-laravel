@extends('backend.layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Login History')
        </x-slot>

        <x-slot name="body">
            <livewire:login-history-table company-id="{{ $logged_in_user->company_id }}" />
        </x-slot>
    </x-backend.card>
@endsection
