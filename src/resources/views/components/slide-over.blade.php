@props(['name', 'title' => '', 'maxWidth' => 'xl'])

@php
$maxWidthClass = [
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-xl';
@endphp

<div x-data="{ 
        show: false, 
        name: '{{ $name }}' 
    }"
    x-show="show"
    @open-slide-over.window="if ($event.detail === name) { show = true; document.body.classList.add('overflow-hidden'); }"
    @close-slide-over.window="if ($event.detail === name) { show = false; document.body.classList.remove('overflow-hidden'); }"
    @keydown.escape.window="if (show) { show = false; document.body.classList.remove('overflow-hidden'); }"
    class="relative z-50"
    style="display: none;"
    aria-labelledby="slide-over-title-{{ $name }}" role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="show" 
         x-transition:enter="ease-in-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in-out duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-navy-950/40 backdrop-blur-sm transition-opacity" 
         @click="show = false; document.body.classList.remove('overflow-hidden');"></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                {{-- Panel --}}
                <div x-show="show" 
                     x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full" 
                     class="pointer-events-auto w-screen {{ $maxWidthClass }}">
                    
                    <div class="flex h-full flex-col bg-white shadow-2xl rounded-l-2xl">
                        {{-- Header --}}
                        <div class="px-5 py-4 sm:px-6 border-b border-border-clean flex items-center justify-between bg-surface-0 rounded-tl-2xl">
                            <h2 class="text-[17px] font-bold text-navy-900" id="slide-over-title-{{ $name }}">{{ $title }}</h2>
                            <button type="button" 
                                    @click="show = false; document.body.classList.remove('overflow-hidden');" 
                                    class="rounded-md p-1.5 text-text-muted hover:text-navy-900 hover:bg-surface-1 focus:outline-none transition-colors">
                                <span class="sr-only">Đóng</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Body --}}
                        <div class="relative flex-1 px-5 sm:px-6 py-5 overflow-y-auto">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
