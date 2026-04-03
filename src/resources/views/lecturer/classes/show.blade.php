<x-app-layout>
    @section('title', ($section->name ?? $section->code) . ' — EMS')
    @section('page-title', 'Class Workspace')

    @php
    $activeTab = request()->query('tab', 'overview');
    if (!in_array($activeTab, ['overview', 'students', 'attendance', 'leaves', 'grading', 'statistics', 'complaints'], true)) {
    $activeTab = 'overview';
    }

    $ownedExamsForSection = \App\Models\Exam::query()
    ->where('created_by', auth()->id())
    ->where('subject_id', $section->subject_id)
    ->with('subject')
    ->orderByDesc('id')
    ->get();

    $quickQuestionPool = \App\Models\Question::query()
    ->where('subject_id', $section->subject_id)
    ->orderByDesc('updated_at')
    ->limit(120)
    ->get(['id', 'content']);

    $semesterLifecycle = $section->semester?->lifecycle_status;
    $isUpcomingSemesterWorkspace = $semesterLifecycle === \App\Models\Semester::STATUS_UPCOMING;
    $workspaceStatusLabel = match (true) {
    $section->status === 'archived' => 'Đã lưu trữ',
    $section->status === 'cancelled' => 'Đã huỷ',
    $semesterLifecycle === \App\Models\Semester::STATUS_UPCOMING => 'Sắp mở',
    $semesterLifecycle === \App\Models\Semester::STATUS_CURRENT => 'Đang diễn ra',
    $semesterLifecycle === \App\Models\Semester::STATUS_ENDED => 'Đã kết thúc',
    default => 'Đang hoạt động',
    };
    $workspaceStatusClass = match (true) {
    $section->status === 'archived' => 'bg-surface-1 text-text-muted border border-border-clean',
    $section->status === 'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
    $semesterLifecycle === \App\Models\Semester::STATUS_UPCOMING => 'bg-amber-50 text-amber-800 border border-amber-200',
    $semesterLifecycle === \App\Models\Semester::STATUS_CURRENT => 'bg-teal-50 text-teal-800 border border-teal-200',
    $semesterLifecycle === \App\Models\Semester::STATUS_ENDED => 'bg-slate-100 text-slate-700 border border-slate-300',
    default => 'bg-teal-50 text-teal-800 border border-teal-200',
    };
    @endphp

    <div class="space-y-6" x-data="classWorkspaceManager('{{ $activeTab }}')">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('lecturer.classes.index') }}"
                    class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay về danh sách lớp
                </a>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-text-muted mt-4 mb-1">{{ $section->code }}</p>
                <h2 class="text-[28px] font-bold text-navy-900 leading-tight">{{ $section->name ?? $section->code }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-navy-900 border border-blue-200">
                        {{ $section->subject->code ?? 'N/A' }} - {{ $section->subject->name ?? 'Chưa gán môn' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-1 text-text-muted border border-border-clean">
                        {{ $section->semester->name ?? 'Chưa gán học kỳ' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $workspaceStatusClass }}">
                        {{ $workspaceStatusLabel }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('manage', $section)
                <x-button variant="outline" @click="$dispatch('open-modal', 'create-notification-modal')">
                    Tạo thông báo nhanh
                </x-button>
                <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-schedule-inline-slide')">
                    Tạo bài thi cho lớp này
                </x-button>
                @endcan
            </div>
        </div>

{{-- Cảnh báo học kỳ sắp mở (Từ nhánh main) --}}
        @if($isUpcomingSemesterWorkspace)
        <div class="rounded-[10px] border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900 mb-4">
            <p class="font-semibold">Không gian lớp đang ở chế độ học kỳ sắp mở.</p>
            <p class="mt-1 text-[12px] text-amber-800">Dữ liệu lớp được hiển thị ở dạng chuẩn bị trước khai giảng để phân biệt với lớp đang diễn ra.</p>
        </div>         
        @endif

        <div class="relative">
            {{-- Lớp phủ làm mờ (Từ nhánh main) --}}
            @if($isUpcomingSemesterWorkspace)
            <div class="pointer-events-none absolute inset-0 z-20 rounded-[10px] bg-white/45 backdrop-blur-[1px]"></div>
            <div class="pointer-events-none absolute right-3 top-3 z-30 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-800">
                Sắp mở
            </div>
            @endif

            {{-- ═══ Mã mời tham gia lớp (Từ nhánh khanh-update) ═══ --}}
            @can('manage', $section)
            <x-card
                padding="true"
                variant="featured"
                x-data="inviteCodeCardState()"
                data-invite-code="{{ $section->invite_code ?? '' }}"
                data-join-qr-url="{{ route('student.join-class.qr') }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-text-muted mb-1">Mã mời tham gia lớp</p>
                        <p class="text-[12px] text-text-muted">Gửi mã này cho sinh viên để họ tham gia lớp học phần.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center bg-white border-[1.5px] border-[#D6E2F0] rounded-[6px] overflow-hidden">
                            <span class="px-4 py-2 font-mono text-[16px] font-black text-[#1A3A6B] tracking-[0.2em] select-all" x-text="inviteCode || '—'"></span>
                            @if($section->invite_code)
                            <button type="button"
                                @click="copyInviteCode()"
                                class="px-3 py-2 border-l border-[#D6E2F0] text-[#1A3A6B] hover:bg-[#F4F7FC] transition-colors inline-flex items-center gap-1.5"
                                :title="copied ? 'Đã sao chép!' : 'Copy mã'">
                                <svg class="w-4 h-4" :class="copied ? 'text-teal-600' : 'text-[#6B7C99]'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[12px] font-semibold" x-text="copied ? 'Đã copy' : 'Copy mã'"></span>
                            </button>
                            @endif
                        </div>
                        @if($section->invite_code)
                        <button type="button"
                            @click="showInviteQr()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-[12px] font-semibold text-[#1A3A6B] bg-white border-[1.5px] border-[#D6E2F0] rounded-[6px] hover:border-[#185FA5] hover:bg-[#F4F7FC] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a2 2 0 012-2h2M4 17v2a2 2 0 002 2h2m8-18h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M9 12h6" />
                            </svg>
                            Hiện QR và mã
                        </button>
                        @endif
                        <form method="POST" action="{{ route('lecturer.classes.regenerate-code', $section) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-[12px] font-semibold text-[#6B7C99] bg-white border-[1.5px] border-[#D6E2F0] rounded-[6px] hover:text-[#1A3A6B] hover:border-[#185FA5] transition-colors"
                                data-confirm-message="Tạo mã mới sẽ vô hiệu hóa mã cũ. Tiếp tục?">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Tạo mã mới
                            </button>
                        </form>
                    </div>
                </div>
            </x-card>
            @endcan
        </div>

            <x-card padding="false" class="overflow-hidden">
                <div class="px-4 sm:px-6 border-b border-border-clean bg-surface-1">
                    <div class="flex flex-wrap items-center gap-2 py-2">
                        <button type="button"
                            @click="switchTab('overview')"
                            :class="activeTab === 'overview' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                            Thông tin chung
                        </button>
                        <button type="button"
                            @click="switchTab('students')"
                            :class="activeTab === 'students' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                            Danh sách sinh viên
                        </button>
                        <button type="button"
                            @click="switchTab('attendance')"
                            :class="activeTab === 'attendance' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                            Điểm danh
                        </button>
                        <button type="button"
                            @click="switchTab('leaves')"
                            :class="activeTab === 'leaves' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors flex items-center gap-1.5">
                            Xin nghỉ phép
                            @if($section->leaveRequests->where('status', 'pending')->count() > 0)
                            <span class="bg-yellow-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                {{ $section->leaveRequests->where('status', 'pending')->count() }}
                            </span>
                            @endif
                        </button>
                        <button type="button"
                            @click="switchTab('grading')"
                            :class="activeTab === 'grading' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                            Điểm quá trình
                        </button>
                        <button type="button"
                            @click="switchTab('statistics')"
                            :class="activeTab === 'statistics' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                            Thống kê
                        </button>
                        <button type="button"
                            @click="switchTab('complaints')"
                            :class="activeTab === 'complaints' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                            class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors flex items-center gap-1.5">
                            Khiếu nại điểm
                            @if($section->complaints->where('status', 'pending')->count() > 0)
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                {{ $section->complaints->where('status', 'pending')->count() }}
                            </span>
                            @endif
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-6" x-show="activeTab === 'overview'" x-transition.opacity.duration.150ms>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <x-card variant="default" padding="true">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-text-muted">Sĩ số lớp</p>
                            <p class="mt-2 text-[28px] font-bold text-navy-900 leading-none">{{ $section->students->count() }}</p>
                            <p class="text-[12px] text-text-muted mt-1">/ {{ $section->max_students }} sinh viên</p>
                        </x-card>

                        <x-card variant="default" padding="true">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-text-muted">Lịch thi đã tạo</p>
                            <p class="mt-2 text-[28px] font-bold text-navy-900 leading-none">{{ $section->examSchedules->count() }}</p>
                            <p class="text-[12px] text-text-muted mt-1">ca thi trong lớp học phần</p>
                        </x-card>
                    </div>

                    <x-card padding="true">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[16px] font-bold text-navy-900">Bảng tin lớp học phần</h3>
                            <span class="text-[12px] text-text-muted">{{ $sectionFeedItems->count() }} mục gần nhất</span>
                        </div>

                        @if($sectionFeedItems->isEmpty())
                        <div class="text-center py-10 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                            <x-ui-icon name="bell-slash" class="w-10 h-10 text-blue-100 mx-auto mb-3" />
                            <p class="text-[13px] text-text-muted">Chưa có thông báo nào bạn đã gửi cho lớp này.</p>
                        </div>
                        @else
                        <div class="space-y-3">
                            @foreach($sectionFeedItems as $feed)
                            <div class="border-[0.5px] border-border-clean rounded-[8px] p-4 bg-white hover:bg-surface-0 transition-colors">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider">
                                        {{ $feed->created_at?->format('H:i — d/m/Y') ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-[4px] border
                                    {{ ($feed->source ?? '') === 'exam_schedule' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-teal-50 text-teal-700 border-teal-200' }}">
                                        {{ ($feed->source ?? '') === 'exam_schedule' ? 'Lịch thi' : 'Thông báo' }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-navy-900 mb-1">{{ $feed->title }}</h4>
                                <p class="text-xs text-text-muted leading-relaxed whitespace-pre-wrap">{{ $feed->message }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </x-card>

                    <x-card padding="true">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-[16px] font-bold text-navy-900">Lịch thi của lớp</h3>
                            @can('manage', $section)
                            <x-button variant="secondary" size="sm" @click="$dispatch('open-slide-over', 'create-schedule-inline-slide')">
                                + Lên lịch thi
                            </x-button>
                            @endcan
                        </div>

                        @if($section->examSchedules->isEmpty())
                        <div class="text-center py-10 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                            <p class="text-[13px] font-medium text-text-muted mb-1">Chưa có lịch thi nào cho lớp này.</p>
                            <p class="text-[12px] text-text-muted">Bạn có thể tạo lịch thi ngay mà không cần rời khỏi không gian lớp.</p>
                        </div>
                        @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b-[1.5px] border-border-clean">
                                        <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Đề thi</th>
                                        <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Thời gian</th>
                                        <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Trạng thái</th>
                                        <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-clean/70">
                                    @foreach($section->examSchedules->sortByDesc('created_at') as $schedule)
                                    <tr>
                                        <td class="py-3 px-3">
                                            <p class="text-[13px] font-semibold text-navy-900">{{ $schedule->exam->title }}</p>
                                            <p class="text-[11px] text-text-muted mt-0.5">{{ $schedule->exam->duration_minutes }} phút · {{ $schedule->exam->questions_count ?? 0 }} câu</p>
                                        </td>
                                        <td class="py-3 px-3 text-[12px] text-text-muted">
                                            {{ $schedule->date_range_text }}
                                            @if($schedule->start_time)
                                            <div>{{ $schedule->time_range_text }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-flex items-center uppercase text-[10px] font-bold px-2 py-1 rounded-[4px]
                                                    @if($schedule->status === 'in_progress') bg-teal-50 text-teal-800 border border-teal-200
                                                    @elseif($schedule->status === 'scheduled') bg-blue-50 text-blue-700 border border-blue-200
                                                    @elseif($schedule->status === 'completed') bg-gray-50 text-gray-700 border border-gray-200
                                                    @else bg-surface-1 text-text-muted border border-border-clean @endif">
                                                {{ match($schedule->status) {
                                                        'in_progress' => 'Đang thi',
                                                        'scheduled'   => 'Đã lên lịch',
                                                        'completed'   => 'Đã hoàn thành',
                                                        default       => 'Đã huỷ',
                                                    } }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            @can('manageLecturer', $schedule->exam)
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('lecturer.schedules.edit', $schedule) }}" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700">Sửa</a>
                                                <form method="POST" action="{{ route('lecturer.schedules.destroy', $schedule) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[12px] font-semibold text-red-600 hover:text-red-700"
                                                        data-confirm-message="Xoá lịch thi này?">
                                                        Xoá
                                                    </button>
                                                </form>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </x-card>
                </div>

                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'students'" x-transition.opacity.duration.150ms style="display:none;">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-[18px] font-bold text-navy-900">Danh sách sinh viên ({{ $section->students->count() }})</h3>
                        @can('manage', $section)
                        <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-schedule-inline-slide')">
                            Tạo bài thi cho lớp này
                        </x-button>
                        @endcan
                    </div>

                    @if($section->students->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                        <p class="text-[13px] font-medium text-text-muted">Hiện chưa có sinh viên nào trong lớp học phần này.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Họ tên</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">MSSV</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Email</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Trạng thái</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Điều kiện thi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($section->students->sortBy('name') as $student)
                                @php
                                $enrollStatus = $student->pivot->status ?? 'enrolled';
                                $isBlocked = $enrollStatus !== 'enrolled';
                                @endphp
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-3 px-4 text-[13px] font-semibold text-navy-900">{{ $student->name }}</td>
                                    <td class="py-3 px-4 text-[12px] text-text-muted font-mono">{{ $student->student_code ?? '—' }}</td>
                                    <td class="py-3 px-4 text-[12px] text-text-muted">{{ $student->email }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center text-[10px] font-bold uppercase px-2 py-1 rounded-[4px]
                                                @if($enrollStatus === 'enrolled') bg-teal-50 text-teal-800 border border-teal-200
                                                @elseif($enrollStatus === 'dropped') bg-red-50 text-red-700 border border-red-200
                                                @else bg-surface-1 text-text-muted border border-border-clean @endif">
                                            {{ $enrollStatus === 'enrolled' ? 'Đang học' : ($enrollStatus === 'dropped' ? 'Đã rời lớp' : 'Tạm dừng') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center text-[10px] font-bold uppercase px-2 py-1 rounded-[4px]
                                                {{ $isBlocked ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                            {{ $isBlocked ? 'Cấm thi tạm thời' : 'Đủ điều kiện' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'attendance'" x-transition.opacity.duration.150ms style="display:none;" x-data="attendanceManager({{ $section->id }})">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-[18px] font-bold text-navy-900">Lưới điểm danh ({{ $section->attendanceSessions->count() }} buổi)</h3>
                        </div>
                        @can('manage', $section)
                        <x-button variant="primary" @click="$dispatch('open-modal', 'create-attendance-session-modal')">
                            + Tạo buổi điểm danh
                        </x-button>
                        @endcan
                    </div>

                    @if($section->attendanceSessions->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                        <x-ui-icon name="clipboard-document-check" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                        <p class="text-sm text-text-muted font-medium">Lớp chưa có buổi điểm danh nào.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="sticky left-0 z-10 bg-surface-1 py-3 px-4 w-[250px] text-[12px] font-semibold text-text-muted border-r border-border-clean shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">Sinh viên</th>
                                    @foreach($section->attendanceSessions->sortBy('date') as $session)
                                    <th class="py-2 px-3 text-center border-r border-border-clean min-w-[220px] align-top">
                                        <p class="text-[12px] font-bold text-navy-900 leading-tight whitespace-normal break-words" title="{{ $session->title }}">{{ $session->title }}</p>
                                        <p class="text-[10px] text-text-muted mt-1 mb-2">{{ $session->date->format('d/m/Y') }}</p>
                                        <div class="grid grid-cols-2 gap-2 mt-1" x-init="sessions[{{ $session->id }}] = { is_open: {{ $session->is_open ? 'true' : 'false' }}, code: '{{ $session->secret_code }}' }">
                                            <button @click="toggleSessionOpen({{ $session->id }})"
                                                :disabled="isTogglingSession"
                                                class="w-full min-h-[42px] px-2 py-2 text-[10px] font-bold uppercase rounded-[8px] border transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                                :class="sessions[{{ $session->id }}]?.is_open ? 'bg-teal-50 text-teal-700 border-teal-200 hover:bg-teal-100' : 'bg-surface-0 text-text-muted border-border-clean hover:bg-surface-1'">
                                                <span x-text="sessions[{{ $session->id }}]?.is_open ? 'Đang mở điểm danh' : 'Đã đóng điểm danh'"></span>
                                            </button>
                                            <button
                                                @click="sessions[{{ $session->id }}]?.is_open ? showPinCode(sessions[{{ $session->id }}]?.code) : null"
                                                :disabled="!sessions[{{ $session->id }}]?.is_open"
                                                class="w-full min-h-[42px] px-2 py-2 rounded-[8px] border transition-colors flex flex-col items-center justify-center gap-0.5"
                                                :class="sessions[{{ $session->id }}]?.is_open ? 'border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700' : 'border-border-clean bg-surface-0 text-text-muted cursor-not-allowed opacity-70'">
                                                <span class="text-[10px] font-bold uppercase tracking-wide">Hiện QR và mã</span>
                                                <span class="text-[11px] font-mono font-black tracking-[0.16em]" x-text="sessions[{{ $session->id }}]?.is_open ? sessions[{{ $session->id }}]?.code : '-----'"></span>
                                            </button>
                                        </div>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($section->students->sortBy('name') as $student)
                                <tr class="hover:bg-surface-0 transition-colors group">
                                    <td class="sticky left-0 z-10 bg-white py-3 px-4 border-r border-border-clean shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] group-hover:bg-surface-0 transition-colors">
                                        <p class="text-[13px] font-semibold text-navy-900">{{ $student->name }}</p>
                                        <p class="text-[11px] text-text-muted font-mono mt-0.5">{{ $student->student_code ?? '—' }}</p>
                                    </td>
                                    @foreach($section->attendanceSessions->sortBy('date') as $session)
                                    @php
                                    $record = $session->records->where('student_id', $student->id)->first();
                                    $status = $record ? $record->status : 'absent';
                                    $recordId = $record ? $record->id : 'null';
                                    @endphp
                                    <td class="p-0 text-center border-r border-border-clean align-middle">
                                        <button type="button"
                                            @if($recordId !=='null' )
                                            class="w-full h-full min-h-[50px] px-2 py-2 flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                                            :class="{
                                            'bg-teal-50 hover:bg-teal-100': records['{{ $session->id }}_{{ $student->id }}'] === 'present',
                                            'bg-red-50 hover:bg-red-100': records['{{ $session->id }}_{{ $student->id }}'] === 'absent',
                                            'bg-gray-50 hover:bg-gray-100': records['{{ $session->id }}_{{ $student->id }}'] === 'excused'
                                        }"
                                            @click="toggleStatus({{ $session->id }}, {{ $student->id }}, {{ $recordId }})"
                                            x-init="records['{{ $session->id }}_{{ $student->id }}'] = '{{ $status }}'"
                                            @else
                                            class="w-full h-full min-h-[50px] flex items-center justify-center bg-gray-50 text-gray-300"
                                            disabled
                                            @endif>
                                            @if($recordId !== 'null')
                                            <div class="w-5 h-5 rounded-full flex items-center justify-center shadow-sm border transition-colors"
                                                :class="{
                                                'bg-teal-500 border-teal-600 text-white': records['{{ $session->id }}_{{ $student->id }}'] === 'present',
                                                'bg-white border-red-300 text-red-500': records['{{ $session->id }}_{{ $student->id }}'] === 'absent',
                                                'bg-surface-1 border-gray-300 text-gray-500': records['{{ $session->id }}_{{ $student->id }}'] === 'excused'
                                            }">
                                                <template x-if="records['{{ $session->id }}_{{ $student->id }}'] === 'present'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </template>
                                                <template x-if="records['{{ $session->id }}_{{ $student->id }}'] === 'absent'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </template>
                                                <template x-if="records['{{ $session->id }}_{{ $student->id }}'] === 'excused'">
                                                    <span class="text-[9px] font-bold uppercase">P</span>
                                                </template>
                                            </div>
                                            @else
                                            <span class="text-xs text-text-muted">N/A</span>
                                            @endif
                                        </button>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- ═══ TAB: Xin nghỉ phép ═══ --}}
                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'leaves'" x-transition.opacity.duration.150ms style="display:none;" x-data="leaveRequestManager({{ $section->id }})">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[18px] font-bold text-navy-900">Đơn xin nghỉ phép</h3>
                        <p class="text-[12px] text-text-muted font-medium mt-1">Duyệt hoặc từ chối các đơn xin nghỉ phép của sinh viên.</p>
                    </div>

                    @if($section->leaveRequests->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                        <x-ui-icon name="document-text" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                        <p class="text-sm text-text-muted font-medium">Lớp chưa có đơn xin nghỉ phép nào.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Sinh viên</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Ngày xin nghỉ</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Lý do / Minh chứng</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center pr-6">Trạng thái / Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($section->leaveRequests->sortByDesc('created_at') as $leaveReq)
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-4 px-4 align-top w-[200px]">
                                        <p class="text-[13px] font-bold text-navy-900">{{ $leaveReq->student->name }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">{{ $leaveReq->student->student_code ?? '—' }}</p>
                                        <p class="text-[10px] text-text-muted mt-2 italic">Gửi lúc: {{ $leaveReq->created_at->format('H:i d/m/y') }}</p>
                                    </td>
                                    <td class="py-4 px-4 align-top w-[150px]">
                                        <span class="inline-flex items-center px-2 py-1 rounded-[4px] text-[12px] font-mono font-bold bg-surface-1 text-navy-900 border border-border-clean">
                                            {{ $leaveReq->date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <p class="text-[13px] text-navy-900 leading-relaxed max-w-[400px] break-words">{{ $leaveReq->reason }}</p>
                                        @if($leaveReq->proof_image_url)
                                        <div class="mt-2 flex flex-col gap-2">
                                            <a href="{{ $leaveReq->proof_image_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center gap-1.5 text-[12px] font-semibold text-blue-700 hover:text-blue-900">
                                                <x-ui-icon name="eye" class="w-4 h-4" />
                                                Xem ảnh minh chứng
                                            </a>
                                            <a href="{{ $leaveReq->proof_image_url }}" target="_blank" rel="noopener noreferrer" class="w-fit">
                                                <img src="{{ $leaveReq->proof_image_url }}" alt="Ảnh minh chứng nghỉ phép" class="w-28 h-28 rounded-[8px] border border-border-clean object-cover" loading="lazy">
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-top text-center w-[180px]">
                                        @if($leaveReq->status === 'pending')
                                        <div class="flex flex-col gap-2 relative">
                                            <div x-show="activeRequestId === {{ $leaveReq->id }}" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center">
                                                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </div>
                                            <button type="button"
                                                @click="updateLeaveStatus({{ $leaveReq->id }}, 'approved')"
                                                class="w-full h-8 text-[11px] font-bold uppercase text-teal-700 bg-teal-50 border border-teal-200 rounded-[6px] hover:bg-teal-100 transition-colors">
                                                Duyệt phép
                                            </button>
                                            <button type="button"
                                                @click="updateLeaveStatus({{ $leaveReq->id }}, 'rejected')"
                                                class="w-full h-8 text-[11px] font-bold uppercase text-red-700 bg-red-50 border border-red-200 rounded-[6px] hover:bg-red-100 transition-colors">
                                                Từ chối
                                            </button>
                                        </div>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-[4px] text-[11px] font-bold uppercase border
                                            {{ $leaveReq->status === 'approved' ? 'bg-teal-50 text-teal-700 border-teal-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                            {{ $leaveReq->status === 'approved' ? 'Đã duyệt' : 'Từ chối' }}
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- ═══ TAB: Điểm quá trình ═══ --}}
                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'grading'" x-transition.opacity.duration.150ms style="display:none;" x-data="gradeManager({{ $section->id }})" x-init="initData()">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-[18px] font-bold text-navy-900">Điểm quá trình</h3>
                            <p class="text-[12px] text-text-muted font-medium mt-1">Quản lý các cột điểm thành phần. Tổng trọng số: <span class="font-bold border-b border-dashed" :class="totalWeight > 100 ? 'text-red-500 border-red-500' : 'text-teal-600 border-teal-600'" x-text="totalWeight + '%'"></span></p>
                        </div>
                        @can('manage', $section)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('lecturer.classes.grades.export', $section) }}"
                                class="inline-flex h-10 items-center px-4 rounded-[8px] border border-border-clean bg-white text-[13px] font-semibold text-navy-900 hover:bg-surface-0 transition-colors">
                                Xuất Excel
                            </a>
                            <x-button variant="primary" @click="$dispatch('open-modal', 'column-modal'); isEditingColumn = false; columnData = {name: '', weight: 10};">
                                + Thêm cột điểm
                            </x-button>
                        </div>
                        @endcan
                    </div>

                    @if($section->students->isEmpty())
                    <div class="text-center py-10 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                        <p class="text-[13px] text-text-muted">Lớp học phần chưa có sinh viên, không thể nhập điểm.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white shadow-sm">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="sticky left-0 z-10 bg-surface-1 py-3 px-4 w-[250px] text-[12px] font-semibold text-text-muted border-r border-border-clean shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        Thành viên lớp
                                    </th>
                                    @forelse($section->gradeColumns->sortBy('order') as $col)
                                    <th class="py-2 px-3 text-center border-r border-border-clean min-w-[130px] group relative">
                                        <p class="text-[13px] font-bold text-navy-900 leading-tight" title="{{ $col->name }}">{{ \Illuminate\Support\Str::limit($col->name, 20) }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5 mb-1">{{ (float)$col->weight }}%</p>
                                        @can('manage', $section)
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 backdrop-blur-sm px-1 py-0.5 rounded shadow-sm border border-border-clean flex items-center hidden group-hover:flex gap-1.5 z-20">
                                            <button @click="editColumn({{ $col->id }}, '{{ addslashes($col->name) }}', {{ (float)$col->weight }})" class="text-[10px] text-blue-600 hover:text-blue-800" title="Sửa cột"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg></button>
                                            <button @click="deleteColumn({{ $col->id }})" class="text-[10px] text-red-600 hover:text-red-800" title="Xóa cột"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg></button>
                                        </div>
                                        @endcan
                                    </th>
                                    @empty
                                    <th class="py-3 px-4 text-center text-[12px] text-text-muted italic bg-surface-0 border-border-clean">
                                        Chưa có cột điểm nào. Hãy nhấp "+ Thêm cột điểm".
                                    </th>
                                    @endforelse
                                    <!-- Cột tính tổng tạm điểm quá trình -->
                                    @if($section->gradeColumns->count() > 0)
                                    <th class="py-2 px-3 text-center border-l bg-surface-0/50 min-w-[100px]">
                                        <p class="text-[12px] font-bold text-indigo-700">Tổng điểm QT</p>
                                        <p class="text-[10px] text-text-muted">(Tạm tính)</p>
                                    </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70 relative">
                                <!-- Overlay loading indicator per row inline update could exist, but we do top progress bar -->
                                <div x-show="isSaving" class="absolute top-0 left-0 right-0 h-0.5 bg-blue-500 z-20 animate-pulse" style="display: none;"></div>

                                @foreach($section->students->sortBy('name') as $student)
                                <tr class="hover:bg-surface-0 transition-colors group">
                                    <td class="sticky left-0 z-10 bg-white py-2.5 px-4 border-r border-border-clean shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] group-hover:bg-surface-0 transition-colors">
                                        <p class="text-[13px] font-semibold text-navy-900">{{ $student->name }}</p>
                                        <p class="text-[11px] text-text-muted font-mono mt-0.5">{{ $student->student_code ?? '—' }}</p>
                                    </td>

                                    @foreach($section->gradeColumns->sortBy('order') as $col)
                                    @php
                                    $grade = $col->studentGrades->where('student_id', $student->id)->first();
                                    $score = $grade ? $grade->score : '';
                                    $note = $grade ? $grade->note : '';
                                    @endphp
                                    <td class="p-0 border-r border-border-clean align-middle relative">
                                        <div class="absolute right-0 top-0 bottom-0 w-1 bg-green-400 opacity-0 transition-opacity" :class="{'opacity-100': saved['{{$col->id}}_{{$student->id}}']}"></div>
                                        <input type="number" step="0.01" min="0" max="10"
                                            class="w-full h-full min-h-[46px] text-center border-none bg-transparent text-[14px] font-bold text-navy-900 focus:ring-0 focus:bg-blue-50/50 hover:bg-gray-50/50 transition-colors outline-none cursor-text
                                            {{ $score === '' ? 'text-gray-400 font-normal placeholder:text-gray-300' : '' }}"
                                            placeholder="-"
                                            @can('manage', $section)
                                            x-model="scores['{{$col->id}}_{{$student->id}}']"
                                            @blur="saveScore({{ $col->id }}, {{ $student->id }}, $event.target.value)"
                                            @keydown.enter="$event.target.blur()"
                                            @endcan
                                            @cannot('manage', $section)
                                            value="{{ $score }}" disabled
                                            @endcannot />
                                        @can('manage', $section)
                                        <input type="hidden" x-init="initialScores['{{$col->id}}_{{$student->id}}'] = '{{ $score }}'; scores['{{$col->id}}_{{$student->id}}'] = '{{ $score }}';">
                                        @endcan
                                    </td>
                                    @endforeach

                                    @if($section->gradeColumns->count() > 0)
                                    <td class="py-2 px-3 text-center border-l bg-surface-0/30 align-middle">
                                        <div class="text-[14px] font-black text-indigo-700" x-text="calculateProcessGrade({{ $student->id }})"></div>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[12px] text-text-muted mt-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-1 align-baseline"></span> Có viền xanh lá: Điểm đã được lưu tự động thành công. Tab/Click ra ngoài để lưu điểm.
                    </p>
                    @endif

                    <x-modal name="column-modal" maxWidth="md">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-[18px] font-bold text-navy-900" x-text="isEditingColumn ? 'Sửa cột điểm' : 'Thêm cột điểm mới'"></h3>
                                <button @click="$dispatch('close-modal', 'column-modal')" class="text-text-muted hover:text-navy-900"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg></button>
                            </div>

                            <form @submit.prevent="submitColumnForm()" class="space-y-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Tên cột <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="columnData.name" required class="w-full border border-border-clean rounded-[6px] px-3 py-2 text-[13px] focus:border-blue-500 focus:ring-1 focus:ring-blue-200" placeholder="VD: Giữa kỳ, Bài tập số 1...">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Trọng số (%) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.5" min="0" max="100" x-model="columnData.weight" required class="w-full border border-border-clean rounded-[6px] px-3 py-2 text-[13px] focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
                                    <p class="text-[11px] text-text-muted mt-1">Nên tính trên tổng 100% của tất cả các cột điểm quá trình.</p>
                                </div>

                                <div class="pt-4 flex justify-end gap-2 border-t border-border-clean">
                                    <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'column-modal')">Huỷ</x-button>
                                    <x-button type="submit" variant="primary">
                                        <span x-show="!isSubmittingColumn" x-text="isEditingColumn ? 'Cập nhật' : 'Thêm cột'"></span>
                                        <span x-show="isSubmittingColumn">Đang xử lý...</span>
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </x-modal>
                </div>

                {{-- ═══ TAB: Thống kê ═══ --}}
                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'statistics'" x-transition.opacity.duration.150ms style="display:none;">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-[18px] font-bold text-navy-900">Thống kê điểm bài thi</h3>
                            <p class="text-[12px] text-text-muted font-medium mt-1">Theo từng bài thi: trung bình, cao nhất, thấp nhất và tỷ lệ đạt.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-1 text-text-muted border border-border-clean">
                            {{ $examStatistics->count() }} bài thi
                        </span>
                    </div>

                    @if($examStatistics->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                        <p class="text-[13px] font-medium text-text-muted">Lớp này chưa có bài thi để thống kê.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px] bg-white">
                        <table class="w-full text-left border-collapse min-w-[920px]">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Bài thi</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Mốc đạt</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Đã nộp</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Trung bình</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Cao nhất</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Thấp nhất</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Tỷ lệ đạt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($examStatistics as $stat)
                                @php
                                $statusLabel = match($stat->status) {
                                'in_progress' => ['Đang thi', 'bg-teal-50 text-teal-800 border-teal-200'],
                                'scheduled' => ['Đã lên lịch', 'bg-blue-50 text-blue-700 border-blue-200'],
                                'completed' => ['Đã hoàn thành', 'bg-gray-50 text-gray-700 border-gray-200'],
                                default => ['Đã huỷ', 'bg-red-50 text-red-700 border-red-200'],
                                };
                                @endphp
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-3 px-4 align-top">
                                        <p class="text-[13px] font-semibold text-navy-900">{{ $stat->exam_title }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">{{ $stat->date_range_text }} · {{ $stat->time_range_text }}</p>
                                        <span class="inline-flex items-center text-[10px] font-bold uppercase px-2 py-0.5 rounded-[4px] border mt-2 {{ $statusLabel[1] }}">
                                            {{ $statusLabel[0] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center text-[13px] font-semibold text-navy-900">
                                        {{ number_format($stat->pass_threshold, 2) }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-[13px] font-semibold text-navy-900">
                                        {{ $stat->submitted_count }}/{{ $stat->assigned_count }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-[13px] font-bold text-navy-900">
                                        {{ $stat->average_score !== null ? number_format($stat->average_score, 2) : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-[13px] font-bold text-blue-700">
                                        {{ $stat->highest_score !== null ? number_format($stat->highest_score, 2) : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-[13px] font-bold text-amber-700">
                                        {{ $stat->lowest_score !== null ? number_format($stat->lowest_score, 2) : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($stat->pass_rate !== null)
                                        <p class="text-[14px] font-black text-teal-700">{{ number_format($stat->pass_rate, 1) }}%</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">{{ $stat->passed_count }}/{{ $stat->submitted_count }} đạt</p>
                                        @else
                                        <span class="text-[12px] text-text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[12px] text-text-muted">Tỷ lệ đạt được tính trên số bài đã nộp của từng bài thi.</p>
                    @endif
                </div>

                {{-- ═══ TAB: Khiếu nại ═══ --}}
                <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'complaints'" x-transition.opacity.duration.150ms style="display:none;">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[18px] font-bold text-navy-900">Khiếu nại điểm thi</h3>
                        <p class="text-[12px] text-text-muted font-medium">Danh sách các khiếu nại của lớp học này.</p>
                    </div>

                    @if($section->complaints->isEmpty())
                    <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                        <x-ui-icon name="information-circle" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                        <p class="text-[13px] text-text-muted">Chưa có khiếu nại nào từ sinh viên trong lớp này.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto border border-border-clean rounded-[8px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-1 border-b border-border-clean">
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Sinh viên</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Điểm</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Lý do</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Trạng thái</th>
                                    <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($section->complaints as $complaint)
                                <tr class="hover:bg-surface-0 transition-colors">
                                    <td class="py-3 px-4 align-top">
                                        <p class="text-[13px] font-bold text-navy-900">{{ $complaint->student->name }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">{{ $complaint->created_at->format('H:i d/m/y') }}</p>
                                    </td>
                                    <td class="py-3 px-4 align-top text-center">
                                        <span class="text-[14px] font-bold text-navy-900">{{ number_format($complaint->current_score, 2) }}</span>
                                        @if($complaint->updated_score)
                                        <span class="block text-[11px] font-bold text-teal-600">→ {{ number_format($complaint->updated_score, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <p class="text-[12px] text-text-muted line-clamp-2" title="{{ $complaint->reason }}">{{ $complaint->reason }}</p>
                                    </td>
                                    <td class="py-3 px-4 align-top text-center">
                                        @php
                                        $st = match($complaint->status) {
                                        'pending' => ['bg-yellow-50 text-yellow-700 border-yellow-200', 'Chờ'],
                                        'resolved' => ['bg-teal-50 text-teal-700 border-teal-200', 'Xong'],
                                        'rejected' => ['bg-red-50 text-red-700 border-red-200', 'Từ chối'],
                                        default => ['bg-gray-50 text-gray-500 border-gray-200', 'N/A']
                                        };
                                        @endphp
                                        <span class="inline-flex items-center text-[10px] font-bold uppercase rounded-[4px] px-2 py-0.5 border {{ $st[0] }}">
                                            {{ $st[1] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 align-top text-center">
                                        @if($complaint->status === 'pending')
                                        <button @click="openReviewModal({{ $complaint->id }}, '{{ addslashes($complaint->student->name) }}', '{{ addslashes($complaint->reason) }}', {{ $complaint->current_score }})"
                                            class="text-[12px] font-bold text-blue-600 hover:underline">Xử lý</button>
                                        @else
                                        <span class="text-[11px] text-text-muted">Đã xử lý</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>

        @can('manage', $section)
        <x-slide-over name="create-schedule-inline-slide" title="Tạo lịch thi cho {{ $section->name ?? $section->code }}" maxWidth="2xl">
            <form @submit.prevent="submitScheduleForm($el)" class="space-y-5">
                @csrf
                <input type="hidden" name="course_section_ids[]" value="{{ $section->id }}">

                <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                    <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Đang lên lịch cho lớp</p>
                    <p class="text-[14px] font-bold text-navy-900 mt-0.5">{{ $section->name ?? $section->code }} ({{ $section->code }})</p>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <label class="text-[12px] font-semibold text-navy-900">Đề thi <span class="text-red-500">*</span></label>
                        <button type="button"
                            class="text-[12px] font-semibold text-blue-600 hover:text-blue-700"
                            @click="$dispatch('open-slide-over', 'quick-create-exam-slide')">
                            + Tạo đề thi mới
                        </button>
                    </div>
                    <select id="inline-exam-id" name="exam_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                        <option value="">-- Chọn đề thi --</option>
                        @foreach($ownedExamsForSection as $ex)
                        <option value="{{ $ex->id }}">[{{ $ex->subject->code }}] {{ $ex->title }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-[11px] text-text-muted">Nếu chưa có đề, bạn có thể tạo nhanh ngay tại đây rồi quay lại dropdown này.</p>
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="exam_id"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ngày bắt đầu <span class="text-red-500">*</span></label>
                        <input type="date" name="exam_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="exam_date"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ngày kết thúc <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="end_date"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ bắt đầu <span class="text-red-500">*</span></label>
                        <input type="text" name="start_time" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 08:30" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="start_time"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ kết thúc <span class="text-red-500">*</span></label>
                        <input type="text" name="end_time" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 17:45" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="end_time"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ghi chú</label>
                    <textarea name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" rows="3" placeholder="Lưu ý cho ca thi..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-slide-over', 'create-schedule-inline-slide')">Huỷ</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingSchedule">
                        <span x-show="!isSubmittingSchedule">Tạo lịch thi</span>
                        <span x-show="isSubmittingSchedule" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Đang tạo...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>

        <x-slide-over name="quick-create-exam-slide" title="Tạo đề thi nhanh cho {{ $section->subject->name ?? 'môn học' }}" maxWidth="2xl">
            <form @submit.prevent="submitQuickExamForm($el)" class="space-y-5">
                @csrf

                <input type="hidden" name="subject_id" value="{{ $section->subject_id }}">
                <input type="hidden" name="creation_mode" value="manual">
                <input type="hidden" name="exam_type" value="official">
                <input type="hidden" name="allow_late_entrance" value="1">
                <input type="hidden" name="late_entrance_limit_minutes" value="15">
                <input type="hidden" name="late_entrance_behavior" value="fixed_end">
                <input type="hidden" name="min_duration_before_submit" value="0">
                <input type="hidden" name="show_score_after_submit" value="1">
                <input type="hidden" name="show_answers_after_submit" value="0">
                <input type="hidden" name="multiple_choice_scoring_method" value="all_or_nothing">

                <div class="p-3 bg-surface-1 border border-border-clean rounded-lg">
                    <p class="text-[11px] font-bold text-text-muted uppercase tracking-wider">Môn học</p>
                    <p class="text-[14px] font-bold text-navy-900 mt-0.5">{{ $section->subject->code ?? 'N/A' }} - {{ $section->subject->name ?? 'Chưa gán môn' }}</p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Tên đề thi <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="VD: Kiểm tra nhanh tuần 5">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Thời lượng (phút) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration_minutes" min="1" value="45" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Mô tả</label>
                        <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Mô tả ngắn (tuỳ chọn)">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[12px] font-semibold text-navy-900">Chọn ít nhất 1 câu hỏi <span class="text-red-500">*</span></label>
                        <a href="{{ route('lecturer.exams.create') }}" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700">
                            Mở trình tạo đầy đủ
                        </a>
                    </div>

                    @if($quickQuestionPool->isEmpty())
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-[12px] text-amber-800">
                        Chưa có câu hỏi đã duyệt cho môn này. Vui lòng tạo câu hỏi trước tại Ngân hàng câu hỏi.
                    </div>
                    @else
                    <div class="max-h-[260px] overflow-y-auto border border-gray-200 rounded-lg bg-surface-0 divide-y divide-border-clean/70">
                        @foreach($quickQuestionPool as $question)
                        <label class="flex items-start gap-3 p-3 hover:bg-white cursor-pointer">
                            <input type="checkbox" name="question_ids[]" value="{{ $question->id }}"
                                class="mt-0.5 rounded border-gray-300 text-navy-900 focus:ring-indigo-500">
                            <span class="text-[12px] text-navy-900 leading-relaxed">{{ \Illuminate\Support\Str::limit(trim(strip_tags($question->content)), 180) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-slide-over', 'quick-create-exam-slide')">Huỷ</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingQuickExam || {{ $quickQuestionPool->isEmpty() ? 'true' : 'false' }}">
                        <span x-show="!isSubmittingQuickExam">Tạo đề và chọn luôn</span>
                        <span x-show="isSubmittingQuickExam" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Đang tạo...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>

        <x-modal name="show-pin-modal" maxWidth="2xl">
            <div class="p-5 sm:p-7 lg:p-10 text-center bg-gradient-to-br from-indigo-900 via-navy-900 to-blue-900 relative overflow-hidden">
                <div class="absolute top-4 right-4 sm:top-5 sm:right-5 z-10">
                    <button @click="$dispatch('close-modal', 'show-pin-modal')" class="p-3 sm:p-3.5 text-white/60 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-colors backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="relative z-10 w-full flex flex-col items-center">
                    <h3 class="text-base sm:text-xl font-semibold text-blue-200 mb-5 sm:mb-7 uppercase tracking-[0.2em]">Mã điểm danh</h3>
                    <div class="bg-white p-4 sm:p-6 lg:p-7 rounded-2xl shadow-[0_0_40px_rgba(59,130,246,0.5)] flex flex-col items-center w-full max-w-[560px]">
                        <div class="w-[280px] h-[280px] sm:w-[340px] sm:h-[340px] lg:w-[420px] lg:h-[420px] mb-5 border-[0.5px] border-border-clean p-3 rounded-[14px] bg-white">
                            <img id="display-qr-code" src="" alt="QR Code" class="w-full h-full object-contain" />
                        </div>
                        <p id="display-pin-code" class="text-[44px] sm:text-[60px] lg:text-[76px] leading-none font-black text-navy-900 font-mono tracking-[0.22em] pl-[0.22em]"></p>
                    </div>
                    <p class="text-base sm:text-lg font-semibold text-white/80 mt-6 sm:mt-8 mb-2">Sinh viên quét QR để tự động check-in</p>
                    <p class="text-sm sm:text-base text-blue-100/70 uppercase tracking-[0.18em]">Hoặc nhập thủ công mã chữ cái</p>
                </div>

                <!-- Background decorations -->
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl"></div>
            </div>
        </x-modal>

        <x-modal name="show-invite-code-modal" maxWidth="2xl">
            <div class="p-5 sm:p-7 lg:p-10 text-center bg-gradient-to-br from-indigo-900 via-navy-900 to-blue-900 relative overflow-hidden">
                <div class="absolute top-4 right-4 sm:top-5 sm:right-5 z-10">
                    <button @click="$dispatch('close-modal', 'show-invite-code-modal')" class="p-3 sm:p-3.5 text-white/60 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-colors backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="relative z-10 w-full flex flex-col items-center">
                    <h3 class="text-base sm:text-xl font-semibold text-blue-200 mb-5 sm:mb-7 uppercase tracking-[0.2em]">Mã vào lớp học phần</h3>
                    <div class="bg-white p-4 sm:p-6 lg:p-7 rounded-2xl shadow-[0_0_40px_rgba(59,130,246,0.5)] flex flex-col items-center w-full max-w-[560px]">
                        <div class="w-[280px] h-[280px] sm:w-[340px] sm:h-[340px] lg:w-[420px] lg:h-[420px] mb-5 border-[0.5px] border-border-clean p-3 rounded-[14px] bg-white">
                            <img id="display-invite-qr-code" src="" alt="QR Code" class="w-full h-full object-contain" />
                        </div>
                        <p id="display-invite-code" class="text-[44px] sm:text-[60px] lg:text-[76px] leading-none font-black text-navy-900 font-mono tracking-[0.22em] pl-[0.22em]"></p>
                    </div>
                    <p class="text-base sm:text-lg font-semibold text-white/80 mt-6 sm:mt-8 mb-2">Sinh viên quét QR để tự động tham gia lớp</p>
                    <p class="text-sm sm:text-base text-blue-100/70 uppercase tracking-[0.18em]">Hoặc nhập thủ công mã mời</p>
                </div>

                <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl"></div>
            </div>
        </x-modal>

        <x-modal name="create-attendance-session-modal" maxWidth="lg">
            <div class="p-6 md:p-8" x-data="attendanceManager({{ $section->id }})">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-bold text-navy-900">Tạo phiên điểm danh mới</h3>
                    <button @click="$dispatch('close-modal', 'create-attendance-session-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAttendanceSessionForm($el)" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Tên phiên điểm danh <span class="text-red-500">*</span></label>
                        <x-text-input name="title" type="text" value="Buổi học ngày {{ now()->format('d/m/Y') }}" required placeholder="Ví dụ: Buổi 1, Bù tuần 2..." />
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="title"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Ngày thực hiện <span class="text-red-500">*</span></label>
                        <x-text-input name="date" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="date"></p>
                    </div>
                    <div class="pt-2 flex justify-end gap-3">
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'create-attendance-session-modal')">Hủy</x-button>
                        <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingSession">
                            <span x-show="!isSubmittingSession">Tạo phiên</span>
                            <span x-show="isSubmittingSession">Đang tạo...</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal>

        <x-modal name="create-notification-modal" maxWidth="lg">
            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-bold text-navy-900">Tạo thông báo</h3>
                    <button @click="$dispatch('close-modal', 'create-notification-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('lecturer.classes.notifications.store', $section) }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Tiêu đề</label>
                        <x-text-input name="title" type="text" :value="old('title')" required placeholder="Nhập tiêu đề thông báo mới" />
                        @error('title')
                        <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-navy-900 mb-1.5">Nội dung chi tiết</label>
                        <textarea name="message" required rows="5" placeholder="Viết nội dung thông báo gửi đến sinh viên..." class="w-full p-4 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[14px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none resize-y">{{ old('message') }}</textarea>
                        @error('message')
                        <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary">
                            Đăng thông báo
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal>

        {{-- Modal Xử lý khiếu nại --}}
        <x-modal name="review-modal" maxWidth="lg">
            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[20px] font-bold text-navy-900">Xử lý khiếu nại</h3>
                    <button @click="$dispatch('close-modal', 'review-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-surface-1 border border-border-clean rounded-lg space-y-3">
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Sinh viên</span>
                            <span class="text-[14px] font-bold text-navy-900" x-text="reviewStudentName"></span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Lý do khiếu nại</span>
                            <span class="text-[13px] text-navy-900 leading-relaxed break-words" x-text="reviewReason"></span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-text-muted font-medium uppercase tracking-wider mb-1">Điểm hiện tại</span>
                            <span class="text-[14px] font-bold text-red-500" x-text="reviewCurrentScore"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-[8px] cursor-pointer transition-all"
                            :class="resolutionStatus === 'resolved' ? 'border-teal-500 bg-teal-50/30' : 'border-border-clean hover:bg-surface-0'">
                            <input type="radio" name="status" value="resolved" x-model="resolutionStatus" class="sr-only">
                            <div class="w-4 h-4 rounded-full border border-teal-500 flex items-center justify-center" :class="resolutionStatus === 'resolved' ? 'bg-teal-500' : 'bg-white'">
                                <svg x-show="resolutionStatus === 'resolved'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-[13px] font-bold text-teal-700">Chấp nhận</span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-[8px] cursor-pointer transition-all"
                            :class="resolutionStatus === 'rejected' ? 'border-red-500 bg-red-50/30' : 'border-border-clean hover:bg-surface-0'">
                            <input type="radio" name="status" value="rejected" x-model="resolutionStatus" class="sr-only">
                            <div class="w-4 h-4 rounded-full border border-red-500 flex items-center justify-center" :class="resolutionStatus === 'rejected' ? 'bg-red-500' : 'bg-white'">
                                <svg x-show="resolutionStatus === 'rejected'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <span class="text-[13px] font-bold text-red-700">Từ chối</span>
                        </label>
                    </div>

                    <div x-show="resolutionStatus === 'resolved'" x-transition.opacity.duration.200ms>
                        <label for="updatedScore" class="block text-[12px] font-semibold text-navy-900 mb-1.5">Điểm mới (thay thế điểm cũ) <span class="text-red-500">*</span></label>
                        <input id="updatedScore" type="number" step="0.01" min="0" x-model="updatedScore" placeholder="Nhập điểm cập nhật" class="w-full h-11 px-3 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[14px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none" />
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Ghi chú phản hồi <span class="text-red-500">*</span></label>
                        <textarea x-model="reviewerNote" rows="3" placeholder="Nhập giải thích cho quyết định của bạn..."
                            class="w-full p-3 bg-white border-[1.5px] border-border-clean rounded-[6px] text-[13px] text-navy-900 placeholder:text-text-muted focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none resize-y"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'review-modal')">Hủy</x-button>
                        <x-button type="button" variant="primary" @click="submitReview()" x-bind:disabled="isSubmittingReview">
                            <span x-show="!isSubmittingReview">Lưu kết quả</span>
                            <span x-show="isSubmittingReview">Đang lưu...</span>
                        </x-button>
                    </div>
                </div>
            </div>
        </x-modal>
        @endcan
    </div>

    @php
    $lecturerClassShowConfig = [
    'scheduleStoreUrl' => route('lecturer.schedules.store'),
    'examStoreUrl' => route('lecturer.exams.store'),
    'subjectCode' => $section->subject->code ?? 'SUB',
    'gradeTotalWeight' => (float) $section->gradeColumns->sum('weight'),
    'gradeWeights' => $section->gradeColumns->mapWithKeys(fn($col) => [(string) $col->id => (float) $col->weight]),
    ];
    @endphp
    <script id="lecturer-class-show-config" type="application/json">
        @json($lecturerClassShowConfig)
    </script>

    @push('scripts')
    @vite(['resources/js/pages/lecturer/classes-show.js'])
    @endpush
</x-app-layout>