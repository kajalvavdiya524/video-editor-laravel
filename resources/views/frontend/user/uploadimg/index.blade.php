@extends('frontend.layouts.app')

@section('title', __('Upload Images'))

@section('content')

    <div class="loadingOverlay">
        <div class="loadingOverlay-inner">
            <span class="fa fa-spinner fa-spin"></span>
            <br/>
            Please Wait A Moment...
        </div>
    </div>

    <x-forms.post :action="route('frontend.file.uploadimg.upload_images')" id="form-file-upload" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Upload Images')
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-6 file-input-group">
                        <div class="form-group">
                            <input type="file" class="form-control-file" name="upload_images[]" data-browse-on-zone-click="true" multiple />
                        </div>
                    </div>
                    <div class="col-md-6 form-group file-url-input-group">
                        <label>Upload from Web - Name (optional) and URL</label>
                        <textarea class="form-control" name="upload_images_url" style="height: 120px"></textarea>
                    </div>
                </div>
                <div class="form-row">
                    @if ($logged_in_user->isMasterAdmin())
                        <div class="col-md-3 col-sm-4 form-group">
                            <label>Company</label>
                            <select id ="company_id" name="company_id" class="form-control">
                                @foreach ($companies as $company)
                                <option value="{{$company->id}}">{{$company->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-3 col-sm-4 form-group">
                        <label>Image Type</label>
                        <select id="image_type" name="image_type" class="form-control">
                            <option value="product_image">Product Image</option>
                            <option value="stock_image">Stock Image</option>
                        </select>
                    </div>
                </div>
                <div class="progress d-none">
                    <div class="progress-bar" role="progressbar" style="width: 0;" aria-valuenow="1" aria-valuemin="1" aria-valuemax="100">0%</div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="form-group background-remove float-left">
                    <input type="checkbox" name="background_remove" id="background_remove" />
                    <label for="background_remove">Remove background</label>
                </div>
                <button class="btn btn-sm btn-primary float-right" id="file_upload_button" type="submit">@lang('Upload')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    <x-backend.card>
        <x-slot name="header">
            @lang('History')
        </x-slot>

        <x-slot name="body">
            <livewire:upload-image-table />
        </x-slot>
    </x-backend.card>

@endsection

@section('modals')
    <!-- Share Modal -->
    <div class="modal fade" id="editUploadImage" tabindex="-1" role="dialog" aria-labelledby="editUploadImageLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <x-forms.post :action="route('frontend.file.uploadimg.update_image')" enctype="multipart/form-data">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id" />
                        @if ($logged_in_user->isMasterAdmin())
                            <div class="form-row">
                                <div class="col-md-3 col-sm-4 form-group">
                                    <label>Company</label>
                                    <select name="company" id="company" class="form-control">
                                        @foreach ($companies as $company)
                                            <option value="{{$company->id}}">{{$company->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="col-md-3 col-sm-4 form-group">
                                <label>ASIN</label>
                                <input type="text" name="asin" id="asin" class="form-control" />
                            </div>
                            <div class="col-md-3 col-sm-4 form-group">
                                <label>UPC</label>
                                <input type="text" name="upc" id="upc" class="form-control" />
                            </div>
                            <div class="col-md-3 col-sm-4 form-group">
                                <label>GTIN</label>
                                <input type="text" name="gtin" id="gtin" class="form-control" />
                            </div>
                        </div>
                            
                        <div class="form-row">
                            <div class="col-md-3 col-sm-4 form-group">
                                <label>Width</label>
                                <input type="number" name="width" id="width" class="form-control" step="0.01" required/>
                            </div>
                            <div class="col-md-3 col-sm-4 form-group">
                                <label>Height</label>
                                <input type="number" name="height" id="height" class="form-control" step="0.01" required/>
                            </div>
                            <!-- <div class="col-md-3 col-sm-4 form-group">
                                <label>Depth</label>
                                <input type="number" name="depth" id="depth" class="form-control" step="0.01" required/>
                            </div> -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </x-forms.post>
            </div>
        </div>
    </div>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/uploadimg.js") }}"></script>
@endpush