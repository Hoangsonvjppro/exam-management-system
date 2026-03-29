@props([
'align' => 'left',
'sortable' => false,
'sortKey' => null,
'sortDirection' => null,
])

@php
$alignment = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left',
};
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-2.5 text-[11px] font-semibold text-navy-900 uppercase tracking-[0.03em] bg-surface-1 border-b-[1.5px] border-border-clean $alignment"]) }}>
    @if($sortable)
    <button class="inline-flex items-center gap-1 group hover:text-navy-600 transition-colors">
        {{ $slot }}
        <svg class="w-3.5 h-3.5 text-text-muted group-hover:text-navy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
    </button>
    @else
    {{ $slot }}
    @endif
</th>