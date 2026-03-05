@props(['label'])

<div>
    <div class="sidebar-section-title">{{ $label }}</div>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
