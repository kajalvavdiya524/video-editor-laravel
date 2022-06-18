@extends('backend.layouts.app')

@section('title', __('Positioning'))

@section('content')
    <x-forms.post :action="route('admin.auth.positioning.upload')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Positioning File Upload')
            </x-slot>
            
            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-data-transfer-down"
                    :href="route('admin.auth.positioning.export')"
                    :text="__('Download XLSX')" />
            </x-slot>
            
            <x-slot name="body">
                <div class="form-row">
                    <div class="col-md-12 file-input-group">
                        <div class="form-group">
                            <label>XLSX</label>
                            <input type="file" class="form-control-file" name="positioning" data-show-preview="false" required>
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
            @lang('Positioning Data')
        </x-slot>

        <x-slot name="body">
            <div class="form-row">
            </div>
        </x-slot>
    </x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/positioning.js') }}"></script>
@endpush