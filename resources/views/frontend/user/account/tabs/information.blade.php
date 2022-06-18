<x-forms.patch :action="route('frontend.user.profile.update')">
    <div class="form-group row">
        <label for="first_name" class="col-md-3 col-form-label text-md-right">@lang('First Name')</label>

        <div class="col-md-9">
            <input type="text" name="first_name" class="form-control" placeholder="{{ __('First Name') }}" value="{{ old('first_name') ?? $logged_in_user->first_name }}" required autofocus autocomplete="first_name" />
        </div>
    </div><!--form-group-->

    <div class="form-group row">
        <label for="last_name" class="col-md-3 col-form-label text-md-right">@lang('Last Name')</label>

        <div class="col-md-9">
            <input type="text" name="last_name" class="form-control" placeholder="{{ __('Last Name') }}" value="{{ old('last_name') ?? $logged_in_user->last_name }}" required autofocus autocomplete="last_name" />
        </div>
    </div><!--form-group-->

    <div class="form-group row">
        <label for="customer_id" class="col-md-3 col-form-label text-md-right">@lang('Default Customer')</label>
        <div class="col-md-9">
            <select name="customer_id" class="form-control">
                @foreach ($customers as $c)
                    <option {{ $logged_in_user->customer_id == $c->id ? "selected" : "" }} value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div><!--form-group-->

    <div class="form-group row">
        <label class="form-check-label col-md-3 text-md-right" for="is_download_draft">Save Draft - Download</label>
        <div class="col-md-9">
            <input type="checkbox"id="is_download_draft" name="is_download_draft" {{ (!empty($logged_in_user->is_download_draft) && $logged_in_user->is_download_draft) ? "checked" : "" }} />
        </div>
    </div>

    <div class="form-group row">
        <label class="form-check-label col-md-3 text-md-right" for="is_download_project">Save Project - Download</label>
        <div class="col-md-9">
            <input type="checkbox" id="is_download_project" name="is_download_project" {{ (!empty($logged_in_user->is_download_project) && $logged_in_user->is_download_project) ? "checked" : "" }} />
        </div>
    </div>

    @if ($logged_in_user->canChangeEmail())
        <div class="form-group row">
            <label for="email" class="col-md-3 col-form-label text-md-right">@lang('E-mail Address')</label>

            <div class="col-md-9">
                <x-utils.alert type="info" class="mb-3" :dismissable="false">
                    <i class="fas fa-info-circle"></i> @lang('If you change your e-mail you will be logged out until you confirm your new e-mail address.')
                </x-utils.alert>

                <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('E-mail Address') }}" value="{{ old('email') ?? $logged_in_user->email }}" required autocomplete="email" />
            </div>
        </div><!--form-group-->
    @endif

    <div class="form-group row mb-0">
        <div class="col-md-12 text-right">
            <button class="btn btn-sm btn-primary float-right" type="submit">@lang('Update')</button>
        </div>
    </div><!--form-group-->
</x-forms.patch>
