@extends('backend.layouts.app')

@section('title', __('View Customer'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('View Customer')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.customer.index')" :text="__('Back')" />
        </x-slot>

        <x-slot name="body">
            <table class="table table-hover">
                <tr>
                    <th>@lang('Id')</th>
                    <td>{{ $customer->id }}</td>
                </tr>
                <tr>
                    <th>@lang('Name')</th>
                    <td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <th>@lang('Logo')</th>
                    <td><img class="img-thumbnail rounded" src="{{ asset($customer->image_url) }}" width="100" /></td>
                </tr>
                <tr>
                    <th>@lang('XLSX Template')</th>
                    @if (empty($customer->xlsx_template_url))
                    <td><span class="badge badge-danger">No</span></td>
                    @else
                    <td><x-utils.link class="card-header-action" :href="route('admin.auth.customer.download_xlsx_template', $customer)" :text="__('Download')" /></td>
                    @endif
                </tr>
                <tr>
                    <th>@lang('Companies')</th>
                    <td>
                        @foreach ($customer->companies as $company)
                        <div>{{ $company->name }}</div>
                        @endforeach
                    </td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="footer">
            <small class="float-right text-muted">
                <strong>@lang('Customer Created'):</strong> @displayDate($customer->created_at) ({{ $customer->created_at->diffForHumans() }}),
                <strong>@lang('Last Updated'):</strong> @displayDate($customer->updated_at) ({{ $customer->updated_at->diffForHumans() }})
            </small>
        </x-slot>
    </x-backend.card>
@endsection
