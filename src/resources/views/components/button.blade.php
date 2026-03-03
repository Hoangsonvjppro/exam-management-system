@props([
    'variant' => 'primary',
    'size' => 'md',
    'iconOnly' => false,
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition ease-in-out duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary'   => 'bg-primary-500 text-white border border-transparent shadow-sm hover:bg-primary-600 active:bg-primary-700 focus:ring-primary-500',
        'secondary' => 'bg-white text-gray-700 border border-border shadow-sm hover:bg-surface-hover hover:border-border-dark focus:ring-primary-500',
        'danger'    => 'bg-danger-500 text-white border border-transparent shadow-sm hover:bg-danger-600 active:bg-danger-600 focus:ring-danger-500',
        'success'   => 'bg-success-500 text-white border border-transparent shadow-sm hover:bg-success-600 active:bg-success-600 focus:ring-success-500',
        'warning'   => 'bg-warning-500 text-white border border-transparent shadow-sm hover:bg-warning-600 active:bg-warning-600 focus:ring-warning-500',
        'ghost'     => 'bg-transparent text-gray-600 border border-transparent hover:bg-surface-hover hover:text-gray-800 focus:ring-primary-500',
        'outline'   => 'bg-transparent text-primary-500 border border-primary-500 hover:bg-primary-50 focus:ring-primary-500',
    ];

    $sizes = [
        'xs'  => $iconOnly ? 'p-1.5'    : 'px-3 py-1.5 text-xs',
        'sm'  => $iconOnly ? 'p-2'      : 'px-4 py-2 text-sm',
        'md'  => $iconOnly ? 'p-2.5'    : 'px-5 py-2.5 text-sm',
        'lg'  => $iconOnly ? 'p-3'      : 'px-6 py-3 text-base',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
