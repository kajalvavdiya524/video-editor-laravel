@extends('backend.layouts.app')

@section('title', __('Settings'))

@section('content')
<x-backend.card>
    <x-slot name="header">
        @lang('Theme')
    </x-slot>

    <x-slot name="headerActions">
        <x-utils.link icon="c-icon cil-plus" class="card-header-action" :href="route('admin.auth.settings.theme.create', $customer_id)" :text="__('Create Theme')" />
    </x-slot>

    <x-slot name="body">
        <div class="form-row">
            <div class="col-2">
                <label class="font-weight-bold">Customer</label>
                <div class="form-group">
                    <select class="form-control" name="customer">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->value }}" {{ $customer->value == $customer_name ? "selected": "" }}>{{$customer->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <livewire:theme-table customerId="{{ $customer_id }}" />
    </x-slot>
</x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset("js/template.js") }}"></script>
@endpush