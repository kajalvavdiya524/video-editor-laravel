@extends('frontend.layouts.app')

@section('title', __('Banner'))

@section('content')
	@php
		$drop_shadows = array("left");
		$color_names = array(
			"blue" => "blue/mist | mermaid",
			"teal" => "teal/sage | lagoon",
			"green" => "green/dew | jungle",
			"yellow" => "yellow/nectar | smile",
			"red" => "red/lily | harvest",
			"pink" => "pink/primrose | coral",
			"purple" => "purple/orchid | lavender"
		);
		$circle_positions = array("none", "top", "bottom", "center");

        $team_list = "";
        foreach ($logged_in_user->teams as $team) {
            $team_list .= $team->name;
        }
        $alignments = array("left", "center", "right");
        $drop_shadows = array("none", "left", "right");
	@endphp
	<div class="alert alert-danger errors" role="alert" style="display: none;"></div>
    <div class="alert alert-success success" role="alert" style="display: none;"></div>
	<div class="d-none" id="preview-images" href="#"></div>
	<div class="d-none" id="product-images"></div>
	<form id="adForm" enctype="multipart/form-data">
        <div class="position-relative">
			@include('frontend.create.includes.customer')
			<div class="template-wrapper">
				<p>Copy Style</p>
				@php
                	$customer_name = "AmazonFresh";
					$output_dimensions = Config::get("templates.".$customer_name.".output_dimensions");
					$default_template = !empty($settings->output_dimensions) ? $settings->output_dimensions : 0;
					$show_3h = empty($settings->show_3h) ? "" : $settings->show_3h;
        			$count = count($output_dimensions);
				@endphp
				<input type="hidden" name="output_dimensions" value="{{ $default_template }}" />
				<div class="selected-template">
					<div class="slide-item">
						<img class="selected" src="{{ asset('img/templates/'.$customer_name.'/'.$default_template.'.png') }}" title="{{ $output_dimensions[$default_template] }}" />
					</div>
				</div>
				<div class="templates slide-popup">
					<div class="templates-carousel-hidden d-none">
						<div class="slide-item">
							<img class="selected" src="{{ asset('img/templates/'.$customer_name.'/'.$default_template.'.png') }}" title="{{ $output_dimensions[$default_template] }}" data-value="{{ $default_template }}" />
						</div>
						@for ($i = $default_template + 1; $i < $count; $i++)
							@if ($output_dimensions[$i] != "3H" || $show_3h == "on")
								<div class="slide-item">
									<img src="{{ asset('img/templates/'.$customer_name.'/'.$i.'.png') }}" title="{{ $output_dimensions[$i] }}" data-value="{{ $i }}" />
								</div>
							@endif
						@endfor
						@for ($i = 0; $i < $default_template; $i++)
							@if ($output_dimensions[$i] != "3H" || $show_3h == "on")
								<div class="slide-item">
									<img src="{{ asset('img/templates/'.$customer_name.'/'.$i.'.png') }}" title="{{ $output_dimensions[$i] }}" data-value="{{ $i }}" />
								</div>
							@endif
						@endfor
					</div>
				</div>
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
		<div class="form-row d-none-parent">
			<div class="col-md-3 form-group">
				<label>Product Spacing</label>
				<input type="text" name="product_space" class="form-control" value="{{ empty($settings->product_space) ? "0" : $settings->product_space }}">
			</div>
			<div class="col-md-3 form-group">
				<label>Product Layering</label>
				<div class="product_custom_layering {{ empty($settings->product_layering) || $settings->product_layering != 'Custom' ? 'd-none' : 'd-inline' }} ">
					<i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Change the layer order by changing the order of the numbers. 1 is the back-most layer. For example, 1 2 3 will put the left-most image in back (1) and the right-most image in front (3). 3 2 1 will put the left-most image in front and the right-most image in back."></i>
				</div>
				<div class="d-flex">
					<select class="form-control w-100" name="product_layering">
						@php
							$product_layering_options = Config::get("templates.product_layering");
							$default = !empty($settings->product_layering) ? $settings->product_layering : $product_layering_options[0];
						@endphp
						@foreach($product_layering_options as $layering)
							<option {{ $default == $layering ? "selected" : "" }} value="{{ $layering }}">{{ $layering }}</option>
						@endforeach
					</select>
					<input type="text" name="product_custom_layering" value="{{ empty($settings->product_custom_layering) ? '1 2 3' : $settings->product_custom_layering }}" class="form-control w-50 ml-2 product_custom_layering {{ empty($settings->product_layering) || $settings->product_layering != 'Custom' ? 'd-none' : '' }}" />
				</div>
			</div>
			<div class="col-md-3 form-group">
				<label>Products Bottom Position</label>
				<input type="number" name="bottom_position" class="form-control" value="{{ empty($settings->bottom_position) ? '-20' : $settings->bottom_position }}">
			</div>
			<div class="col-md-3 form-group">
				<label>Products Left/Right Position</label>
				<div class="d-inline">
					<i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" title="Adjust images position left/right. Negative values move images left. Positive moves them right."></i>
				</div>
				<input type="number" name="images_position" class="form-control" value="{{ empty($settings->images_position) ? '0' : $settings->images_position }}">
			</div>
		</div>
		@if (!empty($settings->output_dimensions) && ($settings->output_dimensions == 1))
		<div class="form-row-group new_discount d-none-parent">
			<label class="new-discount-label">New / Discount</label>
			<div class="form-row new-discount">
				<div class="col-12 form-group">
					<input type="text" name="subheadline[]" class="form-control" value="{{ empty($settings->subheadline) ? '' : $settings->subheadline[0] }}" placeholder="New / Discount">
				</div>
			</div>
		</div>
		@endif
		<div class="form-row-group d-none-parent">
			<label class="headline-label">Headline</label>&nbsp;
			<span class="badge badge-success character-count">
				@php
					$character_count = 0;
					if (!empty($settings->headline)) {
						$character_count += (strlen($settings->headline[0]) + strlen($settings->headline[1]) + strlen($settings->headline[2]));
					}
					if (!empty($settings->subheadline)) {
						$character_count += (strlen($settings->subheadline[0]) + strlen($settings->subheadline[1]));
					}
				@endphp
				{{ $character_count }} / 40
			</span>
			<span class="font-italic" style="font-size: 75%">total copy character count</span>
			<div class="form-row headline-1">
				<div class="col-12 form-group">
					<input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[0] }}" placeholder="Headline1">
				</div>
			</div>
			<div class="form-row headline-2 {{ !empty($settings->output_dimensions) && ($settings->output_dimensions == 2 || $settings->output_dimensions == 3 || $settings->output_dimensions == 5) ? "" : "d-none" }}">
				<div class="col-12 form-group">
					<input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[1] }}" placeholder="Headline2">
				</div>
			</div>
			<div class="form-row headline-3 {{ !empty($settings->output_dimensions) && ($settings->output_dimensions == 5) ? "" : "d-none" }}">
				<div class="col-12 form-group">
					<input type="text" name="headline[]" class="form-control" value="{{ empty($settings->headline) ? "" : $settings->headline[2] }}" placeholder="Headline3">
				</div>
			</div>
		</div>
		<div class="form-row-group subheadline d-none-parent {{ empty($settings->output_dimensions) || ($settings->output_dimensions != 1 && $settings->output_dimensions != 2 && $settings->output_dimensions != 5) ? "" : "d-none" }}">
			<label class="subheadline-label">Sub-headline</label>
			<div class="form-row subheadline-1">
				<div class="col-12 form-group">
					<input type="text" name="subheadline[]" class="form-control" value="{{ empty($settings->subheadline) ? "" : $settings->subheadline[0] }}" placeholder="Subheadline1">
				</div>
			</div>
			<div class="form-row subheadline-2 {{ !empty($settings->output_dimensions) && ($settings->output_dimensions == 4) ? "" : "d-none" }}">
				<div class="col-12 form-group">
					<input type="text" name="subheadline[]" class="form-control" value="{{ empty($settings->subheadline) ? "" : $settings->subheadline[1] }}" placeholder="Subheadline2">
				</div>
			</div>
		</div>
		<div class="form-row d-none-parent">
			<div class="col-12 form-group">
				<label>T&C</label>
				<input type="text" name="CTA" class="form-control" value="{{ empty($settings->CTA) ? "" : $settings->CTA }}">
			</div>
		</div>
		<div class="form-row d-none-parent">
			<div class="col-md-3 form-group">
				<label>Background and Circle </label>
				<div class="two-color-picker">
					<select class="form-control two-color-picker" name="color_name">
						@php
							$default = !empty($settings->color_name) ? $settings->color_name : "blue";
						@endphp
						@foreach($color_names as $key => $value)
							<option {{ $default == $key ? "selected" : "" }} value="{{ $key }}">{{ $value }}</option>
						@endforeach
					</select>
					<span class="background-color {{ $default }}">
						<i class="circle-color"></i>
					</span>
				</div>
				<input type="hidden" name="background_color" value="{{ !empty($settings->background_color) ? $settings->background_color : "#b8dde1" }}">
				<input type="hidden" name="circle_color" value="{{ !empty($settings->circle_color) ? $settings->circle_color : "#05a4b4" }}">
			</div>
			<div class="col-md-2 form-group">
				<label>Circle Position</label>
				<select class="form-control" name="circle_position">
					@php
						$default = !empty($settings->circle_position) ? $settings->circle_position : "top";
					@endphp
					@foreach($circle_positions as $position)
						<option {{ $default == $position ? "selected" : "" }} value="{{ $position }}">{{ $position }}</option>
					@endforeach
				</select>
			</div>
			@php
				$show_text_tracking = empty($settings->show_text_tracking) ? "" : $settings->show_text_tracking;
			@endphp
			@if ($show_text_tracking == "on")
				<div class="col-md-2 col-sm-4 col-6 form-group">
					<label>Text tracking</label>
					<input type="number" name="text_tracking" class="form-control" value="{{ empty($settings->text_tracking) ? "-10" : $settings->text_tracking }}">
				</div>
			@endif
			<div class="col-md-2 col-sm-4 col-6 form-group d-none">
				<label>Drop Shadow</label>
				<select name="drop_shadow" class="form-control">
					@php
						$default = !empty($settings->drop_shadow) ? $settings->drop_shadow : "left";
					@endphp
					@foreach($drop_shadows as $shadow)
						<option {{ $default == $shadow ? "selected" : "" }} value="{{ $shadow }}">{{ $shadow }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="form-row d-none-parent">
			<div class="col-md-6 col-sm-4 col-12 form-group">
				<label>Output file name (optional)</label>
				<input type="text" name="output_filename" class="form-control" value="{{ empty($settings->output_filename) ? "" : $settings->output_filename }}">
			</div>
			<div class="col-md-2 col-sm-4 col-6 form-group">
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
        <canvas id="canvas" width="1460" height="300"></canvas>
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
	<script type="text/javascript" src="{{ asset('js/create_AmazonFresh.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/preview/amazonfresh.js') }}"></script>
@endpush