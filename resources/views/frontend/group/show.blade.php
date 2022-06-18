@extends('frontend.layouts.app')

@section('title', __('Preview | Group'))

@section('content')
<?php
    $layout_options = json_decode($layout->options);
    $group_names = [];
    foreach($layout->templates as $layout_template) {
        if (isset($layout_template->template)) {
            foreach ($layout_template->template->fields as $field) {
                if ($field['type'] == 'Group') {
                    $field_options = json_decode($field["options"], true);
                    if (!in_array($field['name'], $group_names)) {
                        $group_names[] = $field['name'];
                    }
                }
            }
        }
    }
?>
<div class="form-row mb-2 align-items-end">
    <div class="country-select col form-group mb-0">
        <label>Region</label>
        <select name="country_id" class="form-control">
        </select>
    </div>
    <div class="language-select col form-group mb-0">
        <label>Language</label>
        <select name="language_id" class="form-control">
        </select>
    </div>
    <div class="col d-flex align-items-center">
        <div class="mr-2">
            <x-utils.form-button :action="route('frontend.banner.group.change_aligns', ['customer_id' => $customer->id, 'layout' => $layout])" button-class="btn btn-primary">
                @if ($layout->alignment == 0)
                <i class="cil-align-center"></i> Center
                @else
                <i class="cil-align-left"></i> Left
                @endif
            </x-utils.form-button>
        </div>
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Download
            </button>
            <div class="dropdown-menu">
                {{-- <a class="dropdown-item btn-download-assets" href="#">Each Asset</a> --}}
                <a class="dropdown-item btn-download-each-image" href="#">Each Image</a>
                <a class="dropdown-item btn-download-assets-one-file" href="#">Assets Zip</a>
                <a class="dropdown-item btn-download-logos" href="#">Logos</a>
                <a class="dropdown-item btn-download-spreadsheet{{ empty($customer->xlsx_template_url) ? ' disabled' : '' }}" href="#">Spreadsheet</a>
                <a class="dropdown-item btn-download-proof" href="#">Proof</a>
                <a class="dropdown-item btn-download-overlay-proof{{ isset($layout_options->show_mockup) && $layout_options->show_mockup ? '' : ' disabled' }}" href="#">Mockup Proof</a>
                <a class="dropdown-item btn-download-web{{ !isset($layout_options->web_page_file_name) ? ' disabled' : '' }}" href="#">Web page</a>
            </div>
        
            <div id="downloading_spinner" class="align-items-center d-none">
            <div class="spinner-border ml-auto text-primary" role="status" aria-hidden="true"></div>
            <span>&nbsp;Downloading...</span>
            </div>


        </div>
        <div class="mr-2">
            <button type="button" class="btn btn-primary btn-save-changes" disabled>Save</button>
        </div>
        <div class="btn-settings" data-toggle="tooltip" title="Settings" data-placement="right">
            <i class='c-icon cil-settings' data-toggle="modal" data-target="#layoutSettingsModal"></i>
        </div>
        <x-forms.post :action="route('frontend.banner.download_sheet_output', $customer->id)" id="download-xlsx-form">
            <input type="hidden" name="template_settings" />
        </x-forms.post>
    </div>
