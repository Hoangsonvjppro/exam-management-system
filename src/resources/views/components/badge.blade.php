@props([
    'type' => 'info',
])

@php
    $types = [
        'info'    => 'bg-primary-50 text-primary-700 ring-primary-500/20',
        'success' => 'bg-success-50 text-success-600 ring-success-500/20',
        'warning' => 'bg-warning-50 text-warning-600 ring-warning-500/20',
        'danger'  => 'bg-danger-50 text-danger-600 ring-danger-500/20',
        'neutral' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset ' . ($types[$type] ?? $types['info'])]) }}>
    {{ $slot }}
</span>
