@props(['disabled' => false])

<div x-data="{ show: false }" class="relative w-full">
    <input 
        {{ $attributes->merge(['class' => 'w-full border-[1.5px] border-border-clean bg-white text-navy-900 placeholder-text-muted focus:border-navy-600 focus:ring-[3px] focus:ring-navy-50 rounded-[6px] shadow-none text-[13px] py-2 px-3 pr-10 disabled:bg-surface-1 disabled:cursor-not-allowed transition duration-150 outline-none font-sans']) }}
        :type="show ? 'text' : 'password'"
        @disabled($disabled)
    >
    <button 
        type="button" 
        class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted hover:text-navy-900 focus:outline-none transition-colors"
        @click="show = !show"
        tabindex="-1"
    >
        <span class="material-symbols-outlined text-[20px]" x-show="!show">visibility</span>
        <span class="material-symbols-outlined text-[20px]" x-show="show" x-cloak>visibility_off</span>
    </button>
</div>
