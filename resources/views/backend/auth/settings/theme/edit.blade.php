@extends('backend.layouts.app')

@section('title', __('Update Theme'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Update Theme')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link class="card-header-action" :href="route('admin.auth.settings.theme.index', $customer_id)" :text="__('Cancel')" />
        </x-slot>

        <x-slot name="body">
            <input type="hidden" id="theme_id" value="{{ $theme->id }}">
            <input type="hidden" id="customer_id" value="{{ $customer_id }}">
            <input type="hidden" id="customer_name" value="{{ $customer_name }}">
            <div class="form-group row">
                <label for="theme_name" class="col-md-2 col-form-label">@lang('Theme Name')</label>
                <div class="col-md-10">
                    <input type="text" name="theme_name" class="form-control" placeholder="{{ __('Theme Name') }}" value="{{ $theme->name }}" required />
                </div>
            </div>
            <?php
                $attributes = json_decode($theme->attributes);
                $font_colors = [];
                foreach ($attributes as $attribute) {
                    if ($attribute->name == "Font Colors") {
                        $font_colors = $attribute->list;
                    }
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
                        @if (count($options) > 0)
                            @foreach ($options[0]->list as $attr)
                                <th>{{ ucfirst($attr->name) }}</th>
                            @endforeach
                        @elseif (isset(Config::get('theme')[$attribute->name]))
                            @foreach (Config::get('theme')[$attribute->name] as $attr)
                                <th>{{ $attr }}</th>
                            @endforeach
                        @endif
                        <th style="width: 5%">Action</th>
                    </thead>
                    <tbody>
                        @foreach ($options as $option)
                        <tr>
                            <td>
                                <input type="text" class="form-control option-name" value="{{ $option->name }}">
                            </td>
                            <?php
                                $fill_type = "solid";
                            ?>
                            @foreach ($option->list as $attr)
                                <?php 
                                    $type = isset($attr->type) ? $attr->type : "color"; 
                                ?>
                                @if ($type == "locked")
                                    @continue;
                                @endif
                                <td data-type="{{ $type }}">
                                    @if ($type == "color")
                                        @if ($fill_type == "gradient" || $fill_type == "animation")
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
                                    <?php
                                        $fill_type = isset($attr->value) ? $attr->value : "solid";
                                    ?>
                                    <div class="form-group">
                                        <select class="form-control fill-type">
                                            <option value="solid" {{ isset($attr->value) && $attr->value == "solid" ? "selected" : "" }}>Solid</option>
                                            <option value="gradient" {{ isset($attr->value) && $attr->value == "gradient" ? "selected" : "" }}>Gradient</option>
                                            <option value="animation" {{ isset($attr->value) && $attr->value == "animation" ? "selected" : "" }}>Animation</option>
                                        </select>
                                    </div>
                                    @elseif ($type == "font_color")
                                    @php
                                        if (isset($attr->value)) {
                                            $cc = explode(",", $attr->value);
                                        } else {
                                            $cc = [];
                                        }
                                    @endphp
                                    <div class="form-group">
                                        <select class="form-control font-color" multiple>
                                            @foreach ($font_colors as $font_color)
                                                @if (in_array($font_color->name, $cc))
                                                    <option value="{{ $font_color->name }}" selected>{{ $font_color->name }}</option>
                                                @else
                                                    <option value="{{ $font_color->name }}">{{ $font_color->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    @elseif ($type == "background")
                                        <div class="form-row">
                                            <div class="form-group col-md-9">
                                                <?php
                                                    $arr = explode(".", $attr->value);
                                                    $arr[count($arr) - 1] = "png";
                                                    $arr = implode(".", $arr);
                                                    $filename = array_reverse(explode('/', $arr))[0];
                                                ?>
                                                <a href="{{ $arr }}" class="background-url-link" target="_blank">{{ $filename }}</a>
                                                <span class="background-url d-none">{{ $arr }}</span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <input type="file" name="bk-image" class="form-control d-none">
                                                <button type="button" class="btn btn-primary btn-sm btn-browse">Browse</button>
                                            </div>
                                        </div>
                                    @elseif ($type == "background_template")
                                        <div class="form-group">
                                        @if ($customer_name == 'Amazon')
                                            <select class="form-control template">
                                                <option value="-2" {{ $attr->value == "-2" ? "selected" : "" }}>None</option>
                                                <option value="-1" {{ $attr->value == "-1" ? "selected" : "" }}>All</option>
                                                @foreach ($templates as $template)
                                                    @if ($template['system'])
                                                    <option value="{{ $template['system_key'] }}" {{ $attr->value == $template['system_key'] ? 'selected' : '' }} >
                                                        Template {{ $template['system_key'] + 1 }} ({{ $template['name'] }})
                                                    </option>
                                                    @else
                                                    <option value="{{ $template['id'] }}" {{ $attr->value == $template['id'] ? 'selected' : '' }} >
                                                        {{ $template['name'] . ' (' . $template['width'] . 'x' . $template['height'] . ')' }}
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <?php
                                                $output_dimensions = config("templates.$customer_name.output_dimensions");
                                                if (!isset($output_dimensions)) {
                                                    $output_dimensions = [];
                                                }
                                            ?>
                                            <select class="form-control template">
                                                <option value="-2" {{ $attr->value == "-2" ? "selected" : "" }}>None</option>
                                                <option value="-1" {{ $attr->value == "-1" ? "selected" : "" }}>All</option>
                                                @for($i = 0; $i < count($output_dimensions); $i++)
                                                    <option value="{{ $i }}" {{ $attr->value == "$i" ? "selected" : "" }}>Template{{ $i + 1 }} ({{ $output_dimensions[$i] }})</option>
                                                @endfor
                                                @for($i = 0; $i < count($templates); $i++)
                                                    <option value="{{ $templates[$i]['id'] }}" {{ $attr->value == $templates[$i]['id'] ? "selected" : "" }}>Template{{ count($output_dimensions) + $i + 1 }} ({{ $templates[$i]['name'] }})</option>
                                                @endfor
                                            </select>
                                        @endif
                                        </div>
                                    @elseif ($type == "number")
                                    <div class="form-group">
                                        <input type="number" class="form-control" value="{{ $attr->value }}" />
                                    </div>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if ($attribute->name == 'Shadow Effects')
                                <button type="button" class="btn btn-sm btn-danger btn-delete-attr">Delete</button>
                                @else
                                <button type="button" class="btn btn-sm btn-danger btn-delete-attr" {{ count($options) > 1 ? '' : 'disabled' }}>Delete</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($attribute->name == "Background Images")
                    <div class="form-group">
                        <input type="file" class="form-control-file" id="mass_upload" name="mass_upload" data-browse-on-zone-click="true" multiple />
                    </div>
                @endif
            </div>
            @endforeach
        </x-slot>

        <x-slot name="footer">
            <button type="button" class="btn btn-sm btn-primary float-right" id="btn_update_theme">@lang('Update Theme')</button>
        </x-slot>
    </x-backend.card>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/template.js") }}"></script>
    <!-- <script>
        var isThemeUpdate = false;
        var hasChanged = false;
        window.onbeforeunload = function() {
            if (!isThemeUpdate && hasChanged) {
                return "Do you really want to leave our brilliant application?";
            }
        };
    </script> -->
@endpush