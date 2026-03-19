@props([
    'variant' => 'primary',
    'size' => 'md',
    'iconOnly' => false,
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-1.5 font-medium transition-opacity duration-150 hover:opacity-85 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary'   => 'bg-navy-900 text-white',
        'secondary' => 'bg-navy-50 text-navy-900',
        'outline'   => 'bg-transparent text-navy-900 border-[1.5px] border-navy-900',
        'ghost'     => 'bg-transparent text-navy-900 border-[1.5px] border-border-clean',
        'danger'    => 'bg-red-50 text-red-600',
        'success'   => 'bg-teal-50 text-teal-800',
        'warning'   => 'bg-amber-50 text-amber-600',
        'icon'      => 'bg-navy-50 text-navy-900',
    ];

    $sizes = [
        'xs'  => $iconOnly ? 'p-1.5 rounded-[5px]' : 'px-3 py-1.5 text-[11px] rounded-[5px]',
        'sm'  => $iconOnly ? 'p-2 rounded-[5px]'   : 'px-3 py-1.5 text-[11px] rounded-[5px]',
        'md'  => $iconOnly ? 'p-2 rounded-[6px]'   : 'px-[18px] py-2 text-[13px] rounded-[6px]',
        'lg'  => $iconOnly ? 'p-2.5 rounded-[8px]' : 'px-6 py-[11px] text-[15px] rounded-[8px]',
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
