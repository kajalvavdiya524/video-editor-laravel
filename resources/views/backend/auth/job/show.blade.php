@extends('backend.layouts.app')

@section('title', __('View Job'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('View Job')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.job.index')" :text="__('Back')" />
        </x-slot>

        <x-slot name="body">
            <table class="table table-hover">

                <tr>
                    <th>@lang('Id')</th>
                    <td>{{ $job->id }}</td>
                </tr>

             
                <tr>
                    <th>@lang('Status')</th>
                    <td>{{ $job->job_statuses->name }}</td>
                </tr>

            </table>
            <h4> Job details </h4>
            <table class="table table-hover">

            <tr>
                <th>ID</th>
                <th>Input</th>
                <th>Output</th>
                <th>Status</th>
            </tr>
            @foreach ($job->details as $detail)

            <tr>
                <td>{{$detail->id}}</td>
                <td><pre>{{json_encode(json_decode($detail->input), JSON_PRETTY_PRINT)}}</pre></td>
                <td>{{$detail->output}}</td>
                <td>{{$detail->job_statuses->name}}</td>
            </tr>

            @endforeach


            </table>
        </x-slot>

        

        <x-slot name="footer">
            <small class="float-right text-muted">
                <strong>@lang('Job Created'):</strong> @displayDate($job->created_at) ({{ $job->created_at->diffForHumans() }}),
                <strong>@lang('Last Updated'):</strong> @displayDate($job->updated_at) ({{ $job->updated_at->diffForHumans() }})
            </small>
        </x-slot>
    </x-backend.card>
@endsection
