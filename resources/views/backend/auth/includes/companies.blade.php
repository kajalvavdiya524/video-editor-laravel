@if ($logged_in_user->isMasterAdmin())
    <div class="form-group row">
        <label for="companies" class="col-md-2 col-form-label">@lang('Companies')</label>

        <div class="col-md-10">
            <select name="company" id="companies" class="form-control">
                @forelse($companies as $company)
                    <option {{ isset($user) && $user->company_id == $company->id ? "selected" : "" }} value="{{ $company->id }}">{{ $company->name }}</option>
                @empty
                    <option value="0">@lang('There are no companies to choose from.')</option>
                @endforelse
            </select>
        </div>
    </div><!--form-group-->    
@else
    <input type="hidden" name="company" value="{{ $logged_in_user->company_id }}" />
@endif
