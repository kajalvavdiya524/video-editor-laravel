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
                $customer_name = "Superama";
            @endphp
            <div class="inline-template-selector" style="display:inline-block">
                @include('frontend.create.includes.template')
            </div>
        </div>
        @include('frontend.create.includes.project_name')
        <div class="form-row d-none-parent">
            <div class="form-group col-md-4">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline }}" placeholder="Precios">
            </div>
            <div class="form-group col-md-4">
                <label>Sub headline</label>
                <input type="text" name="subheadline" class="form-control" value="{{ empty($settings->subheadline) ? "" : $settings->subheadline }}" placeholder="irresistibles">
            </div>
            <div class="form-group col-md-4">
                <label>Description</label>
                <input type="text" name="description" class="form-control" value="{{ empty($settings->description) ? "" : $settings->description }}" placeholder="Vigencia del 16 al 30 de noviembre de 2020.">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-9 form-group">
                <label>UPC / GTIN / ASIN / TCIN / WMT-ID</label>
                <input type="text" name="file_ids" class="form-control" autofocus value="{{ empty($settings->file_ids) ? "" : $settings->file_ids }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Product Spacing</label>
                <input type="number" name="product_space1" class="form-control" autofocus value="{{ empty($settings->product_space1) ? "0" : $settings->product_space1 }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="form-group col-md-3">
                <label>Multiple(x)</label>
                <input type="number" name="multi1" class="form-control" value="{{ empty($settings->multi1) ? "" : $settings->multi1 }}" placeholder="2">
            </div>
            <div class="form-group col-md-3">
                <label>Price($)</label>
                <input type="number" name="price1" class="form-control" value="{{ empty($settings->price1) ? "" : $settings->price1 }}" placeholder="38">
            </div>
            <div class="form-group col-md-3">
                <label>Cost Per Unit</label>
                <input type="number" name="unit_cost1" class="form-control" value="{{ empty($settings->unit_cost1) ? "" : $settings->unit_cost1 }}" placeholder="22">
            </div>
            <div class="form-group col-md-3">
                <label>Weight(g)</label>
                <input type="number" name="weight1" class="form-control" value="{{ empty($settings->weight1) ? "" : $settings->weight1 }}" placeholder="500">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-9 form-group">
                <label>UPC / GTIN / ASIN / TCIN / WMT-ID</label>
                <input type="text" name="file_ids2" class="form-control" autofocus value="{{ empty($settings->file_ids2) ? "" : $settings->file_ids2 }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Product Spacing</label>
                <input type="number" name="product_space2" class="form-control" autofocus value="{{ empty($settings->product_space2) ? "0" : $settings->product_space2 }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="form-group col-md-3">
                <label>Multiple(x)</label>
                <input type="text" name="multi2" class="form-control" value="{{ empty($settings->multi2) ? "" : $settings->multi2 }}" placeholder="2">
            </div>
            <div class="form-group col-md-3">
                <label>Price($)</label>
                <input type="text" name="price2" class="form-control" value="{{ empty($settings->price2) ? "" : $settings->price2 }}" placeholder="25">
            </div>
            <div class="form-group col-md-3">
                <label>Cost Per Unit</label>
                <input type="text" name="unit_cost2" class="form-control" value="{{ empty($settings->unit_cost2) ? "" : $settings->unit_cost2 }}" placeholder="15">
            </div>
            <div class="form-group col-md-3">
                <label>Weight(g)</label>
                <input type="text" name="weight2" class="form-control" value="{{ empty($settings->weight2) ? "" : $settings->weight2 }}" placeholder="580">
            </div>
        </div>
        <!-- <div class="form-row">
            <div class="col-sm-6 col-md-6">
                <label>Background</label>
                <div class="form-group">
                    <input type="file" class="form-control-file" name="background" data-show-preview="false">
                </div>
            </div>
        </div> -->
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ empty($settings->include_psd) || $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
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
        <canvas id="canvas" width="1680" height="320"></canvas>
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
	<script type="text/javascript" src="{{ asset('js/preview/superama.js') }}"></script>
@endpush