@props([
'align' => 'left',
])

@php
$alignment = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left',
};
@endphp

<td {{ $attributes->merge(['class' => "px-4 py-[9px] text-[12px] text-[#374151] border-b-[0.5px] border-[#ebf2fa] whitespace-nowrap $alignment"]) }}>
    {{ $slot }}
</td>