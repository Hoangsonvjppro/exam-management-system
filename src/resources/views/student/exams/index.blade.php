<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Danh Sách Bài Thi</h2>
                <p class="text-[13px] text-[#6B7C99]">Bài thi từ các lớp học phần bạn đang theo học.</p>
            </div>

            @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] text-[13px] rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Bài thi đang mở --}}
            @if($available->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-[15px] font-bold text-[#1A3A6B] mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#10B981]"></span> Đang mở
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($available as $schedule)
                    <a href="{{ route('student.exams.show', $schedule->id) }}" class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 hover:shadow-md transition-shadow group">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-[14px] font-bold text-[#1A3A6B] group-hover:text-[#185FA5] transition-colors">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#065F46]">Đang mở</span>
                        </div>
                        <p class="text-[12px] text-[#6B7C99] mb-2">{{ $schedule->courseSection->name ?? '—' }}</p>
                        <div class="flex items-center gap-3 text-[11.5px] text-[#6B7C99]">
                            <span>⏱ {{ $schedule->exam->duration_minutes }} phút</span>
                            @if($schedule->end_time)
                            <span>📅 Hết hạn: {{ $schedule->end_time->format('d/m H:i') }}</span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Bài thi sắp tới --}}
            @if($upcoming->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-[15px] font-bold text-[#1A3A6B] mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#3B82F6]"></span> Sắp tới
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcoming as $schedule)
                    <div class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 opacity-80">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-[14px] font-bold text-[#1A3A6B]">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full bg-[#EBF2FA] text-[#1A3A6B]">Sắp tới</span>
                        </div>
                        <p class="text-[12px] text-[#6B7C99] mb-2">{{ $schedule->courseSection->name ?? '—' }}</p>
                        <div class="flex items-center gap-3 text-[11.5px] text-[#6B7C99]">
                            <span>⏱ {{ $schedule->exam->duration_minutes }} phút</span>
                            @if($schedule->start_time)
                            <span>📅 Mở: {{ $schedule->start_time->format('d/m H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Bài thi đã kết thúc --}}
            @if($ended->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-[15px] font-bold text-[#6B7C99] mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#9CA3AF]"></span> Đã kết thúc
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($ended as $schedule)
                    <a href="{{ route('student.exams.result', $schedule->id) }}" class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 opacity-60 hover:opacity-80 transition-opacity">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-[14px] font-bold text-[#374151]">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[11px] font-medium px-2.5 py-0.5 rounded-full bg-[#F3F4F6] text-[#6B7C99]">Kết thúc</span>
                        </div>
                        <p class="text-[12px] text-[#6B7C99] mb-2">{{ $schedule->courseSection->name ?? '—' }}</p>
                        <div class="text-[11.5px] text-[#6B7C99]">
                            Kết thúc: {{ $schedule->end_time->format('d/m/Y H:i') }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($upcoming->isEmpty() && $available->isEmpty() && $ended->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#D6E2F0]">
                    <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Chưa có bài thi nào</h4>
                <p class="text-[13px] text-[#6B7C99]">Bạn chưa được giao bài thi nào. Hãy kiểm tra lại lớp học phần.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
