@props(['label'])

<div class="mb-4">
    <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-indigo-400">
        {{ $label }}
    </p>
    {{ $slot }}
</div>
