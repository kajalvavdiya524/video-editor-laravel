
@include('frontend.create.includes.project_name')
<input type="hidden" name="customer" value="{{ $customer->value }}" />
<input type="hidden" name="customer_id" value="{{ $customer->id }}" />
<input type="hidden" name="layout_id" value="{{ $layout_id }}" />
<input type="hidden" name="template_id" value="{{ $template->id }}" />
<input type="hidden" name="instance_id" value="{{ $instance_id }}" />
<input type="hidden" name="product_texts" value="" />
<input type="hidden" name="logos" value="" />
<div class="form-row mb-2">
    @php
        $image_index = 0;
        $hasListType = false;
        $background_color_inx = 0;
        $background_image_inx = 0;
        $img_from_bk_inx = 0;
    @endphp
    @foreach ($template->fields as $field)
        @php
        $options = json_decode($field["options"], true);
        @endphp
        @if ($field["type"] == "UPC/GTIN")
            <div class="col-md-12 d-none-parent">
                <label>
                    UPC / GTIN / ASIN / TCIN / WMT-ID
                    <span class="{{ empty($settings->file_ids) ? 'isDisabled' : '' }}"><a href="#" id="view-img" class="{{ empty($settings->file_ids) ? 'disabled' : '' }}">Edit Images</a></span>
                </label>
                <div class="form-row">
                    <div class="col-12 form-group">
                        <input type="text" name="file_ids" class="form-control" autofocus value="{{ empty($settings->file_ids) ? "" : $settings->file_ids }}">
                    </div>
                </div>
            </div>
        @elseif ($field["type"] == "Product Space")
            <div class="col-md-{{$field['grid_col']}} form-group">
                <label>{{ $field["name"] }}</label>
                <input type="number" name="{{ $field['element_id'] }}" class="form-control" value="{{ isset($settings->{$field['element_id']}) ? $settings->{$field['element_id']} : (!empty($options['Option1']) ? $options['Option1'] : '0') }}">
            </div>
        @elseif ($field["type"] == "Text" || $field["type"] == "Text Options")
            <div class="col-md-{{$field['grid_col']}} form-group">
                @php
                    if ($settings)
                        $default_value = (property_exists($settings, $field['element_id'])) ? $settings->{$field['element_id']} : $options['Placeholder'];
                    else
                        $default_value =  $options['Placeholder'];
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
                    <select name="{{ $field['element_id'] . '_alignment' }}" id="{{ $field['element_id'] . '_alignment' }}" class="form-control">
                        <option value="left" {{ (isset($settings->{$field['element_id'] . '_alignment'}) && $settings->{$field['element_id'] . '_alignment'} == "left") ? "selected" : "" }} >Left</option>
                        <option value="center" {{ (isset($settings->{$field['element_id'] . '_alignment'}) && $settings->{$field['element_id'] . '_alignment'} == "center") ? "selected" : "" }} >Center</option>
                        <option value="right" {{ (isset($settings->{$field['element_id'] . '_alignment'}) && $settings->{$field['element_id'] . '_alignment'} == "right") ? "selected" : "" }} >Right</option>
                    </select>
                </div>
                @endif
                @if (!empty($options['Cell']))
                <div class="cell-fields d-none">
                    <label>Cell</label>
                    <input type="text" name="cell_{{ $field['element_id'] }}" class="form-control" value="{{ isset($settings->{'cell_' . $field['element_id']}) ? $settings->{'cell_' . $field['element_id']} : $options['Cell'] }}">
                </div>
                @endif
            </div>
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
            </div>
            @if (isset($options["Font Selector"]) && $options["Font Selector"] == "Yes")
                @php
                    include(base_path().'/resources/lib/fonts.php');
                @endphp
                <div class="col-md-4 form-row">
                    <div class="col-md-6 col-sm-6 form-group">
                        <label>Font</label>
                        <select name="{{ $field['element_id'] }}_font" id="{{ $field['element_id'] }}_font" class="form-control">
                            @foreach($fonts as $key => $value)
                            @if ($key == $options["Font"])
                            <option value="{{ $key }}" selected>{{ $value }}</option>
                            @else
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-6 form-group">
                        <label>Font Size</label>
                        <input type="number" name="{{ $field['element_id'] }}_fontsize" id="{{ $field['element_id'] }}_fontsize" class="form-control" value="{{ isset($settings->{$field['element_id'].'_fontsize'}) ? $settings->{$field['element_id'].'_fontsize'} : (empty($options['Font Size']) ? '20' : $options['Font Size']) }}">
                    </div>
                </div>
            @endif
            @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes")
                <div class="col-md-2 form-row">
                    <label>Color</label>
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
        @elseif ($field["type"] == "Static Text")
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
            </div>
        @elseif ($field["type"] == "Rectangle" || $field["type"] == "Circle")
            @if (isset($options["Color Selector"]) && $options["Color Selector"] == "Yes")
            <div class="col-md-2">
                <label>{{ $field['name'] }} Color</label>
                <div class="form-row">
                    <div class="col-md-6 col-sm-6 form-group">
                        <input type="text" name="{{ $field['element_id'] }}_fill_color" id="{{ $field['element_id'] }}_fill_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ isset($settings->{$field['element_id'].'_fill_color'}) ? $settings->{$field['element_id'].'_fill_color'} : (isset($options['Option3']) ? $options['Option3'] : '#ffffff') }}">
                        <input type="checkbox" class="toggle-shape" name="{{ $field['element_id'] }}_toggle_shape" id="{{ $field['element_id'] }}_toggle_shape" {{ isset($settings->{$field['element_id'].'_toggle_shape'}) && $settings->{$field['element_id'].'_toggle_shape'} == 'on' ? 'checked' : '' }} />
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
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scaleX" id="{{ $field['element_id'] }}_scaleX" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scaleX'}) ? $settings->{$field['element_id'].'_scaleX'} : '1' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scaleY" id="{{ $field['element_id'] }}_scaleY" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scaleY'}) ? $settings->{$field['element_id'].'_scaleY'} : '1' }}">
            </div>
        @elseif ($field["type"] == "Circle Type")
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
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
            </div>
        @elseif ($field["type"] == "Line")
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
            </div>
        @elseif ($field["type"] == "List All" && !$hasListType)
            @php
            $hasListType = true;
            @endphp
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
            <div class="col-md-{{$field['grid_col']}} form-group">
                <label>{{ $field["name"] }}</label>
                <input type="file" class="form-control-file" name="{{ $field['element_id'] }}" data-show-preview="false">
                <input type="hidden" name="{{ $field['element_id'] }}_saved" id="{{ $field['element_id'] }}_saved" value="{{ isset($settings->{$field['element_id'].'_saved'}) ? $settings->{$field['element_id'].'_saved'} : '' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_saved_name" id="{{ $field['element_id'] }}_saved_name" value="{{ isset($settings->{$field['element_id'].'_saved_name'}) ? $settings->{$field['element_id'].'_saved_name'} : '' }}">
            </div>
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_width" id="{{ $field['element_id'] }}_width" class="form-control" value="{{ isset($settings->{$field['element_id'].'_width'}) ? $settings->{$field['element_id'].'_width'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_height" id="{{ $field['element_id'] }}_height" class="form-control" value="{{ isset($settings->{$field['element_id'].'_height'}) ? $settings->{$field['element_id'].'_height'} : '0' }}">
            </div>
        @elseif ($field["type"] == "Background Image Upload")
            <div class="col-md-{{$field['grid_col']}} form-group">
                <label>{{ $field["name"] }}</label>
                <input type="file" class="form-control-file" name="{{ $field['element_id'] }}" data-show-preview="false">
                <input type="hidden" name="{{ $field['element_id'] }}_saved" id="{{ $field['element_id'] }}_saved" value="{{ isset($settings->{$field['element_id'].'_saved'}) ? $settings->{$field['element_id'].'_saved'} : '' }}">
            </div>
        @elseif ($field["type"] == "Static Image")
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
            </div>
        @elseif ($field["type"] == "Background Mockup")
        <div class="offset d-none">
            <input type="number" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
            <input type="number" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
            <input type="number" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
            <input type="number" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
        </div>
        @elseif ($field["type"] == "Image List")
            <div class="col-md-{{$field['grid_col']}} form-group">
                <label>{{ $field["name"] }}</label>
                <select class="form-control" id="{{ $field['element_id'] }}" name="{{ $field['element_id'] }}">
                    @foreach ($image_list as $image)
                        @if ($image->list_id == $options["Option1"])
                        <option value="{{ $image->url }}" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == $image->url) ? "selected" : "" }} >{{ $image->name }}</option>
                        @endif
                    @endforeach
                    <option value="none" {{ (isset($settings->{$field['element_id']}) && $settings->{$field['element_id']} == "none") ? "selected" : "" }} >None</option>
                </select>
                {{--<label>Cell</label>
                <input type="text" name="cell_{{ $field['element_id'] }}" class="form-control" value="{{ isset($settings->{'cell_' . $field['element_id']}) ? $settings->{'cell_' . $field['element_id']} : $options['Cell'] }}">--}}
            </div>
            <div class="offset d-none">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_x" id="{{ $field['element_id'] }}_offset_x" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_x'}) ? $settings->{$field['element_id'].'_offset_x'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_offset_y" id="{{ $field['element_id'] }}_offset_y" class="form-control" value="{{ isset($settings->{$field['element_id'].'_offset_y'}) ? $settings->{$field['element_id'].'_offset_y'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_angle" id="{{ $field['element_id'] }}_angle" class="form-control" value="{{ isset($settings->{$field['element_id'].'_angle'}) ? $settings->{$field['element_id'].'_angle'} : '0' }}">
                <input type="hidden" name="{{ $field['element_id'] }}_scale" id="{{ $field['element_id'] }}_scale" class="form-control" value="{{ isset($settings->{$field['element_id'].'_scale'}) ? $settings->{$field['element_id'].'_scale'} : '1' }}">
            </div>
        @elseif ($field["type"] == "Product Image")
            <div class="col-md-12 form-row d-none">
                <div class="form-group col-md-3">
                    <label>Offset X</label>
                    @if ($options["Option1"] == "Hero")
                    <input type="hidden" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[$image_index]) ? 0 : $settings->x_offset[$image_index] }}" default-value="0">
                    @else
                    <input type="hidden" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[$image_index]) ? (!empty($options['X']) ? $options['X'] : 0) : $settings->x_offset[$image_index] }}" default-value="{{ !empty($options['X']) ? $options['X'] : 0 }}">
                    @endif
                </div>
                <div class="form-group col-md-3">
                    <label>Offset Y</label>
                    @if ($options["Option1"] == "Hero")
                    <input type="hidden" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[$image_index]) ? 0 : $settings->y_offset[$image_index] }}" default-value="0">
                    @else
                    <input type="hidden" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[$image_index]) ? (!empty($options['Y']) ? $options['Y'] : 0) : $settings->y_offset[$image_index] }}" default-value="{{ !empty($options['Y']) ? $options['Y'] : 0 }}">
                    @endif
                </div>
                <div class="form-group col-md-3">
                    <label>Angle</label>
                    <input type="hidden" name="angle[]" class="form-control" value="{{ empty($settings->angle[$image_index]) ? (!empty($options['Angle']) ? $options['Angle'] : 0) : $settings->angle[$image_index] }}" default-value="{{ !empty($options['Angle']) ? $options['Angle'] : 0 }}">
                </div>
                <div class="form-group col-md-3">
                    <label>Scale</label>
                    <input type="hidden" name="scale[]" class="form-control" value="{{ empty($settings->scale[$image_index]) ? (!empty($options['Scale']) ? $options['Scale'] : 1) : $settings->scale[$image_index] }}" default-value="{{ !empty($options['Scale']) ? $options['Scale'] : 1 }}">
                </div>
                <input type="hidden" name="moveable[]" value="{{ empty($settings->moveable[$image_index]) ? (!empty($options['Moveable']) ? $options['Moveable'] : '') : $settings->moveable[$image_index] }}">
                <input type="hidden" name="p_width[]" value="{{ empty($settings->p_width[$image_index]) ? 0 : $settings->p_width[$image_index] }}">
                <input type="hidden" name="p_height[]" value="{{ empty($settings->p_height[$image_index]) ? 0 : $settings->p_height[$image_index] }}">
            </div>
            @php
                $image_index++;
            @endphp
        @elseif ($field["type"] == "Background Theme Image")
            <?php $background = null; ?>
            <div class="col-md-2 form-group position-relative">
                <label>{{ $field["name"] }}</label>
                <button class="btn btn-primary select-bkimg" style="display:block" type="button" data-type="{{ $field['type'] }}">Image…</button>
                <div class="selected-image">
                    @if (isset($settings->background[$background_image_inx]))
                    <?php
                        $bkg_img = $settings->background[$background_image_inx];
                        $arr = explode('/', $bkg_img);
                        $background = implode('/', $arr);
                    ?>
                    <img class="background-preview" src="{{ $background }}" />
                    @endif
                    <input type="hidden" class="{{ stripos($field['name'], 'logo') !== false ? 'logo-image' : '' }}" name="background[]" value="{{ isset($background) ? $background : '' }}" />
                </div>
                {{--<label>Cell</label>
                <input type="text" name="cell_background" class="form-control" value="{{ isset($settings->cell_background) ? $settings->cell_background : $options['Cell'] }}">--}}
            </div>
            <div class="offset d-none">
                <input type="hidden" name="bk_img_offset_x[]" class="form-control" value="{{ isset($settings->bk_img_offset_x[$background_image_inx]) ? $settings->bk_img_offset_x[$background_image_inx] : '0' }}">
                <input type="hidden" name="bk_img_offset_y[]" class="form-control" value="{{ isset($settings->bk_img_offset_y[$background_image_inx]) ? $settings->bk_img_offset_y[$background_image_inx] : '0' }}">
                <input type="hidden" name="bk_img_scale[]" class="form-control" value="{{ isset($settings->bk_img_scale[$background_image_inx]) ? $settings->bk_img_scale[$background_image_inx] : '1' }}">
            </div>
            <?php $background_image_inx++;  ?>
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
                <select name="background_color[]" class="form-control">
                    @if (count($themes) > 0)
                    @foreach ($background_theme_colors->list as $color)
                    <?php
                    $c = implode(",", array_column($color->list, 'value'));
                    ?>
                    <option value="{{ $c }}" {{ (!empty($settings->background_color[$background_color_inx]) && $settings->background_color[$background_color_inx] == $c) ? "selected" : "" }}>{{$color->name}}</option>
                    @endforeach
                    @endif
                </select>
            </div>
            @php
            $background_color_inx++;
            @endphp
        @elseif ($field["type"] == "Background Color Picker")
            <div class="col-md-{{$field['grid_col']}} form-group">
                <label>Background Color</label>
                <input type="color" id="background_color" name="background_color" class="form-control" value="{{ empty($settings->background_color) ? "#4864C0" : $settings->background_color }}">
            </div>
        @elseif ($field["type"] == "Image From Background")
            <div class="col-md-2 form-group position-relative">
                <label>{{ $field["name"] }}</label>
                <button class="btn btn-primary select-bkimg" style="display:block" type="button" data-type="{{ $field['type'] }}">Image…</button>
                <div class="selected-image">
                    @if (isset($settings->img_from_bk[$img_from_bk_inx]))
                    <?php $background = $settings->img_from_bk[$img_from_bk_inx]; ?>
                    <img class="background-preview" src="{{ $background }}" />
                    @endif
                    <input type="hidden" name="img_from_bk[]" value="{{ isset($background) ? $background : '' }}" />
                </div>
            </div>
            <div class="offset d-none">
                <input type="hidden" name="img_from_bk_offset_x[]" class="form-control" value="{{ isset($settings->img_from_bk_offset_x[$img_from_bk_inx]) ? $settings->img_from_bk_offset_x[$img_from_bk_inx] : '0' }}">
                <input type="hidden" name="img_from_bk_offset_y[]" class="form-control" value="{{ isset($settings->img_from_bk_offset_y[$img_from_bk_inx]) ? $settings->img_from_bk_offset_y[$img_from_bk_inx] : '0' }}">
                <input type="hidden" name="img_from_bk_scale[]" class="form-control" value="{{ isset($settings->img_from_bk_scale[$img_from_bk_inx]) ? $settings->img_from_bk_scale[$img_from_bk_inx] : '1' }}">
            </div>
            <?php $img_from_bk_inx++; ?>
        @elseif ($field["type"] == "Max File Size")
            <div class="max-file-size form-group d-none">
                <label>Max File Size</label>
                <input type="number" name="max_file_size" class="form-control" value="{{ !empty($options['Option1']) ? $options['Option1'] : 50 }}">
            </div>
        @elseif ($field["type"] == "DPI")
            <div class="dpi form-group d-none">
                <label>DPI</label>
                <input type="number" name="dpi" class="form-control" value="{{ empty($settings->dpi) ? (!empty($options['Option1']) ? $options['Option1'] : 300) : $settings->dpi }}">
            </div>
        @elseif ($field["type"] == "Product Dimensions")
            <div class="product-image-alignment form-group d-none">
                <label>Product Dimensions</label>
                <input type="text" name="product_image_alignment" class="form-control" value="{{ !isset($settings->product_image_alignment) ? (!empty($options['Alignment']) ? $options['Alignment'] : 'left') : $settings->product_image_alignment }}">
            </div>
        @elseif ($field["type"] == "Filename Cell")
            <div class="cell-fields form-group d-none">
                <label>Filename Cell</label>
                <input type="text" name="filename_cell" class="form-control" value="{{ !isset($settings->filename_cell) ? (!empty($options['Cell']) ? $options['Cell'] : '') : $settings->filename_cell }}">
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
            @endif
    @endforeach
