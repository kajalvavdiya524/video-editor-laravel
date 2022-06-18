<div class="modal fade" id="companyModal" tabindex="-1" role="dialog" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="/banner/{{ $customer_id }}/group/assign">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer_id }}" />
                <input type="hidden" name="layout_id" value="0" />
                <div class="modal-header">
                    <h5 class="modal-title" id="companyModalLabel">Companies</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach ($companies as $company)
                    <div class="form-group d-flex">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="company_{{ $company->id }}" name="company_id_{{ $company->id }}" class="custom-control-input">
                            <label class="custom-control-label" for="company_{{ $company->id }}">{{ $company->name }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-save-grid">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>