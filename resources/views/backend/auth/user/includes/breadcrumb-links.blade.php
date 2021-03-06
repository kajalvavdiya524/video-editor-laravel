<x-utils.link
    class="c-subheader-nav-link"
    :href="route('admin.auth.user.deactivated')"
    :text="__('Deactivated Users')"
    permission="access.user.reactivate" />

@if (!$logged_in_user->isMember())
    <x-utils.link class="c-subheader-nav-link" :href="route('admin.auth.user.deleted')" :text="__('Deleted Users')" />
@endif
