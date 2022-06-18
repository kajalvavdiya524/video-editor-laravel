@extends('frontend.layouts.app')

@section('title', __('File'))

@section('content')
    <div class="d-none" id="product-images"></div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    <a href="#" class="card-header-action" id="product-image-tab">Product Images</a> | 
                    <a href="#" class="card-header-action" id="background-image-tab">Background Images</a>
                </x-slot>

                <x-slot name="headerActions">
                    <div class="file-operations">
                        <a class="card-header-action" id="file-operations" href="#">
                            <i class="c-icon cil-file"></i>
                            Actions
                        </a>
                        <ul>
                            <li id="reindex" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Reindexing files...">Reindex Files</li>
                            @if ($logged_in_user->isMasterAdmin()) 
                                <li id="generate-thumbnail"  data-loading-text="<i class='fa fa-spinner fa-spin'></i> Generate Thumbnails" data-text="Generate Thumbnails">Generate Thumbnails</li>
                                <li id="re-generate-thumbnail" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Re-generate Thumbnails" data-text="Re-generate Thumbnails">Re-generate Thumbnails</li>
                            @endif
                        </ul>
                    </div> | 
                    <x-utils.link
                        class="card-header-action"
                        icon="c-icon cil-image-plus"
                        :href="route('frontend.file.index')"
                        :text="__('Image View')" /> | 
                    <x-utils.link
                        class="card-header-action"
                        icon="c-icon cil-list-rich"
                        :href="route('frontend.file.list')"
                        :text="__('List View')" />
                </x-slot>

                <x-slot name="body">
                    <button type="button" class="close" id="global-download-unselect" aria-label="Close">
                        <span aria-hidden="true" class="text-warning">&times; Clear all selections</span>
                    </button>
                    <div class="search-block float-none">
                        <input type="text" id="index-search-input" placeholder="Search... (Separate multiple files, products, brands with commas or spaces.)"/>
                    </div>
                    <div class="image-grid-responsive">
                        <div class="grid"></div>
                    </div>
                    <div class="image-grid-pagination"></div>
                    <div class="button-group">
                        <button id="export" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Exporting file list...">Export Filelist</button>
                        <button id="preview" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Preview Files" data-text="Preview Files">Preview Files</button>
                        <button id="create-ads" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Create" data-text="Create">Create</button>
                        <div class="download-block">
                            <p></p>
                            <button id="download" type="button" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Processing Files" data-text="Download">Download</button>
                        </div>
                    </div>
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->
@endsection

@section('modals')
    <!-- Select product view Modal -->
	<div class="modal fade" id="productViewModal" tabindex="-1" role="dialog" aria-labelledby="productViewModal" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="detail-view">
                        <div class="prev_next_group">
                            <a href="#" id="prev_product">Prev</a> | 
                            <a href="#" id="next_product">Next</a>
                        </div>
                        <div style="display: flex;">
                            <div class="product-info">
                                <table border="1" cellpadding="5" cellspacing="5" width="100%">
                                    <tr>
                                        <th>Product Name</th>
                                        <td id="product_name"></td>
                                    </tr>
                                    <tr>
                                        <th>Brand</th>
                                        <td id="brand"></td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <td id="category"></td>
                                    </tr>
                                    <tr>
                                        <th>Tags</th>
                                        <td id="tags"></td>
                                    </tr>
                                    <tr>
                                        <th>Primary Image Filename</th>
                                        <td id="primary_filename"></td>
                                    </tr>
                                    <tr>
                                        <th>ASIN</th>
                                        <td id="asin"></td>
                                    </tr>
                                    <tr>
                                        <th>UPC</th>
                                        <td id="upc"></td>
                                    </tr>
                                    <tr>
                                        <th>GTIN</th>
                                        <td id="gtin"></td>
                                    </tr>
                                    <!-- <tr>
                                        <th>Child Links</th>
                                        <td id="child_links"></td>
                                    </tr>
                                    <tr>
                                        <th>Child1</th>
                                        <td id="child1"></td>
                                    </tr>
                                    <tr>
                                        <th>Child1 Child Links</th>
                                        <td id="child1_child_links"></td>
                                    </tr>
                                    <tr>
                                        <th>Child2</th>
                                        <td id="child2"></td>
                                    </tr> -->
                                    <tr>
                                        <th>Width</th>
                                        <td id="width"></td>
                                    </tr>
                                    <tr>
                                        <th>Height</th>
                                        <td id="height"></td>
                                    </tr>
                                    <tr>
                                        <th>Depth</th>
                                        <td id="depth"></td>
                                    </tr>
                                </table>
                                <div class="image-edit-tools">
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
                            <div class="product-image">
                                <div class="thumbnail-images">
                                    <ul>
                                        <li class="selected">
                                            <img id="primary-thumbnail" src="" loading="lazy" />
                                        </li>
                                        <li>
                                            <img id="nf-thumbnail" src="" loading="lazy" />
                                        </li>
                                        <li>
                                            <img id="ingredient-thumbnail" src="" loading="lazy" />
                                        </li>
                                    </ul>
                                </div>
                                <div class="image-view">
                                    <img id="full-size-image" src="" loading="lazy"/>
                                    <img id="full-size-image_nf" src="" style="display: none;"/>
                                    <img id="full-size-image_ingredient" src="" style="display: none;"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="single-image" style="display: none;">
                        <a id="back-to-detail" href="#" style="display: block; float: left;">Back</a>
                        <img src="" id="single" loading="lazy" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="edit-product-data" style="display: none;">Edit</button>
                    <button type="button" class="btn btn-secondary" id="close-product-data" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-product-data" style="display: none;">Save</button>
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

@push('after-scripts')
	<script type="text/javascript" src="{{ asset('js/file_image.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cropper.js') }}"></script>
@endpush
