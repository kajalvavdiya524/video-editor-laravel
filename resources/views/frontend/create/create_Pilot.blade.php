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
                $customer_name = "Pilot";
            @endphp
            @include('frontend.create.includes.template')
        </div>
        @include('frontend.create.includes.project_name')
        <div class="form-row d-none-parent">
            <div class="col-md-3 form-group">
                <label>Theme</label>
                <select class="form-control" name="background_type" id="background_type">
                    <option value="background_image" {{ isset($settings->background_type) && $settings->background_type == "background_image" ? 'selected' : '' }}>Background Image</option>
                    <option value="product_image" {{ isset($settings->background_type) && $settings->background_type == "product_image" ? 'selected' : '' }}>Product Image</option>
                </select>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-9 form-group">
                <label for="text1">Text 1</label>
                <input type="text" name="text1" class="form-control" value="{{ empty($settings->text1) ? "" : $settings->text1 }}">
            </div>
            <div class="col-md-3 form-row">
                <div class="col-md-6 col-sm-6 form-group">
                    <label for="text2">Color-Hex 1</label>
                    <input type="text" name="text1_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->text1_color) ? "#FFFFFF" : $settings->text1_color }}">
                </div>
                <div class="col-md-6 col-sm-6 form-group">
                    <label for="text2">Color 1</label>
                    <input type="color" class="form-control" id="text1_color" value="{{ empty($settings->text1_color) ? "#FFFFFF" : $settings->text1_color }}">
                </div>
            </div>
        </div>
        <div class="form-row text2-row d-none-parent" style="{{ $settings->output_dimensions == 0 ? '' : 'display: none' }}">
            <div class="col-md-5 form-group">
                <label for="text2">Text 2</label>
                <input type="text" name="text2" class="form-control" value="{{ empty($settings->text2) ? "" : $settings->text2 }}">
            </div>
            <div class="col-md-2 form-group">
                <label for="text2">Font Family</label>
                <select class="form-control" name="text2_font">
                    <option value="GothamNarrow-Ultra" {{ isset($settings->text2_font) && $settings->text2_font == "GothamNarrow-Ultra" ? 'selected' : '' }}>Gotham Narrow Ultra</option>
                    <option value="MuseoSans-300Italic" {{ isset($settings->text2_font) && $settings->text2_font == "MuseoSans-300Italic" ? 'selected' : '' }}>MuseoSans</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label for="text2">Font Size</label>
                <input type="number" name="text2_font_size" class="form-control" value="{{ empty($settings->text2_font_size) ? '70' : $settings->text2_font_size }}">
            </div>
            <div class="col-md-3 form-row">
                <div class="col-md-6 col-sm-6 form-group">
                    <label for="text2">Color-Hex 2</label>
                    <input type="text" name="text2_color" class="form-control color-hex" placeholder="Color Hex Code" value="{{ empty($settings->text2_color) ? "#FFFFFF" : $settings->text2_color }}">
                </div>
                <div class="col-md-6 col-sm-6 form-group">
                    <label for="text2">Color 2</label>
                    <input type="color" class="form-control" id="text2_color" value="{{ empty($settings->text2_color) ? "#FFFFFF" : $settings->text2_color }}">
                </div>
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
            <div class="col-md-3 form-group">
                <label>Background Color</label>
                <?php
                    $bk_colors = ["#1F4D79", "#B6C7D9", "#C43442", "#E9B5C3", "#D35E2B", "#ECBD9D", "#82B856", "#CAE5BB", "#CFA137", "#EEDEA1", "#81C0E9", "#CCE8F3", "#BCBEC0", "#E5E9E7"];
                ?>
                <select class="form-control" name="background_color" id="background_color">
                    @foreach($bk_colors as $c)
                        <option value="{{ $c }}" {{ isset($settings->background_color) && $settings->background_color == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <span class="background-color-preview" style="background: {{ empty($settings->background_color) ? '#1f4d79' : $settings->background_color }}"></span>
            </div>
            <div class="col-md-3 form-group">
                <label>Background Pattern</label>
                <select class="form-control" name="background_pattern" id="background_pattern">
                    <option value="texture" {{ isset($settings->background_pattern) && $settings->background_pattern == "texture" ? 'selected' : '' }}>Textured Background</option>
                    <option value="solid" {{ isset($settings->background_pattern) && $settings->background_pattern == "solid" ? 'selected' : '' }}>Solid Background</option>
                </select>
            </div>
        </div>
        <div class="d-none">
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset 1</label>
                    <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? '0' : $settings->x_offset[0] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset 1</label>
                    <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? '0' : $settings->y_offset[0] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Angle 1</label>
                    <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[0]) ? '0' : $settings->angle[0] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Scale 1</label>
                    <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[0]) ? '1' : $settings->scale[0] }}">
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset 2</label>
                    <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? '0' : $settings->x_offset[1] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset 2</label>
                    <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? '0' : $settings->y_offset[1] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Angle 2</label>
                    <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[1]) ? '0' : $settings->angle[1] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Scale 2</label>
                    <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[1]) ? '1' : $settings->scale[1] }}">
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset 3</label>
                    <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[2]) ? '0' : $settings->x_offset[2] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset 3</label>
                    <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[2]) ? '0' : $settings->y_offset[2] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Angle 3</label>
                    <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[2]) ? '0' : $settings->angle[2] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Scale 3</label>
                    <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[2]) ? '1' : $settings->scale[2] }}">
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset button 1</label>
                    <input type="number" name="x_offset_button[]" class="form-control" value="{{ empty($settings->x_offset_button[0]) ? '0' : $settings->x_offset_button[0] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset button 1</label>
                    <input type="number" name="y_offset_button[]" class="form-control" value="{{ empty($settings->y_offset_button[0]) ? '0' : $settings->y_offset_button[0] }}">
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset button 2</label>
                    <input type="number" name="x_offset_button[]" class="form-control" value="{{ empty($settings->x_offset_button[1]) ? '0' : $settings->x_offset_button[1] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset button 2</label>
                    <input type="number" name="y_offset_button[]" class="form-control" value="{{ empty($settings->y_offset_button[1]) ? '0' : $settings->y_offset_button[1] }}">
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-2 form-group">
                    <label for="text2">X offset button 3</label>
                    <input type="number" name="x_offset_button[]" class="form-control" value="{{ empty($settings->x_offset_button[2]) ? '0' : $settings->x_offset_button[2] }}">
                </div>
                <div class="col-md-2 form-group">
                    <label for="text2">Y offset button 3</label>
                    <input type="number" name="y_offset_button[]" class="form-control" value="{{ empty($settings->y_offset_button[2]) ? '0' : $settings->y_offset_button[2] }}">
                </div>
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-3 form-group background-image-select" style = "{{ (isset($settings->background_type) && $settings->background_type != 'background_image') ? 'display: none' : ''}}">
                <label>Background Image</label>
                <button class="btn btn-primary select-bkimg" style="display:block" type="button">Background image</button>
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
            <div class="col-md-12 product-image-select" style="{{ (!isset($settings->background_type) || $settings->background_type != 'product_image') ? 'display: none' : '' }}">
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
        </div>
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ !empty($settings->include_psd) && $settings->include_psd == "on" ? "checked" : "" }}>
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="export_all" name="export_all" {{ (!empty($settings->export_all) && $settings->export_all == "on") ? "checked" : "" }} />
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
    <script>
        var productTexts = JSON.parse({!! isset($settings->product_texts) ? json_encode($settings->product_texts) : "'{}'" !!});
    </script>
	<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/pilot.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/pilot.js') }}"></script>
@endpush