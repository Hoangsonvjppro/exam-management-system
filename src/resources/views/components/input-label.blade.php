@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 mb-1']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-danger-500">*</span>
    @endif
</label>
