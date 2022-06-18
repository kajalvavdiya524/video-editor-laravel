@extends('frontend.layouts.app')

@section('title', __('Banner'))

@section('content')
    @php
        $image_index = 0;
        $team_list = "";
        foreach ($logged_in_user->teams as $team) {
            $team_list .= $team->name;
        }
        include(base_path().'/resources/lib/fonts.php');
        $existAdditionalFields = false;
        $bShowFontSelector = false;
    @endphp
    <div class="alert alert-danger errors" role="alert" style="display: none;"></div>
    <div class="alert alert-success success" role="alert" style="display: none;"></div>
    <div class="d-none" id="preview-images"></div>
    <div class="d-none" id="product-images"></div>
    <input type="hidden" id="is_download_draft" value="{{ $logged_in_user->is_download_draft }}" />
    <input type="hidden" id="is_download_project" value="{{ $logged_in_user->is_download_project }}" />
    <form id="adForm" enctype="multipart/form-data">
        <div class="position-relative">
            @include('frontend.create.includes.customer')
            @php
                $customer_name = "Amazon";
            @endphp
            <div class="inline-template-selector" style="display:inline-block">
                @include('frontend.create.includes.template_new')
            </div>
        </div>
        @if (isset($template) && count($template_fields) > 0)
            @include('frontend.create.includes.project_name')
            <input type="hidden" id="template_id" name="template_id" value="{{ $template_fields[0]['template_id'] }}" />
            <div class="form-row mb-2 template-components">
                <?php
                    $hasListType = false;
                    $background_color_inx = 0;
                    $background_image_inx = 0;
                    $img_from_bk_inx = 0;
                    $group_color = [];
                    $group_font = [];
                    $image_list_group = [];
                    foreach ($template_fields as $field) {
                        if ($field["type"] == "Group Color") {
                            $options = json_decode($field["options"], true);
                            $group_color = explode(",", $options['Option1']);
                        }
                        if ($field["type"] == "Group Font") {
                            $options = json_decode($field["options"], true);
                            $group_font = explode(",", $options['Option1']);
                        }
                        if ($field["type"] == "Image List Group") {
                            $options = json_decode($field["options"], true);
                            $image_list_group = explode(",", $options['Option1']);
                        }
                    }
                ?>
                @foreach ($template_fields as $field)
                    @if ($field["type"] == "UPC/GTIN")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-12 d-none-parent">
                            <label>
                                UPC / GTIN / ASIN / TCIN / WMT-ID
                                <span class="{{ empty($settings->file_ids) && empty($options['Placeholder']) ? 'isDisabled' : '' }}"><a href="#" id="view-img" class="{{ empty($settings->file_ids) && empty($options['Placeholder']) ? 'disabled' : '' }}">Edit Images</a></span>
                                <a href="#" id="upload-from-web">Upload from Web...</a>
                            </label>
                            <div class="form-row">
                                <div class="col-12 form-group">
                                    <input type="text" name="file_ids" class="form-control" autofocus value="{{ empty($settings->file_ids) ? $options['Placeholder'] : $settings->file_ids }}">
                                </div>
                            </div>
                        </div>
                    @elseif ($field["type"] == "Product Space")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>{{ $field["name"] }}</label>
                            <input type="text" name="product_space" class="form-control" value="{{ isset($settings->product_space) ? $settings->product_space : (!empty($options['Option1']) ? $options['Option1'] : '0') }}">
                        </div>
                    @elseif ($field["type"] == "Text" || $field["type"] == "Text Options")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            @php
                                $default_value = isset($settings->{$field['element_id']}) ? $settings->{$field['element_id']} : $options['Placeholder'];
                                $max_chars = !empty($options['Max Chars']) ? (int)$options['Max Chars'] : 0;
                            @endphp
                            <label class="text-label">
                                <span>{{ $field["name"] }}</span>
                                @if ($max_chars > 0)
                                    <span class="badge {{ strlen($default_value) > $max_chars ? 'badge-danger' : 'badge-success' }}">
                                        {{ strlen($default_value) }}/{{ $max_chars }}
                                    </span>
                                @endif
                            </label>
                            @if ($field["type"] == "Text")
                            <input type="text" name="{{ $field['element_id'] }}" class="template-text-field form-control" value="{{ $default_value }}" data-max-chars="{{ $max_chars }}">
                            @else
                            <select name="{{ $field['element_id'] }}" id="{{ $field['element_id'] }}" class="template-text-field form-control">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if (!empty($options["Option". $i]))
                                        <option value="{{ $options["Option". $i] }}">{{ $options["Option". $i] }}</option>
                                    @endif
                                @endfor
                            </select>
                            @endif
                            @if (isset($options['ShowAlignment']) && $options['ShowAlignment'])
                            <div class="mt-2">
                                <select name="{{ $field['element_id'] . '_alignment' }}" class="form-control">
                                    <option value="left" {{ $options['Alignment'] === 'left' ? "selected" : "" }} >Left</option>
                                    <option value="center" {{ $options['Alignment'] === 'center' ? "selected" : "" }} >Center</option>
                                    <option value="right" {{ $options['Alignment'] === 'right' ? "selected" : "" }} >Right</option>
                                </select>
                                </div>
                            @endif
                        </div>
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                        </div>
                        @if (isset($options["Font Selector"]) && $options["Font Selector"] == "Yes" && !in_array($field["name"], $group_font))
                        <?php $bShowFontSelector = true; ?>
                        <div class="col-md-2 form-row">
                            <div class="form-group">
                                <label>Font</label>
                                <select name="{{ $field['element_id'] }}_font" id="{{ $field['element_id'] }}_font" class="form-control font-select" style="width: 100%; height: 38px;">
                                    @foreach($fonts as $key => $value)
                                        @if ($key == $options["Font"])
                                            <option value="{{ $key }}" selected>{{ $value }}</option>
                                        @else
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-2 form-row {{$bShowFontSelector ? '' : 'd-none'}}">
                        @if ($field["type"] == "Text")
                            <div class="form-group">
                                <label>Font Size</label>
                                <br/>
                                <div class="input-group mb-3">
                                    <input type="number" class=" w-25 form-control" name="{{ $field['element_id'] }}_fontsize" id="{{ $field['element_id'] }}_fontsize" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fontsize'}) ? $settings->{$field['element_id'].'_fontsize'} : (empty($options['Font Size']) ? '20' : $options['Font Size']) }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">
                                        <input id="{{ $field['element_id'] }}_carryfontsize" type="checkbox" name="{{ $field['element_id'] }}_carryfontsize" ></input>
                                        <label class="ml-1 mb-0" for="{{ $field['element_id'] }}_carryfontsize"> Carry over </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                <label>Font Size</label>
                                <input type="number" name="{{ $field['element_id'] }}_fontsize" id="{{ $field['element_id'] }}_fontsize" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fontsize'}) ? $settings->{$field['element_id'].'_fontsize'} : (empty($options['Font Size']) ? '20' : $options['Font Size']) }}">
                            </div>
                        @endif
                        </div>
                        @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes" && !in_array($field["name"], $group_color))
                        <div class="col-md-2">
                            <label>{{ $field['name'] }} Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{ $field['element_id'] }}_color" id="{{ $field['element_id'] }}_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_color'}) ? $settings->{$field['element_id'].'_color'} : (isset($options['Font Color']) ? $options['Font Color'] : '#000000') }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{ $field['element_id'] }}_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_color'}) ? $settings->{$field['element_id'].'_color'} : (isset($options['Font Color']) ? $options['Font Color'] : '#000000') }}">
                                </div>
                            </div>
                        </div>
                        @endif
                    @elseif ($field["type"] == "Text from Spreadsheet")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>{{ $field["name"] }}</label>
                            <div class="d-flex">
                                <input type="text" class="form-control" list="{{ $field['element_id'] }}_datalist" name="{{ $field['element_id'] }}" id="{{ $field['element_id'] }}" data-header="{{ isset($options['Option1']) ? $options['Option1'] : '' }}" value="{{ isset($settings->{$field['element_id']}) ? $settings->{$field['element_id']} : '' }}">
                                <datalist id="{{ $field['element_id'] }}_datalist"></datalist>
                                <button type="button" class="btn btn-secondary btn-clear"><i class="glyphicon glyphicon-ban-circle"></i></button>
                                <div class="btn btn-primary btn-file">
                                    <span class="hidden-xs">...</span>
                                    <input type="file" class="form-control text-from-spreadsheet" accept=".csv, .xlsx, .xls">
                                </div>
                            </div>
                        </div>
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                        </div>
                    @elseif ($field["type"] == "Static Text")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                        </div>
                    @elseif ($field["type"] == "Rectangle" || $field["type"] == "Circle")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes")
                        <div class="col-md-2">
                            <label>{{ $field['name'] }} Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{ $field['element_id'] }}_fill_color" id="{{ $field['element_id'] }}_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option3']) ? $options['Option3'] : '#ffffff') }}">
                                    <input type="checkbox" class="toggle-shape" name="{{ $field['element_id'] }}_toggle_shape" id="{{ $field['element_id'] }}_toggle_shape" checked />
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{ $field['element_id'] }}_fill_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option3']) ? $options['Option3'] : '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label>{{ $field['name'] }} Stroke Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{ $field['element_id'] }}_stroke_color" id="{{ $field['element_id'] }}_stroke_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_stroke_color'}) ? $settings->{$field['element_id'].'_stroke_color'} : (isset($options['Option1']) ? $options['Option1'] : '#ffffff') }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{ $field['element_id'] }}_stroke_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_stroke_color'}) ? $settings->{$field['element_id'].'_stroke_color'} : (isset($options['Option1']) ? $options['Option1'] : '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_scaleX" id="{{ $field['element_id'] }}_scaleX" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scaleX'}) ? $settings->{$field['element_id'].'_scaleX'} : '1' }}">
                            <input type="number" name="{{ $field['element_id'] }}_scaleY" id="{{ $field['element_id'] }}_scaleY" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scaleY'}) ? $settings->{$field['element_id'].'_scaleY'} : '1' }}">
                        </div>
                    @elseif ($field["type"] == "Circle Type")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes")
                        <div class="col-md-2">
                            <label>{{ $field['name'] }} Fill Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{ $field['element_id'] }}_fill_color" id="{{ $field['element_id'] }}_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option1']) ? $options['Option1'] : '#ffffff') }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{ $field['element_id'] }}_fill_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option1']) ? $options['Option1'] : '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                        </div>
                    @elseif ($field["type"] == "Line")
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                        </div>
                    @elseif ($field["type"] == "List All" && !$hasListType)
                        <?php
                            $hasListType = true;
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>List Type</label>
                            <select name="list_type" class="form-control">
                                <option value="circle">Circle</option>
                                <option value="square">Square</option>
                                <option value="checkmark">Checkmark</option>
                                <option value="star">Star</option>
                            </select>
                        </div>
                        @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes")
                        <div class="col-md-2">
                            <label>List Fill</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="list_fill_color" id="list_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option4']) ? $options['Option4'] : '#000000') }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="list_fill_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option4']) ? $options['Option4'] : '#000000') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label>List Stroke</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="list_stroke_color" id="list_stroke_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_stroke_color'}) ? $settings->{$field['element_id'].'_stroke_color'} : (isset($options['Option2']) ? $options['Option2'] : '#ffffff') }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="list_stroke_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_stroke_color'}) ? $settings->{$field['element_id'].'_stroke_color'} : (isset($options['Option2']) ? $options['Option2'] : '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label>Text</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="list_text_color" id="list_text_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_text_color'}) ? $settings->{$field['element_id'].'_text_color'} : '#ffffff' }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="list_text_color" class="form-control" value="{{ isset($settings->{$field['element_id'].'_text_color'}) ? $settings->{$field['element_id'].'_text_color'} : '#ffffff' }}">
                                </div>
                            </div>
                        </div>
                        @endif
                    @elseif ($field["type"] == "Upload Image")
                    <?php
                        $options = json_decode($field["options"], true);
                    ?>
                    <div class="col-md-{{$field['grid_col']}} form-group">
                        <label>{{ $field["name"] }}</label>
                        <input type="file" class="form-control-file" name="{{ $field['element_id'] }}" data-show-preview="false">
                        <input type="hidden" name="{{ $field['element_id'] }}_saved" id="{{ $field['element_id'] }}_saved" value="{{ isset($settings->{$field['element_id'].'_saved'}) ? $settings->{$field['element_id'].'_saved'} : ($options['Filename'] ? $options['Filename'] : '') }}">
                    </div>
                    <div class="upload_image_offset d-none">
                        <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}" data-default="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}" data-default="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}" data-default="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}" data-default="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                        <input type="number" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}" data-default="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_height" id="{{ $field['element_id'] }}_height" class="form-control" value="{{ isset($settings->{$field['element_id'].'_height'}) ? $settings->{$field['element_id'].'_height'} : '0' }}" data-default="{{ isset($settings->{$field['element_id'].'_height'}) ? $settings->{$field['element_id'].'_height'} : '0' }}">
                    </div>
                    @include('frontend.includes.saving_image')
                    
                    @elseif ($field["type"] == "Background Image Upload")
                    <div class="col-md-{{$field['grid_col']}} form-group">
                        <label>{{ $field["name"] }}</label>
                        <input type="file" class="form-control-file" name="{{ $field['element_id'] }}" data-show-preview="false">
                        <input type="hidden" name="{{ $field['element_id'] }}_saved" id="{{ $field['element_id'] }}_saved" value="{{ isset($settings->{$field['element_id'].'_saved'}) ? $settings->{$field['element_id'].'_saved'} : '' }}">
                    </div>
                    @include('frontend.includes.saving_image')
                    @elseif ($field["type"] == "Static Image")
                    <div class="offset d-none">
                        <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                        <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                    </div>
                    @elseif ($field["type"] == "Image List")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        @if (!in_array($field["name"], $image_list_group))
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>{{ $field["name"] }}</label>
                            <select class="form-control image-list" id="{{ $field['element_id'] }}" name="{{ $field['element_id'] }}">
                                @foreach ($image_list as $image)
                                    @if ($image->list_id == $options["Option1"])
                                    <option value="{{ $image->url }}" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == $image->url) ? "selected" : "" }} >{{ $image->name }}</option>
                                    @endif
                                @endforeach
                                <option value="none" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == "none") ? "selected" : "" }} >None</option>
                            </select>
                        </div>
                        @endif
                        <div class="offset d-none">
                            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                            <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                        </div>
                        
                    @elseif ($field["type"] == "Product Image")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-12 form-row d-none">
                            <div class="form-group col-md-3">
                                <label>Offset X</label>
                                @if ($options["Option1"] == "Hero")
                                    <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[$image_index]) ? 0 : $settings->x_offset[$image_index] }}" default-value = "0">
                                @else
                                    <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[$image_index]) ? (!empty($options['X']) ? $options['X'] : 0) : $settings->x_offset[$image_index] }}" default-value = "{{ !empty($options['X']) ? $options['X'] : 0 }}">
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label>Offset Y</label>
                                @if ($options["Option1"] == "Hero")
                                    <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[$image_index]) ? 0 : $settings->y_offset[$image_index] }}" default-value = "0">
                                @else
                                    <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[$image_index]) ? (!empty($options['Y']) ? $options['Y'] : 0) : $settings->y_offset[$image_index] }}" default-value = "{{ !empty($options['Y']) ? $options['Y'] : 0 }}">
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label>Angle</label>
                                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[$image_index]) ? (!empty($options['Angle']) ? $options['Angle'] : 0) : $settings->angle[$image_index] }}" default-value = "{{ !empty($options['Angle']) ? $options['Angle'] : 0 }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Scale</label>
                                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[$image_index]) ? (!empty($options['Scale']) ? $options['Scale'] : 1) : $settings->scale[$image_index] }}" default-value = "{{ !empty($options['Scale']) ? $options['Scale'] : 1 }}">
                            </div>
                            <input type="hidden" name="moveable[]" value="{{ empty($settings->moveable[$image_index]) ? (!empty($options['Moveable']) ? $options['Moveable'] : '') : $settings->moveable[$image_index] }}">
                            <input type="hidden" name="p_width[]" value="{{ empty($settings->p_width[$image_index]) ? 0 : $settings->p_width[$image_index] }}">
                            <input type="hidden" name="p_height[]" value="{{ empty($settings->p_height[$image_index]) ? 0 : $settings->p_height[$image_index] }}">
                        </div>
                        <?php
                            $image_index ++;
                        ?>
                    @elseif ($field["type"] == "Background Theme Image")
                    <div class="col-md-2 form-group position-relative">
                        <label>{{  $field["name"] }}</label>
                        <button class="btn btn-primary select-bkimg" style="display:block" type="button" data-type="{{ $field['type'] }}" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Image…" data-text="Image…">Image…</button>
                        <div class="selected-image">
                        @if (isset($settings->background[$background_image_inx]))
                        <?php
                            $bkg_img = $settings->background[$background_image_inx];
                            $background_image_inx ++;
                            $arr = explode('/', $bkg_img);
                            // $arr[count($arr) - 2] = $settings->output_dimensions;
                            $background = implode('/', $arr);
                        ?>
                        <img class="background-preview" src="{{ $background }}" />
                        @endif
                        <input type="hidden" name="background[]" value="{{ isset($background) ? $background : '' }}" />
                        </div>
                    </div>
                    <div class="offset d-none">
                        <input type="hidden" name="bk_img_offset_x[]" class="form-control" value="{{ isset($settings->bk_img_offset_x[$background_image_inx]) ? $settings->bk_img_offset_x[$background_image_inx] : '0' }}">
                        <input type="hidden" name="bk_img_offset_y[]" class="form-control" value="{{ isset($settings->bk_img_offset_y[$background_image_inx]) ? $settings->bk_img_offset_y[$background_image_inx] : '0' }}">
                        <input type="hidden" name="bk_img_scale[]" class="form-control" value="{{ isset($settings->bk_img_scale[$background_image_inx]) ? $settings->bk_img_scale[$background_image_inx] : '1' }}">
                    </div>
                    @elseif ($field["type"] == "Background Theme")
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>{{ $field["name"] }}</label>
                            <select id="theme" name="theme" class="form-control">
                                @foreach ($themes as $theme)
                                    <option value="{{ strtolower($theme['id']) }}" {{ (!empty($settings->theme) && ($settings->theme == strtolower($theme['name']) || $settings->theme == $theme['id'] )) ? "selected" : "" }}>{{ ucfirst($theme['name']) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($field["type"] == "Background Theme Color")
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>Background Theme Color</label>
                            <?php
                                if (count($themes) > 0) {
                                    $attributes = $themes[0]['attributes'];
                                    $background_theme_colors = $attributes[0];
                                }
                            ?>
                            <div class="select-custom">
                                <select name="background_color[]">
                                    @if (count($themes) > 0)
                                        @foreach ($background_theme_colors->list as $color)
                                            <?php
                                                $c = implode(",", array_column($color->list, 'value'));
                                            ?>
                                            <option value="{{ $c }}" {{ (!empty($settings->background_color[$background_color_inx]) && $settings->background_color[$background_color_inx] == $c) ? "selected" : "" }}>
                                                {{$color->name}}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="select-items select-hide">
                                    @if (count($themes) > 0)
                                        @foreach ($background_theme_colors->list as $color)
                                            <?php
                                                $c = implode(",", array_column($color->list, 'value'));
                                                $c_arr = explode(",", $c);
                                            ?>
                                            @if ($c_arr[0] == 'solid')
                                                <div value="{{ $c }}" class="option-item {{ (!empty($settings->background_color[$background_color_inx]) && $settings->background_color[$background_color_inx] == $c) ? 'same-as-selected' : '' }}">
                                                    <span>{{$color->name}}</span>
                                                    <span class="color-pane" style="background: {{ $c_arr[1] }}"></span>
                                                </div>
                                            @else
                                                <div value="{{ $c }}" class="option-item {{ (!empty($settings->background_color[$background_color_inx]) && $settings->background_color[$background_color_inx] == $c) ? 'same-as-selected' : '' }}">
                                                    <span>{{$color->name}}</span>
                                                    <span class="color-pane" style="background: {{ $c_arr[1] }}; background: linear-gradient(90deg, {{ $c_arr[1] }} 0%, {{ $c_arr[2] }} 100%);"></span>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <?php
                            $background_color_inx ++;
                        ?>
                    @elseif ($field["type"] == "Background Color Picker")
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>Background Color</label>
                            <input type="color" id="background_color" name="background_color" class="form-control" value="{{ empty($settings->background_color) ? "#4864C0" : $settings->background_color }}" >
                        </div>
                    @elseif ($field["type"] == "Image From Background")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-2 form-group position-relative">
                            <label>{{ $field["name"] }}</label>
                            <button class="btn btn-primary select-bkimg" style="display:block" type="button" data-type="{{ $field['type'] }}" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Image…" data-text="Image…">Image…</button>
                            <div class="selected-image">
                            @if (isset($settings->img_from_bk[$img_from_bk_inx]))
                            <?php
                                $background = $settings->img_from_bk[$img_from_bk_inx];
                            ?>
                            <img class="background-preview" src="{{ $background }}" />
                            @endif
                            <input type="hidden" name="img_from_bk[]" value="{{ isset($settings->img_from_bk[$img_from_bk_inx]) ? $settings->img_from_bk[$img_from_bk_inx] : '' }}" />
                            </div>
                        </div>
                        <div class="offset d-none">
                            <input type="number" name="img_from_bk_offset_x[]" class="form-control" value="{{ isset($settings->img_from_bk_offset_x[$img_from_bk_inx]) ? floatval($settings->img_from_bk_offset_x[$img_from_bk_inx]) : 0 }}">
                            <input type="number" name="img_from_bk_offset_y[]" class="form-control" value="{{ isset($settings->img_from_bk_offset_y[$img_from_bk_inx]) ? floatval($settings->img_from_bk_offset_y[$img_from_bk_inx]) : 0 }}">
                            <input type="number" name="img_from_bk_scale[]" class="form-control" value="{{ isset($settings->img_from_bk_scale[$img_from_bk_inx]) ? floatval($settings->img_from_bk_scale[$img_from_bk_inx]) : 1 }}">
                        </div>
                        @php
                            $img_from_bk_inx ++;
                        @endphp
                    @elseif ($field["type"] == "DPI")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="dpi form-group d-none">
                            <label>DPI</label>
                            <input type="number" name="dpi" class="form-control" value="{{ empty($settings->dpi) ? (!empty($options['Option1']) ? $options['Option1'] : 300) : $settings->dpi }}">
                        </div>
                    @elseif ($field["type"] == "Max File Size")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="max-file-size form-group d-none">
                            <label>Max File Size</label>
                            <input type="number" name="max_file_size" class="form-control" value="{{ !empty($options['Option1']) ? $options['Option1'] : 50 }}">
                        </div>
                    @elseif ($field["type"] == "Product Dimensions")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="product-image-alignment form-group d-none">
                            <label>Product Dimensions</label>
                            <input type="text" name="product_image_alignment" class="form-control" value="{{ !isset($settings->product_image_alignment) ? (!empty($options['Alignment']) ? $options['Alignment'] : 'left') : $settings->product_image_alignment }}">
                        </div>
                    @elseif ($field["type"] == "Stroke")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-2 col-4 form-group">
                            <input type="hidden" id="stroke_color" name="stroke_color" value="{{ !empty($options['Option1']) ? $options['Option1'] : '#6d6d6d' }}" />
                            <input type="hidden" id="stroke_width" name="stroke_width" value="{{ !empty($options['Option2']) ? $options['Option2'] : 1 }}" />
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="show_stroke" name="show_stroke" {{ isset($settings->show_stroke) && $settings->show_stroke == "on" ? "checked" : "" }}>
                                <label class="form-check-label" for="show_stroke">Show Stroke</label>
                            </div>
                        </div>
                    @elseif ($field["type"] == "Overlay Area")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        @if (!isset($isOverlayArea))
                            <div class="col-md-2 col-4 form-group">
                                <input type="hidden" id="overlay_area_color" name="overlay_area_color" value="{{ !empty($options['Option1']) ? $options['Option1'] : '#00000000' }}" />
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="overlay_area" name="overlay_area" {{ isset($settings->overlay_area) && $settings->overlay_area == "on" ? "checked" : "" }}>
                                    <label class="form-check-label" for="overlay_area">Overlay Area</label>
                                </div>
                            </div>
                        @endif
                        <?php
                            $isOverlayArea = true;
                        ?>
                    @elseif ($field["type"] == "Save Image Position")
                        <input type="hidden" class="save-image-position">
                    @elseif ($field["type"] == "Group Color")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-4 form-group">
                            <label>{{ $field['name'] }}</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{ $field['element_id'] }}" id="{{ $field['element_id'] }}_color_hex" class="form-control color-hex group-color" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_color'}) ? $settings->{$field['element_id'].'_color'} : (isset($options['Font Color']) ? $options['Font Color'] : '#000000') }}" data-group="{{ $options['Option1'] }}">
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{ $field['element_id'] }}_color" class="form-control group-color" value="{{ isset($settings->{$field['element_id'].'_color'}) ? $settings->{$field['element_id'].'_color'} : (isset($options['Font Color']) ? $options['Font Color'] : '#000000') }}">
                                </div>
                            </div>
                        </div>
                    @elseif ($field["type"] == "Group Font")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-4 form-group">
                            <label>Font</label>
                            <select name="{{ $field['element_id'] }}" id="{{ $field['element_id'] }}_font" class="form-control group-font" data-group="{{ $options['Option1'] }}">
                                @foreach($fonts as $key => $value)
                                    @if (isset($settings->{$field['element_id'].'_font'}) && $key == $settings->{$field['element_id'].'_font'})
                                        <option value="{{ $key }}" selected>{{ $value }}</option>
                                    @else
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @elseif ($field["type"] == "Image List Group")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        <div class="col-md-{{$field['grid_col']}} form-group">
                            <label>{{ $field["name"] }}</label>
                            <select class="form-control image-list image-list-group" id="{{ $field['element_id'] }}" name="{{ $field['element_id'] }}" data-group="{{ $options['Option1'] }}">
                                @foreach ($image_list as $image)
                                    @if ($image->list_id == $options["Option2"])
                                        <option value="{{ $image->url }}" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == $image->url) ? "selected" : "" }} >{{ $image->name }}</option>
                                    @endif
                                @endforeach
                                <option value="none" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == "none") ? "selected" : "" }} >None</option>
                            </select>
                        </div>
                    @elseif ($field["type"] == "Text Oversampling")
                        <?php
                            $options = json_decode($field["options"], true);
                        ?>
                        @if ($options["Option2"] != 0)
                        <div class="col-md-2 col-4 form-group">
                            <div class="form-check">
                                <input type="hidden" name="text_oversampling_value" value='{{ isset($settings->text_oversampling_value) ? $settings->text_oversampling_value : $options["Option1"] }}' />
                                <input type="checkbox" class="form-check-input" id="text_oversampling" name="text_oversampling" {{ ((isset($settings->text_oversampling) && $settings->text_oversampling == "on") || $options["Option2"] == 2) ? "checked" : "" }} />
                                <label class="form-check-label" for="text_oversampling">Text Oversampling</label>
                            </div>
                        </div>
                        @endif
                    @elseif ($field["type"] == "Half Size")
                    <?php
                        $options = json_decode($field["options"], true);
                    ?>
                    <div class="col-md-2 col-4 form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="half_size" name="half_size" {{ (isset($settings->half_size) && $settings->half_size == "on") ? "checked" : "" }} />
                            <label class="form-check-label" for="half_size">Half Size</label>
                        </div>
                    </div>
                    @elseif ($field["type"] == "Additional Fields")
                        <?php
                            $existAdditionalFields = true;
                        ?>
                    @endif
                @endforeach
                @foreach ($settings as $key => $value)
                    @if (str_contains($key, "add_text_") && strlen($key) == 22)
                        <div class="col-md-3 form-group {{$key}}">
                            <label class="text-label">Text</label>
                            <input type="text" ward name="{{$key}}" class="form-control additionalText" value="{{$value}}" />
                        </div>
                        <div class="col-md-4 form-row {{$key}}">
                            <div class="col-md-6 col-sm-6 form-group">
                                <label>Font</label>
                                <select name="{{$key}}_font" id="{{$key}}_font" class="form-control font-select" style="width: 100%; height: 38px;">
                                    @foreach($fonts as $k => $v)
                                        @if ($key == $settings->{$key."_font"})
                                            <option value="{{ $k }}" selected>{{ $v }}</option>
                                        @else
                                            <option value="{{ $k }}">{{ $v }}</option>
                                            @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-6 form-group">
                                <label>Font Size</label>
                                <input type="number" name="{{$key}}_fontsize" id="{{$key}}_fontsize" class="form-control" value="{{ $settings->{$key.'_fontsize'} }}" />
                            </div>
                        </div>
                        <div class="col-md-2 {{$key}}">
                            <label>Text Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{$key}}_color" id="{{$key}}_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ $settings->{$key.'_color'} }}" />
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{$key}}_color" class="form-control" value="{{ $settings->{$key.'_color'} }}" />
                                </div>
                            </div>
                        </div>
                        <div class="offset d-none {{$key}}">
                            <input type="number" name="{{$key}}_offset_x" id="{{$key}}_offset_x" class="form-control" value="{{ $settings->{$key.'_offset_x'} }}" />
                            <input type="number" name="{{$key}}_offset_y" id="{{$key}}_offset_y" class="form-control" value="{{ $settings->{$key.'_offset_y'} }}" />
                            <input type="number" name="{{$key}}_width" id="{{$key}}_width" class="form-control" value="{{ $settings->{$key.'_width'} }}" />
                            <input type="number" name="{{$key}}_angle" id="{{$key}}_angle" class="form-control" value="{{ $settings->{$key.'_angle'} }}" />
                        </div>
                    @elseif ((str_contains($key, "add_rectangle_") && strlen($key) == 27) || (str_contains($key, "add_circle_") && strlen($key) == 24))
                        <?php $type = str_contains($key, "add_rectangle_") ? "Rectangle" : "Circle" ?>
                        <div class="col-md-2 {{$key}}">
                            <label>{{$type}} Color</label>
                            <div class="form-row">
                                <input type="hidden" name="{{$key}}" class="additional{{$type}}" />
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{$key}}_fill_color" id="{{$key}}_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ $settings->{$key.'_fill_color'} }}" />
                                    <input type="checkbox" class="toggle-shape" name="{{$key}}_toggle_shape" id="{{$key}}_toggle_shape" checked />
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{$key}}_fill_color" class="form-control" value="{{ $settings->{$key.'_fill_color'} }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 {{$key}}">
                            <label>{{$type}} Stroke Color</label>
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="text" name="{{$key}}_stroke_color" id="{{$key}}_strke_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ $settings->{$key.'_stroke_color'} }}" />
                                </div>
                                <div class="col-md-6 col-sm-6 form-group">
                                    <input type="color" id="{{$key}}_stroke_color" class="form-control" value="{{ $settings->{$key.'_stroke_color'} }}" />
                                </div>
                            </div>
                        </div>
                        <div class="offset d-none {{$key}}">
                            <input type="number" name="{{$key}}_offset_x" id="{{$key}}_offset_x" class="form-control" value="{{ $settings->{$key.'_offset_x'} }}" />
                            <input type="number" name="{{$key}}_offset_y" id="{{$key}}_offset_y" class="form-control" value="{{ $settings->{$key.'_offset_y'} }}" />
                            <input type="number" name="{{$key}}_angle" id="{{$key}}_angle" class="form-control" value="{{ $settings->{$key.'_angle'} }}" />
                            <input type="number" name="{{$key}}_width" id="{{$key}}_width" class="form-control" value="{{ $settings->{$key.'_width'} }}" />
                            <input type="number" name="{{$key}}_height" id="{{$key}}_height" class="form-control" value="{{ $settings->{$key.'_height'} }}" />
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="form-row d-none-parent">
                <div class="col-md-2 col-4 form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ (!empty($settings->include_psd) && $settings->include_psd == "on") ? "checked" : "" }} />
                        <label class="form-check-label" for="include_psd">Include PSD</label>
                    </div>
                </div>
                @php
                $show_text = false;
                $download_all = false;
                foreach ($template_fields as $field) {
                    if ($field['type'] == 'Show Text') {
                        $show_text = true;
                    }
                    if ($field['type'] == 'Download All') {
                        $download_all = true;
                    }
                }
                @endphp
                @if ($show_text)
                <div class="col-md-2 col-4 form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="show_text" name="show_text" {{ (!isset($settings->show_text) || $settings->show_text == "on") ? "checked" : "" }} />
                        <label class="form-check-label" for="show_text">Show Text</label>
                    </div>
                </div>
                @endif
                @if ($download_all)
                <div class="col-md-2 col-4 form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="download_all" name="download_all" {{ (isset($settings->download_all) && $settings->download_all == "on") ? "checked" : "" }} />
                        <label class="form-check-label" for="download_all">Download All</label>
                    </div>
                </div>
                @endif
            </div>
            <div class="form-row mb-2">
                <button id="preview-ads" class="btn btn-primary mr-2 d-none-parent">Preview</button>
                <button id="download-ads" class="btn btn-primary mr-2 d-none-parent">Download</button>
                <button id="generate-ads" class="btn btn-primary mr-2 d-none-parent">Save Draft</button>
                @if (!empty($dc->xlsx_template_url))
                <button type="button" id="download-xlsx" class="btn btn-primary mr-2 d-none-parent" data-toggle="tooltip" title="Download spreadsheet">
                    <i class="c-icon cil-data-transfer-down"></i> Spreadsheet
                </button>
                @endif
                <input type="hidden" id="saved-draft" />
                @if ($logged_in_user->isTeamMember())
                    <button id="publish-team-ads" class="btn btn-primary mr-2" title="Publishes to: {{ $team_list }}">Publish Project</button>
                @else
                    <button id="publish-team-ads" class="btn btn-primary mr-2">Save Project</button>
                @endif
                <input type="hidden" id="published-project" />
                <button id="share-ads" type="button" class="btn btn-primary mr-2 d-none-parent">Share...</button>
                <button id="reset_to_defaults" type="button" class="btn btn-primary mr-2 d-none-parent">Reset</button>
                <div class="generate-alert">Generating...</div>
                @if ($showlogs == 1)
                    <button type="button" id="show-logs" class="btn btn-secondary" data-toggle="modal" data-target="#logModal">Show logs</button>
                @endif
            </div>
        @endif
    </form>

    <div id="preview-popup">
        <div id="drag-handler">
            <span>Preview</span>
            <span class="toggle-button preview-control"><i class="cil-window-minimize"></i></span>
            <span class="edit-button edit preview-control"><i class="cil-pencil"></i></span>
            <span class="canvas-button psd preview-control"><img src="/images/canvas.png" /></span>
            <span class="reset-hero-button preview-control"><i class="cil-reload"></i></span>
            <span class="toggle-grid-button preview-control"><i class="cil-grid"></i></span>
            <span class="rotate-button preview-control"><i class="cil-crop-rotate"></i></span>
            <span class="safe-zone-button preview-control"><i class="cil-rectangle"></i></span>
            <ul class="safe-zone-list" style="display: none">
            </ul>
            @if ($existAdditionalFields)
            <span class="add-button add preview-control">
                <img src="/img/icons/toolbar.png" style="width: 16px;height: 16px;margin-bottom: 9px;" />
            </span>
            <ul class="list-field-type" style="display: none">
                <li value="Text">Text</li>
                <li value="Rectangle">Rectangle</li>
                <li value="Circle">Circle</li>
            </ul>
            @endif
        </div>
        <div id="footer" class="pt-1 d-flex justify-content-around align-items-center">
            <div>
                <input type="checkbox" id="snap_to" title="Snap to" />
            </div>
            <div>
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
    </div>

@endsection

@section('modals')
    <div class="modal fade" id="webUploadModal" tabindex="-1" role="dialog" aria-labelledby="webUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upload from Web</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name (optional) and Image URL</label>
                        <textarea class="form-control" name="upload_images_url" style="height: 200px"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <div>
                                <input type="checkbox" name="background_remove" id="background_remove" />
                                <label for="background_remove">Remove background</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary download-from-web" id="download_from_web" data-dismiss="modal">Upload</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Log Modal -->
    <div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <p class="log-block"></p>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    @include('frontend.includes.modals.share')

    <!-- Download XLSX Modal -->
    @include('frontend.includes.modals.download_xlsx')

	<!-- Select available image Modal -->
	<div class="modal fade" id="selectImgModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Available Images</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
					<div class="available-image-grid">
						<div class="image-grid-responsive">
							<div class="grid"></div>
						</div>
					</div>
					<div class="full-size-image" style="display: none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Select background image Modal -->
	<div class="modal fade" id="selectBkImgModal" tabindex="-1" role="dialog" aria-labelledby="selectBkImgModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Background Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="background-wrapper">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Original</a>
                                <a class="nav-link" id="nav-stock-tab" data-toggle="tab" href="#nav-stock" role="tab" aria-controls="nav-stock" aria-selected="false">Stock</a>
                                <a class="nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Edited</a>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                <div class="background-image-grid">
                                    <div class="image-grid-responsive">
                                        <div class="grid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-stock" role="tabpanel" aria-labelledby="nav-stock-tab">
                                <div class="stock-image-grid">
                                    <div class="image-grid-responsive">
                                        <div class="grid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                <div class="background-cropped-image-grid">
                                    <div class="image-grid-responsive">
                                        <div class="grid"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="full-size-image" style="display: none"></div>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <input type="file" class="form-control-file" name="new-background-image" data-show-preview="false" multiple>
                    <div>
                        <button type="button" id="upload" class="btn btn-primary" style="display: none;">Upload</button>
                        <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Select</button>
                        <button type="button" id="delete" class="btn btn-primary" style="display: none;">Delete</button>
                        <button type="button" id="delete_cropped" class="btn btn-primary" style="display: none;">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("after-scripts")
    <script>
        var productTexts = JSON.parse({!! isset($settings->product_texts) ? json_encode($settings->product_texts) : "'{}'" !!});
        var positioningOptions = {!! isset($settings->positioning_options) ? json_encode($settings->positioning_options) : 'undefined' !!};
    </script>
	<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/new_template.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/new_template.js') }}"></script>


@endpush
