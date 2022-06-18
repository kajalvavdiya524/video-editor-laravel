@extends('backend.layouts.app')

@section('title', __('View Company'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('View Company')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.company.index')" :text="__('Back')" />
        </x-slot>

        <x-slot name="body">
            <table class="table table-hover">

                <tr>
                    <th>@lang('Id')</th>
                    <td>{{ $company->id }}</td>
                </tr>

                <tr>
                    <th>@lang('Name')</th>
                    <td>{{ $company->name }}</td>
                </tr>

                <tr>
                    <th>@lang('Address')</th>
                    <td>{{ $company->address }}</td>
                </tr>

                <tr>
                    <th>@lang('Storage Service')</th>
                    <td>{{ $company->use_azure ? 'Azure' : 'S3' }}</td>
                </tr>

                <tr>
                    <th>@lang('MRHI')</th>
                    <td>{{ $company->has_mrhi ? 'Yes' : 'No' }}</td>
                </tr>

                <tr>
                    <th>@lang('Pilot')</th>
                    <td>{{ $company->has_pilot ? 'Yes' : 'No' }}</td>
                </tr>

                <tr>
                    <th>@lang('Status')</th>
                    <td>@include('backend.auth.company.includes.status', ['company' => $company])</td>
                </tr>

            </table>
        </x-slot>

        <x-slot name="footer">
            <small class="float-right text-muted">
                <strong>@lang('Company Created'):</strong> @displayDate($company->created_at) ({{ $company->created_at->diffForHumans() }}),
                <strong>@lang('Last Updated'):</strong> @displayDate($company->updated_at) ({{ $company->updated_at->diffForHumans() }})

                @if($company->trashed())
                    <strong>@lang('Account Deleted'):</strong> @displayDate($company->deleted_at) ({{ $company->deleted_at->diffForHumans() }})
                @endif
            </small>
        </x-slot>
    </x-backend.card>
@endsection
