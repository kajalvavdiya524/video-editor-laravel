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

    <x-forms.post :action="route('admin.auth.settings.advanced.psd2png')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('PSD To PNG')
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-3">
                        <input type="checkbox" name="psd2png" id="psd2png" {{ isset($settings['psd2png']) && $settings['psd2png'] == 'on' ? 'checked' : '' }}>
                        <label for="psd2png">Convert PSD To PNG</label>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Submit')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    <x-forms.post :action="route('admin.auth.settings.advanced.dimension')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Dimensions File')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-cloud-download"
                    :href="route('admin.auth.settings.advanced.download_dimension')"
                    :text="__('Download Dimensions Template')" />
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-data-transfer-down"
                    :href="route('admin.auth.settings.advanced.export_dimension')"
                    :text="__('Export Dimensions')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-control" name="action">
                                <option value="update">Update/add to existing dimension data</option>
                                <option value="delete">Delete all existing dimension data</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9 file-input-group">
                        <div class="form-group">
                            <input type="file" class="form-control-file" name="dimension" data-show-preview="false">
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Submit')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

    <x-forms.post :action="route('admin.auth.settings.advanced.parent_child')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Parent-Child File')
            </x-slot>

            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-cloud-download"
                    :href="route('admin.auth.settings.advanced.download_parent_child')"
                    :text="__('Download Parent-Child Template')" />
                <x-utils.link
                class="card-header-action"
                icon="c-icon cil-data-transfer-down"
                :href="route('admin.auth.settings.advanced.export_parent_child')"
                :text="__('Export Parent-Child')" />
            </x-slot>

            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select class="form-control" name="action">
                                <option value="update">Update/add to existing parent-child data</option>
                                <option value="delete">Delete all existing parent-child data</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9 file-input-group">
                        <div class="form-group">
                            <input type="file" class="form-control-file" name="parent_child" data-show-preview="false">
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
            @lang('Product Selections')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link
                class="card-header-action"
                icon="c-icon cil-data-transfer-down"
                :href="route('admin.auth.settings.product_selection.generate_product_selections_report')"
                :text="__('Export Table')" />
        </x-slot>
        
        <x-slot name="body">
            <livewire:product-selection-table />
        </x-slot>

    </x-backend.card>

    <x-backend.card>
        <x-slot name="header">
            @lang('Created Ads Exceptions')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link
                class="card-header-action"
                icon="c-icon cil-data-transfer-down"
                :href="route('admin.auth.settings.exception.generate_exceptions_report')"
                :text="__('Export Table')" />
        </x-slot>
        
        <x-slot name="body">
            <livewire:exception-table />
        </x-slot>

    </x-backend.card>

    <x-forms.post :action="route('admin.auth.settings.advanced.notification_email')">
        <x-backend.card>
            <x-slot name="header">
                @lang('Email Notification')
            </x-slot>

            @if (! $logged_in_user->isMember()) 
            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-9 file-input-group">
                        <div class="form-group">
                            @php
                                if ($logged_in_user->isMasterAdmin()) {
                                    $email = empty($settings['Notification_Emails']) ? '' : $settings['Notification_Emails'];
                                } else {
                                    $email = empty($logged_in_user->company->notification_emails) ? '' : $logged_in_user->company->notification_emails;
                                }
                            @endphp
                            <input type="text" class="form-control" name="notification" placeholder="{{ __('Please input email addresses separating by commas') }}" value= "{{ $email }}" />
                        </div>
                    </div>
                </div>
            </x-slot>
            @endif
            
            <x-slot name="footer">
                <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Save')</button>
            </x-slot>
        </x-backend.card>
    </x-forms.post>

@endsection

@push("after-scripts")
	<script type="text/javascript" src="{{ asset("js/data.js") }}"></script>
@endpush