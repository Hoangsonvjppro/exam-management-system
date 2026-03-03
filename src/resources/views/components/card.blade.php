@props([
    'padding' => true,
    'hoverable' => false,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-card shadow-card border border-border/60'
        . ($padding ? ' p-6' : '')
        . ($hoverable ? ' hover:shadow-card-hover transition-shadow duration-200' : '')
]) }}>
    @isset($header)
        <div class="flex items-center justify-between mb-4 {{ $padding ? '' : 'px-6 pt-6' }}">
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

    <div class="{{ !$padding && isset($header) ? 'px-6 pb-6' : '' }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-border mt-4 pt-4 {{ $padding ? '' : 'px-6 pb-6' }}">
            {{ $footer }}
        </div>
    @endisset
</div>
