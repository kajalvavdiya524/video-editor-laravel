<div class="modal fade" id="downloadXlsxModal" tabindex="-1" role="dialog" aria-labelledby="downloadXlsxModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Download XLSX</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <x-forms.post :action="route('frontend.banner.download_xlsx_output', $customer_id)" id="download-xlsx-form">
                <div class="modal-body">
                    <div class="template-list">
                        @foreach($templates as $template)
                            <div class="form-group d-flex">
                                <div class="custom-control custom-checkbox">
                                    <input checked type="checkbox" id="template_{{ $template['id'] }}" class="custom-control-input">
                                    <label class="custom-control-label" for="template_{{ $template['id'] }}">{{ $template['name'] }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="template_settings" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="download-xlsx-output">Download</button>
                </div>
            </x-forms.post>
        </div>
    </div>
</div>