</div>
<div class="grid-stack" id="template-group-preview" data-customer-id="{{ $customer->id }}" data-layout-name="{{ $layout->name }}" data-layout-id="{{ $layout->id }}">
</div>
<div id="text-editor-control">
    @php
    include(base_path().'/resources/lib/fonts.php');
    @endphp
    <div class="d-flex p-2">
        <div class="form-group mb-0 mr-2 font-selector">
            <select class="form-control" id="text-editor-font-family">
                @foreach($fonts as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-0 mr-2 font-selector">
            <input class="form-control" placeholder="12px" id="text-editor-font-size" />
        </div>
        <div class="form-group mb-0 color-selector">
            <input class="form-control" type="color" id="text-editor-font-color" style="width: 80px;" />
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="layoutSettingsModal" tabindex="-1" role="dialog" aria-labelledby="layoutSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <x-forms.patch :action="route('frontend.banner.group.update_options', ['customer_id' => $customer->id, 'layout' => $layout])" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="col-2">
                            <label>Title</label>
                        </div>
                        <div class="col form-group">
                            <input class="form-control" name="title" value="{{ $layout_options == null ? $layout->name : $layout_options->title }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-2">
                            <label>Brand</label>
                        </div>
                        <div class="col form-group">
                            <input class="form-control" name="brand" value="{{ $layout_options != null && isset($layout_options->brand) ? $layout_options->brand : '' }}">
                        </div>
                        <div class="col">
                            <div class="form-check form-group">
                                <input type="checkbox" class="form-check-input" id="prepend_to_filename" name="prepend_to_filename" {{ $layout_options != null && isset($layout_options->prepend_to_filename) && $layout_options->prepend_to_filename ? "checked" : "" }}>
                                <label class="form-check-label" for="prepend_to_filename">Prepend to filename</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-2">
                            <label>Size</label>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control" name='resolution_size'>
                                    <option value='100' {{ $layout_options != null && isset($layout_options->resolution_size) && $layout_options->resolution_size == '100' ? 'selected' : '' }}>100%</option>
                                    <option value='50' {{ $layout_options != null && isset($layout_options->resolution_size) && $layout_options->resolution_size == '50' ? 'selected' : '' }}>50%</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group input-group">
                                <input type="text" class="form-control" name="resolution_size_suffix" value="{{ $layout_options != null && isset($layout_options->resolution_size_suffix) ? $layout_options->resolution_size_suffix : '' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">Suffix</span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="resolution_size_suffix_text" value="{{ $layout_options != null && isset($layout_options->resolution_size_suffix_text) ? $layout_options->resolution_size_suffix_text : '' }}" />
                    </div>
                    <div class="form-row">
                        <div class="col-2">
                            <label>Group</label>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control" name='group'>
                                    <option value="All" {{ $layout_options != null && isset($layout_options->group) && $layout_options->group == 'All' ? 'selected' : '' }}>All</option>
                                    @foreach ($group_names as $group_name)
                                        <option value="{{ $group_name }}" {{ $layout_options != null && isset($layout_options->group) && $layout_options->group == $group_name ? 'selected' : '' }}>{{ $group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-2">
                            <label>Download</label>
                        </div>
                        <div class="col-md-10 form-group">
                            <select multiple="multiple" id="downloadable_templates" name="downloadable_templates[]" class="form-control">
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-check form-group">
                                <input type="checkbox" class="form-check-input" id="include_template_name" name="include_template_name" {{ $layout_options != null && $layout_options->include_template_name ? "checked" : "" }}>
                                <label class="form-check-label" for="include_template_name">Template Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-group">
                                <input type="checkbox" class="form-check-input" id="include_template_size" name="include_template_size" {{ $layout_options != null && $layout_options->include_template_size ? "checked" : "" }}>
                                <label class="form-check-label" for="include_template_size">Template Size</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-none">
                            <div class="form-group">
                                <select class="form-control" name="template_name_size_position">
                                    <option value="upper_left" {{ $layout_options != null && $layout_options->template_name_size_position == 'upper_left' ? 'selected' : '' }}>Upper Left</option>
                                    <option value="lower_left" {{ $layout_options != null && $layout_options->template_name_size_position == 'lower_left' ? 'selected' : '' }}>Lower Left</option>
                                    <option value="upper_right" {{ $layout_options != null && $layout_options->template_name_size_position == 'upper_right' ? 'selected' : '' }}>Upper Right</option>
                                    <option value="lower_right" {{ $layout_options != null && $layout_options->template_name_size_position == 'lower_right' ? 'selected' : '' }}>Lower Right</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-check" style="margin-top: 5px;">
                                <input type="checkbox" class="form-check-input" id="show_separator" name="show_separator" {{ $layout_options != null && $layout_options->show_separator ? "checked" : "" }}>
                                <label class="form-check-label" for="show_separator">Show separator</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select class="form-control" name='separator_color'>
                                    <option value='#ffffff' {{ $layout_options != null && $layout_options->separator_color == '#ffffff' ? 'selected' : '' }}>White</option>
                                    <option value='#000000' {{ $layout_options != null && $layout_options->separator_color == '#000000' ? 'selected' : '' }}>Black</option>
                                    <option value='#cccccc' {{ $layout_options != null && $layout_options->separator_color == '#cccccc' ? 'selected' : '' }}>Gray</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group input-group">
                                <input type="text" class="form-control" name="separator_height" value="{{ $layout_options != null && isset($layout_options->separator_height) ? $layout_options->separator_height : '20' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-3">
                            <div class="form-check" style="margin-top: 5px;">
                                <input type="checkbox" class="form-check-input" id="show_stroke" name="show_stroke" {{ $layout_options != null && isset($layout_options->show_stroke) && $layout_options->show_stroke ? "checked" : "" }}>
                                <label class="form-check-label" for="show_stroke">Show Stroke</label>
                            </div>
                        </div>
                        <div class="col form-group">
                            <input class="form-control" name="stroke_color" value="{{ $layout_options != null ? $layout_options->stroke_color : '#A9A9A9' }}">
                        </div>
                        <div class="col form-group">
                            <input class="form-control" name="stroke_width" value="{{ $layout_options != null ? $layout_options->stroke_width : 1 }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-4">
                            <div class="form-check" style="margin-top: 5px;">
                                <input type="checkbox" class="form-check-input" id="use_custom_naming" name="use_custom_naming" {{ $layout_options != null && isset($layout_options->use_custom_naming) && $layout_options->use_custom_naming ? "checked" : "" }}>
                                <label class="form-check-label" for="use_custom_naming">Custom Naming</label>
                            </div>
                        </div>
                        <div class="col form-group">
                            <input class="form-control" name="custom_name" value="{{ $layout_options != null && isset($layout_options->custom_name) ? $layout_options->custom_name : '' }}">
                            <span style="font-size: 12px;"><b>Variables: </b>%Brand%, %TemplateName%, %ProjectName%, %LayoutName%, %TemplateWidth%, %TemplateHeight%, %LayoutTitle%, %SpaceToUnderscore%</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-2">
                            <label class="mt-2">Web page</label>
                        </div>
                        <div class="col">
                            <div class="file-input-group">
                                <input type="file" class="form-control-file" name="web_page" data-show-preview="false">
                            </div>
                            <div class="mb-2">
                                <x-utils.link :href="route('frontend.banner.group.download_html', ['customer_id' => $customer->id, 'layout' => $layout])" :text="__('Download')" :class="$layout_options != null && isset($layout_options->web_page_file_name) ? 'btn btn-link' : 'btn disabled' " />
                                <x-utils.link :href="route('frontend.banner.group.edit_html', ['customer_id' => $customer->id, 'layout' => $layout])" :text="__('Edit')" :class="$layout_options != null && isset($layout_options->web_page_file_name) ? 'ml-1 btn btn-link' : 'ml-1 btn disabled' " />
                            </div>
                            <input type="hidden" name="web_page_file_name" value="{{ $layout_options != null && isset($layout_options->web_page_file_name) ? $layout_options->web_page_file_name : '' }}" />
                            <input type="hidden" name="web_page_file_path" value="{{ $layout_options != null && isset($layout_options->web_page_file_path) ? $layout_options->web_page_file_path : '' }}" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mr-2">
                                <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ $layout_options != null && $layout_options->include_psd ? "checked" : "" }}>
                                <label class="form-check-label" for="include_psd">Include PSD</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mr-2">
                                <input type="checkbox" class="form-check-input" id="show_overlay" name="show_overlay" {{ $layout_options == null || !isset($layout_options->show_overlay) || $layout_options->show_overlay ? "checked" : "" }}>
                                <label class="form-check-label" for="show_overlay">Overlay</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mr-2">
                                <input type="checkbox" class="form-check-input" id="show_canvas" name="show_canvas" {{ $layout_options == null || !isset($layout_options->show_canvas) || $layout_options->show_canvas ? "checked" : "" }}>
                                <label class="form-check-label" for="show_canvas">Canvas</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mr-2">
                                <input type="checkbox" class="form-check-input" id="show_mockup" name="show_mockup" {{ $layout_options != null && isset($layout_options->show_mockup) && $layout_options->show_mockup ? "checked" : "" }}>
                                <label class="form-check-label" for="show_mockup">Mockup</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </x-forms.pat>
        </div>
    </div>
</div>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/group/show.js') }}"></script>
@endpush