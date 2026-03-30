<x-app-layout>
    @section('title', 'Kết quả thi - ' . $exam->title)
    @section('page-title', 'Kết quả bài thi')

    <div class="space-y-6">
        {{-- Header kết quả --}}
        <x-card padding="true" variant="featured">
            <div class="text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-navy-900 leading-tight mb-2">
                    {{ $exam->title }}
                </h2>
                <p class="text-sm text-text-muted mb-4">
                    {{ $exam->subject->name ?? '—' }}
                </p>
            </div>
        </x-card>

        {{-- Thông tin tổng quan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Điểm số (hệ 10) --}}
            <x-card padding="true">
                <div class="text-center">
                    <p class="text-xs font-medium text-text-muted mb-1">Điểm số</p>
                    @if($exam->show_score_after_submit)
                    <p class="text-4xl font-bold leading-none text-navy-900">
                        {{ number_format($attempt->total_score, 1) }}
                    </p>
                    <p class="text-xs text-text-muted mt-1">/ 10 điểm</p>
                    @else
                    <p class="text-3xl font-bold text-navy-900 leading-none">—</p>
                    <p class="text-xs text-text-muted mt-1">Giảng viên chưa cho phép xem điểm</p>
                    @endif
                </div>
            </x-card>

            {{-- Số câu đúng --}}
            <x-card padding="true">
                <div class="text-center">
                    <p class="text-xs font-medium text-text-muted mb-1">Số câu đúng</p>
                    @if($exam->show_score_after_submit)
                    <p class="text-4xl font-bold text-navy-900 leading-none">
                        {{ $correctCount }}<span class="text-lg text-text-muted">/{{ $totalQuestions }}</span>
                    </p>
                    <p class="text-xs text-text-muted mt-1">
                        {{ $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0 }}% chính xác
                    </p>
                    @else
                    <p class="text-3xl font-bold text-navy-900 leading-none">—</p>
                    <p class="text-xs text-text-muted mt-1">Chưa công bố</p>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Thông tin bài thi --}}
        <x-card padding="true">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-xs font-medium text-text-muted mb-0.5">Bắt đầu</p>
                    <p class="text-sm font-semibold text-navy-900">{{ $attempt->started_at->format('H:i - d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-muted mb-0.5">Nộp bài</p>
                    <p class="text-sm font-semibold text-navy-900">{{ $attempt->completed_at->format('H:i - d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-muted mb-0.5">Thời gian làm bài</p>
                    <p class="text-sm font-semibold text-navy-900">
                        @php
                            $duration = $attempt->started_at->diff($attempt->completed_at);
                            $h = $duration->h + ($duration->days * 24);
                            $m = $duration->i;
                        @endphp
                        {{ $h > 0 ? "{$h} giờ " : "" }}{{ $m }} phút
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-text-muted mb-0.5">Thời gian cho phép</p>
                    <p class="text-sm font-semibold text-navy-900">
                        @if($exam->duration_minutes >= 60)
                            {{ floor($exam->duration_minutes / 60) }} giờ {{ $exam->duration_minutes % 60 }} phút
                        @else
                            {{ $exam->duration_minutes }} phút
                        @endif
                    </p>
                </div>
            </div>
        </x-card>

        {{-- Chi tiết từng câu hỏi --}}
        @if($exam->show_answers_after_submit)
        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-navy-900">Chi tiết đáp án</h3>
            </x-slot>

            <div class="space-y-4">
                @foreach($answers as $index => $answer)
                <div class="border-[0.5px] border-border-clean rounded-[8px] p-4 {{ $answer->is_correct ? 'bg-teal-50/50' : 'bg-red-50/50' }}">
                    {{-- Câu hỏi --}}
                    <div class="flex items-start gap-3 mb-3">
                        <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white {{ $answer->is_correct ? 'bg-teal-500' : 'bg-red-400' }}">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-navy-900 leading-relaxed">{!! $answer->question->content !!}</p>
                        </div>
                        <span class="flex-shrink-0 text-xs font-semibold {{ $answer->is_correct ? 'text-teal-600' : 'text-red-500' }}">
                            {{ $answer->is_correct ? '✓ Đúng' : '✗ Sai' }}
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
                        <div class="flex items-center gap-2 px-3 py-2 rounded-[6px] border-[0.5px] {{ $optClass }} text-sm">
                            <span class="w-5 text-center font-bold">{{ $icon }}</span>
                            <span class="flex-1">{!! $option->content !!}</span>
                            @if($isStudentChoice)
                            <span class="text-xs font-medium opacity-75">Bạn chọn</span>
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
                <p class="text-base text-text-muted font-medium flex items-center justify-center gap-2">
                    <x-ui-icon name="exclamation-triangle" class="w-5 h-5" />
                    Giảng viên chưa cho phép xem chi tiết đáp án cho bài thi này.
                </p>
            </div>
        </x-card>
        @endif

        {{-- Nút quay lại --}}
        <div class="flex items-center justify-center gap-3">
            <x-button variant="outline" href="{{ route('student.exams.show', $schedule->id) }}">
                ← Quay lại đề thi
            </x-button>
            <x-button variant="primary" href="{{ route('student.dashboard') }}">
                Về trang chủ
            </x-button>
        </div>
    </div>
</x-app-layout>