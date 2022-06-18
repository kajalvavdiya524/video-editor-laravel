@if (! $logged_in_user->isMember())
    <x-utils.view-button :href="route('admin.auth.team.show', $team)" />
    <x-utils.edit-button :href="route('admin.auth.team.edit', $team)" />
    <x-utils.delete-button :href="route('admin.auth.team.destroy', $team)" />
@endif
