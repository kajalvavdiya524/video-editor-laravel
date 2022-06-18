@extends('backend.layouts.app')

@section('title', __('Create Theme'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Create Theme')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.settings.theme.index', $customer_id)" :text="__('Cancel')" />
        </x-slot>

        <x-slot name="body">
            <input type="hidden" name="customer_id" id="customer_id" value="{{ $customer_id }}">
            <input type="hidden" id="customer_id" value="{{ $customer_id }}">
            <div class="form-group row">
                <label for="theme_name" class="col-md-2 col-form-label">@lang('Theme Name')</label>
                <div class="col-md-10">
                    <input type="text" name="theme_name" class="form-control" placeholder="{{ __('Theme Name') }}" required />
                </div>
            </div>
            <?php
            if (isset($theme->attributes)) {
                $attributes = json_decode($theme->attributes);
            }else{
                $attributes = array();
            }
            ?>
            @foreach ($attributes as $attribute)
            <div class="form-group attribute mt-4">
                <?php
                    $options = $attribute->list;
                ?>
                <div class="attribute-header">
                    <p class="attribute-name float-left">{{ $attribute->name }}</p>
                    <button type="button" class="btn btn-sm btn-success btn-add-attr float-right">+Add</button>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <th style="width: 15%">Name</th>
                        @foreach ($options[0]->list as $attr)
                            <th>{{ ucfirst($attr->name) }}</th>
                        @endforeach
                        <th style="width: 5%">Action</th>
                    </thead>
                    <tbody>
                        @foreach ($options as $option)
                        <tr>
                            <td>
                                <input type="text" class="form-control option-name" value="{{ $option->name }}">
                            </td>
                            <?php
                                $isGradient = false;
                            ?>
                            @foreach ($option->list as $attr)
                                <?php 
                                    $type = isset($attr->type) ? $attr->type : "color"; 
                                ?>
                                <td data-type="{{ $type }}">
                                    @if ($type == "color")
                                        @if ($isGradient)
                                        <?php
                                            $gradient = explode(",", $attr->value);
                                        ?>
                                        <div class="form-row">
                                            <div class="form-row col-md-6">
                                                <div class="form-group col-md-6">
                                                    <input type="text" class="form-control color-hex" value="{{ $gradient[0] }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <input type="color" class="form-control" value="{{ $gradient[0] }}">
                                                </div>
                                            </div>
                                            <div class="form-row col-md-6">
                                                <div class="form-group col-md-6">
                                                    <input type="text" class="form-control color-hex" value="{{ isset($gradient[1]) ? $gradient[1] : '#ffffff' }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <input type="color" class="form-control" value="{{ isset($gradient[1]) ? $gradient[1] : '#ffffff' }}">
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <input type="text" class="form-control color-hex" value="{{ $attr->value }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <input type="color" class="form-control" value="{{ $attr->value }}">
                                            </div>
                                        </div>
                                        @endif
                                    @elseif ($type == "fill_type")
                                    <div class="form-group">
                                        <select class="form-control fill-type">
                                            <option value="solid">Solid</option>
                                            <option value="gradient">Gradient</option>
                                        </select>
                                    </div>
                                    @elseif ($type == "font_color")
                                    <div class="form-group">
                                        <select class="form-control font-color">
                                            <option value="Black">Black</option>
                                        </select>
                                    </div>
                                    @elseif ($type == "background")
                                    <div class="form-row">
                                        <div class="form-group col-md-10">
                                            <span class="background-url">{{ $attr->value }}</span>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <input type="file" name="bk-image" class="form-control d-none">
                                            <button type="button" class="btn btn-primary btn-sm btn-browse">Browse</button>
                                        </div>
                                    </div>
                                    @elseif ($type == "background_template")
                                    <div class="form-group">
                                        <?php
                                            $output_dimensions = config("templates.$customer_name.output_dimensions");
                                            if (!isset($output_dimensions)) {
                                                $output_dimensions = [];
                                            }
                                        ?>
                                        <select class="form-control template">
                                            <option value="-2">None</option>
                                            <option value="-1" selected>All</option>
                                            @for($i = 0; $i < count($output_dimensions); $i++)
                                                <option value="{{ $i }}">Template{{ $i + 1 }} ({{ $output_dimensions[$i] }})</option>
                                            @endfor
                                            @for($i = 0; $i < count($templates); $i++)
                                                <option value="{{ $templates[$i]['id'] }}" {{ $attr->value == $templates[$i]['id'] ? "selected" : "" }}>Template{{ count($output_dimensions) + $i + 1 }} ({{ $templates[$i]['name'] }})</option>
                                            @endfor
                                        </select>
                                    </div>
                                    @elseif ($type == "number")
                                    <div class="form-group">
                                        <input type="number" class="form-control" value="{{ $attr->value }}" />
                                    </div>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-attr" disabled>Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </x-slot>

        <x-slot name="footer">
            <button type="button" class="btn btn-sm btn-primary float-right" id="btn_create_theme" disabled>@lang('Create Theme')</button>
        </x-slot>
    </x-backend.card>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/template.js") }}"></script>
@endpush