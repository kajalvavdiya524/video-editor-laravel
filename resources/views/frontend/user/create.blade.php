@extends('frontend.layouts.app')

@section('title', __('Banner'))

@section('content')
    @php
        $team_list = "";
        foreach ($logged_in_user->teams as $team) {
            $team_list .= $team->name;
        }
        $alignments = array("left", "center", "right");
        $drop_shadows = array("none", "left", "right");
    @endphp
    <div class="alert alert-danger errors" role="alert" style="display: none;"></div>
    <div class="alert alert-success success" role="alert" style="display: none;"></div>
    <div class="d-none" id="preview-images"></div>
    <div class="d-none" id="product-images"></div>
    <form id="adForm" enctype="multipart/form-data">
        <div class="position-relative">
            @include('frontend.create.includes.customer')
            @php
                $customer_name = "Generic";
            @endphp
            <div class="inline-template-selector" style="display:inline-block">
                @include('frontend.create.includes.template')
            </div>
        </div>
        @include('frontend.create.includes.project_name')
        <div class="form-row d-none-parent">
            <div class="col-upc-gtin">
                <label>
                    UPC / GTIN / ASIN / TCIN / WMT-ID
				<span class="{{ empty($settings->file_ids) ? 'isDisabled' : '' }}"><a href="#" id="view-img" class="{{ empty($settings->file_ids) ? 'disabled' : '' }}">Edit Images</a></span>
                </label>
                <div class="form-row">
                    <div class="col-12 form-group">
                        <input type="text" name="file_ids" class="form-control" autofocus value="{{ empty($settings->file_ids) ? "" : $settings->file_ids }}">
                    </div>
                    <!--
                        <div class="col-3 form-group">
                            <input type="file" class="form-control-file" name="products[]" data-show-preview="false" multiple>
                        </div>
                    -->
                </div>
            </div>
            <div class="col-product-spacing">
                <div class="form-group">
                    <label>Product Spacing</label>
                    <input type="text" name="product_space" class="form-control" value="{{ empty($settings->product_space) ? "0" : $settings->product_space }}">
                </div>
            </div>
        </div>
        <div class="d-none-parent">
            <label>Headline</label>
            <div class="d-inline">
                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="To apply a superscript, include <sup></sup> tags in any text field. Examples: <sup>$</sup>2.99 each, Save 20<sup>%</sup>"></i>
            </div>
            <div class="form-check-inline ml-2">
                <input type="checkbox" class="form-check-input" id="multi_headline" name="multi_headline" {{ !empty($settings->multi_headline) && $settings->multi_headline == "on" ? "checked" : "" }}>
                <label class="form-check-label" for="multi_headline">Multi-line</label>
            </div>
            <div class="form-row">
                <div class="col-md-6 col-sm-9 form-group">
                    <input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[0] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <select name="headline_alignment[]" class="form-control">
                        @foreach($alignments as $alignment)
                            <option {{ !empty($settings->headline_alignment) && $settings->headline_alignment[0] == $alignment ? "selected" : "" }} value="{{ $alignment }}">{{ $alignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-3 form-group">
                    <select name="headline_font[]" class="form-control">
                        @foreach( Config::get("templates.Generic.fonts") as $font )
                            <option {{ !empty($settings->headline_font) && $settings->headline_font[0] == $font ? "selected": "" }} value="{{ $font }}" >{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <div class="input-group">
                        <input type="text" name="headline_font_size[]" class="form-control headline_font_size" value="{{ empty($settings->headline_font_size) ? "44,40,70,24,52" : $settings->headline_font_size[0] }}">
                        <div class="input-group-append ml-1 align-items-center">
                            <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify font sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                            Or, specify a font size for each template by first selecting a size in Output Dimensions dropdown."></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="text" name="headline_color[]" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[0] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="color" class="form-control" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[0] }}">
                </div>
            </div>
            <div class="form-row {{ !empty($settings->multi_headline) && $settings->multi_headline == "on" ? "" : "d-none" }} multi-headline">
                <div class="col-md-6 col-sm-9 form-group">
                    <input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[1] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <select name="headline_alignment[]" class="form-control">
                        @foreach($alignments as $alignment)
                            <option {{ !empty($settings->headline_alignment) && $settings->headline_alignment[1] == $alignment ? "selected" : "" }} value="{{ $alignment }}">{{ $alignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-3 form-group">
                    <select name="headline_font[]" class="form-control">
                        @foreach( Config::get("templates.Generic.fonts") as $font )
                            <option {{ !empty($settings->headline_font) && $settings->headline_font[1] == $font ? "selected": "" }} value="{{ $font }}" >{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <div class="input-group">
                        <input type="text" name="headline_font_size[]" class="form-control headline_font_size" value="{{ empty($settings->headline_font_size) ? "44,40,70,24,52" : $settings->headline_font_size[1] }}">
                        <div class="input-group-append ml-1 align-items-center">
                            <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify font sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                            Or, specify a font size for each template by first selecting a size in Output Dimensions dropdown."></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="text" name="headline_color[]" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[1] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="color" class="form-control" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[1] }}">
                </div>
            </div>
            <div class="form-row {{ !empty($settings->multi_headline) && $settings->multi_headline == "on" ? "" : "d-none" }} multi-headline">
                <div class="col-md-6 col-sm-9 form-group">
                    <input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[2] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <select name="headline_alignment[]" class="form-control">
                        @foreach($alignments as $alignment)
                            <option {{ !empty($settings->headline_alignment) && $settings->headline_alignment[2] == $alignment ? "selected" : "" }} value="{{ $alignment }}">{{ $alignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-3 form-group">
                    <select name="headline_font[]" class="form-control">
                        @foreach( Config::get("templates.Generic.fonts") as $font )
                            <option {{ !empty($settings->headline_font) && $settings->headline_font[2] == $font ? "selected": "" }} value="{{ $font }}" >{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <div class="input-group">
                        <input type="text" name="headline_font_size[]" class="form-control headline_font_size" value="{{ empty($settings->headline_font_size) ? "44,40,70,24,52" : $settings->headline_font_size[2] }}">
                        <div class="input-group-append ml-1 align-items-center">
                            <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify font sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                            Or, specify a font size for each template by first selecting a size in Output Dimensions dropdown."></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="text" name="headline_color[]" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[2] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="color" class="form-control" value="{{ empty($settings->headline_color) ? "#000000" : $settings->headline_color[2] }}">
                </div>
            </div>
        </div>
        <div class="d-none-parent">
            <label>Sub-headline</label>
            <div class="form-row">
                <div class="col-md-6 col-sm-9 form-group">
                    <input type="text" name="subheadline[]" class="form-control" value="{{ empty($settings->subheadline) ? "" : $settings->subheadline[0] }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <select name="subheadline_alignment" class="form-control">
                        @foreach($alignments as $alignment)
                            <option {{ !empty($settings->subheadline_alignment) && $settings->subheadline_alignment == $alignment ? "selected" : "" }} value="{{ $alignment }}">{{ $alignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-3 form-group">
                    <select name="subheadline_font" class="form-control">
                        @foreach( Config::get("templates.Generic.fonts") as $font )
                            <option {{ !empty($settings->subheadline_font) && $settings->subheadline_font == $font ? "selected": "" }} value="{{ $font }}" >{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <div class="input-group">
                        <input type="text" name="subheadline_font_size" class="form-control subheadline_font_size" value="{{ empty($settings->subheadline_font_size) ? "18,16,28,0,0" : $settings->subheadline_font_size }}">
                        <div class="input-group-append ml-1 align-items-center">
                            <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify font sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                            Or, specify a font size for each template by first selecting a size in Output Dimensions dropdown."></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="text" name="subheadline_color" id="subheadline_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->subheadline_color) ? "#000000" : $settings->subheadline_color }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="color" class="form-control" id="subheadline_color" value="{{ empty($settings->subheadline_color) ? "#000000" : $settings->subheadline_color }}">
                </div>
            </div>
        </div>
        <div class="d-none-parent">
            <label>Call to Action</label>
            <div class="form-row">
                <div class="col-md-5 col-sm-9 form-group">
                    <input type="text" name="CTA" class="form-control" value="{{ empty($settings->CTA) ? "" : $settings->CTA }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group center-checkbox">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="CTA_opaque" name="CTA_opaque" {{ !empty($settings->CTA_opaque) && $settings->CTA_opaque == "on" ? "checked" : "" }}>
                        <label class="form-check-label" for="CTA_opaque">Opaque</label>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <select name="CTA_alignment" class="form-control">
                        @php
                            $default = !empty($settings->CTA_alignment) ? $settings->CTA_alignment : "center";
                        @endphp
                        @foreach($alignments as $alignment)
                            <option {{ $alignment == $default ? "selected" : "" }} value="{{ $alignment }}">{{ $alignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 form-group">
                    <select name="CTA_font" class="form-control">
                        @foreach( Config::get("templates.Generic.fonts") as $font )
                            <option {{ !empty($settings->CTA_font) && $settings->CTA_font == $font ? "selected": "" }} value="{{ $font }}" >{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <div class="input-group">
                        <input type="text" name="CTA_font_size" class="form-control CTA_font_size" value="{{ empty($settings->CTA_font_size) ? "19,19,16,11,19" : $settings->CTA_font_size }}">
                        <div class="input-group-append ml-1 align-items-center">
                            <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify font sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                            Or, specify a font size for each template by first selecting a size in Output Dimensions dropdown."></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="text" name="CTA_color" id="CTA_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->CTA_color) ? "#000000" : $settings->CTA_color }}">
                </div>
                <div class="col-md-1 col-sm-3 form-group">
                    <input type="color" class="form-control" id="CTA_color" value="{{ empty($settings->CTA_color) ? "#000000" : $settings->CTA_color }}">
                </div>
            </div>
            <div>
                <label>Button Container</label>
                <div class="form-row">
                    <div class="col-md-1 col-sm-2 form-group">
                        <div class="input-group">
                            <input type="text" name="CTA_border_width" class="form-control" value="{{ empty($settings->CTA_border_width) ? "0" : $settings->CTA_border_width }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="border width"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-2 form-group">
                        <input type="text" name="CTA_border_color" id="CTA_border_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->CTA_border_color) ? "#000000" : $settings->CTA_border_color }}">
                    </div>
                    <div class="col-md-1 col-sm-2 form-group">
                        <input type="color" class="form-control" id="CTA_border_color" value="{{ empty($settings->CTA_border_color) ? "#000000" : $settings->CTA_border_color }}">
                    </div>
                    <div class="col-md-1 col-sm-2 form-group">
                        <div class="input-group">
                            <input type="text" name="CTA_border_radius" class="form-control" placeholder="Radius" value="{{ empty($settings->CTA_border_radius) ? "0" : $settings->CTA_border_radius }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="border radius"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-2 form-group">
                        <div class="input-group">
                            <input type="text" name="CTA_border_padding" class="form-control" placeholder="Padding" value="{{ empty($settings->CTA_border_padding) ? "0" : $settings->CTA_border_padding }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="border padding"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-none-parent">
            <label>Background</label>
            <div class="form-row">
                <div class="col-sm-6 col-md-10">
                    <div class="form-group">
                        <input type="file" class="form-control-file" name="background" data-show-preview="false">
                    </div>
                </div>
                <div class="col-sm-3 col-md-1">
                    <div class="form-group">
                        <input type="text" name="background_color" id="background_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->background_color) ? "#ffffff" : $settings->background_color }}">
                    </div>
                </div>
                <div class="col-sm-3 col-md-1">
                    <div class="form-group">
                        <input type="color" class="form-control" id="background_color" value="{{ empty($settings->background_color) ? "#ffffff" : $settings->background_color }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label>Button</label>
                    <input type="file" class="form-control-file" name="button" data-show-preview="false">
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label>Retailer Logo</label>
                    <input type="file" class="form-control-file" name="logo" data-show-preview="false">
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-3 col-sm-4 form-group">
                <label>Output file name (optional)</label>
                <input type="text" name="output_filename" class="form-control" value="{{ empty($settings->output_filename) ? "" : $settings->output_filename }}">
            </div>
            <div class="col-md-2 col-sm-3 col-6 form-group">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="compress" name="compress" {{ !empty($settings->compress) && $settings->compress == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="compress">Compress (to KB)</label>
                </div>
                <div class="input-group">
                    <input type="text" name="compress_size" class="form-control compress_size" value="{{ empty($settings->compress_size) ? "40,30,80,10,25" : $settings->compress_size }}">
                    <div class="input-group-append ml-1 align-items-center">
                        <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Specify compress sizes for multiple templates by separating them with commas, in the same order as listed in Output Dimensions dropdown, below.
                        Or, specify a compress size for each template by first selecting a size in Output Dimensions dropdown."></i>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-6 form-group">
                <label>Text Tracking</label>
                <input type="number" name="text_tracking" class="form-control" value="{{ empty($settings->text_tracking) ? "-10" : $settings->text_tracking }}">
            </div>
            <div class="col-md-2 col-sm-3 col-6 form-group">
                <label>Drop Shadow</label>
                <select name="drop_shadow" class="form-control">
                    @php
                        $default = !empty($settings->drop_shadow) ? $settings->drop_shadow : "none";
                    @endphp
                    @foreach($drop_shadows as $shadow)
                        <option {{ $default == $shadow ? "selected" : "" }} value="{{ $shadow }}">{{ $shadow }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>Product Layering</label>
                <div class="product_custom_layering {{ empty($settings->product_layering) || $settings->product_layering != 'Custom' ? 'd-none' : 'd-inline' }} ">
                    <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Change the layer order by changing the order of the numbers. 1 is the back-most layer. For example, 1 2 3 will put the left-most image in back (1) and the right-most image in front (3). 3 2 1 will put the left-most image in front and the right-most image in back."></i>
                </div>
                <div class="d-flex">
                    <select class="form-control w-50" name="product_layering">
                        @php
                            $product_layering_options = Config::get("templates.product_layering");
                            $default = !empty($settings->product_layering) ? $settings->product_layering : $product_layering_options[0];
                        @endphp
                        @foreach($product_layering_options as $layering)
                            <option {{ $default == $layering ? "selected" : "" }} value="{{ $layering }}">{{ $layering }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="product_custom_layering" value="{{ empty($settings->product_custom_layering) ? "1 2 3" : $settings->product_custom_layering }}" class="form-control w-50 ml-2 product_custom_layering {{ empty($settings->product_layering) || $settings->product_layering != 'Custom' ? 'd-none' : '' }}" />
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="image_shadow" name="image_shadow" {{ empty($settings->image_shadow) || $settings->image_shadow == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="image_shadow">Image Mirror</label>
                </div>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="fade" name="fade" {{ empty($settings->fade) || $settings->fade == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="fade">Fade</label>
                </div>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ empty($settings->include_psd) || $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-sm-3 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="border" name="border" {{ empty($settings->border) || $settings->border == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="border">Exterior Stroke (Border)</label>
                </div>
            </div>
            <div class="col-md-2 col-sm-3 col-6">
                <div class="form-group">
                    <input type="text" name="border_color" id="border_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->border_color) ? "#000000" : $settings->border_color }}">
                </div>
            </div>
            <div class="col-md-2 col-sm-3 col-6">
                <div class="form-group">
                    <input type="color" class="form-control" id="border_color" value="{{ empty($settings->border_color) ? "#000000" : $settings->border_color }}">
                </div>
            </div>
        </div>
        <div class="form-row mb-2">
            <button id="preview-ads" class="btn btn-primary mr-2 d-none-parent">Preview</button>
            <button id="download-ads" class="btn btn-primary mr-2 d-none-parent">Download</button>
            <button id="generate-ads" class="btn btn-primary mr-2 d-none-parent">Save Draft</button>
			<input type="hidden" id="saved-draft" />
            @if ($logged_in_user->isTeamMember())
                <button id="publish-team-ads" class="btn btn-primary mr-2" title="Publishes to: {{ $team_list }}">Publish Project</button>
            @else
                <button id="publish-team-ads" class="btn btn-primary mr-2">Save Project</button>
            @endif
			<input type="hidden" id="published-project" />
            <button id="share-ads" type="button" class="btn btn-primary mr-2 d-none-parent">Share...</button>
            <div class="generate-alert">Generating...</div>
            @if ($showlogs === "1")
                <button type="button" id="show-logs" class="btn btn-secondary" data-toggle="modal" data-target="#logModal">Show logs</button>
            @endif
        </div>
    </form>
@endsection

@section('modals')
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
@endsection

@section('modals')
    <!-- Share Modal -->
    @include('frontend.includes.modals.share')

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
                    <div class="image-edit-tools" style="display: none">
                        <div class="image-crop">
                            <!-- <a href="#" class="image-edit-button" id="image-crop-button"><i class="cil-crop"></i></a> -->
                            <div class="button-group">
                                <input type="checkbox" id="crop-fix-ratio" name="crop-fix-ratio" >
                                <label name="crop-fix-ratio">Fix ratio</lable>
                                <a href="#" id="crop-original-button">Original</a> | 
                                <a href="#" id="crop-cancel-button">Cancel</a> | 
                                <a href="#" id="crop-save-button">Save</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="crop-overlay">
        <div>
            <i class='fa fa-spinner fa-spin'></i> Saving...
        </div>
    </div>
@endsection

@push("after-scripts")
    <script>
        var productTexts = JSON.parse({!! isset($settings->product_texts) ? json_encode($settings->product_texts) : "'{}'" !!});
    </script>
	<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
@endpush