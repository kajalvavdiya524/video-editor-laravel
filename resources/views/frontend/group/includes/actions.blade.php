<x-utils.view-button :href="route('frontend.banner.group.show', ['customer_id' => $layout->customer_id, 'layout' => $layout])" />
<x-utils.link :href="route('frontend.banner.group.copy', ['customer_id' => $layout->customer_id, 'layout' => $layout])" text="Copy" class="btn btn-success btn-sm" icon="fas fa-copy"/>
@if (true || $layout->user_id == $logged_in_user->id)
    <x-utils.edit-button :href="route('frontend.banner.group.edit', ['customer_id' => $layout->customer_id, 'layout' => $layout])" />
    <x-utils.delete-button :href="route('frontend.banner.group.destroy', ['customer_id' => $layout->customer_id, 'layout' => $layout])" />
@endif
@if ($logged_in_user->isMasterAdmin())
    @php
        $company_ids = $layout->companies->map(function ($item, $key) {
            return (string)$item['id'];
        })->all();
        $data = implode(" ", $company_ids);
    @endphp
    <button
        class="btn btn-primary btn-sm btn-layout-assign"
        data-layout-id="{{ $layout->id }}"
        data-layout-companies="{{ $data }}"
    >
        Assign
    </button>
@endif