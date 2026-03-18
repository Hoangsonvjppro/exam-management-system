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
                <div class="bg-white brutal-border brutal-shadow p-10 text-center">
                    <p class="text-slate-500 font-bold">Bạn chưa có thông báo nào.</p>
                </div>
            @else
                <div class="bg-white brutal-border brutal-shadow">
                    @foreach($notifications as $notification)
                        @php
                            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                            $className = $data['course_section_name'] ?? 'Hệ thống';
                        @endphp
                        <div class="p-5 border-b-2 border-dashed border-slate-300 last:border-b-0 hover:bg-slate-50 flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div>
                                <span class="inline-block px-2 py-1 bg-ems-primary/10 text-ems-primary text-[10px] font-black uppercase brutal-border mb-2 object-left">
                                    {{ $className }}
                                </span>
                                <h3 class="font-black text-lg mb-2 uppercase">{{ $notification->title }}</h3>
                                <p class="text-slate-600 font-semibold text-sm line-clamp-2 md:max-w-3xl mb-3">{{ $notification->message }}</p>
                                <p class="text-xs text-slate-400 font-bold">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="shrink-0 mt-2 md:mt-0">
                                <button x-on:click="
                                        currentTitle = '{{ addslashes($notification->title) }}';
                                        currentMessage = '{{ addslashes(str_replace(["\r", "\n"], ["", "\\n"], $notification->message)) }}';
                                        currentDate = '{{ $notification->created_at->format('d/m/Y H:i') }}';
                                        currentClass = '{{ addslashes($className) }}';
                                        modalOpen = true;
                                    "
                                    class="h-9 px-4 bg-white hover:bg-ems-primary hover:text-white text-black brutal-border brutal-btn font-black text-xs uppercase brutal-shadow">
                                    Chi tiết
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

        {{-- Detail Modal --}}
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div x-on:click.outside="modalOpen = false" class="bg-white brutal-border brutal-shadow-lg w-full max-w-2xl flex flex-col max-h-[90vh]">
                <div class="p-5 border-b-4 border-black flex items-center justify-between shrink-0 bg-ems-primary/5">
                    <div>
                        <span x-text="currentClass" class="text-[10px] font-black text-ems-primary uppercase tracking-widest mb-1 block"></span>
                        <h3 x-text="currentTitle" class="text-xl font-black uppercase"></h3>
                    </div>
                    <button x-on:click="modalOpen = false" class="text-3xl font-black leading-none hover:text-ems-primary self-start">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto whitespace-pre-wrap font-semibold text-slate-700 leading-relaxed text-sm" x-text="currentMessage">
                </div>
                <div class="p-4 border-t-4 border-black text-xs font-bold text-slate-500 text-right shrink-0 bg-slate-50">
                    Đã gửi lúc: <span x-text="currentDate"></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
