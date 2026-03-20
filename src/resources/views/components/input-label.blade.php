@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block mb-1.5 text-[12px] font-medium text-navy-900']) }}>
    {{ $value ?? $slot }}
    @if($required)
    <span class="text-danger-500">*</span>
    @endif
</label>