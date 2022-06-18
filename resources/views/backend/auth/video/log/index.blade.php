@extends('backend.layouts.app')

@section('title', __('Video Creation Logs'))

@section('content')
<x-backend.card>
    <x-slot name="header">
        <h4>Creation Logs</h4>
    </x-slot>
    <x-slot name="body">
        <table class="table table-striped table-bordered datatable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Percent</th>
                    <th>Type</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td style="min-width: 180px;">
                            {{ $log->updated_at }}
                        </td>
                        <td style="min-width: 200px;">
                            {{ $log->xlsx }}
                        </td>
                        <td>
                            {{ $log->status }}
                        </td>
                        <td>
                            {{ $log->percent }}
                        </td>
                        <td>
                            {{ $log->type }}
                        </td>
                        <td>
                            @if($log->status == 'FAIL')
                                {{ isset(json_decode($log->last_details)->error) ? json_decode($log->last_details)->error : '' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $logs->links() }}
    </x-slot>
</x-backend.card>
@endsection
