@extends('frontend.layouts.app')

@section('title', __('Banner'))

@section('content')
    @php
        $team_list = "";
        foreach ($logged_in_user->teams as $team) {
            $team_list .= $team->name;
        }
    @endphp
    <div class="alert alert-danger errors" role="alert" style="display: none;"></div>
    <div class="alert alert-success success" role="alert" style="display: none;"></div>
    <div class="d-none" id="preview-images"></div>
    <div class="d-none" id="product-images"></div>
    <form id="adForm" enctype="multipart/form-data">
        <div class="position-relative">
            @include('frontend.create.includes.customer')
            @php
                $customer_name = "MRHI";
            @endphp
            <div class="inline-template-selector" style="display:inline-block">
                @include('frontend.create.includes.template')
            </div>
        </div>
        @include('frontend.create.includes.project_name')
        <div class="d-none-parent">
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
        @if ($settings->output_dimensions != 3 && $settings->output_dimensions != 7)
        <div class="form-row d-none-parent">
            <div class="col-12">
                <label>Product Format</label>
                <div class="form-row">
                    <div class="col-md-6 col-sm-12 form-group">
                        <input type="text" name="product_format" class="form-control" value="{{ empty($settings->product_format) ? "" : $settings->product_format }}">
                    </div>

                    <div class="col-md-1 col-sm-3 form-group">
                        <div class="input-group">
                            <input type="number" name="product_size" class="form-control" value="{{ empty($settings->product_size) ? "290" : $settings->product_size }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Font Size"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-sm-6 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="product_format_text_color" id="product_format_text_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->product_format_text_color) ? "#FFFFFF" : $settings->product_format_text_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="product_format_text_color_picker" value="{{ empty($settings->product_format_text_color) ? "#FFFFFF" : $settings->product_format_text_color }}">
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-6 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="product_format_bk_color" id="product_format_bk_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->product_format_bk_color) ? "#0000FF" : $settings->product_format_bk_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="product_format_bk_color_picker" value="{{ empty($settings->product_format_bk_color) ? "#0000FF" : $settings->product_format_bk_color }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label>Sub Text</label>
                <div class="form-row">
                    <div class="col-md-6 col-sm-12 form-group">
                        <input type="text" name="sub_text" class="form-control" value="{{ empty($settings->sub_text) ? "" : $settings->sub_text }}">
                    </div>

                    <div class="col-md-1 col-sm-3 form-group">
                        <div class="input-group">
                            <input type="number" name="sub_text_size" class="form-control" value="{{ empty($settings->sub_text_size) ? "190" : $settings->sub_text_size }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Font Size"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-sm-6 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="sub_text_color" id="sub_text_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->sub_text_color) ? "#FFFFFF" : $settings->sub_text_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="sub_text_color_picker" value="{{ empty($settings->sub_text_color) ? "#FFFFFF" : $settings->sub_text_color }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label>Quantity/Volume</label>
                <div class="form-row">
                    <div class="col-md-6 col-sm-5 form-group">
                        <input type="text" name="quantity" class="form-control" value="{{ empty($settings->quantity) ? "" : $settings->quantity }}">
                    </div>
                    <div class="col-md-1 col-sm-3 form-group">
                        <div class="input-group">
                            <input type="number" name="quantity_size" class="form-control" value="{{ empty($settings->quantity_size) ? "230" : $settings->quantity_size }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Font Size"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-5 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="quantity_text_color" id="quantity_text_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->quantity_text_color) ? "#FFFFFF" : $settings->quantity_text_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="quantity_text_color_picker" value="{{ empty($settings->quantity_text_color) ? "#FFFFFF" : $settings->quantity_text_color }}">
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-5 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="quantity_bk_color" id="quantity_bk_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->quantity_bk_color) ? "#0000FF" : $settings->quantity_bk_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="quantity_bk_color_picker" value="{{ empty($settings->quantity_bk_color) ? "#0000FF" : $settings->quantity_bk_color }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <label>Units</label>
                <div class="form-row">
                    <div class="col-md-6 col-sm-5 form-group">
                        <input type="text" name="unit" class="form-control" value="{{ empty($settings->unit) ? "" : $settings->unit }}">
                    </div>
                    <div class="col-md-1 col-sm-2 form-group">
                        <div class="input-group">
                            <input type="number" name="unit_size" class="form-control" value="{{ empty($settings->unit_size) ? "230" : $settings->unit_size }}">
                            <div class="input-group-append ml-1 align-items-center">
                                <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Font Size"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-5 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="unit_text_color" id="unit_text_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->unit_text_color) ? "#FFFFFF" : $settings->unit_text_color }}">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" class="form-control" id="unit_text_color_picker" value="{{ empty($settings->unit_text_color) ? "#FFFFFF" : $settings->unit_text_color }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="form-row d-none-parent">
            <div class="col-md-3 col-sm-4 form-group">
                <label>Output file name (optional)</label>
                <input type="text" name="output_filename" class="form-control" value="{{ empty($settings->output_filename) ? "" : $settings->output_filename }}">
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ empty($settings->include_psd) || $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
            <input type="hidden" name="background_color" value="#ffffff">
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
    
    <div id="preview-popup">
        <div id="drag-handler">
            <span>Quick Preview</span>
            <span class="toggle-button">-</span>
        </div>
        @if ($settings->output_dimensions < 4)
            <canvas id="canvas" width="3000" height="3000"></canvas>
        @else
            <canvas id="canvas" width="1500" height="1500"></canvas>
        @endif
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
                            <a href="#" class="image-edit-button" id="image-crop-button"><i class="cil-crop"></i></a>
                            <div class="button-group">
                                <input type="checkbox" id="crop-fix-ratio" name="crop-fix-ratio" checked>
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
	<script type="text/javascript" src="{{ asset('js/preview/mrhi.js') }}"></script>
@endpush