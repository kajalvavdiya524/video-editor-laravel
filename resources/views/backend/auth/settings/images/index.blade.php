@extends('backend.layouts.app')

@section('title', __('Settings'))

@section('content')
<x-backend.card>
    <x-slot name="header">
        @lang('Images')
    </x-slot>

    <x-slot name="headerActions">
        <x-utils.link icon="c-icon cil-plus" class="card-header-action" :href="route('admin.auth.settings.images.upload')" :text="__('Upload')" />
    </x-slot>

    <x-slot name="body">
        <livewire:images-table  />
    </x-slot>
</x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset("js/images.js") }}"></script>
@endpush