</div>
<div class="form-row mb-2">
        <div class="col sm-2 ">
        
        <div class="form-check">
        <input type="checkbox" class="form-check-input" id="show_cells" name="show_cells">
            <label class="form-check-label" for="show_cells">Show Cells</label>
        </div>

        <button id="save-group-template" class="btn btn-primary mr-2">Save</button>

        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#bulkUpdateModal">Bulk Update...</button>
        <a href="/banner/{{ $customer->id }}/group/{{ $layout_id }}/edit" class="btn btn-primary mr-2">Cancel</a>
                

        
        </div>


      
        <div class="col sm-2 text-right">
        <div class="form-check pr-4">
        <input type="checkbox" @if (isset($settings->carry_over) && $settings->carry_over == "on") checked="checked" @endif class="form-check-input" id="carry_over" name="carry_over">
        <label class="form-check-label" for="carry_over">Carry over values</label>
        </div>
        <input type="hidden" name="next_template" id="next_template" value="">
        <div class="col sm-2 text-right">
        @if ($previous_template)
        <button class="btn btn-primary mr-2 navigate_templates" data-next-template="{{$previous_template}}">< Previous Template</button>
        @endif
        @if ($next_template)
        <button class="btn btn-primary mr-2 navigate_templates" data-next-template="{{$next_template}}">Next Template ></button>
        @endif
        </div>


</div>