@props([
    'padding' => true,
    'hoverable' => false,
    'variant' => 'default', // default, accent, featured
])

@php
    $variants = [
        'default'  => 'bg-white border-[0.5px] border-border-clean rounded-[10px]',
        'accent'   => 'bg-white border-[0.5px] border-border-clean border-t-[3px] border-t-navy-900 rounded-[10px]',
        'featured' => 'bg-surface-1 border-[0.5px] border-blue-200 rounded-[10px]',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => $variants[$variant]
        . ($padding ? ' p-4' : '')
        . ($hoverable ? ' transition-shadow duration-200 hover:shadow-card' : '')
]) }}>
    @isset($header)
        <div class="flex items-center justify-between mb-4 {{ $padding ? '' : 'px-4 pt-4' }}">
            <div>
                {{ $header }}
            </div>
            @isset($headerActions)
                <div class="flex items-center gap-2">
                    {{ $headerActions }}
                </div>
            @endisset
        </div>
    @endisset

    <div class="{{ !$padding && isset($header) ? 'px-4 pb-4' : '' }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t-[0.5px] border-border-clean mt-4 pt-4 {{ $padding ? '' : 'px-4 pb-4' }}">
            {{ $footer }}
        </div>
    @endisset
</div>
