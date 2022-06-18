@php
$summary = $project->getApprovalSummary();
@endphp
@if ($summary['count'] == 0)
<span class='badge badge-default'>
    No approvals yet
</span>
@else
    @if ($summary['approved']['count'] > 0)
    <span class='badge badge-success' data-toggle='tooltip' title='{{ $summary["approved"]["title"] }}'>
        {{ $summary['approved']['count'] }} Approvals
    </span>
    @endif
    @if ($summary['rejected']['count'] > 0)
    <span class='badge badge-danger' data-toggle='tooltip' title='{{ $summary["rejected"]["title"] }}'>
        {{ $summary['rejected']['count'] }} Rejections
    </span>
    @endif
@endif