@props(['active' => '', 'text' => '', 'hide' => false, 'icon' => false, 'permission' => false, 'class' => ''])

@if ($permission)
    @if ($logged_in_user->can($permission))
        @if (!$hide)
            <a {{ $attributes->merge(['href' => '#', 'class' => $active . ' ' . $class]) }}>@if ($icon)<i class="{{ $icon }}"></i> @endif{{ $text }}</a>
        @endif
    @endif
@else
    @if (!$hide)
        <a {{ $attributes->merge(['href' => '#', 'class' => $active . ' ' . $class]) }}>@if ($icon)<i class="{{ $icon }}"></i> @endif{{ $text }}</a>
    @endif
@endif
