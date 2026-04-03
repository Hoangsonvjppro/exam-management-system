<x-app-layout>
    @section('title', ($section->name ?? $section->code) . ' — EMS')
    @section('page-title', 'Lớp học phần')

    @php
    $activeTab = request()->query('tab', 'feed');
    if (!in_array($activeTab, ['feed', 'exams', 'grades', 'attendance', 'leaves'], true)) {
    $activeTab = 'feed';
    }
    @endphp

    <div class="space-y-6" x-data="studentClassWorkspace('{{ $activeTab }}')">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('student.dashboard') }}"
                    class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay về tổng quan
                </a>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-text-muted mt-4 mb-1">{{ $section->code }}</p>
                <h2 class="text-[28px] font-bold text-navy-900 leading-tight">{{ $section->name ?? $section->code }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-navy-900 border border-blue-200">
                        {{ $section->subject->code ?? 'N/A' }} — {{ $section->subject->name ?? 'Chưa gán môn' }}
                    </span>
                    @if($section->lecturer)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-1 text-text-muted border border-border-clean">
                        GV: {{ $section->lecturer->name }}
                    </span>
                    @endif
                    @if($section->semester)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-1 text-text-muted border border-border-clean">
                        {{ $section->semester->name }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Card --}}
        <x-card padding="false" class="overflow-hidden">
            {{-- Tab Bar --}}
            <div class="px-4 sm:px-6 border-b border-border-clean bg-surface-1">
                <div class="flex flex-wrap items-center gap-2 py-2">
                    <button type="button"
                        @click="switchTab('feed')"
                        :class="activeTab === 'feed' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Bảng tin
                        </span>
                    </button>
                    <button type="button"
                        @click="switchTab('exams')"
                        :class="activeTab === 'exams' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Kỳ thi
                            @if($examSchedules->where('student_status', 'in_progress')->count() > 0)
                            <span class="ml-1 bg-teal-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ $examSchedules->where('student_status', 'in_progress')->count() }}</span>
                            @endif
                        </span>
                    </button>
                    <button type="button"
                        @click="switchTab('grades')"
                        :class="activeTab === 'grades' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Điểm số
                        </span>
                    </button>
                    <button type="button"
                        @click="switchTab('attendance')"
                        :class="activeTab === 'attendance' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Điểm danh
                        </span>
                    </button>
                    <button type="button"
                        @click="switchTab('leaves')"
                        :class="activeTab === 'leaves' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Xin phép
                        </span>
                    </button>
                </div>
            </div>

            {{-- ═══ TAB 1: Bảng tin (Feed) ═══ --}}
            <div class="p-4 sm:p-6 space-y-4" x-show="activeTab === 'feed'" x-transition.opacity.duration.150ms>
                @if($notifications->isEmpty())
                <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="bell-slash" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
                    <p class="text-sm text-text-muted font-medium">Chưa có thông báo nào từ giảng viên.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                    <div class="border-[0.5px] border-border-clean rounded-[8px] p-4 bg-white hover:bg-surface-0 transition-colors">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider">
                                {{ $notification->created_at->format('H:i — d/m/Y') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-navy-900 mb-1">{{ $notification->title }}</h4>
                        <p class="text-xs text-text-muted leading-relaxed whitespace-pre-wrap">{{ $notification->message }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ═══ TAB 2: Kỳ thi ═══ --}}
            <div class="p-4 sm:p-6 space-y-4" x-show="activeTab === 'exams'" x-transition.opacity.duration.150ms style="display:none;">
                @if($examSchedules->isEmpty())
                <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="document-text" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
                    <p class="text-sm text-text-muted font-medium">Chưa có kỳ thi nào cho lớp này.</p>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($examSchedules as $schedule)
                    <div class="border-[0.5px] border-border-clean rounded-[10px] p-5 bg-white flex flex-col justify-between hover:shadow-sm transition-shadow
                        {{ $schedule->student_status === 'in_progress' ? 'border-teal-300 bg-teal-50/20' : '' }}">
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                @php
                                $statusConfig = match($schedule->student_status) {
                                'upcoming' => ['bg-blue-50 text-blue-700 border-blue-200', 'Sắp mở'],
                                'in_progress' => ['bg-teal-50 text-teal-700 border-teal-200', 'Đang diễn ra'],
                                'submitted' => ['bg-gray-50 text-gray-600 border-gray-200', 'Đã nộp'],
                                'graded' => ['bg-teal-50 text-teal-800 border-teal-200', 'Đã có điểm'],
                                'ended' => ['bg-gray-50 text-gray-500 border-gray-200', 'Đã kết thúc'],
                                'cancelled' => ['bg-red-50 text-red-700 border-red-200', 'Đã hủy'],
                                default => ['bg-surface-1 text-text-muted border-border-clean', 'N/A'],
                                };
                                @endphp
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $statusConfig[0] }}">
                                    @if($schedule->student_status === 'in_progress')
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                                    @endif
                                    {{ $statusConfig[1] }}
                                </span>
                                <span class="text-[11px] text-text-muted font-semibold">{{ $schedule->date_range_text }}</span>
                            </div>
                            <h4 class="font-bold text-base text-navy-900 leading-snug mb-1">{{ $schedule->exam->title }}</h4>
                            <div class="flex items-center gap-3 mt-2 text-[11px] text-text-muted">
                                <span class="font-semibold">{{ $schedule->time_range_text }}</span>
                                <span>·</span>
                                <span>{{ $schedule->exam->duration_minutes }} phút</span>
                                <span>·</span>
                                <span>{{ $schedule->exam->questions_count ?? '?' }} câu</span>
                            </div>
                        </div>

                        @if($schedule->student_status === 'in_progress')
                        <a href="{{ route('student.exams.show', $schedule->id) }}"
                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-navy-900 text-white text-sm font-semibold rounded-[6px] hover:bg-navy-950 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Vào thi
                        </a>
                        @elseif(in_array($schedule->student_status, ['submitted', 'graded']))
                        <a href="{{ route('student.exams.result', $schedule->id) }}"
                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-surface-1 text-navy-900 text-sm font-semibold rounded-[6px] hover:bg-blue-50 border border-border-clean transition-colors">
                            Xem kết quả
                        </a>
                        @elseif($schedule->student_status === 'upcoming')
                        <div class="text-center text-xs text-text-muted font-medium py-2 bg-surface-0 rounded-[6px] border border-border-clean border-dashed">
                            Chưa đến giờ thi
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ═══ TAB 3: Điểm số ═══ --}}
            <div class="p-4 sm:p-6 space-y-8" x-show="activeTab === 'grades'" x-transition.opacity.duration.150ms style="display:none;">
                {{-- 1. Điểm Quá Trình --}}
                <div class="space-y-3">
                    <h4 class="text-[15px] font-bold text-navy-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        Điểm quá trình
                    </h4>
                    @if($section->gradeColumns->isEmpty())
                    <div class="text-center py-6 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                        <p class="text-[13px] text-text-muted">Chưa có cột điểm nào được thiết lập.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 w-[250px] text-[12px] font-semibold text-text-muted border-r border-border-clean">Cột điểm</th>
                                    <th class="py-3 px-4 text-[12px] font-semibold text-text-muted text-center border-r border-border-clean">Trọng số</th>
                                    <th class="py-3 px-4 text-[12px] font-semibold text-text-muted text-center">Điểm số</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70 bg-white">
                                @php
                                $totalWeight = $section->gradeColumns->sum('weight');
                                $processScoreTotal = 0;
                                $hasAnyProcessScore = false;
                                @endphp
                                @foreach($section->gradeColumns->sortBy('order') as $col)
                                @php
                                $grade = $col->studentGrades->first();
                                if ($grade && $grade->score !== null) {
                                $processScoreTotal += ($grade->score * ($col->weight / 100));
                                $hasAnyProcessScore = true;
                                }
                                @endphp
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-2.5 px-4 border-r border-border-clean">
                                        <p class="text-[13px] font-semibold text-navy-900">{{ $col->name }}</p>
                                    </td>
                                    <td class="py-2.5 px-4 text-center border-r border-border-clean text-[13px] text-text-muted font-mono">
                                        {{ floatval($col->weight) }}%
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if($grade && $grade->score !== null)
                                        <span class="text-[14px] font-bold text-navy-900">{{ number_format($grade->score, 2) }}</span>
                                        @else
                                        <span class="text-[14px] font-medium text-text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if($hasAnyProcessScore && $totalWeight > 0)
                            <tfoot class="bg-indigo-50/30 border-t-2 border-indigo-100">
                                <tr>
                                    <td colspan="2" class="py-3 px-4 text-left border-r border-border-clean">
                                        <p class="text-[13px] font-bold text-indigo-800">Điểm quá trình (Tạm tính)</p>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="text-[16px] font-black text-indigo-700">
                                            {{ number_format(($processScoreTotal * 100) / $totalWeight, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    @endif
                </div>

                {{-- 2. Điểm Thi EMS --}}
                <div class="space-y-3">
                    <h4 class="text-[15px] font-bold text-navy-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Điểm bài thi hệ thống
                    </h4>
                    @if($completedAttempts->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                        <x-ui-icon name="document-check" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
                        <p class="text-sm text-text-muted font-medium">Chưa có bài thi nào được hoàn thành.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Bài thi</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Điểm (hệ 10)</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Số câu đúng</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Nộp lúc</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($completedAttempts as $attempt)
                                @php
                                $exam = $attempt->schedule->exam;
                                $correctCount = $attempt->correct_count ?? 0;
                                $totalQuestions = $exam->questions_count ?? $exam->questions()->count();
                                @endphp
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-3 px-4">
                                        <p class="text-[13px] font-semibold text-navy-900">{{ $exam->title }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">Lần {{ $attempt->attempt_number }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="text-lg font-bold text-navy-900">
                                            {{ number_format($attempt->total_score, 1) }}/10
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="text-[13px] font-semibold text-navy-900">{{ $correctCount }}/{{ $totalQuestions }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-[12px] text-text-muted">
                                        {{ $attempt->completed_at?->format('H:i — d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('student.exams.result', $attempt->exam_schedule_id) }}"
                                                class="text-[12px] font-semibold text-blue-600 hover:text-blue-700">Chi tiết</a>
                                            @if($attempt->complaint)
                                            <span class="text-[12px] font-medium text-orange-500/70" title="Đã gửi khiếu nại">
                                                Đã khiếu nại
                                            </span>
                                            @else
                                            <button type="button"
                                                @click="openComplaintModal('{{ addslashes($exam->title) }}', '{{ $attempt->total_score }}', '{{ $attempt->id }}', '{{ $correctCount }}', '{{ $totalQuestions }}')"
                                                class="text-[12px] font-medium text-text-muted hover:text-red-600 transition-colors">
                                                Gửi khiếu nại
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ═══ TAB 4: Điểm danh (Attendance) ═══ --}}
            <div class="p-4 sm:p-6 space-y-4" x-show="activeTab === 'attendance'" x-transition.opacity.duration.150ms style="display:none;" x-data="studentAttendanceTab({{ $section->id }})">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-[10px] p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
                    <div>
                        <h4 class="text-[15px] font-bold text-navy-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Điểm danh mã PIN / QR
                        </h4>
                        <p class="text-[12px] text-text-muted mt-1 leading-relaxed">Nhập mã PIN do giảng viên cung cấp để xác nhận có mặt.</p>
                    </div>
                    <form @submit.prevent="submitQRCheckIn" class="flex items-center gap-2 w-full md:w-auto relative">
                        <input type="text" x-model="secretCode" placeholder="Nhập mã PIN (VD: AB12C3)" required
                            class="h-10 px-4 w-full md:w-64 bg-white border border-border-clean rounded-[6px] text-[13px] font-mono font-bold text-navy-900 uppercase tracking-widest placeholder:tracking-normal focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all outline-none" />
                        <button type="submit" :disabled="isSubmittingCode"
                            class="h-10 px-5 bg-blue-600 text-white text-[13px] font-bold rounded-[6px] hover:bg-blue-700 disabled:opacity-75 disabled:cursor-not-allowed transition-colors whitespace-nowrap flex items-center gap-2">
                            <span x-show="!isSubmittingCode">Xác nhận</span>
                            <span x-show="isSubmittingCode" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full block"></span>
                        </button>
                    </form>
                </div>
                @if($attendanceSessions->isEmpty())
                <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="clipboard-document-check" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
                    <p class="text-sm text-text-muted font-medium">Chưa có thông tin điểm danh.</p>
                </div>
                @else
                @php
                $totalSessions = $attendanceSessions->count();
                $presentCount = 0;
                foreach ($attendanceSessions as $session) {
                $record = $session->records->first();
                if ($record && in_array($record->status, ['present', 'late', 'excused'])) {
                $presentCount++;
                }
                }
                $absentCount = $totalSessions - $presentCount;
                $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 100;
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="p-4 rounded-xl border border-border-clean bg-white flex flex-col justify-center">
                        <p class="text-[11px] font-bold text-text-muted uppercase tracking-wider mb-1">Tỷ lệ đi học</p>
                        <p class="text-2xl font-black {{ $attendanceRate >= 80 ? 'text-teal-600' : 'text-red-500' }}">{{ $attendanceRate }}%</p>
                    </div>
                    <div class="p-4 rounded-xl border border-border-clean bg-white flex flex-col justify-center">
                        <p class="text-[11px] font-bold text-text-muted uppercase tracking-wider mb-1">Có mặt / Đi trễ</p>
                        <p class="text-2xl font-black text-navy-900">{{ $presentCount }} <span class="text-sm text-text-muted font-medium ml-1">buổi</span></p>
                    </div>
                    <div class="p-4 rounded-xl border border-border-clean bg-white flex flex-col justify-center">
                        <p class="text-[11px] font-bold text-text-muted uppercase tracking-wider mb-1">Vắng mặt</p>
                        <p class="text-2xl font-black {{ $absentCount > 0 ? 'text-red-500' : 'text-navy-900' }}">{{ $absentCount }} <span class="text-sm text-text-muted font-medium ml-1">buổi</span></p>
                    </div>
                </div>

                <div class="border-[0.5px] border-border-clean rounded-[8px] bg-white overflow-hidden">
                    <ul class="divide-y divide-border-clean/70">
                        @foreach($attendanceSessions as $session)
                        @php
                        $record = $session->records->first();
                        $status = $record ? $record->status : 'absent';
                        @endphp
                        <li class="flex items-center justify-between p-4 hover:bg-surface-0 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border {{ $status === 'present' ? 'bg-teal-50 border-teal-200 text-teal-600' : ($status === 'late' ? 'bg-yellow-50 border-yellow-200 text-yellow-600' : ($status === 'excused' ? 'bg-gray-50 border-gray-200 text-gray-500' : 'bg-red-50 border-red-200 text-red-600')) }}">
                                    @if($status === 'present')
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    @elseif($status === 'late')
                                    <span class="text-[11px] font-bold uppercase">M</span>
                                    @elseif($status === 'excused')
                                    <span class="text-[11px] font-bold uppercase">P</span>
                                    @else
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-navy-900">{{ $session->title }}</h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">{{ $session->date->format('H:i - d/m/Y') }}</p>
                                </div>
                            </div>
                            <div>
                                <span class="inline-flex items-center text-[10px] font-bold uppercase px-2 py-0.5 rounded-[4px] border {{ $status === 'present' ? 'bg-teal-50 text-teal-700 border-teal-200' : ($status === 'late' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : ($status === 'excused' ? 'bg-gray-50 text-gray-500 border-gray-200' : 'bg-red-50 text-red-700 border-red-200')) }}">
                                    {{ match($status) { 'present' => 'Có mặt', 'absent' => 'Vắng mặt', 'late' => 'Đi muộn', 'excused' => 'Có phép', default => 'N/A' } }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
    </div>

    {{-- ═══ TAB 5: Xin phép (Leaves) ═══ --}}
    <div class="p-4 sm:p-6 space-y-4" x-show="activeTab === 'leaves'" x-transition.opacity.duration.150ms style="display:none;" x-data="studentLeaveRequestForm({{ $section->id }}, '{{ now()->format('Y-m-d') }}')">
        <div class="bg-surface-0 border-[0.5px] border-border-clean rounded-[10px] p-5 mb-6">
            <h4 class="text-[15px] font-bold text-navy-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Tạo đơn xin nghỉ phép
            </h4>
            <form @submit.prevent="submitLeaveRequest" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Ngày nghỉ <span class="text-red-500">*</span></label>
                    <input type="date" x-model="formDate" required min="{{ now()->format('Y-m-d') }}"
                        class="w-full h-10 px-3 bg-white border border-border-clean rounded-[6px] text-[13px] text-navy-900 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all outline-none" />
                </div>
                <div class="md:col-span-5">
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Lý do nghỉ <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formReason" required placeholder="Nhập lý do vắng mặt..." minlength="5" maxlength="1000"
                        class="w-full h-10 px-3 bg-white border border-border-clean rounded-[6px] text-[13px] text-navy-900 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all outline-none" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Ảnh minh chứng</label>
                    <input type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                        @change="formProofImage = $event.target.files[0] ?? null"
                        class="w-full h-10 px-2.5 py-1 bg-white border border-border-clean rounded-[6px] text-[11px] text-text-muted file:mr-2 file:px-2 file:py-1 file:border-0 file:rounded file:bg-surface-1 file:text-navy-900 file:text-[11px] file:font-semibold focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all outline-none" />
                </div>
                <div class="md:col-span-2">
                    <button type="submit" :disabled="isSubmittingLeave"
                        class="w-full h-10 bg-navy-900 text-white text-[13px] font-bold rounded-[6px] hover:bg-navy-800 disabled:opacity-75 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2">
                        <span x-show="!isSubmittingLeave">Gửi đơn</span>
                        <span x-show="isSubmittingLeave" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full block"></span>
                    </button>
                </div>
                <p class="md:col-span-12 text-[11px] text-text-muted">Tùy chọn: tải ảnh JPG/PNG/WEBP tối đa 4MB để làm minh chứng.</p>
            </form>
        </div>

        @if($leaveRequests->isEmpty())
        <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
            <x-ui-icon name="document-text" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
            <p class="text-sm text-text-muted font-medium">Bạn chưa có đơn xin nghỉ phép nào cho lớp này.</p>
        </div>
        @else
        <div class="border-[0.5px] border-border-clean rounded-[8px] bg-white overflow-hidden">
            <ul class="divide-y divide-border-clean/70">
                @foreach($leaveRequests as $req)
                <li class="p-4 hover:bg-surface-0 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="inline-flex items-center text-[10px] font-bold uppercase px-2 py-0.5 rounded-[4px] border 
                                        {{ $req->status === 'pending' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : ($req->status === 'approved' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'bg-red-50 text-red-700 border-red-200') }}">
                                {{ match($req->status) { 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', default => $req->status } }}
                            </span>
                            <span class="text-[11px] font-mono text-text-muted">Gửi lúc {{ $req->created_at->format('H:i d/m/') }}</span>
                        </div>
                        <h4 class="text-[13px] font-bold text-navy-900">Xin nghỉ ngày: {{ $req->date->format('d/m/Y') }}</h4>
                        <p class="text-[12px] text-text-muted mt-0.5">Lý do: {{ $req->reason }}</p>
                        @if($req->proof_image_url)
                        <div class="mt-2 flex flex-col gap-2">
                            <a href="{{ $req->proof_image_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center gap-1.5 text-[12px] font-semibold text-blue-700 hover:text-blue-900">
                                <x-ui-icon name="eye" class="w-4 h-4" />
                                Xem ảnh minh chứng
                            </a>
                            <a href="{{ $req->proof_image_url }}" target="_blank" rel="noopener noreferrer" class="w-fit">
                                <img src="{{ $req->proof_image_url }}" alt="Ảnh minh chứng xin nghỉ" class="w-24 h-24 rounded-[8px] border border-border-clean object-cover" loading="lazy">
                            </a>
                        </div>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    </x-card>

    {{-- ═══ Modal Khiếu nại (Không gian 4) ═══ --}}
    <x-modal name="complaint-modal" maxWidth="lg">
        <div class="p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[20px] font-bold text-navy-900">Gửi khiếu nại điểm</h3>
                <button @click="$dispatch('close-modal', 'complaint-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="p-3 bg-surface-1 border border-border-clean rounded-lg space-y-2">
                    <div class="flex justify-between text-[12px]">
                        <span class="text-text-muted font-medium">Lớp học phần:</span>
                        <span class="font-semibold text-navy-900">{{ $section->name ?? $section->code }}</span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-text-muted font-medium">Bài thi:</span>
                        <span class="font-semibold text-navy-900" x-text="complaintExamTitle">—</span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-text-muted font-medium">Điểm hiện tại:</span>
                        <span class="font-bold text-navy-900" x-text="complaintCurrentScore + '/10'">—</span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-text-muted font-medium">Số câu đúng:</span>
                        <span class="font-bold text-navy-900" x-text="complaintCorrectCount + '/' + complaintTotalQuestions">—</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Lý do khiếu nại <span class="text-red-500">*</span></label>
                    <textarea x-model="complaintReason" rows="4" placeholder="Vui lòng mô tả chi tiết lý do bạn muốn phúc khảo bài thi này..."
                        class="w-full p-3 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[13px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none resize-y"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'complaint-modal')">Hủy</x-button>
                    <x-button type="button" variant="primary" @click="submitComplaint()">
                        Gửi khiếu nại
                    </x-button>
                </div>
            </div>
        </div>
    </x-modal>
    </div>

    @php
    $studentClassShowConfig = [
    'complaintStoreUrl' => route('student.complaints.store'),
    ];
    @endphp
    <script id="student-class-show-config" type="application/json">
        @json($studentClassShowConfig)
    </script>

    @push('scripts')
    @vite(['resources/js/pages/student/classes-show.js'])
    @endpush
</x-app-layout>