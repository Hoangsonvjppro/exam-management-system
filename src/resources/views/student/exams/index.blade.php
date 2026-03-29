<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-[#1A3A6B] mb-1">Danh Sách Bài Thi</h2>
                <p class="text-sm text-[#6B7C99]">Bài thi từ các lớp học phần bạn đang theo học.</p>
            </div>


            {{-- Bài thi đang mở --}}
            @if($available->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-sm font-bold text-[#1A3A6B] mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <x-ui-icon name="play" class="w-4 h-4 text-[#10B981]" />
                    Đang mở
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($available as $schedule)
                    <a href="{{ route('student.exams.show', $schedule->id) }}" class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 hover:shadow-md transition-shadow group flex flex-col">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-base font-bold text-[#1A3A6B] group-hover:text-[#185FA5] transition-colors line-clamp-1">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-100">Đang mở</span>
                        </div>
                        <p class="text-xs text-[#6B7C99] mb-4 flex items-center gap-1">
                            <x-ui-icon name="academic-cap" class="w-3.5 h-3.5" />
                            {{ $schedule->courseSection->name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-[#F1F5F9]">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-[#6B7C99] uppercase font-bold tracking-tighter">Hạn nộp</span>
                                <span class="text-xs font-bold text-[#1A3A6B]">{{ $schedule->end_datetime->format('H:i - d/m/Y') }}</span>
                            </div>
                            <x-ui-icon name="arrow-right" class="w-4 h-4 text-[#1A3A6B] group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Bài thi sắp tới --}}
            @if($upcoming->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-sm font-bold text-[#1A3A6B] mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <x-ui-icon name="clock" class="w-4 h-4 text-[#3B82F6]" />
                    Sắp tới
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcoming as $schedule)
                    <div class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 opacity-80 flex flex-col">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-base font-bold text-[#1A3A6B] line-clamp-1">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">Sắp tới</span>
                        </div>
                        <p class="text-xs text-[#6B7C99] mb-4 flex items-center gap-1">
                            <x-ui-icon name="academic-cap" class="w-3.5 h-3.5" />
                            {{ $schedule->courseSection->name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-[#F1F5F9]">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-[#6B7C99] uppercase font-bold tracking-tighter">Bắt đầu</span>
                                <span class="text-xs font-bold text-[#1A3A6B]">{{ $schedule->start_datetime->format('H:i - d/m/Y') }}</span>
                            </div>
                            <x-ui-icon name="lock-closed" class="w-4 h-4 text-[#6B7C99] opacity-50" />
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Bài thi đã kết thúc --}}
            @if($ended->isNotEmpty())
            <div class="mb-8 opacity-75">
                <h3 class="text-sm font-bold text-[#6B7C99] mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <x-ui-icon name="check-circle" class="w-4 h-4 text-[#9CA3AF]" />
                    Đã kết thúc
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($ended as $schedule)
                    <a href="{{ route('student.exams.result', $schedule->id) }}" class="bg-white rounded-[10px] border border-[#D6E2F0] p-5 opacity-60 hover:opacity-80 transition-opacity flex flex-col">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-base font-bold text-[#374151] line-clamp-1">{{ $schedule->exam->title }}</h4>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">Kết thúc</span>
                        </div>
                        <p class="text-xs text-[#6B7C99] mb-4 flex items-center gap-1">
                            <x-ui-icon name="academic-cap" class="w-3.5 h-3.5" />
                            {{ $schedule->courseSection->name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-[#F1F5F9]">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-[#6B7C99] uppercase font-bold tracking-tighter">Kết thúc</span>
                                <span class="text-xs font-bold text-gray-500">{{ $schedule->end_datetime->format('H:i - d/m/Y') }}</span>
                            </div>
                            <x-ui-icon name="arrow-right" class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($upcoming->isEmpty() && $available->isEmpty() && $ended->isEmpty())
            <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-border-clean/50">
                <x-ui-icon name="document-text" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                <h4 class="text-lg font-bold text-[#1A3A6B] mb-2">Chưa có bài thi nào</h4>
                <p class="text-sm text-[#6B7C99]">Bạn chưa được giao bài thi nào. Hãy kiểm tra lại lớp học phần.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
