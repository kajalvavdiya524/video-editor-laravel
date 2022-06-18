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
                $customer_name = "Sam";
            @endphp
            @include('frontend.create.includes.template')
        </div>
		<div class="project-name form-group mt-3 d-none-parent">
			<label>Project Name</label>
			<input type="text" name="project_name" class="form-control" value="{{ empty($settings->project_name) ? "" : $settings->project_name }}">
		</div>
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
            <div class="col-md-4 form-group">
                <label for="pre_header">Pre Header</label>
                <?php
                    $list = ["TRY PICKUP", "NEW", "NEW LOWER PRICE"];
                    $selected = false;
                ?>
                <select class="form-control" name="pre_header">
                    @foreach ($list as $l)
                        @if (isset($settings->pre_header) && $l == $settings->pre_header)
                            <?php $selected = true; ?>
                            <option value="{{ $l }}" selected>{{ $l }}</option>
                        @else
                            <option value="{{ $l }}">{{ $l }}</option>
                        @endif
                    @endforeach
                    @if (isset($settings->pre_header) && !$selected)
                        <option value="{{ $settings->pre_header }}" id="custom_pre_header" selected>CUSTOM</option>
                    @else
                        <option value="custom" id="custom_pre_header">CUSTOM</option>
                    @endif
                </select>
            </div>
            <div class="col-md-6 form-group custom-pre-header" style="{{ $selected == false ? 'display: none' : '' }}">
                <label for="custom_pre_header">Custom Pre Header</label>
                <input type="text" name="custom_pre_header" class="form-control" value="{{ !isset($settings->pre_header) ? "" : $settings->pre_header }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-6 form-group">
                <label for="header">Header</label>
                <input type="text" name="header" class="form-control" value="{{ empty($settings->header) ? "" : $settings->header }}">
            </div>
            <div class="col-md-6 form-group">
                <label for="subhead">Subhead (Optional)</label>
                <input type="text" name="subhead" class="form-control" value="{{ empty($settings->subhead) ? "" : $settings->subhead }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-9 form-group">
                <label>Logo</label>
                <input type="file" class="form-control-file" name="logo" data-show-preview="false">
                <input type="hidden" name="logo_saved" id="logo_saved" value="{{ isset($settings->logo) ? $settings->logo : '' }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-4 form-group">
                <label for="disclaimer">Disclaimer</label>
                <input type="text" name="disclaimer" class="form-control" value="{{ empty($settings->disclaimer) ? "" : $settings->disclaimer }}">
            </div>
            <div class="col-md-4 form-group">
                <label>CTA</label>
                <select class="form-control" name="cta">
                    <option value="Shop Now">Shop Now</option>
                    <option value="Buy Now">Buy Now</option>
                </select>
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X Offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? "0" : $settings->x_offset[0] }}">
            </div>
            <div class="form-group col-md-3">
                <label>Y Offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? "0" : $settings->y_offset[0] }}">
            </div>
            <div class="form-group col-md-3">
                <label>X Offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? "0" : $settings->x_offset[1] }}">
            </div>
            <div class="form-group col-md-3">
                <label>Y Offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? "0" : $settings->y_offset[1] }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ !empty($settings->include_psd) && $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
        </div>
        <div class="form-row mb-2 d-none-parent">
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
            <span>Preview</span>
            <span class="toggle-button preview-control"><i class="cil-window-minimize"></i></span>
            <span class="edit-button edit preview-control"><i class="cil-pencil"></i></span>
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
    <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Share...</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="form-group" id="select-choice">
                        <p>Project needs to be saved before sharing:</p>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="save_draft" name="share_ads" class="custom-control-input" value="save" checked>
                            <label class="custom-control-label" for="save_draft">Save Draft</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="publish_to_team" name="share_ads" class="custom-control-input" value="publish">
                            <label class="custom-control-label" for="publish_to_team">{{ $logged_in_user->isTeamMember() ? "Publish Project" : "Save Project" }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <p class="mb-0">Please enter emails to share with:</p>
                        <p style="color: grey">(Separate multiple emails with commas or spaces.)</p>
                        <input type="text" name="share_email" id="share_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Share</button>
                </div>
            </div>
        </div>
    </div>

	<!-- Select available image Modal -->
	<div class="modal fade" id="selectImgModal" tabindex="-1" role="dialog" aria-labelledby="selectImgModalLabel" aria-hidden="true">
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
					<div class="background-image-grid">
						<div class="image-grid-responsive">
							<div class="grid"></div>
						</div>
					</div>
					<div class="full-size-image" style="display: none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Select</button>
                    <button type="button" id="cancel" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/sam.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/sam.js') }}"></script>
@endpush