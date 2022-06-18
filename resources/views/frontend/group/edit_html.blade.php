@extends('frontend.layouts.app')

@section('title', __('Preview | Group'))

@section('content')
<div class="mt-4">
    <x-frontend.card>
        <x-slot name="header">
            @lang('Edit HTML Content')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('frontend.banner.group.show', ['customer_id' => $layout->customer_id, 'layout' => $layout])" :text="__('Cancel')" />
        </x-slot>
        
        <x-slot name="body">
            <x-forms.post :action="route('frontend.banner.group.save_html', ['customer_id' => $customer_id, 'layout' => $layout])">
                <div class="form-group">
                    <textarea class="form-control" style="height: 500px;" name="content">{{ $content }}</textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" type="submit">@lang('Save')</button>
                </div>
            </x-forms.post>
        </x-slot>
    </x-frontend.card>
</div>
@endsection
