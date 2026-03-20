<x-app-layout>
    @section('title', 'Kết quả thi - ' . $exam->title)
    @section('page-title', 'Kết quả bài thi')

    <div class="space-y-6">
        {{-- Header kết quả --}}
        <x-card padding="true" variant="featured">
            <div class="text-center">
                <h2 class="text-[22px] md:text-[28px] font-bold text-navy-900 leading-tight mb-2">
                    {{ $exam->title }}
                </h2>
                <p class="text-[13px] text-text-muted mb-4">
                    {{ $exam->courseSection->name ?? $exam->courseSection->code }}
                </p>

                @if(session('success'))
                <div class="p-3 bg-teal-50 border-[0.5px] border-teal-200 rounded-[6px] font-medium text-teal-800 text-[13px] mb-4 inline-block">
                    {{ session('success') }}
                </div>
                @endif
            </div>
        </x-card>

        {{-- Thông tin tổng quan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Điểm số --}}
            <x-card padding="true">
                <div class="text-center">
                    <p class="text-[12px] font-medium text-text-muted mb-1">Điểm số</p>
                    @if($exam->show_score_after_submit)
                        <p class="text-[36px] font-bold leading-none {{ $passed ? 'text-teal-600' : 'text-red-500' }}">
                            {{ number_format($attempt->total_score, 2) }}
                        </p>
                        <p class="text-[12px] text-text-muted mt-1">/ {{ number_format($exam->total_points, 2) }} điểm</p>
                    @else
                        <p class="text-[28px] font-bold text-navy-900 leading-none">—</p>
                        <p class="text-[12px] text-text-muted mt-1">Giảng viên chưa cho phép xem điểm</p>
                    @endif
                </div>
            </x-card>

            {{-- Kết quả --}}
            <x-card padding="true">
                <div class="text-center">
                    <p class="text-[12px] font-medium text-text-muted mb-1">Kết quả</p>
                    @if($exam->show_score_after_submit)
                        @if($passed)
                            <div class="inline-flex items-center gap-2">
                                <span class="text-[28px]">✅</span>
                                <span class="text-[20px] font-bold text-teal-600">ĐẠT</span>
                            </div>
                            <p class="text-[12px] text-text-muted mt-1">Điểm đạt: {{ number_format($exam->pass_points, 2) }}</p>
                        @else
                            <div class="inline-flex items-center gap-2">
                                <span class="text-[28px]">❌</span>
                                <span class="text-[20px] font-bold text-red-500">CHƯA ĐẠT</span>
                            </div>
                            <p class="text-[12px] text-text-muted mt-1">Cần tối thiểu: {{ number_format($exam->pass_points, 2) }}</p>
                        @endif
                    @else
                        <p class="text-[28px] font-bold text-navy-900 leading-none">—</p>
                        <p class="text-[12px] text-text-muted mt-1">Chưa công bố</p>
                    @endif
                </div>
            </x-card>

            {{-- Thống kê --}}
            <x-card padding="true">
                <div class="text-center">
                    <p class="text-[12px] font-medium text-text-muted mb-1">Số câu đúng</p>
                    @if($exam->show_answers_after_submit)
                        <p class="text-[36px] font-bold text-navy-900 leading-none">
                            {{ $correctCount }}<span class="text-[18px] text-text-muted">/{{ $totalQuestions }}</span>
                        </p>
                        <p class="text-[12px] text-text-muted mt-1">
                            {{ $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0 }}% chính xác
                        </p>
                    @else
                        <p class="text-[28px] font-bold text-navy-900 leading-none">—</p>
                        <p class="text-[12px] text-text-muted mt-1">Chưa công bố</p>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Thông tin bài thi --}}
        <x-card padding="true">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-[11px] font-medium text-text-muted mb-0.5">Bắt đầu</p>
                    <p class="text-[13px] font-semibold text-navy-900">{{ $attempt->started_at->format('H:i - d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-text-muted mb-0.5">Nộp bài</p>
                    <p class="text-[13px] font-semibold text-navy-900">{{ $attempt->completed_at->format('H:i - d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-text-muted mb-0.5">Thời gian làm bài</p>
                    <p class="text-[13px] font-semibold text-navy-900">
                        {{ $attempt->started_at->diffInMinutes($attempt->completed_at) }} phút
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-text-muted mb-0.5">Thời gian cho phép</p>
                    <p class="text-[13px] font-semibold text-navy-900">{{ $exam->duration_minutes }} phút</p>
                </div>
            </div>
        </x-card>

        {{-- Chi tiết từng câu hỏi --}}
        @if($exam->show_answers_after_submit)
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-[17px] font-semibold text-navy-900">Chi tiết đáp án</h3>
            </x-slot>

            <div class="space-y-4">
                @foreach($answers as $index => $answer)
                <div class="border-[0.5px] border-border-clean rounded-[8px] p-4 {{ $answer->is_correct ? 'bg-teal-50/50' : 'bg-red-50/50' }}">
                    {{-- Câu hỏi --}}
                    <div class="flex items-start gap-3 mb-3">
                        <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-[12px] font-bold text-white {{ $answer->is_correct ? 'bg-teal-500' : 'bg-red-400' }}">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1">
                            <p class="text-[14px] font-medium text-navy-900 leading-relaxed">{!! $answer->question->content !!}</p>
                        </div>
                        <span class="flex-shrink-0 text-[12px] font-semibold {{ $answer->is_correct ? 'text-teal-600' : 'text-red-500' }}">
                            {{ $answer->is_correct ? '+' . number_format($answer->points_awarded, 2) : '0.00' }} đ
                        </span>
                    </div>

                    {{-- Danh sách đáp án --}}
                    <div class="space-y-1.5 ml-10">
                        @foreach($answer->question->options as $option)
                        @php
                            $isStudentChoice = $answer->question_option_id == $option->id;
                            $isCorrectOption = $option->is_correct;

                            if ($isCorrectOption && $isStudentChoice) {
                                $optClass = 'bg-teal-100 border-teal-300 text-teal-800';
                                $icon = '✓';
                            } elseif ($isCorrectOption) {
                                $optClass = 'bg-teal-50 border-teal-200 text-teal-700';
                                $icon = '✓';
                            } elseif ($isStudentChoice) {
                                $optClass = 'bg-red-100 border-red-300 text-red-800';
                                $icon = '✗';
                            } else {
                                $optClass = 'bg-white border-border-clean text-text-muted';
                                $icon = '';
                            }
                        @endphp
                        <div class="flex items-center gap-2 px-3 py-2 rounded-[6px] border-[0.5px] {{ $optClass }} text-[13px]">
                            <span class="w-5 text-center font-bold">{{ $icon }}</span>
                            <span class="flex-1">{!! $option->content !!}</span>
                            @if($isStudentChoice)
                                <span class="text-[11px] font-medium opacity-75">Bạn chọn</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
        @else
        <x-card padding="true">
            <div class="text-center py-8">
                <p class="text-[15px] text-text-muted font-medium">🔒 Giảng viên chưa cho phép xem chi tiết đáp án cho bài thi này.</p>
            </div>
        </x-card>
        @endif

        {{-- Nút quay lại --}}
        <div class="flex items-center justify-center gap-3">
            <x-button variant="outline" href="{{ route('student.exams.show', $exam->id) }}">
                ← Quay lại đề thi
            </x-button>
            <x-button variant="primary" href="{{ route('student.dashboard') }}">
                Về trang chủ
            </x-button>
        </div>
    </div>
</x-app-layout>
