@props(['active' => false, 'icon' => null])

@php
    $classes = $active
        ? 'sidebar-link active'
        : 'sidebar-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="flex-shrink-0 w-5 h-5">
            {{ $icon }}
        </span>
    @endif
    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>
