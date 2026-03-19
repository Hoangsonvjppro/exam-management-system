@props([
    'type' => 'info',
])

@php
    $types = [
        'info'    => 'bg-navy-50 text-navy-600',
        'success' => 'bg-teal-50 text-teal-800',
        'warning' => 'bg-amber-50 text-amber-600',
        'danger'  => 'bg-red-50 text-red-600',
        'neutral' => 'bg-surface-1 text-navy-900 border-[0.5px] border-border-clean',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-[10px] py-[3px] rounded-full text-[11px] font-medium ' . ($types[$type] ?? $types['info'])]) }}>
    {{ $slot }}
</span>
