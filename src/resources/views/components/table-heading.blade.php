@props([
    'align' => 'left',
    'sortable' => false,
    'sortKey' => null,
    'sortDirection' => null,
])

@php
    $alignment = match($align) {
        'center' => 'text-center',
        'right'  => 'text-right',
        default  => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider $alignment"]) }}>
    @if($sortable)
        <button class="inline-flex items-center gap-1 group hover:text-gray-700 transition-colors">
            {{ $slot }}
            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
            </svg>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
