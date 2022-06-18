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
        <div class="d-none-parent">
            <label>
                UPC / GTIN / ASIN / TCIN / WMT-ID
				<span class="{{ empty($settings->file_ids) ? 'isDisabled' : '' }}"><a href="#" id="view-img" class="{{ empty($settings->file_ids) ? 'disabled' : '' }}">Edit Images</a></span>
            </label>
            <div class="form-row">
                <div class="col-12 form-group">
                    <input type="text" name="file_ids" id="file_ids" class="form-control" autofocus value="{{ empty($settings->file_ids) ? "" : $settings->file_ids }}">
                </div>
            </div>
        </div>
        <div class="d-none-parent">
            <div>
                <a href="#" id="toggleOptionalText">+ Show Optional Text Fields</a>
            </div>
            <div class="form-row image-selector">
                <div class="col-md-3 form-group">
                    <label>Select Image</label>
                    <select id="images" class="form-control">
                    @for ($i = 0; $i < count($settings->images); $i ++)
                        <option value="{{$i}}">{{$settings->images[$i]}}</option>
                    @endfor
                    </select>
                </div>
                <div class="col-md-3 ml-5 form-group">
                    <div class="form-check use-prev-text">
                        <input type="checkbox" class="form-check-input" id="use_prev_text" name="use_prev_text"/>
                        <label for="use_prev_text">Same text as previous image</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="headline d-none-parent">
            <div>
                <label>Top Headline</label>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="top_headline" id="top_headline" class="form-control" value="">
                    </div>
                    <div class="col-md-3 form-group">
                        <input type="number" name="top_head_size" id="top_head_size" class="form-control" value="60">
                    </div>
                    <div class="col-md-3 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="top_head_color" id="top_head_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" id="top_head_color" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label>Top Subheadline</label>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="top_subheadline" id="top_subheadline" class="form-control" value="">
                    </div>
                    <div class="col-md-3 form-group">
                        <input type="number" name="top_subhead_size" id="top_subhead_size" class="form-control" value="40">
                    </div>
                    <div class="col-md-3 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="top_subhead_color" id="top_subhead_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" id="top_subhead_color" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label>Bottom Headline</label>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="bottom_headline" id="bottom_headline" class="form-control" value="">
                    </div>
                    <div class="col-md-3 form-group">
                        <input type="number" name="bottom_head_size" id="bottom_head_size" class="form-control" value="60">
                    </div>
                    <div class="col-md-3 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="bottom_head_color" id="bottom_head_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" id="bottom_head_color" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label>Bottom Subheadline</label>
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="bottom_subheadline" id="bottom_subheadline" class="form-control" value="">
                    </div>
                    <div class="col-md-3 form-group">
                        <input type="number" name="bottom_subhead_size" id="bottom_subhead_size" class="form-control" value="40">
                    </div>
                    <div class="col-md-3 form-row">
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="text" name="bottom_subhead_color" id="bottom_subhead_color_hex" class="form-control color-hex" placeholder="Color Hex Code" value="#000000">
                        </div>
                        <div class="col-md-6 col-sm-6 form-group">
                            <input type="color" id="bottom_subhead_color" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="form-row d-none-parent">
            <div class="col-md-2 col-sm-3 form-group">
				<label>Display Time (seconds)</label>
                <input type="number" name="duration" id="duration" class="form-control" value="{{ empty($settings->duration) ? "3" : $settings->duration }}">
			</div>
            <div class="col-md-2 col-sm-3 form-group">
				<label>Fade Type</label>
				<select name="fade_type" class="form-control">
					<option value="cut" {{ (!empty($settings->fade_type) && $settings->fade_type == "cut") ? "selected" : "" }}>Cut</option>
					<option value="dissolve" {{ (!empty($settings->fade_type) && $settings->fade_type == "dissolve") ? "selected" : "" }}>Dissolve</option>
				</select>
			</div>
			<div class="col-md-4 col-sm-4 form-group">
                <label>Output file name (optional)</label>
                <input type="text" name="output_filename" class="form-control" value="{{ empty($settings->output_filename) ? "" : $settings->output_filename }}">
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
	
	<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Video</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
					<video width="100%" controls autoplay>
						<source src="" type="video/mp4">
					</video>
                </div>
                <div class="modal-footer">
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
	<script type="text/javascript" src="{{ asset('js/video.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/create.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
@endpush