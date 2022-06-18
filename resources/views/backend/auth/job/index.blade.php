@extends('backend.layouts.app')

@section('title', __('Job Management'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Job Management')
        </x-slot>
       
        <x-slot name="body">
            <livewire:jobs-table />
        </x-slot>
    </x-backend.card>
@endsection
