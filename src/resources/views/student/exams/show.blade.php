<x-app-layout>
    @section('title', $exam->title)
    @section('page-title', 'Thông tin đề thi')

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-3 bg-teal-50 border-[0.5px] border-teal-200 rounded-[6px] font-medium text-teal-800 text-[13px] inline-block">
            {{ session('success') }}
        </div>
        @endif
        @if(session('info'))
        <div class="p-3 bg-blue-50 border-[0.5px] border-blue-200 rounded-[6px] font-medium text-blue-800 text-[13px] inline-block">
            {{ session('info') }}
        </div>
        @endif
        @if(session('error'))
        <div class="p-3 bg-red-50 border-[0.5px] border-red-200 rounded-[6px] font-medium text-red-800 text-[13px] inline-block">
            {{ session('error') }}
        </div>
        @endif

        {{-- Header đề thi --}}
        <x-card padding="true" variant="featured" class="text-center">
            <h2 class="text-[22px] md:text-[28px] font-bold text-navy-900 leading-tight mb-2">{{ $exam->title }}</h2>
            @if($exam->isPractice())
                <span class="inline-block px-3 py-1 bg-navy-50 text-navy-600 border border-blue-200 rounded-full text-[12px] font-semibold mb-4">
                    🎯 Đề Luyện Tập (Được thi nhiều lần)
                </span>
            @else
                <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[12px] font-semibold mb-4">
                    📝 Đề Chính Thức (Chỉ được thi 1 lần)
                </span>
            @endif
            
            <p class="text-[14px] text-text-muted mb-6 max-w-2xl mx-auto">{{ $exam->description }}</p>

            <div class="flex flex-wrap justify-center gap-4 md:gap-8 mb-8">
                <div class="text-center">
                    <span class="block text-[11px] font-medium text-text-muted mb-1">Thời gian làm bài</span>
                    <span class="text-[18px] font-bold text-navy-900">{{ $exam->duration_minutes }} Phút</span>
                </div>
                <div class="text-center">
                    <span class="block text-[11px] font-medium text-text-muted mb-1">Số câu hỏi</span>
                    <span class="text-[18px] font-bold text-navy-900">{{ $exam->questions->count() }} Câu</span>
                </div>
                <div class="text-center">
                    <span class="block text-[11px] font-medium text-text-muted mb-1">Điểm đạt</span>
                    <span class="text-[18px] font-bold text-navy-900">{{ number_format($exam->pass_points, 2) }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col items-center gap-3">
                @if($inProgressAttempt)
                    <p class="text-[13px] font-medium text-amber-600 mb-2">⚠️ Bạn đang có bài thi chưa hoàn thành (Lần {{ $inProgressAttempt->attempt_number }})!</p>
                    <a href="{{ route('student.exams.room', $schedule->id) }}" class="inline-flex items-center px-8 py-3 bg-amber-500 border border-transparent rounded-[8px] font-semibold text-[14px] text-white tracking-wide hover:bg-amber-600 transition shadow-sm">
                        ▶️ Tiếp tục làm bài
                    </a>
                @elseif($canStartNew)
                    <form action="{{ route('student.exams.start', $schedule->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-navy-900 border border-transparent rounded-[8px] font-semibold text-[14px] text-white tracking-wide hover:bg-navy-950 transition shadow-sm">
                            {{ $pastAttempts->isNotEmpty() ? '🔄 Thi lại lần ' . ($pastAttempts->first()->attempt_number + 1) : '▶️ Bắt đầu làm bài' }}
                        </button>
                    </form>
                @else
                    <p class="text-teal-600 font-bold text-[15px] mb-2">🎉 Bạn đã hoàn thành bài thi này!</p>
                @endif
                
                <a href="{{ route('student.dashboard') }}" class="text-[13px] text-blue-600 hover:underline">Về trang chủ</a>
            </div>
        </x-card>

        {{-- Lịch sử làm bài --}}
        @if($pastAttempts->isNotEmpty())
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-[17px] font-semibold text-navy-900">Lịch sử làm bài của bạn</h3>
            </x-slot>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-border-clean">
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Lần thi</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Trạng thái</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Điểm</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted">Nộp bài lúc</th>
                            <th class="text-center py-2 px-3 font-semibold text-text-muted"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastAttempts as $past)
                        <tr class="border-b border-border-clean hover:bg-surface-0 transition-colors">
                            <td class="py-3 px-3 text-center font-medium text-navy-900">Lần {{ $past->attempt_number }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($past->total_score >= $exam->pass_points)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-teal-50 text-teal-700 border-[0.5px] border-teal-200">ĐẠT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border-[0.5px] border-red-200">CHƯA ĐẠT</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $past->total_score >= $exam->pass_points ? 'text-teal-600' : 'text-red-500' }}">
                                {{ number_format($past->total_score, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center text-text-muted">{{ $past->completed_at?->format('H:i - d/m/Y') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($past->attempt_number === $pastAttempts->first()->attempt_number)
                                    {{-- Chỉ cho xem kết quả chi tiết của lần thi MỚI NHẤT (tránh nhầm lẫn do updateOrCreate answers ko lưu version attempt lịch sử) 
                                         * Note: Do cấu trúc DB StudentAnswer đang overwrite nếu user thi lại * --}}
                                    <a href="{{ route('student.exams.result', $schedule->id) }}" class="text-blue-600 hover:underline text-[12px] font-medium">Chi tiết</a>
                                @else
                                    <span class="text-blue-200 text-[11px] italic">Lưu trữ</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($exam->isPractice())
                <p class="text-[12px] text-text-muted mt-4 italic">Lưu ý: Bạn có thể thi lại nhiều lần. Kết quả chi tiết (đáp án đúng/sai) chỉ hiển thị cho lần làm bài gần nhất của bạn.</p>
            @endif
        </x-card>
        @endif

    </div>
</x-app-layout>