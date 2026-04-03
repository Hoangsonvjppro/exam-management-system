<x-card class="flex flex-col h-full overflow-hidden"
    x-show="(searchQuery === '' || '{{ strtolower(($section->name ?? '') . ' ' . $section->code) }}'.includes(searchQuery.toLowerCase()))
        && (statusFilter === 'all' || statusFilter === '{{ $section->status }}')
        && (semesterFilter === 'all' || semesterFilter === '{{ (string) $section->semester_id }}')
        && (subjectFilter === 'all' || subjectFilter === '{{ (string) $section->subject_id }}')">
    @php
    $semesterLifecycle = $section->semester?->lifecycle_status;

    if ($section->status === 'archived') {
    $cardStatusText = 'Đã lưu trữ';
    $cardStatusClass = 'bg-surface-1 text-text-muted border-[0.5px] border-border-clean';
    } elseif ($section->status === 'cancelled') {
    $cardStatusText = 'Đã huỷ';
    $cardStatusClass = 'bg-red-50 text-red-700 border-[0.5px] border-red-200';
    } elseif ($semesterLifecycle === \App\Models\Semester::STATUS_UPCOMING) {
    $cardStatusText = 'Sắp mở';
    $cardStatusClass = 'bg-amber-50 text-amber-800 border-[0.5px] border-amber-200';
    } elseif ($semesterLifecycle === \App\Models\Semester::STATUS_CURRENT) {
    $cardStatusText = 'Đang diễn ra';
    $cardStatusClass = 'bg-teal-50 text-teal-800 border-[0.5px] border-teal-200';
    } elseif ($semesterLifecycle === \App\Models\Semester::STATUS_ENDED) {
    $cardStatusText = 'Đã kết thúc';
    $cardStatusClass = 'bg-slate-100 text-slate-700 border-[0.5px] border-slate-300';
    } else {
    $cardStatusText = 'Đang hoạt động';
    $cardStatusClass = 'bg-teal-50 text-teal-800 border-[0.5px] border-teal-200';
    }
    @endphp

    {{-- Card Top --}}
    <div class="px-5 py-4 border-b-[0.5px] border-border-clean
        @if($section->status === 'active') bg-surface-1
        @elseif($section->status === 'archived') bg-surface-1
        @else bg-red-50 @endif">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-bold text-[10px] uppercase tracking-widest text-text-muted opacity-70">{{ $section->code }}</p>
                <h3 class="font-bold text-lg text-navy-900 leading-tight mt-1 group-hover:text-blue-600 transition-colors">{{ $section->name ?? $section->code }}</h3>
            </div>
            <span class="uppercase text-[10px] font-bold px-2 py-1 rounded-[4px] shrink-0 {{ $cardStatusClass }}">
                {{ $cardStatusText }}
            </span>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-6 flex-1 space-y-4">
        <div class="flex items-center justify-between text-sm">
            <span class="font-bold text-xs uppercase tracking-wider text-text-muted opacity-80">Môn học</span>
            <span class="font-bold text-navy-900 text-right max-w-[65%] truncate">{{ $section->subject->code ?? 'N/A' }} - {{ $section->subject->name ?? 'Chưa gán môn' }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="font-bold text-xs uppercase tracking-wider text-text-muted opacity-80">Học kỳ</span>
            <span class="font-bold text-navy-900">{{ $section->semester->name ?? 'Chưa gán học kỳ' }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="font-bold text-xs uppercase tracking-wider text-text-muted opacity-80">Sinh viên</span>
            <span class="font-bold text-navy-900">{{ $section->students_count ?? 0 }} <span class="text-text-muted font-medium ml-1">/ {{ $section->max_students }}</span></span>
        </div>
    </div>

    {{-- Card Footer --}}
    <div class="px-5 pb-5 pt-2 flex gap-3">
        <x-button variant="primary" href="{{ route('lecturer.classes.show', $section) }}" class="flex-1 text-center justify-center">
            Mở workspace
        </x-button>
        <x-button variant="outline" href="{{ route('lecturer.classes.show', ['section' => $section, 'tab' => 'attendance']) }}" class="px-3">
            Điểm danh
        </x-button>
    </div>
</x-card>