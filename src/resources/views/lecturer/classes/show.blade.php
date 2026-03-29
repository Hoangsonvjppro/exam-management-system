<x-app-layout>
    @section('title', ($section->name ?? $section->code) . ' — EMS')
    @section('page-title', 'Class Workspace')

    @php
    $activeTab = request()->query('tab', 'overview');
    if (!in_array($activeTab, ['overview', 'students', 'attendance', 'grading', 'complaints'], true)) {
    $activeTab = 'overview';
    }

    $classSchedules = $section->classSchedules()
    ->orderBy('day_of_week')
    ->orderBy('start_period')
    ->get();

    $ownedExamsForSection = \App\Models\Exam::query()
    ->where('created_by', auth()->id())
    ->where('subject_id', $section->subject_id)
    ->with('subject')
    ->orderByDesc('id')
    ->get();

    $quickQuestionPool = \App\Models\Question::approved()
    ->where('subject_id', $section->subject_id)
    ->orderByDesc('updated_at')
    ->limit(120)
    ->get(['id', 'content']);

    $dayMap = [
    2 => 'Thứ Hai',
    3 => 'Thứ Ba',
    4 => 'Thứ Tư',
    5 => 'Thứ Năm',
    6 => 'Thứ Sáu',
    7 => 'Thứ Bảy',
    8 => 'Chủ Nhật',
    ];
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
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                        @if($section->status === 'active') bg-teal-50 text-teal-800 border border-teal-200
                        @elseif($section->status === 'archived') bg-surface-1 text-text-muted border border-border-clean
                        @else bg-red-50 text-red-700 border border-red-200 @endif">
                        {{ match($section->status) {
                            'active'   => 'Đang mở',
                            'archived' => 'Đã lưu trữ',
                            default    => 'Đã huỷ',
                        } }}
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
                        @click="switchTab('grading')"
                        :class="activeTab === 'grading' ? 'bg-white text-navy-900 border-border-clean' : 'text-text-muted border-transparent'"
                        class="h-9 px-4 border rounded-[6px] text-[13px] font-semibold transition-colors">
                        Điểm quá trình
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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

                    <x-card variant="featured" padding="true">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-text-muted">Thông báo nhanh</p>
                        <p class="mt-2 text-[13px] text-navy-900 leading-relaxed">Nên cập nhật thông báo lớp trước mỗi đợt kiểm tra để sinh viên nắm rõ lịch và yêu cầu phòng thi.</p>
                        @can('manage', $section)
                        <x-button variant="secondary" size="sm" class="mt-3" @click="$dispatch('open-modal', 'create-notification-modal')">
                            Đăng thông báo ngay
                        </x-button>
                        @endcan
                    </x-card>
                </div>

                <x-card padding="true">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[16px] font-bold text-navy-900">Thời khóa biểu lớp học phần</h3>
                        <span class="text-[12px] text-text-muted">{{ $classSchedules->count() }} buổi đã cấu hình</span>
                    </div>

                    @if($classSchedules->isEmpty())
                    <div class="text-center py-10 bg-surface-0 border border-border-clean border-dashed rounded-[8px]">
                        <p class="text-[13px] text-text-muted">Chưa có dữ liệu thời khóa biểu cho lớp học phần này.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-[1.5px] border-border-clean">
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Thứ</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Tiết học</th>
                                    <th class="py-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Phòng học</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-clean/70">
                                @foreach($classSchedules as $item)
                                <tr>
                                    <td class="py-3 px-3 text-[13px] font-semibold text-navy-900">{{ $dayMap[$item->day_of_week] ?? 'N/A' }}</td>
                                    <td class="py-3 px-3 text-[13px] text-text-muted">Tiết {{ $item->start_period }} - {{ $item->end_period }}</td>
                                    <td class="py-3 px-3 text-[13px] text-text-muted">{{ $item->room ?: 'Chưa cập nhật' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                                        {{ $schedule->exam_date->format('d/m/Y') }}
                                        @if($schedule->start_time)
                                        <div>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</div>
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
                                                    onclick="return confirm('Xoá lịch thi này?')">
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

            <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'attendance'" x-transition.opacity.duration.150ms style="display:none;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[18px] font-bold text-navy-900">Lưới điểm danh theo buổi</h3>
                </div>

                <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="clipboard-document-check" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                    <p class="text-sm text-text-muted font-medium">Tính năng điểm danh đang được phát triển.</p>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-5" x-show="activeTab === 'grading'" x-transition.opacity.duration.150ms style="display:none;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[18px] font-bold text-navy-900">Điểm quá trình</h3>
                </div>

                <div class="text-center py-12 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                    <x-ui-icon name="chart-bar" class="w-12 h-12 text-blue-100 mx-auto mb-4" />
                    <p class="text-sm text-text-muted font-medium">Tính năng thống kê và quản lý điểm đang được phát triển.</p>
                </div>
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
                                            'pending'   => ['bg-yellow-50 text-yellow-700 border-yellow-200', 'Chờ'],
                                            'resolved'  => ['bg-teal-50 text-teal-700 border-teal-200', 'Xong'],
                                            'rejected'  => ['bg-red-50 text-red-700 border-red-200', 'Từ chối'],
                                            default     => ['bg-gray-50 text-gray-500 border-gray-200', 'N/A']
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
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ngày thi <span class="text-red-500">*</span></label>
                        <input type="date" name="exam_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="exam_date"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Số SV tối đa</label>
                        <input type="number" name="max_students" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Không giới hạn">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ bắt đầu <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="start_time"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ kết thúc <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
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
                                <svg x-show="resolutionStatus === 'resolved'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[13px] font-bold text-teal-700">Chấp nhận</span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-[8px] cursor-pointer transition-all"
                            :class="resolutionStatus === 'rejected' ? 'border-red-500 bg-red-50/30' : 'border-border-clean hover:bg-surface-0'">
                            <input type="radio" name="status" value="rejected" x-model="resolutionStatus" class="sr-only">
                            <div class="w-4 h-4 rounded-full border border-red-500 flex items-center justify-center" :class="resolutionStatus === 'rejected' ? 'bg-red-500' : 'bg-white'">
                                <svg x-show="resolutionStatus === 'rejected'" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
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

    <script>
        function classWorkspaceManager(initialTab) {
            return {
                activeTab: initialTab || 'overview',
                isSubmittingSchedule: false,
                isSubmittingQuickExam: false,
                isSubmittingReview: false,
                complaintId: null,
                reviewStudentName: '',
                reviewReason: '',
                reviewCurrentScore: 0,
                resolutionStatus: 'resolved',
                updatedScore: '',
                reviewerNote: '',

                switchTab(tab) {
                    this.activeTab = tab;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState({}, '', url);
                },

                clearErrors(formElement) {
                    formElement.querySelectorAll('[data-error]').forEach(el => {
                        el.textContent = '';
                        el.classList.add('hidden');
                    });
                },

                showErrors(formElement, errors) {
                    for (const [field, messages] of Object.entries(errors)) {
                        const errorEl = formElement.querySelector(`[data-error="${field}"]`);
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.remove('hidden');
                        }
                    }
                },

                async submitScheduleForm(formElement) {
                    this.isSubmittingSchedule = true;
                    this.clearErrors(formElement);

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.schedules.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.$dispatch('close-slide-over', 'create-schedule-inline-slide');
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: result.message,
                                    type: 'success'
                                }
                            }));
                            setTimeout(() => window.location.reload(), 800);
                        } else if (response.status === 422 && result.errors) {
                            this.showErrors(formElement, result.errors);
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: result.message || 'Có lỗi xảy ra.',
                                    type: 'error'
                                }
                            }));
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Có lỗi hệ thống xảy ra!',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.isSubmittingSchedule = false;
                    }
                },

                async submitQuickExamForm(formElement) {
                    this.isSubmittingQuickExam = true;

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.exams.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                            },
                            body: formData,
                            redirect: 'follow',
                        });

                        if (!response.ok) {
                            throw new Error('Failed to create exam');
                        }

                        const createdUrl = response.url || '';
                        const match = createdUrl.match(/\/lecturer\/exams\/(\d+)/);
                        const examId = match ? match[1] : null;

                        if (!examId) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: 'Không thể tạo đề thi nhanh. Vui lòng thử lại với trình tạo đầy đủ.',
                                    type: 'error'
                                }
                            }));
                            return;
                        }

                        const title = String(formData.get('title') || 'Đề thi mới');
                        const examSelect = document.getElementById('inline-exam-id');
                        if (examSelect) {
                            const exists = Array.from(examSelect.options).some(opt => String(opt.value) === String(examId));
                            if (!exists) {
                                const option = document.createElement('option');
                                option.value = examId;
                                option.textContent = `[{{ $section->subject->code ?? 'SUB' }}] ${title}`;
                                examSelect.appendChild(option);
                            }
                            examSelect.value = examId;
                        }

                        this.$dispatch('close-slide-over', 'quick-create-exam-slide');
                        formElement.reset();

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Đã tạo đề thi mới và tự động chọn vào lịch thi.',
                                type: 'success'
                            }
                        }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Không thể tạo đề thi nhanh. Vui lòng kiểm tra dữ liệu đầu vào.',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.isSubmittingQuickExam = false;
                    }
                },

                openReviewModal(id, studentName, reason, currentScore) {
                    this.complaintId = id;
                    this.reviewStudentName = studentName;
                    this.reviewReason = reason;
                    this.reviewCurrentScore = currentScore;
                    this.resolutionStatus = 'resolved';
                    this.updatedScore = currentScore;
                    this.reviewerNote = '';
                    this.$dispatch('open-modal', 'review-modal');
                },

                async submitReview() {
                    if (!this.reviewerNote || this.reviewerNote.trim().length < 5) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Vui lòng nhập ghi chú phản hồi (ít nhất 5 ký tự).', type: 'error' }
                        }));
                        return;
                    }

                    if (this.resolutionStatus === 'resolved' && (this.updatedScore === '' || this.updatedScore < 0)) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Vui lòng nhập điểm mới hợp lệ.', type: 'error' }
                        }));
                        return;
                    }

                    this.isSubmittingReview = true;

                    try {
                        const response = await fetch(`/lecturer/complaints/${this.complaintId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                status: this.resolutionStatus,
                                reviewer_note: this.reviewerNote,
                                updated_score: this.resolutionStatus === 'resolved' ? parseFloat(this.updatedScore) : null
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message, type: 'success' }
                            }));
                            this.$dispatch('close-modal', 'review-modal');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message || 'Có lỗi xảy ra', type: 'error' }
                            }));
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Lỗi kết nối máy chủ', type: 'error' }
                        }));
                    } finally {
                        this.isSubmittingReview = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>