@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border-[1.5px] border-border-clean bg-white text-navy-900 placeholder-text-muted focus:border-navy-600 focus:ring-[3px] focus:ring-navy-50 rounded-[6px] shadow-none text-[13px] py-2 px-3 disabled:bg-surface-1 disabled:cursor-not-allowed transition duration-150 outline-none font-sans']) }}>
