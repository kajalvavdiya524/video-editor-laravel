@extends('backend.layouts.app')

@section('title', __('View API Key'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('View API Key')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.apikeys.index')" :text="__('Back')" />
        </x-slot>

        <x-slot name="body">
            <table class="table table-hover">

                <tr>
                    <th>@lang('Id')</th>
                    <td>{{ $apiKeys->id }}</td>
                </tr>

                <tr>
                    <th>@lang('Key')</th>
                    <td>{{ $apiKeys->key }}</td>
                </tr>

                <tr>
                    <th>@lang('Company')</th>
                    <td>{{ $apiKeys->company()->first() ? $apiKeys->company()->first()->name : "Global - All companies" }}</td>
                </tr>


            </table>
        </x-slot>

        <x-slot name="footer">
            <small class="float-right text-muted">
                <strong>@lang('API Key Created'):</strong> @displayDate($apiKeys->created_at) ({{ $apiKeys->created_at->diffForHumans() }}),
                <strong>@lang('Last Updated'):</strong> @displayDate($apiKeys->updated_at) ({{ $apiKeys->updated_at->diffForHumans() }})
            </small>
        </x-slot>
    </x-backend.card>
@endsection
