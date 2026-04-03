<x-app-layout>
    @section('title', 'Chi tiết sinh viên — EMS')
    @section('page-title', 'Chi tiết sinh viên')

    <div class="space-y-6">
        <div>
            <a href="{{ route('lecturer.classes.show', ['section' => $section, 'tab' => 'students']) }}"
                class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại danh sách sinh viên
            </a>
            <p class="text-[12px] font-semibold uppercase tracking-wider text-text-muted mt-4 mb-1">{{ $section->code }}</p>
            <h2 class="text-[28px] font-bold text-navy-900 leading-tight">{{ $studentDetail['name'] ?? 'Sinh viên' }}</h2>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-navy-900 border border-blue-200">
                    {{ $section->subject->code ?? 'N/A' }} - {{ $section->subject->name ?? 'Chưa gán môn' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-1 text-text-muted border border-border-clean">
                    {{ $section->semester->name ?? 'Chưa gán học kỳ' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">MSSV</p>
                <p class="mt-2 text-[16px] font-bold text-navy-900">{{ $studentDetail['student_code'] ?? '—' }}</p>
            </x-card>
            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Email</p>
                <p class="mt-2 text-[14px] font-semibold text-navy-900 break-all">{{ $studentDetail['email'] ?? '—' }}</p>
            </x-card>
            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Trạng thái lớp</p>
                <p class="mt-2 text-[16px] font-bold text-navy-900">{{ $studentDetail['enrollment_status_label'] ?? '—' }}</p>
            </x-card>
            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Số lượt đã nộp</p>
                <p class="mt-2 text-[16px] font-bold text-navy-900">{{ $summary['completed_count'] ?? 0 }}/{{ $summary['attempt_count'] ?? 0 }}</p>
            </x-card>
        </div>

        <x-card padding="true">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[18px] font-bold text-navy-900">Chi tiết điểm theo từng bài thi</h3>
                <span class="text-[12px] text-text-muted">TB: {{ isset($summary['average_score']) && $summary['average_score'] !== null ? number_format((float) $summary['average_score'], 2) . '/10' : '—' }}</span>
            </div>

            @if(empty($attempts) || count($attempts) === 0)
            <div class="text-center py-10 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                <p class="text-[13px] text-text-muted">Sinh viên này chưa có lượt làm bài nào trong lớp học phần.</p>
            </div>
            @else
            <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white">
                <table class="w-full text-left border-collapse min-w-[960px]">
                    <thead>
                        <tr class="bg-surface-1 border-b border-border-clean">
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Bài thi</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Lần thi</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Trạng thái</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Điểm</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Số câu đúng</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Bắt đầu</th>
                            <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Nộp bài</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-clean/70">
                        @foreach($attempts as $attempt)
                        @php
                        $status = $attempt['status'] ?? 'unknown';
                        $statusClass = $status === 'completed'
                        ? 'bg-teal-50 text-teal-700 border-teal-200'
                        : 'bg-amber-50 text-amber-700 border-amber-200';
                        $score = $attempt['score'] ?? null;
                        $correct = $attempt['correct_count'] ?? null;
                        $questionCount = $attempt['question_count'] ?? null;
                        @endphp
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-3 px-4 align-top">
                                <p class="text-[13px] font-semibold text-navy-900">{{ $attempt['exam_title'] ?? 'Đề thi' }}</p>
                                <p class="text-[11px] text-text-muted mt-0.5">{{ $attempt['schedule_time'] ?: '—' }}</p>
                            </td>
                            <td class="py-3 px-3 text-center text-[12px] font-semibold text-navy-900">{{ $attempt['attempt_number'] ?? '—' }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center text-[10px] font-bold uppercase rounded-[4px] px-2 py-1 border {{ $statusClass }}">
                                    {{ $attempt['status_label'] ?? 'Không xác định' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center text-[13px] font-bold text-navy-900">
                                {{ $score !== null ? number_format((float) $score, 1) . '/10' : '—' }}
                            </td>
                            <td class="py-3 px-3 text-center text-[12px] text-text-muted">
                                {{ $correct !== null ? ($questionCount !== null ? $correct . '/' . $questionCount : $correct . '/—') : '—' }}
                            </td>
                            <td class="py-3 px-3 text-center text-[12px] text-text-muted">{{ $attempt['started_at'] ?? '—' }}</td>
                            <td class="py-3 px-3 text-center text-[12px] text-text-muted">{{ $attempt['completed_at'] ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-card>
    </div>
</x-app-layout>