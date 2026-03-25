@props(['label'])

<div>
    <div class="sidebar-section-title overflow-hidden whitespace-nowrap transition-opacity duration-300" 
         :class="isExpanded ? 'opacity-100' : 'opacity-0'">
        {{ $label }}
    </div>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
