@extends('frontend.layouts.app')

@section('title', __('Update Layout'))

@section('content')
    <x-forms.patch :action="route('frontend.banner.group.update', ['customer_id' => $customer_id, 'layout' => $layout])" enctype="multipart/form-data">
        <x-frontend.card>
            <x-slot name="header">
                @lang('Update Layout')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link class="card-header-action" :href="route('frontend.banner.group.index', $customer_id)" :text="__('Cancel')" />
            </x-slot>

            <x-slot name="body">
                <input type="hidden" id="customer_id" value="{{ $customer_id }}" />
                <input type="hidden" id="layout_id" value="{{ $layout->id }}" />
                <div class="form-group row">
                    <label for="name" class="col-md-2 col-form-label">@lang('Name')</label>
                    <div class="col-md-10">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Name') }}" value="{{ $layout->name }}" required/>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Layout</label>
                    <div class="col-md-10">
                        <div class="px-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex">
                                <div class="form-inline">
                                    <label class="col-form-label">Template</label>
                                    <select class="form-control ml-2" id="grid-view-template">
                                        @foreach ($templates as $template)
                                        <option value="{{ $template['id'] }}" data-width="{{ $template['width'] }}" data-height="{{ $template['height'] }}">{{ $template['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ml-4">
                                    <button type="button" class="btn btn-primary btn-sm add-grid-item"><i class="c-icon cil-plus"></i>Add</button>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-primary float-right update-layout-btn" type="submit">@lang('Update Layout')</button>
                            </div>
                        </div>
                        <div id="template-grid-layout" class="grid-stack">
                        @php
                            $settings = json_decode($layout->settings);
                        @endphp
                        @foreach ($settings as $row)
                            @if (isset($row->content))
                            <div class="grid-stack-item" gs-x="{{ $row->x }}" gs-y="{{ $row->y }}" gs-w="{{ $row->w }}" gs-h="{{ $row->h }}">
                                <div class="grid-stack-item-content">
                                    {!! $row->content !!}
                                </div>
                            </div>
                            @else
                                @php
                                    $template = $templates->firstWhere('id', $row->template_id);
                                @endphp
                                @if ($template != null)
                                <div class="grid-stack-item" gs-x="{{ $row->x }}" gs-y="{{ $row->y }}" gs-w="{{ $row->w }}" gs-h="{{ $row->h }}">
                                    <div class="grid-stack-item-content">
                                        <div>
                                            <span class="grid-stack-item-remove">&times;</span>
                                            <div class="grid-stack-item-template d-flex align-items-center" data-id="{{ $row->instance_id }}" data-template="{{ $row->template_id }}" data-width="{{ $row->width }}" data-height="{{ $row->height }}">
                                                {{ $template['name'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endif
                        @endforeach
                        </div>
                        <input type="hidden" value="" name="settings" />
                    </div>
                </div>
            </x-slot>
        </x-frontend.card>
    </x-forms.patch>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/group/edit.js') }}"></script>
@endpush