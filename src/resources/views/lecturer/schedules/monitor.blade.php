<x-app-layout>
    @section('title', 'Giám sát ca thi — EMS')
    @section('page-title', 'Giám sát ca thi')

    @php
    $runtimeStatus = $schedule->runtime_status;
    $runtimeStatusMap = [
    'scheduled' => ['Đã lên lịch', 'bg-[#EBF2FA] text-[#1A3A6B] border border-[#D6E2F0]', 'bg-[#1A3A6B]'],
    'in_progress' => ['Đang diễn ra', 'bg-[#FFFBEB] text-[#92400E] border border-[#FDE68A]', 'bg-[#D97706]'],
    'completed' => ['Hoàn thành', 'bg-[#ECFDF5] text-[#065F46] border border-[#A7F3D0]', 'bg-[#059669]'],
    'cancelled' => ['Đã hủy', 'bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]', 'bg-[#DC2626]'],
    ];
    [$statusLabel, $statusClass, $dotClass] = $runtimeStatusMap[$runtimeStatus] ?? ['—', 'bg-[#F3F4F6] text-[#6B7C99] border border-[#E5E7EB]', 'bg-[#6B7C99]'];
    @endphp

    <div class="space-y-6"
        x-data="{
            autoRefresh: true,
            secondsLeft: 20,
            timer: null,
            startTimer() {
                this.timer = setInterval(() => {
                    if (!this.autoRefresh) {
                        return;
                    }

                    if (this.secondsLeft <= 1) {
                        window.location.reload();
                        return;
                    }

                    this.secondsLeft -= 1;
                }, 1000);
            },
            refreshNow() {
                window.location.reload();
            }
        }"
        x-init="startTimer()">

        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-navy-900">{{ $schedule->exam->title }}</h2>
                    <p class="mt-1 text-sm text-text-muted">
                        {{ $schedule->courseSection->name ?? 'Không xác định lớp' }}
                        · {{ $schedule->exam_date->format('d/m/Y') }}
                        · {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                    </p>
                    <p class="mt-1 text-sm text-text-muted">Môn: {{ $schedule->exam->subject->name ?? 'Không xác định' }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }} {{ $runtimeStatus === 'in_progress' ? 'animate-pulse' : '' }}"></span>
                        {{ $statusLabel }}
                    </span>

                    <button type="button"
                        @click="refreshNow()"
                        class="px-3 py-1.5 rounded-[8px] border border-border-clean text-xs font-semibold text-navy-900 hover:bg-surface-1 transition-colors">
                        Làm mới ngay
                    </button>

                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[8px] border border-border-clean text-xs text-text-muted bg-white">
                        <input type="checkbox" class="rounded border-gray-300" x-model="autoRefresh" @change="secondsLeft = 20">
                        Tự làm mới
                        <span class="font-semibold text-navy-900" x-show="autoRefresh" x-text="secondsLeft + 's'"></span>
                    </label>

                    <a href="{{ route('lecturer.schedules.index') }}"
                        class="px-3 py-1.5 rounded-[8px] bg-navy-900 text-white text-xs font-semibold hover:bg-navy-950 transition-colors">
                        Quay lại lịch thi
                    </a>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Được phân thi</p>
                <p class="mt-2 text-2xl font-bold text-navy-900">{{ $assignedCount }}</p>
            </x-card>

            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Chưa thi</p>
                <p class="mt-2 text-2xl font-bold text-[#1D4ED8]">{{ $notStartedCount }}</p>
            </x-card>

            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Đang thi</p>
                <p class="mt-2 text-2xl font-bold text-[#D97706]">{{ $inProgressCount }}</p>
            </x-card>

            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Đã nộp</p>
                <p class="mt-2 text-2xl font-bold text-[#059669]">{{ $submittedCount }}</p>
            </x-card>

            <x-card padding="true">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-text-muted">Tỉ lệ hoàn thành</p>
                <p class="mt-2 text-2xl font-bold text-navy-900">{{ $completionRate }}<span class="text-sm text-text-muted">%</span></p>
            </x-card>
        </div>

        <x-card padding="true">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-bold text-navy-900">Cảnh báo giám sát</h3>
                <span class="text-xs font-semibold text-text-muted">{{ $warnings->count() }} cảnh báo</span>
            </div>

            @if($warnings->isEmpty())
            <div class="text-sm text-text-muted py-3">Chưa có cảnh báo nào trong ca thi này.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-clean">
                            <th class="text-left py-2 px-2 font-semibold text-text-muted">Sinh viên</th>
                            <th class="text-center py-2 px-2 font-semibold text-text-muted">Trạng thái</th>
                            <th class="text-center py-2 px-2 font-semibold text-text-muted">Chuyển tab</th>
                            <th class="text-center py-2 px-2 font-semibold text-text-muted">Mức cảnh báo</th>
                            <th class="text-left py-2 px-2 font-semibold text-text-muted">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $warning)
                        @php
                        $warningClass = match ($warning['warning_level']) {
                        'high' => 'bg-red-50 text-red-700 border border-red-200',
                        'medium' => 'bg-amber-50 text-amber-700 border border-amber-200',
                        default => 'bg-blue-50 text-blue-700 border border-blue-200',
                        };
                        @endphp
                        <tr class="border-b border-border-clean/70 hover:bg-surface-0">
                            <td class="py-2.5 px-2">
                                <p class="font-semibold text-navy-900">{{ $warning['name'] }}</p>
                                <p class="text-xs text-text-muted">{{ $warning['student_code'] ?? '—' }}</p>
                            </td>
                            <td class="py-2.5 px-2 text-center text-text-muted">
                                {{ $warning['attempt_status'] === 'in_progress' ? 'Đang thi' : 'Đã nộp' }}
                            </td>
                            <td class="py-2.5 px-2 text-center font-semibold text-navy-900">
                                {{ $warning['tab_switch_count'] }}
                            </td>
                            <td class="py-2.5 px-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $warningClass }}">
                                    {{ $warning['warning_level'] === 'high' ? 'Cao' : ($warning['warning_level'] === 'medium' ? 'Trung bình' : 'Thấp') }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2 text-text-muted">{{ $warning['warning_message'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-card>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <x-card padding="true" class="xl:col-span-1">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-navy-900">Chưa thi</h3>
                    <span class="text-xs font-semibold text-text-muted">{{ $notStartedCount }} SV</span>
                </div>

                @if($notStartedStudents->isEmpty())
                <p class="text-sm text-text-muted">Không còn sinh viên nào ở trạng thái chưa thi.</p>
                @else
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($notStartedStudents as $student)
                    <div class="rounded-[10px] border border-border-clean bg-surface-0 px-3 py-2">
                        <p class="text-sm font-semibold text-navy-900">{{ $student['name'] }}</p>
                        <p class="text-xs text-text-muted">{{ $student['student_code'] ?? '—' }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-card>

            <x-card padding="true" class="xl:col-span-1">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-navy-900">Đang thi</h3>
                    <span class="text-xs font-semibold text-text-muted">{{ $inProgressCount }} SV</span>
                </div>

                @if($inProgressStudents->isEmpty())
                <p class="text-sm text-text-muted">Hiện chưa có sinh viên nào đang làm bài.</p>
                @else
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($inProgressStudents as $student)
                    <div class="rounded-[10px] border border-amber-200 bg-amber-50/40 px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-navy-900">{{ $student['name'] }}</p>
                            <span class="text-[11px] font-semibold text-amber-700">Tab: {{ $student['tab_switch_count'] }}</span>
                        </div>
                        <p class="text-xs text-text-muted">{{ $student['student_code'] ?? '—' }}</p>
                        <p class="text-xs text-text-muted mt-1">Bắt đầu: {{ $student['started_at']?->format('H:i:s d/m/Y') ?? '—' }}</p>
                        <p class="text-xs text-text-muted">Đã lưu: {{ $student['submitted_answers_count'] }} câu</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-card>

            <x-card padding="true" class="xl:col-span-1">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-navy-900">Đã nộp bài</h3>
                    <span class="text-xs font-semibold text-text-muted">{{ $submittedCount }} SV</span>
                </div>

                @if($submittedStudents->isEmpty())
                <p class="text-sm text-text-muted">Chưa có sinh viên nào nộp bài.</p>
                @else
                <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($submittedStudents as $student)
                    <div class="rounded-[10px] border border-emerald-200 bg-emerald-50/40 px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-navy-900">{{ $student['name'] }}</p>
                            <span class="text-[11px] font-semibold text-emerald-700">
                                {{ $student['total_score'] !== null ? number_format((float) $student['total_score'], 1) . '/10' : '—' }}
                            </span>
                        </div>
                        <p class="text-xs text-text-muted">{{ $student['student_code'] ?? '—' }}</p>
                        <p class="text-xs text-text-muted mt-1">Nộp lúc: {{ $student['completed_at']?->format('H:i:s d/m/Y') ?? '—' }}</p>
                        <p class="text-xs text-text-muted">Tab: {{ $student['tab_switch_count'] }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>