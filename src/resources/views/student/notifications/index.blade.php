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
                <x-ui-icon name="bell-slash" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                <p class="text-base text-navy-900 font-semibold mb-1">Bạn chưa có thông báo nào.</p>
                <p class="text-sm text-text-muted">Các thông báo từ giảng viên sẽ xuất hiện tại đây.</p>
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
                                <span class="text-[10px] text-text-muted font-bold uppercase tracking-tighter">
                                    {{ $notification->created_at->format('H:i - d/m/Y') }}
                                </span>
                            </div>
                            <h3 class="font-bold text-sm text-navy-900 mb-1 truncate">{{ $notification->title }}</h3>
                            <p class="text-text-muted text-xs line-clamp-2 md:max-w-3xl pr-4 leading-relaxed">{{ $notification->message }}</p>
                        </div>
                        <div class="shrink-0 mt-2 md:mt-0 self-start">
                            @php
                            $safeTitle = addslashes($notification->title);
                            $safeMessage = addslashes(str_replace(["\r", "\n"], ["", "\\n"], $notification->message));
                            $safeDate = $notification->created_at->format('d/m/Y H:i');
                            $safeClass = addslashes($className);
                            @endphp
                            <x-button variant="outline" class="!px-3 !py-1.5" x-on:click="
                                            currentTitle = '{{ $safeTitle }}';
                                            currentMessage = '{{ $safeMessage }}';
                                            currentDate = '{{ $safeDate }}';
                                            currentClass = '{{ $safeClass }}';
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
                    <div class="mb-6">
                        <span x-text="currentClass" class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 border-[0.5px] border-blue-200 rounded-[4px] text-[10px] font-bold uppercase tracking-wider mb-2"></span>
                        <h3 x-text="currentTitle" class="text-xl font-bold text-navy-900 leading-tight"></h3>
                    </div>
                    </div>
                    <button x-on:click="modalOpen = false" class="text-text-muted hover:text-navy-900 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto whitespace-pre-wrap text-navy-900 text-sm leading-relaxed" x-text="currentMessage">
                </div>
                <div class="py-3 px-6 border-t-[0.5px] border-border-clean text-xs font-medium text-text-muted text-right shrink-0 bg-surface-0">
                    Đã gửi lúc: <span x-text="currentDate" class="font-semibold text-navy-900 ml-1"></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>