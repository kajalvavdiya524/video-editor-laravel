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
                $customer_name = "Walmart";
            @endphp
            @include('frontend.create.includes.template')
        </div>
        @include('frontend.create.includes.project_name')
        <div class="form-row d-none-parent">
            <div class="col-md-3 form-group">
                <label>Theme</label>
                <select id="theme" name="theme" class="form-control">
                    @foreach ($themes as $theme)
                        <option value="{{ strtolower($theme['id']) }}" {{ (!empty($settings->theme) && $settings->theme == strtolower($theme['name'])) ? "selected" : "" }}>{{ ucfirst($theme['name']) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>Background Image</label>
                <button class="btn btn-primary select-bkimg" style="display:block" type="button">Background image…</button>
                <div class="selected-image">
                @if (!empty($settings->background))
                <?php
                    $arr = explode('/', $settings->background);
                    $arr[count($arr) - 2] = $settings->output_dimensions;
                    $background = implode('/', $arr);
                ?>
                <img class="background-preview" src="{{ $background }}" />
                @endif
                <input type="hidden" name="background" value="{{ isset($background) ? $background : '' }}" />
                </div>
            </div>
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
            <div class="form-group col-md-6">
                <label>Headline 1</label>
                <input type="text" name="headline1" class="form-control" value="{{ empty($settings->headline1) ? "" : $settings->headline1 }}" />
            </div>
            <div class="form-group col-md-6 subheadline">
                <label>Subhead 1</label>
                <input type="text" name="subheadline1" class="form-control" value="{{ empty($settings->subheadline1) ? "" : $settings->subheadline1 }}" />
            </div>
            <div class="form-group col-md-6">
                <label>Headline 2</label>
                <input type="text" name="headline2" class="form-control" value="{{ empty($settings->headline2) ? "" : $settings->headline2 }}" />
            </div>
            <div class="form-group col-md-6 subheadline">
                <label>Subhead 2</label>
                <input type="text" name="subheadline2" class="form-control" value="{{ empty($settings->subheadline2) ? "" : $settings->subheadline2 }}" />
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? "0" : $settings->x_offset[0] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? "0" : $settings->y_offset[0] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 1</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[0]) ? "-10" : $settings->angle[0] }}" placeholder="-10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 1</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[0]) ? "1" : $settings->scale[0] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? "0" : $settings->x_offset[1] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? "0" : $settings->y_offset[1] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 2</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[1]) ? "10" : $settings->angle[1] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 2</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[1]) ? "1" : $settings->scale[1] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 3</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[2]) ? "0" : $settings->x_offset[2] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 3</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[2]) ? "0" : $settings->y_offset[2] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 3</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[2]) ? "10" : $settings->angle[2] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 3</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[2]) ? "1" : $settings->scale[2] }}" />
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[3]) ? "0" : $settings->x_offset[3] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[3]) ? "0" : $settings->y_offset[3] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 1</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[3]) ? "-10" : $settings->angle[3] }}" placeholder="-10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 1</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[3]) ? "1" : $settings->scale[3] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[4]) ? "0" : $settings->x_offset[4] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[4]) ? "0" : $settings->y_offset[4] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 2</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[4]) ? "10" : $settings->angle[4] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 2</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[4]) ? "1" : $settings->scale[4] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 3</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[5]) ? "0" : $settings->x_offset[5] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 3</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[5]) ? "0" : $settings->y_offset[5] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 3</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[5]) ? "10" : $settings->angle[5] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 3</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[5]) ? "1" : $settings->scale[5] }}" />
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[6]) ? "0" : $settings->x_offset[6] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[6]) ? "0" : $settings->y_offset[6] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 1</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[6]) ? "10" : $settings->angle[6] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 1</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[6]) ? "1" : $settings->scale[6] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[7]) ? "0" : $settings->x_offset[7] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[7]) ? "0" : $settings->y_offset[7] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 2</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[7]) ? "-10" : $settings->angle[7] }}" placeholder="-10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 2</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[7]) ? "1" : $settings->scale[7] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 3</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[8]) ? "0" : $settings->x_offset[8] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 3</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[8]) ? "0" : $settings->y_offset[8] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 3</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[8]) ? "10" : $settings->angle[8] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 3</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[8]) ? "1" : $settings->scale[8] }}" />
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[9]) ? "0" : $settings->x_offset[9] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[9]) ? "0" : $settings->y_offset[9] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 1</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[9]) ? "10" : $settings->angle[9] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 1</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[9]) ? "1" : $settings->scale[9] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[10]) ? "0" : $settings->x_offset[10] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[10]) ? "0" : $settings->y_offset[10] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 2</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[10]) ? "10" : $settings->angle[10] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 2</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[10]) ? "1" : $settings->scale[10] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 3</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[11]) ? "0" : $settings->x_offset[11] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 3</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[11]) ? "0" : $settings->y_offset[11] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 3</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[11]) ? "-10" : $settings->angle[11] }}" placeholder="-10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 3</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[11]) ? "1" : $settings->scale[11] }}" />
            </div>
        </div>
        <div class="form-row d-none d-none-parent">
            <div class="form-group col-md-3">
                <label>X offset 1</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[12]) ? "0" : $settings->x_offset[12] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 1</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[12]) ? "0" : $settings->y_offset[12] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 1</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[12]) ? "10" : $settings->angle[12] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 1</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[12]) ? "1" : $settings->scale[12] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 2</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[13]) ? "0" : $settings->x_offset[13] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 2</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[13]) ? "0" : $settings->y_offset[13] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 2</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[13]) ? "10" : $settings->angle[13] }}" placeholder="10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 2</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[13]) ? "1" : $settings->scale[13] }}" />
            </div>
            
            <div class="form-group col-md-3">
                <label>X offset 3</label>
                <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[14]) ? "0" : $settings->x_offset[14] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Y offset 3</label>
                <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[14]) ? "0" : $settings->y_offset[14] }}" />
            </div>
            <div class="form-group col-md-3">
                <label>Angle 3</label>
                <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[14]) ? "-10" : $settings->angle[14] }}" placeholder="-10" />
            </div>
            <div class="form-group col-md-3">
                <label>Scale 3</label>
                <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[14]) ? "1" : $settings->scale[14] }}" />
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <label>CTA</label>
                <?php
                    $cta_list = ["Try Now", "Shop Now", "Add to Cart", "Buy Now"];
                ?>
                <select class="form-control" name="cta">
                    @foreach ($cta_list as $c)
                        <option value="{{ $c }}" {{ (isset($settings->cta) && $c == $settings->cta) ? "selected" : "" }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="show_stroke" name="show_stroke" {{ isset($settings->show_stroke) && $settings->show_stroke == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="show_stroke">Show Stroke</label>
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ isset($settings->include_psd) && $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="export_all" name="export_all" {{ isset($settings->export_all) && $settings->export_all == "on" ? "checked" : "" }} />
                    <label class="form-check-label" for="export_all">Download All Sizes</label>
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
            <span>Preview</span>
            <span class="toggle-button preview-control"><i class="cil-window-minimize"></i></span>
            <span class="edit-button edit preview-control"><i class="cil-pencil"></i></span>
        </div>
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
	<script type="text/javascript" src="{{ asset('js/walmart.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/walmart.js') }}"></script>
@endpush