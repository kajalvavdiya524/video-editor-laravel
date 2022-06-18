@extends('frontend.layouts.app')

@section('title', __('Template | Group'))

@section('content')
<div class="alert alert-danger errors" role="alert" style="display: none;"></div>
<div class="alert alert-success success" role="alert" style="display: none;"></div>
<div class="d-none" id="preview-images"></div>
<div class="d-none" id="product-images"></div>
@if ($layout_template_id == -1)
    <x-forms.post
        :action="route('frontend.banner.group.store_instance', ['customer_id' => $customer->id, 'layout' => $layout_id, 'instance_id' => $instance_id])"
        enctype="multipart/form-data"
    >
        @include('frontend.group.template_view')
    </x-forms.post>
@else
    <x-forms.patch
        :action="route('frontend.banner.group.update_instance', ['customer_id' => $customer->id, 'layout' => $layout_id, 'instance_id' => $instance_id])"
        enctype="multipart/form-data"
    >
        @include('frontend.group.template_view')
    </x-forms.patch>
@endif
<div id="preview-popup">
    <div id="drag-handler">
        <span>Preview</span>
        <span class="toggle-button preview-control"><i class="cil-window-minimize"></i></span>
        <span class="edit-button edit preview-control"><i class="cil-pencil"></i></span>
        <span class="canvas-button psd preview-control"><img src="/images/canvas.png" /></span>
        <span class="reset-hero-button preview-control"><i class="cil-reload"></i></span>
        <span class="toggle-grid-button preview-control"><i class="cil-grid"></i></span>
        <span class="rotate-button preview-control"><i class="cil-crop-rotate"></i></span>
        <span class="safe-zone-button preview-control"><i class="cil-rectangle"></i></span>
        <ul class="safe-zone-list" style="display: none">
        </ul>
    </div>
    <div id="footer" class="pt-2 text-center">
        <label>X: </label>
        <input type="number" id="x_value" style="width: 60px">
    
        <label>Y: </label>
        <input type="number" id="y_value" style="width: 60px">
        
        <label>W: </label>
        <input type="number" id="w_value" style="width: 60px">
        
        <label>H: </label>
        <input type="number" id="h_value" style="width: 60px">
    </div>
</div>
@endsection

@section('modals')
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
                            <input type="checkbox" id="crop-fix-ratio" name="crop-fix-ratio">
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

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">Bulk Update</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Fields</th>
                            <th scope="col">Templates</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="chk_all_fields">
                                        <label class="form-check-label cursor-pointer" for="chk_all_fields">All</label>
                                    </div>
                                </div>
                                <div class="field-checkboxes">
                                    @foreach($template->fields as $field)
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input cursor-pointer" id="{{ $field->element_id }}" data-element-id="{{ $field->element_id }}">
                                            <label class="form-check-label cursor-pointer" for="{{ $field->element_id }}">{{ $field->name }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="chk_all_templates">
                                        <label class="form-check-label cursor-pointer" for="chk_all_templates">All</label>
                                    </div>
                                </div>
                                <div class="template-checkboxes">
                                    @foreach($template_instances as $key => $instance)
                                        @if ($instance['instance_id'] != $instance_id)
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="template_{{ $key }}" data-instance-id="{{ $instance['instance_id'] }}">
                                                <label class="form-check-label cursor-pointer" for="template_{{ $key }}">{{ $instance['name'] }}</label>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <p class="text-muted">
                    Important: Clicking "Update" will update values from the current template to other templates in this layout. This action cannot be undone.
                </p>
                <button type="button" id="bulk-update" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push("after-scripts")
<script>
    // lets not use this anymore for now...
    //var productTexts = JSON.parse({!! isset($settings->product_texts) ? json_encode($settings->product_texts) : "'{}'" !!});
    var productTexts = '';
    
    var positioningOptions = {!! isset($positioning_options) ? json_encode($positioning_options) : 'undefined' !!};
</script>
<script type="text/javascript" src="{{ asset('js/project_type.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/group/edit_template.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/new_template.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/preview/new_template.js') }}"></script>
@endpush
