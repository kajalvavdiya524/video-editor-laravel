@extends('backend.layouts.app')

@section('title', __('Templates'))

@section('content')
    <x-forms.post :action="route('admin.auth.template.upload')" enctype="multipart/form-data">
        <x-backend.card>
            <x-slot name="header">
                @lang('Create Template from File')
            </x-slot>
            
            <x-slot name="headerActions">
                <x-utils.link
                    class="card-header-action"
                    icon="c-icon cil-data-transfer-down"
                    :href="route('admin.auth.template.export', ['customer_id' => 0, 'template' => 0])"
                    :text="__('Download Template')" />
            </x-slot>
            
            <x-slot name="body">
                <div class="form-row">
                    <div class="col-2">
                        <label class="font-weight-bold">Customer</label>
                        <div class="form-group">
                            <select class="form-control" name="customer_id">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-12 file-input-group">
                        <div class="form-group">
                            <label>XLSX</label>
                            <input type="file" class="form-control-file" name="templates" data-show-preview="false" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-12 file-input-group">
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" class="form-control-file" name="image" data-show-preview="false">
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
            @lang('Templates')
        </x-slot>

        <x-slot name="headerActions">
            <x-utils.link
                class="card-header-action"
                icon="c-icon cil-plus"
                :href="route('admin.auth.template.create', ['customer_id' => $customer_id])"
                :text="__('Create Template')" />
        </x-slot>

        <x-slot name="body">
            <div class="form-row">
                <div class="col-2">
                    <label class="font-weight-bold">Customer</label>
                    <div class="form-group">
                        <select class="form-control" name="customer" id="customer">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $customer_id == $customer->id ? 'selected': '' }}>{{ $customer->name }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <livewire:template-table customerId="{{ $customer_id }}" />
        </x-slot>
    </x-backend.card>
@endsection

@push("after-scripts")
<script type="text/javascript" src="{{ asset('js/template/main.js') }}"></script>
@endpush