@if ($company->trashed() && $logged_in_user->isMasterAdmin())
    <x-utils.form-button
        :action="route('admin.auth.company.restore', $company)"
        method="patch"
        button-class="btn btn-info btn-sm"
        icon="fas fa-sync-alt"
        name="confirm-item"
    >
        @lang('Restore')
    </x-utils.form-button>

    @if (config('boilerplate.access.company.permanently_delete'))
        <x-utils.delete-button
            :href="route('admin.auth.company.permanently-delete', $company)"
            :text="__('Permanently Delete')" />
    @endif
@else
    @if ($logged_in_user->isAdmin())
        <x-utils.view-button :href="route('admin.auth.company.show', $company)" />
        <x-utils.edit-button :href="route('admin.auth.company.edit', $company)" />
    @endif

    @if (!$company->isActive())
        <x-utils.form-button
            :action="route('admin.auth.company.mark', [$company, 1])"
            method="patch"
            button-class="btn btn-primary btn-sm"
            icon="fas fa-sync-alt"
            name="confirm-item"
            permission="access.company.reactivate"
        >
            @lang('Reactivate')
        </x-utils.form-button>
    @endif

    @if ($logged_in_user->isAdmin())
        <x-utils.delete-button :href="route('admin.auth.company.destroy', $company)" />
    @endif

    @if (
        $logged_in_user->isAdmin() && // This is not the master admin
        $company->isActive() && // The account is active
        (
            $logged_in_user->can('access.company.deactivate')
        )
    )
        <div class="dropdown d-inline-block">
            <a class="btn btn-sm btn-secondary dropdown-toggle" id="moreMenuLink" href="#" role="button" data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                @lang('More')
            </a>

            <div class="dropdown-menu" aria-labelledby="moreMenuLink">

                    <x-utils.form-button
                        :action="route('admin.auth.company.mark', [$company, 0])"
                        method="patch"
                        name="confirm-item"
                        button-class="dropdown-item"
                        permission="access.company.deactivate"
                    >
                        @lang('Deactivate')
                    </x-utils.form-button>
            </div>
        </div>
    @endif
@endif
