@extends('backend.layouts.app')

@section('title', __('Deactivated Companies'))

@section('breadcrumb-links')
    @include('backend.auth.company.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Deactivated Companies')
        </x-slot>

        <x-slot name="body">
            <livewire:company-table status="deactivated" />
        </x-slot>
    </x-backend.card>
@endsection
