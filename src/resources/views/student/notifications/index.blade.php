<x-app-layout>
    @section('title', 'Thông báo — EMS')
    @section('page-title', 'Thông báo của tôi')

    <div x-data="{ 
        modalOpen: false, 
        currentTitle: '', 
        currentMessage: '', 
        currentDate: '', 
        currentClass: '' 
    }">
        <div class="space-y-6">
            @if($notifications->isEmpty())
                <div class="bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[10px] p-12 text-center">
                    <svg class="w-12 h-12 text-blue-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-[15px] text-navy-900 font-semibold mb-1">Bạn chưa có thông báo nào.</p>
                    <p class="text-[13px] text-text-muted">Các thông báo từ giảng viên sẽ xuất hiện tại đây.</p>
                </div>
            @else
                <x-card class="overflow-hidden">
                    <div class="divide-y-[0.5px] divide-border-clean">
                        @foreach($notifications as $notification)
                            @php
                                $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                $className = $data['course_section_name'] ?? 'Hệ thống';
                            @endphp
                            <div class="p-5 hover:bg-surface-0 transition-colors flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 border-[0.5px] border-blue-200 rounded-[4px] text-[10px] font-bold uppercase tracking-wider">
                                            {{ $className }}
                                        </span>
                                        <span class="text-[11px] text-text-muted font-medium">{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <h3 class="font-bold text-[16px] text-navy-900 mb-1 truncate">{{ $notification->title }}</h3>
                                    <p class="text-text-muted text-[13px] line-clamp-2 md:max-w-3xl pr-4">{{ $notification->message }}</p>
                                </div>
                                <div class="shrink-0 mt-2 md:mt-0 self-start">
                                    <x-button variant="outline" class="!px-3 !py-1.5" x-on:click="
                                            currentTitle = '{{ addslashes($notification->title) }}';
                                            currentMessage = '{{ addslashes(str_replace(["\r\", \"\n\"], [\"\", \"\\n\"], $notification->message)) }}';
                                            currentDate = '{{ $notification->created_at->format('d/m/Y H:i') }}';
                                            currentClass = '{{ addslashes($className) }}';E
                                            modalOpen = true;
                                        ">
                                        Xem chi tiết
                                    </x-button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

        {{-- Detail Modal --}}
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-900/40 backdrop-blur-sm" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div x-on:click.outside="modalOpen = false" class="bg-white border-[0.5px] border-border-clean rounded-[10px] shadow-sm w-full max-w-2xl flex flex-col max-h-[90vh] overflow-hidden">
                <div class="p-6 border-b-[0.5px] border-border-clean flex items-start justify-between shrink-0 bg-surface-0">
                    <div>
                        <span x-text="currentClass" class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 border-[0.5px] border-blue-200 rounded-[4px] text-[10px] font-bold uppercase tracking-wider mb-2"></span>
                        <h3 x-text="currentTitle" class="text-[18px] font-bold text-navy-900 leading-tight"></h3>
                    </div>
                    <button x-on:click="modalOpen = false" class="text-text-muted hover:text-navy-900 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto whitespace-pre-wrap text-navy-900 text-[14px] leading-relaxed" x-text="currentMessage">
                </div>
                <div class="py-3 px-6 border-t-[0.5px] border-border-clean text-[12px] font-medium text-text-muted text-right shrink-0 bg-surface-0">
                    Đã gửi lúc: <span x-text="currentDate" class="font-semibold text-navy-900"></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
