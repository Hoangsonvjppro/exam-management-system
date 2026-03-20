@props(['active'])

@php
$classes = ($active ?? false)
? 'block w-full ps-3 pe-4 py-2 border-l-[3px] border-navy-600 text-start text-[13px] font-medium text-navy-900 bg-surface-1 focus:outline-none transition duration-150 ease-in-out'
: 'block w-full ps-3 pe-4 py-2 border-l-[3px] border-transparent text-start text-[13px] font-medium text-text-muted hover:text-navy-900 hover:bg-surface-1 hover:border-blue-200 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>