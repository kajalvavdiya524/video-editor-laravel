@extends('backend.layouts.app')

@section('title', __('Update Job'))

@section('content')
    <x-forms.patch :action="route('admin.auth.job.update', $job)">
        <x-backend.card>
            <x-slot name="header">
                @lang('Update Job')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('admin.auth.job.index')" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                               
                    <div class="form-group row">
                        <label for="name" class="col-md-2 col-form-label">@lang('Change Job status')</label>

                        <div class="col-md-9">
                            <select class="form-control" id="status_id" name="status_id">
                                
                                @foreach ($statuses as $status)
                                    @if ($status->id == $job->status_id)
                                        <option value="{{ $status->id }}" selected>{{ $status->name }}</option>
                                    @else
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div><!--form-group-->
                
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update Job')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.patch>
@endsection
