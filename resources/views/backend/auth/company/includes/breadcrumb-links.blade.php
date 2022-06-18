<x-utils.link
    class="c-subheader-nav-link"
    :href="route('admin.auth.company.deactivated')"
    :text="__('Deactivated Companies')"
    permission="access.company.reactivate" />

@if ($logged_in_user->isAdmin())
    <x-utils.link class="c-subheader-nav-link" :href="route('admin.auth.company.deleted')" :text="__('Deleted Companies')" />
@endif
