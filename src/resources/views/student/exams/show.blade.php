<x-app-layout>
    @section('title', $exam->title)
    @section('page-title', 'Thông tin đề thi')

    <div class="space-y-6">

        {{-- Header đề thi --}}
        <x-card padding="true" variant="featured" class="text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight mb-2">{{ $exam->title }}</h2>
            @if($exam->isPractice())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-navy-50 text-navy-600 border border-blue-200 rounded-full text-xs font-semibold mb-4">
                    <x-ui-icon name="star" class="w-3.5 h-3.5" />
                    Đề Luyện Tập (Được thi nhiều lần)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-semibold mb-4">
                    <x-ui-icon name="document-text" class="w-3.5 h-3.5" />
                    Đề Chính Thức (Chỉ được thi 1 lần)
                </span>
            @endif
            
            <p class="text-sm text-text-muted mb-6 max-w-2xl mx-auto">{{ $exam->description }}</p>

            <div class="flex flex-wrap justify-center gap-4 md:gap-8 mb-8">
                <div class="text-center">
                    <span class="block text-xs font-medium text-text-muted mb-1">Thời gian làm bài</span>
                    <span class="text-lg font-bold text-navy-900">
                        @if($exam->duration_minutes >= 60)
                            {{ floor($exam->duration_minutes / 60) }} giờ {{ $exam->duration_minutes % 60 }} phút
                        @else
                            {{ $exam->duration_minutes }} phút
                        @endif
                    </span>
                </div>
                <div class="text-center">
                    <span class="block text-xs font-medium text-text-muted mb-1">Số câu hỏi</span>
                    <span class="text-lg font-bold text-navy-900">{{ $exam->questions->count() }} Câu</span>
                </div>
                <div class="text-center">
                    <span class="block text-xs font-medium text-text-muted mb-1">Điểm đạt</span>
                    <span class="text-lg font-bold text-navy-900">{{ number_format($exam->pass_points, 2) }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col items-center gap-3">
                @if($inProgressAttempt)
                    <p class="text-sm font-medium text-amber-600 mb-2 flex items-center gap-1.5">
                        <x-ui-icon name="exclamation-triangle" class="w-4 h-4" />
                        Bạn đang có bài thi chưa hoàn thành (Lần {{ $inProgressAttempt->attempt_number }})!
                    </p>
                    <a href="{{ route('student.exams.room', $schedule->id) }}" class="inline-flex items-center gap-2 px-8 py-3 bg-amber-500 border border-transparent rounded-[8px] font-semibold text-sm text-white tracking-wide hover:bg-amber-600 transition shadow-sm">
                        <x-ui-icon name="play" class="w-4 h-4" />
                        Tiếp tục làm bài
                    </a>
                @elseif($canStartNew)
                    <div x-data="{ loading: false }">
                        <form action="{{ route('student.exams.start', $schedule->id) }}" 
                              method="POST" 
                              @submit="loading = true">
                            @csrf
                            <button type="submit" 
                                    :disabled="loading"
                                    :class="{ 'opacity-70 cursor-not-allowed': loading }"
                                    class="inline-flex items-center gap-2 px-8 py-3 bg-navy-900 border border-transparent rounded-[8px] font-semibold text-sm text-white tracking-wide hover:bg-navy-950 transition shadow-sm">
                                
                                {{-- Loading Spinner --}}
                                <template x-if="loading">
                                    <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>

                                <template x-if="!loading">
                                    <x-ui-icon :name="$pastAttempts->isNotEmpty() ? 'arrow-path' : 'play'" class="w-4 h-4" />
                                </template>

                                <span x-text="loading ? 'Đang khởi tạo...' : '{{ $pastAttempts->isNotEmpty() ? 'Thi lại lần ' . ($pastAttempts->first()->attempt_number + 1) : 'Bắt đầu làm bài' }}'">
                                    {{ $pastAttempts->isNotEmpty() ? 'Thi lại lần ' . ($pastAttempts->first()->attempt_number + 1) : 'Bắt đầu làm bài' }}
                                </span>
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-teal-600 font-bold text-base mb-2 flex items-center gap-1.5">
                        <x-ui-icon name="check-circle" class="w-5 h-5" />
                        Bạn đã hoàn thành bài thi này!
                    </p>
                @endif
                
                <a href="{{ route('student.dashboard') }}" class="text-sm text-blue-600 hover:underline">Về trang chủ</a>
            </div>
        </x-card>

        {{-- Lịch sử làm bài --}}
        @if($pastAttempts->isNotEmpty())
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-navy-900">Lịch sử làm bài của bạn</h3>
            </x-slot>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-sm">
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
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border-[0.5px] border-teal-200">ĐẠT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border-[0.5px] border-red-200">CHƯA ĐẠT</span>
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
                                    <a href="{{ route('student.exams.result', $schedule->id) }}" class="text-blue-600 hover:underline text-xs font-medium">Chi tiết</a>
                                @else
                                    <span class="text-blue-200 text-xs italic">Lưu trữ</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($exam->isPractice())
                <p class="text-xs text-text-muted mt-4 italic">Lưu ý: Bạn có thể thi lại nhiều lần. Kết quả chi tiết (đáp án đúng/sai) chỉ hiển thị cho lần làm bài gần nhất của bạn.</p>
            @endif
        </x-card>
        @endif

    </div>
</x-app-layout>