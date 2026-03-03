@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border-border bg-white text-gray-800 placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm py-2.5 px-4 disabled:bg-surface-hover disabled:cursor-not-allowed transition duration-150']) }}>
