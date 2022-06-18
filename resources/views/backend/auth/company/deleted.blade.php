@extends('backend.layouts.app')

@section('title', __('Deleted Companies'))

@section('breadcrumb-links')
    @include('backend.auth.company.includes.breadcrumb-links')
@endsection

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Deleted Companies')
        </x-slot>

        <x-slot name="body">
            <livewire:company-table status="deleted" />
        </x-slot>
    </x-backend.card>
@endsection
