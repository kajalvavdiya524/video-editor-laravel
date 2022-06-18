@extends('backend.layouts.app')

@section('title', __('View Team'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('View Team')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.team.index')" :text="__('Back')" />
        </x-slot>

        <x-slot name="body">
            <table class="table table-hover">

                <tr>
                    <th>@lang('Id')</th>
                    <td>{{ $team->id }}</td>
                </tr>

                <tr>
                    <th>@lang('Name')</th>
                    <td>{{ $team->name }}</td>
                </tr>

                <tr>
                    <th>@lang('Company')</th>
                    <td>{{ $team->company()->first() ? $team->company()->first()->name : "" }}</td>
                </tr>

                <tr>
                    <th>@lang('Members')</th>
                    <td>
                        @foreach ($team->users as $user)
                            #{{ $user->name }}
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <th>@lang('Customers')</th>
                    <td>
                        @foreach ($team->customers as $customer)
                            {{ $customer->name }} 
                        @endforeach
                    </td>
                </tr>

            </table>
        </x-slot>

        <x-slot name="footer">
            <small class="float-right text-muted">
                <strong>@lang('Team Created'):</strong> @displayDate($team->created_at) ({{ $team->created_at->diffForHumans() }}),
                <strong>@lang('Last Updated'):</strong> @displayDate($team->updated_at) ({{ $team->updated_at->diffForHumans() }})
            </small>
        </x-slot>
    </x-backend.card>
@endsection
