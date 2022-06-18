@extends('backend.layouts.app')

@section('title', __('Update Template'))

@php
$field_types = Config::get('templates.field_types');
$fonts = Config::get('templates.fonts');
$columns = Config::get('templates.xlsx_columns');
$column_widths = Config::get('templates.xlsx_column_widths');
@endphp

@section('content')
    <div class="alert-info"></div>
    <div class="row">
        <div class="col-6">
            <x-forms.post :action="route('admin.auth.template.update_xlsx')" enctype="multipart/form-data">
                <x-backend.card>
                    <x-slot name="header">
                        @lang('Update XLSX File')
                    </x-slot>

                    <x-slot name="body">
                        <input type="hidden" name="template_id" value="{{ $template->id }}" />
                        <div class="form-row">
                            <div class="col-md-12 file-input-group">
                                <div class="form-group">
                                    <label>XLSX</label>
                                    <input type="file" class="form-control-file" name="templates" data-show-preview="false" required>
                                </div>
                            </div>
                        </div>
                    </x-slot>

                    <x-slot name="footer">
                        <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Submit')</button>
                    </x-slot>
                </x-backend.card>
            </x-forms.post>
        </div>
        <div class="col-6 template-image">
            <x-forms.post :action="route('admin.auth.template.update_image')" enctype="multipart/form-data">
                <x-backend.card>
                    <x-slot name="header">
                        @lang('Update Image File')
                    </x-slot>

                    @if ($template->image_url != "")
                    <x-slot name="headerActions">
                        <a href="/{{ $template->image_url }}" target="_blank" download="{{ $template->name }}">Download</a> |
                        <a href="#" id="delete_template_image" data-id="{{ $template->id }}">Delete</a>
                    </x-slot>
                    @endif

                    <x-slot name="body">
                        <input type="hidden" name="template_id" value="{{ $template->id }}" />
                        <div class="form-row">
                            <div class="col-md-12 file-input-group">
                                <div class="form-group">
                                    <label>Image</label>
                                    <input type="file" class="form-control-file" name="image" data-show-preview="false" required>
                                </div>
                            </div>
                        </div>
                    </x-slot>

                    <x-slot name="footer">
                        <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Submit')</button>
                    </x-slot>
                </x-backend.card>
            </x-forms.post>
        </div>
    </div>

    <x-backend.card>
        <x-slot name="header">
            @lang('Update Template')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link
                class="card-header-action"
                icon="c-icon cil-cloud-download"
                :href="route('admin.auth.template.download', ['customer_id' => $customer->id, 'template' => $template->id])"
                :text="__('Download')" />
            <x-utils.link
                class="card-header-action"
                icon="c-icon cil-data-transfer-down"
                :href="route('admin.auth.template.export', ['customer_id' => $customer->id, 'template' => $template->id])"
                :text="__('Download Template')" />
        </x-slot>

        <x-slot name="body">
            <div class="table-responsive template-table-wrapper">
                <form id="templateForm">
                    <input type="hidden" id="customerId" value="{{ $customer->id }}">
                    <input type="hidden" id="customerName" value="{{ $customer->value }}">
                    <input type="hidden" id="templateId" value="{{ $template->id }}">
                    <table class="table table-bordered template-table">
                        <thead>
                            <th style="width: 120px;" class="text-center">
                                <button type="button" class="btn btn-add-row px-0"><span class="c-icon cil-plus"></span> Add Row</button>
                            </th>
                            @for ($i = 0; $i < count($columns); $i++)
                                <th style="width: {{ $column_widths[$i] }}px;">{{ $columns[$i] }}</th>
                            @endfor
                        </thead>
                        <tbody>
                            <tr>
                                <th></th>
                                @foreach ($columns as $column)
                                    @if ($column == 'Field Type')
                                        <th class="align-middle" style="width: 125px;">Template Name</th>
                                    @elseif ($column == 'Name')
                                        <th class="align-middle"><input class="form-control" name="name" value="{{ $template->name }}" /></th>
                                    @else
                                        <td class="align-middle"><input class="form-control" disabled /></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <th></th>
                                @foreach ($columns as $column)
                                    @if ($column == 'Field Type')
                                        <th class="align-middle" >Dimensions</th>
                                    @elseif ($column == 'Name')
                                        <th class="align-middle" ><input class="form-control" disabled /></th>
                                    @elseif ($column == 'Width')
                                        <td class="align-middle"><input class="form-control" name="width" value="{{ $template->width }}"></input></td>
                                    @elseif ($column == 'Height')
                                        <td class="align-middle"><input class="form-control" name="height" value="{{ $template->height }}"></input></td>
                                    @else
                                        <td class="align-middle"><input class="form-control" disabled /></td>
                                    @endif
                                @endforeach
                            </tr>
                            @foreach ($template->fields as $field)
                                @php
                                    $options = json_decode($field->options);
                                @endphp
                                <tr data-field-id="{{ $field->id }}">
                                    <th>
                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn px-1 btn-move-up-row"><span class="c-icon cil-arrow-top"></span></button>
                                            <button type="button" class="btn px-1 btn-move-down-row"><span class="c-icon cil-arrow-bottom"></span></button>
                                            <button type="button" class="btn px-1 btn-delete-row"><span class="c-icon cil-x"></span></button>
                                        </div>
                                    </th>
                                    @foreach($columns as $column)
                                        @if ($column == 'Field Type')
                                            <th data-col-name="{{ $column }}">
                                                <select class="form-control" >
                                                @foreach ($field_types as $field_type)
                                                    <option value="{{ $field_type }}" {{ $field_type == $options->{$column} ? 'selected' : '' }}>{{ $field_type }}</option>
                                                @endforeach
                                                </select>
                                            </th>
                                        @elseif ($column == 'Name')
                                            <th data-col-name="{{ $column }}"><input class="form-control" value="{{ $options->{$column} }}" /></th>
                                        @elseif ($column == 'Font Selector' || $column == 'Color Selector' || $column == 'Moveable')
                                            <td data-col-name="{{ $column }}">
                                                <select class="form-control">
                                                    <option value="No" {{ isset($options->{$column}) && $options->{$column} == 'No' ? 'selected' : '' }}>No</option>
                                                    <option value="Yes" {{ isset($options->{$column}) && $options->{$column} == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                </select>
                                            </td>
                                        @elseif ($column == 'Alignment')
                                            <td data-col-name="{{ $column }}">
                                                <div class="d-flex align-items-center">
                                                    <select class="form-control">
                                                        <option value="left" {{ isset($options->{$column}) && $options->{$column} == 'left' ? 'selected' : '' }}>left</option>
                                                        <option value="center" {{ isset($options->{$column}) && $options->{$column} == 'center' ? 'selected' : '' }}>center</option>
                                                        <option value="right" {{ isset($options->{$column}) && $options->{$column} == 'right' ? 'selected' : '' }}>right</option>
                                                    </select>
                                                    <input type="checkbox" class="ml-1" {{ isset($options->ShowAlignment) && $options->ShowAlignment ? "checked" : "" }}/>
                                                </div>
                                            </td>
                                        @elseif ($column == 'Font')
                                            <td data-col-name="{{ $column }}">
                                                <select class="form-control font-select">
                                                    <option value=""></option>
                                                    @foreach ($fonts as $key => $value)
                                                    <option value="{{ $key }}" {{ $key == $options->{$column} ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @elseif ($column == 'Order')
                                            <td data-col-name="{{ $column }}"><input type="number" class="form-control" value="{{ !empty($field->order) ? $field->order : '1000'}}" min="1" max="1000" /></td>
                                        @elseif ($column == 'Kerning')
                                            <td data-col-name="{{ $column }}">
                                                <select class="form-control">
                                                    <option value="none" {{ isset($options->{$column}) && $options->{$column} == 'none' ? 'selected' : '' }}>none</option>
                                                    <option value="optical" {{ isset($options->{$column}) && $options->{$column} == 'optical' ? 'selected' : '' }}>optical</option>
                                                    <option value="metric" {{ isset($options->{$column}) && $options->{$column} == 'metric' ? 'selected' : '' }}>metric</option>
                                                </select>
                                            </td>
                                        @elseif ($column == 'Filename')
                                            <td data-col-name="{{ $column }}">
                                                @php
                                                    if (isset($options->{$column})) {
                                                        $arr = explode('/', $options->{$column});
                                                        $saved_name = array_reverse($arr)[0];
                                                    }
                                                @endphp
                                                <input type="file" class="form-control-file" data-show-preview="false" />
                                                <span class="{{ $column }}_saved d-none" id="{{ $column }}_saved">{{ isset($options->{$column}) ? $options->{$column} : ''}}</span>
                                                <span class="{{ $column }}_saved_name" id="{{ $column }}_saved_name">
                                                  @if(isset($options->{$column}))
                                                    <a href="{{asset('/img/upload/'.$saved_name)}}" download>{{$saved_name}}</a>
                                                  @endif
                                                </span>
                                            </td>
                                        @elseif (($column == 'Option1' && $options->{'Field Type'} == "Image List") || ($column == 'Option2' && $options->{'Field Type'} == "Image List Group"))
                                            <td data-col-name="{{ $column }}">
                                                <select class="form-control">
                                                @foreach($image_lists as $key => $list)
                                                    <option value="{{ $list->id }}" data-default-url="{{ $default_image_list[$key] }}" {{ isset($options->{$column}) && $options->{$column} == $list->id ? "selected" : "" }}>{{ $list->name }}</option>
                                                @endforeach
                                                </select>
                                            </td>
                                        @elseif (($column == 'Option1' || $column == 'Option3') && ($options->{'Field Type'} == "Rectangle" || $options->{'Field Type'} == "Circle" || $options->{'Field Type'} == "Safe Zone"))
                                            <td data-col-name="{{ $column }}">
                                                <div class="form-row">
                                                    <div class="col-md-6 col-sm-6 form-group">
                                                        <input type="text" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($options->{$column}) ? $options->{$column} : '#000000'}}" >
                                                    </div>
                                                    <div class="col-md-6 col-sm-6 form-group">
                                                        <input type="color" class="form-control" value="{{ isset($options->{$column}) ? $options->{$column} : '#000000'}}" >
                                                    </div>
                                                </div>
                                            </td>
                                        @elseif ($column == 'Size To Fit')
                                            <td data-col-name="{{ $column }}">
                                                <input type="checkbox" {{ isset($options->{$column}) && $options->{$column} ? "checked" : "" }}/>
                                            </td>
                                        @elseif ($column == 'Font Color')
                                            <td data-col-name="{{ $column }}">
                                                <div class="form-row">
                                                    <div class="col-md-6 col-sm-6 form-group">
                                                        <input type="text" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($options->{$column}) ? $options->{$column} : '#000000'}}" >
                                                    </div>
                                                    <div class="col-md-6 col-sm-6 form-group">
                                                        <input type="color" class="form-control" value="{{ isset($options->{$column}) ? $options->{$column} : '#000000'}}" >
                                                    </div>
                                                </div>
                                            </td>
                                        @elseif ($column == 'Leading')
                                            @php
                                                $col = isset($options->{$column}) ? "Leading" : (isset($options->{'Line Spacing'}) ? "Line Spacing" : "");
                                            @endphp
                                            <td data-col-name="{{ $col }}"><input class="form-control" value="{{ isset($options->{$col}) ? $options->{$col} : ''}}" /></td>
                                        @elseif (($column == 'Option1' && $options->{'Field Type'} == "HTML") || ($column == 'Option5' && $options->{'Field Type'} == "Smart Object"))
                                            <td data-col-name="{{ $column }}"><textarea class="form-control">{{ isset($options->{$column}) ? $options->{$column} : ''}}</textarea></td>
                                        @elseif ($column == 'Option1' && $options->{'Field Type'} == "Text Oversampling")
                                            <td data-col-name="{{ $column }}"><input class="form-control" value="{{ isset($options->{$column}) ? $options->{$column} : '1'}}" /></td>
                                        @elseif ($column == 'Option2' && $options->{'Field Type'} == "Text Oversampling")
                                            <td data-col-name="{{ $column }}"><input class="form-control" value="{{ isset($options->{$column}) ? $options->{$column} : '0'}}" /></td>
                                        @else
                                            <td data-col-name="{{ $column }}"><input class="form-control" value="{{ isset($options->{$column}) ? $options->{$column} : ''}}" /></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </x-slot>

        <x-slot name="footer">
            <button type="button" class="btn btn-sm btn-primary float-right" id="btn_update_view_template">@lang('Update & View Template')</button>
            <button type="button" class="btn btn-sm btn-primary float-right mr-2" id="btn_update_template">@lang('Update Template')</button>
            <x-utils.link class="btn btn-sm btn-secondary float-right mr-2" :href="route('admin.auth.template.index', $customer->id)" :text="__('Cancel')" />
        </x-slot>
    </x-backend.card>

    <div id="preview-popup">
        <div id="drag-handler">
            <span>Preview</span>
            <span class="toggle-button preview-control"><i class="cil-window-minimize"></i></span>
            <span class="edit-button edit preview-control"><i class="cil-pencil"></i></span>
            <span class="add-button add preview-control"><img src="/img/icons/toolbar.png" style="width: 16px;height: 16px;margin-bottom: 9px;"></span>
            <ul class="list-field-type" style="display: none">
                @foreach ($field_types as $field_type)
                    <li value="{{ $field_type }}">{{ $field_type }}</li>
                @endforeach
            </ul>
        </div>
        <div id="footer" class="pt-1 text-center">
            <label>X: </label>
            <input type="number" id="x_value" style="width: 50px">

            <label>Y: </label>
            <input type="number" id="y_value" style="width: 50px">

            <label>W: </label>
            <input type="number" id="w_value" style="width: 50px">

            <label>H: </label>
            <input type="number" id="h_value" style="width: 50px">
        </div>
    </div>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/template/preview.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/template/main.js') }}"></script>
@endpush
