@extends('frontend.layouts.app')

@section('title', __('File'))

@section('content')
    <div class="d-none" id="product-images"></div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    @lang('File Browser')
                </x-slot>

                <x-slot name="headerActions">
                    <div class="file-operations">
                        <a class="card-header-action" id="file-operations" href="#">
                            <i class="c-icon cil-file"></i>
                            File Operations
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
                    <div class="search-block">
                        <input type="text" id="index-search-input" placeholder="Search... (file name, product name and brand)">
                    </div>
                    <div class="table-responsive">
                        <table id="files-index-table"  class="display table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>Status</th>
                                    <th>
                                        <input type="checkbox" id="global-download">
                                        <label for="global-download">Select</label>
                                        <button type="button" class="close" id="global-download-unselect" aria-label="Close">
                                            <span aria-hidden="true" class="text-warning">&times; Clear all selections</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <!-- <tbody></tbody> -->
                        </table>
                    </div>
                    <button id="export" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Exporting file list...">Export Filelist</button>
                    <button id="preview" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Preview Files" data-text="Preview Files">Preview Files</button>
                    <button id="create-ads" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Create" data-text="Create">Create</button>
                    <div class="download-block">
                        <p></p>
                        <button id="download" type="button" class="btn btn-primary" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Processing Files" data-text="Download">Download</button>
                    </div>
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->
@endsection

@push('after-scripts')
	<script type="text/javascript" src="{{ asset('js/index.js') }}"></script>
@endpush