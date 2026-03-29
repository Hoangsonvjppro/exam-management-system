@props(['active'])

@php
$classes = ($active ?? false)
? 'inline-flex items-center px-1 pt-1 border-b-[1.5px] border-navy-600 text-[13px] font-medium leading-5 text-navy-900 focus:outline-none transition duration-150 ease-in-out'
: 'inline-flex items-center px-1 pt-1 border-b-[1.5px] border-transparent text-[13px] font-medium leading-5 text-text-muted hover:text-navy-900 hover:border-blue-200 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>