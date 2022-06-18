@extends('frontend.layouts.app')

@section('title', __('Group Layouts'))

@section('content')
    <x-frontend.card>
        <x-slot name="header">
            @lang('Group Layouts')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link
                icon="c-icon cil-plus"
                class="card-header-action"
                :href="route('frontend.banner.group.create', $customer_id)"
                :text="__('Create Group Layout')"
            />
        </x-slot>

        <x-slot name="body">
            <livewire:grid-layout-table
                user-id="{{ $logged_in_user->id }}"
                company-id="{{ $logged_in_user->company_id }}"
                customer-id="{{ $customer_id }}"
            />
        </x-slot>
    </x-frontend.card>

    @include('frontend.includes.modals.company')
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/group/index.js') }}"></script>
@endpush