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
                $customer_name = "Kroger";
            @endphp
            @include('frontend.create.includes.template')
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
        <div class="form-row d-none-parent">
            <div class="col-md-3 form-group">
                <label>Theme</label>
                <div class="theme-picker">
                    <select id="theme" name="theme" class="form-control">
                        @foreach ($themes as $theme)
                            <option value="{{ strtolower($theme['id']) }}" {{ (!empty($settings->theme) && $settings->theme == strtolower($theme['name'])) ? "selected" : "" }}>{{ ucfirst($theme['name']) }}</option>
                        @endforeach
                        <!-- <option value="standard" {{ (!empty($settings->theme) && $settings->theme == "standard") ? "selected" : "" }}>Standard</option> -->
                    </select>
                    <span class="theme-color">
                        <i class="circle-color" style="background: #e6873b"></i>
                        <i class="circle-color" style="background: #f2c54a"></i>
                        <i class="circle-color" style="background: #324b14"></i>
                        <!-- <i class="circle-color" style="background: #f8cd47"></i>
                        <i class="circle-color" style="background: #c02a1d"></i>
                        <i class="circle-color" style="background: #18429e"></i> -->
                    </span>
                </div>
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
            <div class="col-md-3 form-group">
                <label>Message Options</label>
                <select id="message_options" name="message_options" class="form-control">
                    <option value="0" {{ (!empty($settings->message_options) && $settings->message_options == 0) ? "selected" : "" }}>On Sale Now - X for $XX</option>
                    <option value="1" {{ (!empty($settings->message_options) && $settings->message_options == 1) ? "selected" : "" }}>On Sale Now - $Xxx</option>
                    <option value="2" {{ (!empty($settings->message_options) && $settings->message_options == 2) ? "selected" : "" }}>Buy X Save X</option>
                    <option value="3" {{ (!empty($settings->message_options) && $settings->message_options == 3) ? "selected" : "" }}>Save Up To</option>
                    <option value="4" {{ (!empty($settings->message_options) && $settings->message_options == 4) ? "selected" : "" }}>Save</option>
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>Circle and Text Color</label>
                <div class="circle-text-color-picker">
                    <?php
                        $attributes = $themes[0]['attributes'];
                        $circle_tags = $attributes[0];
                        $cc = $circle_tags->list[0];
                        $default_c = implode(",", array_column($cc->list, 'value'));
                    ?>
                    <select id="circle_text_color" name="circle_text_color" class="form-control" data-value="{{ empty($settings->circle_text_color) ? $default_c : $settings->circle_text_color }}">
                        @foreach ($circle_tags->list as $color)
                            <?php
                                $c = implode(",", array_column($color->list, 'value'));
                            ?>
                            <option value="{{ $c }}">{{$color->name}}</option>
                        @endforeach
                    </select>
                    <span class="circle-color" style="background: rgb(230, 135, 59);">
                        <i id="text_color1" class="text-color" style="background: rgb(50, 75, 20);"></i> 
                        <i id="text_color2" class="text-color" style="background: rgb(255, 255, 255);"></i> 
                        <i id="text_color3" class="text-color" style="background: rgb(50, 75, 20);"></i>
                    </span>
                </div>
                <input type="hidden" id="circle_color" name="circle_color" value="{{isset($settings->circle_color) ? $settings->circle_color : ''}}" />
                <input type="hidden" id="text1_color" name="text1_color" value="{{isset($settings->text1_color) ? $settings->text1_color : ''}}" />
                <input type="hidden" id="text2_color" name="text2_color" value="{{isset($settings->text2_color) ? $settings->text2_color : ''}}" />
                <input type="hidden" id="text3_color" name="text3_color" value="{{isset($settings->text3_color) ? $settings->text3_color : ''}}" />
            </div>
        </div>
        <div class="form-row burst-row d-none-parent" style="{{ $settings->output_dimensions ? '' : 'display:none' }}">
            <div class="col-md-3 form-group">
                <label>Burst Color</label>
                <div class="burst-color-picker">
                    <?php
                        $burst_tags = $attributes[1];
                        $bc = $burst_tags->list[0];
                        $default_b = implode(",", array_column($bc->list, 'value'));
                    ?>
                    <select id="burst_color" name="burst_color" class="form-control" data-value="{{ empty($settings->burst_color) ? $default_b : $settings->burst_color }}">
                        @foreach ($burst_tags->list as $color)
                            <?php
                                $c = implode(",", array_column($color->list, 'value'));
                            ?>
                            <option value="{{ $c }}">$color->name</option>
                        @endforeach
                    </select>
                    <span class="burst-circle-color" style="background: #f7cb4d">
                        <i class="burst-text-color" id="burst_text_color1" style="background: #ffffff"></i>
                        <i class="burst-text-color" id="burst_text_color2" style="background: #ffffff"></i>
                        <i class="burst-text-color" id="burst_text_color3" style="background: #ffffff"></i>
                    </span>
                </div>
                <input type="hidden" id="burst_circle_color" name="burst_circle_color" value="{{ empty($settings->burst_circle_color) ? '' : $settings->burst_circle_color }}" />
                <input type="hidden" id="burst_text_color" name="burst_text_color" value="{{ empty($settings->burst_text_color) ? '' : $settings->burst_text_color }}" />
            </div>
            <div class="col-md-9 form-group">
                <label>Burst Text</label>
                <input type="text" name="burst_text" class="form-control" value="{{ empty($settings->burst_text) ? "" : $settings->burst_text }}">
            </div>
        </div>
        <div class="form-row d-none-parent">
            <div class="form-group col-md-3 value1">
                <label>X</label>
                <input type="number" name="value1" class="form-control" value="{{ empty($settings->value1) ? "" : $settings->value1 }}" placeholder="3">
            </div>
            <div class="form-group col-md-3 value2">
                <label>$XX</label>
                <input type="number" name="value2" class="form-control" value="{{ empty($settings->value2) ? "" : $settings->value2 }}" placeholder="5">
            </div>
        </div>
        <div class="form-row d-none-parent product-names">
            <div class="form-group col-md-4">
                <label>Product Name - Row 1</label>
                <input type="text" name="text1" class="form-control" value="{{ empty($settings->text1) ? "" : $settings->text1 }}">
            </div>
            <div class="form-group col-md-4">
                <label>Product Name - Row 2</label>
                <input type="text" name="text2" class="form-control" value="{{ empty($settings->text2) ? "" : $settings->text2 }}">
            </div>
            <div class="form-group col-md-4">
                <label>Product Name - Row 3</label>
                <input type="text" name="text3" class="form-control" value="{{ empty($settings->text3) ? "" : $settings->text3 }}">
            </div>
        </div>
        <div class="d-none-parent">
            <p id="toggleOffsetAngle">+ Product Offsets/Angles</p>
            <div class="offsetAngle-wrapper ml-4 {{ $settings->output_dimensions == 0 ? '' : 'd-none' }}">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 1</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? "0" : $settings->x_offset[0] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 1</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? "0" : $settings->y_offset[0] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 1</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[0]) ? "-5" : $settings->angle[0] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 1</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[0]) ? "1" : $settings->scale[0] }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 2</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? "0" : $settings->x_offset[1] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 2</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? "0" : $settings->y_offset[1] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 2</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[1]) ? "5" : $settings->angle[1] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 2</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[1]) ? "1" : $settings->scale[1] }}">
                    </div>
                </div>
            </div>
            <div class="offsetAngle-wrapper ml-4 {{ $settings->output_dimensions == 1 ? '' : 'd-none' }}">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 1</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? "0" : $settings->x_offset[2] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 1</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? "0" : $settings->y_offset[2] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 1</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[0]) ? "-5" : $settings->angle[2] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 1</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[0]) ? "1" : $settings->scale[2] }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 2</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? "0" : $settings->x_offset[3] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 2</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? "0" : $settings->y_offset[3] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 2</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[1]) ? "5" : $settings->angle[3] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 2</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[1]) ? "1" : $settings->scale[3] }}">
                    </div>
                </div>
            </div>
            <div class="offsetAngle-wrapper ml-4 {{ $settings->output_dimensions == 2 ? '' : 'd-none' }}">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 1</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[0]) ? "0" : $settings->x_offset[4] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 1</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[0]) ? "0" : $settings->y_offset[4] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 1</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[0]) ? "-5" : $settings->angle[4] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 1</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[0]) ? "1" : $settings->scale[4] }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Horizontal Offset 2</label>
                        <input type="number" name="x_offset[]" class="form-control" value="{{ empty($settings->x_offset[1]) ? "0" : $settings->x_offset[5] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Vertical Offset 2</label>
                        <input type="number" name="y_offset[]" class="form-control" value="{{ empty($settings->y_offset[1]) ? "0" : $settings->y_offset[5] }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rotation 2</label>
                        <input type="number" name="angle[]" class="form-control" value="{{ empty($settings->angle[1]) ? "5" : $settings->angle[5] }}">
                    </div>
                    <div class="form-group col-md-4 d-none">
                        <label>scale 2</label>
                        <input type="number" name="scale[]" class="form-control" value="{{ empty($settings->scale[1]) ? "1" : $settings->scale[5] }}">
                    </div>
                </div>
            </div>
        </div>
		<div class="project-name form-group d-none-parent">
			<label>Legal</label>
			<input type="text" name="legal" class="form-control" value="{{ empty($settings->legal) ? "" : $settings->legal }}">
		</div>
        <div class="form-row d-none-parent">
            <div class="col-md-3">
                <input type="checkbox" class="form-check-label" id="show_featured" name="show_featured" {{ (!empty($settings->show_featured) && $settings->show_featured == "on") ? "checked" : "" }} />
                <label for="show_featured">@lang('Show Featured (Preview only)')</label>
            </div>
            <div class="col-md-3 button-show-row" style="{{ $settings->output_dimensions ? '' : 'display:none' }}">
                <input type="checkbox" class="form-check-label" id="show_button" name="show_button" {{ (!empty($settings->show_button) && $settings->show_button == "on") ? "checked" : "" }} />
                <label for="show_button">@lang('Show Shop Now button (Preview only)')</label>
            </div>
        </div>
        <!-- <div class="form-row">
            <p class="col-12">Background</p>
            <div class="col-md-6">
                <div class="custom-control custom-radio ">
                    <input type="radio" name="background_type" id="solid" class="custom-control-input" value="solid" checked>
                    <label class="custom-control-label" for="solid">Solid color</label>
                </div>
                <div class="form-row ml-3">
                    <select id="solid_preset" class="col-md-4 col-sm-4 form-control">
                        <option value="#ffffff">White</option>
                    </select>
                    <div class="col-md-4 col-sm-4 form-group">
                        <input type="text" name="background_solid_color" id="background_solid_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#FFFFFF">
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                        <input type="color" id="background_solid_color" class="form-control" value="#FFFFFF">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="custom-control custom-radio ">
                    <input type="radio" name="background_type" id="gradient" class="custom-control-input" value="gradient">
                    <label class="custom-control-label" for="gradient">Gradient color</label>
                </div>
                <div class="form-row ml-3">
                    <div class="col-md-6">
                        <div class="form-row">
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="text" name="g_start_color" id="g_start_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#FFFFFF">
                            </div>
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="color" id="g_start_color" class="form-control" value="#FFFFFF">
                            </div>  
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-row">
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="text" name="g_end_color" id="g_end_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#FFFFFF">
                            </div>
                            <div class="col-md-6 col-sm-6 form-group">
                                <input type="color" id="g_end_color" class="form-control" value="#FFFFFF">
                            </div>  
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="custom-control custom-radio ">
                    <input type="radio" name="background_type" id="library" class="custom-control-input" value="library">
                    <label class="custom-control-label" for="library">Choose from background images library</label>
                </div>
                <div class="ml-3">
                    Library
                </div>
            </div>
            <div class="col-md-6">
                <div class="custom-control custom-radio ">
                    <input type="radio" name="background_type" id="upload" class="custom-control-input" value="upload">
                    <label class="custom-control-label" for="upload">Upload background image</label>
                </div>
                <div class="ml-3">
                    <div class="form-row">
                        <div class="col-sm-6 col-md-10">
                            <div class="form-group">
                                <input type="file" class="form-control-file" name="background" data-show-preview="false">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="form-row d-none-parent">
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="include_psd" name="include_psd" {{ (!empty($settings->include_psd) && $settings->include_psd == "on") ? "checked" : "" }} />
                    <label class="form-check-label" for="include_psd">Include PSD</label>
                </div>
            </div>
            <div class="col-md-2 col-4 form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="export_all" name="export_all" {{ (!empty($settings->export_all) && $settings->export_all == "on") ? "checked" : "" }} />
                    <label class="form-check-label" for="export_all">Download All Sizes</label>
                </div>
            </div>
            <!-- <div class="col-md-2 col-4 form-group d-none">
                <button class="btn btn-link" id="proof-sheet">Proof sheet</a>
            </div> -->
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
	<script type="text/javascript" src="{{ asset('js/kroger.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/kroger.js') }}"></script>
@endpush