@extends('backend.layouts.app')

@section('title', __('Settings'))

@section('content')
    <x-backend.card>
        <x-slot name="header">
            @lang('Dashboard')
        </x-slot>

        <x-slot name="body">
            <div class="form-row">
                <div class="col-md-9 file-input-group">
                    <div class="form-group">
                        <x-utils.link
                            :href="route('admin.auth.settings.updatefile.export_file_list', ['type' => 'all'])"
                            :text="number_format($file_all)" /> items
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-3">
                    <div class="form-group">
                        <x-utils.link
                            :href="route('admin.auth.settings.updatefile.export_file_list', ['type' => 'dimensions'])"
                            :text="number_format($file_with_dimensions)" /> with dimensions
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-utils.link
                            :href="route('admin.auth.settings.updatefile.export_file_list', ['type' => 'no_dimensions'])"
                            :text="number_format($file_all - $file_with_dimensions)" /> without dimensions
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-utils.link
                            :href="route('admin.auth.settings.updatefile.export_file_list', ['type' => 'child'])"
                            :text="number_format($file_with_child)" /> with child id’s
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-utils.link
                            :href="route('admin.auth.settings.updatefile.export_file_list', ['type' => 'child_no_dimensions'])"
                            :text="number_format($file_with_child_no_dimensions)" /> with child ids but no dimensions
                    </div>
                </div>
            </div>
        </x-slot>

    </x-backend.card>

    <x-forms.post :action="route('admin.auth.settings.updatefile.mapping')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Mapping File')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-cloud-download"
                    :href="route('admin.auth.settings.updatefile.download_mapping')"
                    :text="__('Download Mapping Template')" />
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-data-transfer-down"
                    :href="route('admin.auth.settings.updatefile.export_mapping')"
                    :text="__('Export Mapping')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-control" name="action">
                                <option value="update">Update/add to existing mapping data</option>
                                <option value="delete">Delete all existing mapping data</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9 file-input-group">
                        <div class="form-group">
                            <input type="file" class="form-control-file" name="mapping" data-show-preview="false">
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Submit')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>
    
    <x-backend.card>
        <x-slot name="header">
            @lang('Data Loading History')
        </x-slot>
        <x-slot name="body">
            <livewire:data-loading-table />
        </x-slot>
    </x-backend.card>
    
    <x-forms.post :action="route('admin.auth.settings.updatefile.save_data_import_settings')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Data Import Settings')
            </x-slot>

            <x-slot name="body">
                <div class="one-time">
                    <div class="form-row">
                        <p class="font-weight-bold col-12">One Time</p>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 form-group">
                            <label for="onetime_import_image_type">Image Types</label>
                            @php 
                                $types = ["All images", "Only new/changed images"];
                            @endphp
                            <select id="onetime_import_image_type" name="onetime_import_image_type" class="form-control">
                                @for($i = 0; $i < count($types); $i ++)
                                    <option value="{{$i}}" {{ $settings['onetime_import_image_type'] == $i ? "selected" : "" }}>{{ $types[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 form-group">
                            <label for="onetime_product_image">Get Product images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="onetime_product_image_hidden" name="onetime_product_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="onetime_product_image" name="onetime_product_image" {{ $settings['onetime_product_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="onetime_product_image"></label>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="onetime_nf_image">Get Nutrition Facts images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="onetime_nf_image_hidden" name="onetime_nf_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="onetime_nf_image" name="onetime_nf_image" {{ $settings['onetime_nf_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="onetime_nf_image"></label>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="onetime_ingredient_image">Get Ingredients images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="onetime_ingredient_image_hidden" name="onetime_ingredient_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="onetime_ingredient_image" name="onetime_ingredient_image" {{ $settings['onetime_ingredient_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="onetime_ingredient_image"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="scheduled">
                    <div class="form-row">
                        <p class="font-weight-bold col-12">Scheduled</p>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 form-group">
                            <label for="scheduled_import_file_type">File Types</label>
                            @php 
                                $types = ["Import most recent file only", "Import new files", "Import all files (caution!)"];
                            @endphp
                            <select id="scheduled_import_file_type" name="scheduled_import_file_type" class="form-control">
                                @for($i = 0; $i < count($types); $i ++)
                                    <option value="{{$i}}" {{ $settings['scheduled_import_file_type'] == $i ? "selected" : "" }}>{{ $types[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="scheduled_column_url">Column URL</label>
                            <input type="text" id="scheduled_column_url" name="scheduled_column_url" class="form-control" value="{{ $settings['scheduled_column_url'] }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="scheduled_column_name">Column Name</label>
                            <input type="text" id="scheduled_column_name" name="scheduled_column_name" class="form-control" value="{{ $settings['scheduled_column_name'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 form-group">
                            <label for="scheduled_product_image">Get Product images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="scheduled_product_image_hidden" name="scheduled_product_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="scheduled_product_image" name="scheduled_product_image" {{ $settings['scheduled_product_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="scheduled_product_image"></label>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="scheduled_nf_image">Get Nutrition Facts images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="scheduled_nf_image_hidden" name="scheduled_nf_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="scheduled_nf_image" name="scheduled_nf_image" {{ $settings['scheduled_nf_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="scheduled_nf_image"></label>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="scheduled_ingredient_image">Get Ingredients images</label>
                            <div class="form-group ml-4">
                                <input type="hidden" class="custom-control-input" value="off" id="scheduled_ingredient_image_hidden" name="scheduled_ingredient_image" />
                                <input type="checkbox" class="custom-control-input" value="on" id="scheduled_ingredient_image" name="scheduled_ingredient_image" {{ $settings['scheduled_ingredient_image'] == 'on' ? "checked" : "" }} />
                                <label class="custom-control-label" for="scheduled_ingredient_image"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Save')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    <x-forms.post :action="route('admin.auth.settings.updatefile.upload_file')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Upload Image URLs')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-cloud-download"
                    :href="route('admin.auth.settings.updatefile.download_image_dimension')"
                    :text="__('Download Template')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-9 file-input-group">
                        <div class="form-group">
                            <input type="file" class="form-control-file" name="upload_file" data-show-preview="false">
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Upload')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    @php 
        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $scheduled_days = explode(',', $settings['scheduled_days_of_week']);
        $scheduled_days_name = [];
        foreach($scheduled_days as $day) {
            $scheduled_days_name[] = $days[$day];
        }
    @endphp
    <x-forms.post id="schedule-form" :action="route('admin.auth.settings.updatefile.update_schedule')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Scheduled Import') ({{ count($scheduled_days_name) ? implode(', ', $scheduled_days_name) : 'None' }})
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action btn-expand-contract"
                    icon="c-icon cil-plus"
                    :text="__('Expand')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <p class="font-weight-bold col-12">sftp settings</p>
                    <div class="col-md-3 form-group">
                        <label for="ftp_address">Ftp address</label>
                        <input type="text" id="ftp_address" name="ftp_address" class="form-control" value="{{ isset($settings['sftp_address']) ? $settings['sftp_address'] : '' }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="ftp_address">Ftp port</label>
                        <input type="text" id="ftp_port" name="ftp_port" class="form-control" value="{{ isset($settings['sftp_port']) ? $settings['sftp_port'] : '' }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="dir_path">Directory path</label>
                        <input type="text" id="dir_path" name="dir_path" class="form-control" value="{{ isset($settings['sftp_dir_path']) ? $settings['sftp_dir_path'] : '' }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-3 form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="{{ isset($settings['sftp_username']) ? $settings['sftp_username'] : '' }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <p class="font-weight-bold col-12">configure schedule</p>
                    <div class="col-md-3 form-group">
                        <label for="scheduled_days_of_week">Day(s) of week:</label>
                        <select multiple id="scheduled_days_of_week" name="scheduled_days_of_week[]" class="form-control" size=7 >
                            @for($i = 0; $i < 7; $i ++)
                                <option value="{{$i}}" {{ in_array(strval($i), $scheduled_days) ? "selected" : "" }}>{{ $days[$i] }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="scheduled_time">Time</label>
                        <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" value="{{ $settings['scheduled_time'] }}">
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="button" id="run_now">@lang('Run Now')</button>
                <button class="btn btn-sm btn-primary float-right mr-1" type="submit">@lang('Save')</button>
            </x-slot>
        </x-backend.card>
    </x-forms>

    <x-backend.card>
        <x-slot name="header">
            @lang('History')
        </x-slot>

        <x-slot name="body">
            <livewire:upload-file-table />
        </x-slot>

    </x-backend.card>

    <!-- Image Uploading Progress -->
    <div class="uploading-progress-pane" id="uploading_progress_pane">
        Uploading images to S3 
        (<span id="progress"></span>)
    </div>
@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/upload.js") }}"></script>
@endpush