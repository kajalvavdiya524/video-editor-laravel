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
                $customer_name = "Amazon";
            @endphp
            <div class="inline-template-selector" style="display:inline-block">
                @if ($customer == 2)
                    @include('frontend.create.includes.template_new')
                @else
                    @include('frontend.create.includes.template')
                @endif
            </div>
        </div>
        @include('frontend.create.includes.project_name')
        @if ($settings->output_dimensions == 1)
		<div class="d-none-parent">
			<label>
				UPC / GTIN / ASIN / TCIN / WMT-ID 
			</label>
			<div class="form-row">
				<div class="col-12 form-group">
					<input type="text" name="file_top_ids" class="form-control" autofocus value="{{ empty($settings->file_top_ids) ? "" : $settings->file_top_ids }}">
				</div>
			</div>
		</div>
        @endif
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
		<div class="form-row d-none-parent">
			<div class="col-md-3 form-group d-none">
				<label>Product Layouts</label>
                <select name="product_layouts" class="form-control">
                    <option value="single">Single line</option>
                    <option value="two-line">Two line</option>
                </select>
			</div>
			<div class="col-md-3 form-group">
				<label>Product Layering</label>
                <select name="product_layering" class="form-control">
                    <option value="back-to-front">Back To Front</option>
                    <option value="front-to-back">Front To Back</option>
                    <option value="middle-in-front">Middle In Front</option>
                </select>
			</div>
            <div class="col-md-3 form-group">
				<label>Product Spacing</label>
				<input type="text" name="product_spacing" class="form-control" value="{{ empty($settings->product_spacing) ? "-100" : $settings->product_spacing }}">
			</div>
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
            @if ($showlogs == 1)
                <button type="button" id="show-logs" class="btn btn-secondary" data-toggle="modal" data-target="#logModal">Show logs</button>
            @endif
        </div>
    </form>

    <div id="preview-popup">
        <div id="drag-handler">
            <span>Quick Preview</span>
            <span class="toggle-button">-</span>
        </div>
        <canvas id="canvas" width="3000" height="3000"></canvas>
    </div>

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
@endsection

@push("after-scripts")
    <script>
        var productTexts = JSON.parse({!! isset($settings->product_texts) ? json_encode($settings->product_texts) : "'{}'" !!});
    </script>
	<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/varietypack.js') }}"></script>
@endpush