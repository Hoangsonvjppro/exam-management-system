<x-app-layout>
    @section('title', 'Dashboard - Sinh vien')
    @section('page-title', 'Tong quan hoc tap')

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-4 bg-teal-50 border-[0.5px] border-teal-200 rounded-[6px] font-medium text-teal-800 text-[13px]">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-red-50 border-[0.5px] border-red-200 rounded-[6px] font-medium text-red-800 text-[13px]">{{ session('error') }}</div>
        @endif

        <x-card padding="true" variant="featured">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h2 class="text-[22px] md:text-[28px] font-bold text-navy-900 leading-tight">Xin chào, {{ auth()->user()->name }}!</h2>
                    <p class="mt-2 text-[13px] text-text-muted">
                        @if (auth()->user()->student_code)
                        Mã sinh viên: <span class="font-semibold text-navy-900">{{ auth()->user()->student_code }}</span>
                        @if(auth()->user()->class_name)
                        — Lớp: <span class="font-semibold text-navy-900">{{ auth()->user()->class_name }}</span>
                        @endif
                        @else
                        Bạn chưa cập nhật thông tin sinh viên.
                        <a href="{{ route('onboarding.show') }}" class="text-blue-400 font-medium hover:underline">Hoàn tất hồ sơ ngay</a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="outline" onclick="document.getElementById('join-class-modal').classList.remove('hidden')">
                        + Tham gia lớp
                    </x-button>
                    <x-button variant="secondary" href="{{ route('profile.edit') }}">
                        Hồ sơ
                    </x-button>
                </div>
            </div>
        </x-card>

        @php
        // Lấy danh sách lớp học đã tham gia
        $enrolledSections = auth()->user()->enrolledSections()->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        // Lấy toàn bộ bài kiểm tra thuộc về các lớp sinh viên đã tham gia
        $exams = \App\Models\Exam::whereIn('course_section_id', $sectionIds)
        ->with('courseSection') // Load kèm thông tin lớp để hiển thị tên môn
        ->orderBy('created_at', 'desc')
        ->get();
        @endphp

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-card padding="true">
                <p class="text-[12px] font-medium text-text-muted mb-1">Học phần đang theo</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">{{ $enrolledSections->count() }}</p>
                    <span class="text-[12px] text-text-muted">lớp</span>
                </div>
            </x-card>
            <x-card padding="true" variant="accent">
                <p class="text-[12px] font-medium text-text-muted mb-1">Bài thi</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">{{ $exams->count() }}</p>
                    <span class="text-[12px] text-text-muted">bài</span>
                </div>
            </x-card>
            <x-card padding="true">
                <p class="text-[12px] font-medium text-text-muted mb-1">Chuyên cần</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-[28px] font-bold text-navy-900 leading-none">-%</p>
                    <span class="text-[12px] text-text-muted">đã tham gia</span>
                </div>
            </x-card>
        </section>

        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-[17px] font-semibold text-navy-900">Bài Kiểm Tra & Kỳ Thi</h3>
            </x-slot>

            @if($exams->isEmpty())
            <div class="text-center py-10 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <p class="text-text-muted text-[13px] font-medium">Bạn chưa có bài thi nào.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($exams as $exam)
                <div class="border-[0.5px] border-border-clean p-4 rounded-[8px] bg-white hover:border-blue-200 transition-colors flex flex-col justify-between">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            @if($exam->isCompletedBy(auth()->id()))
                                <x-badge type="success">Đã hoàn thành</x-badge>
                            @elseif($exam->is_not_started)
                                <x-badge type="neutral">Chưa đến giờ</x-badge>
                            @elseif($exam->time_left_minutes > 0)
                                <x-badge type="warning">{{ $exam->time_left_text }}</x-badge>
                            @else
                                <x-badge type="danger">Đã hết giờ</x-badge>
                            @endif
                            <span class="text-[11px] text-text-muted">{{ $exam->start_time->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="font-semibold text-[15px] text-navy-900 leading-snug mb-1">{{ $exam->title }}</h4>
                        <p class="text-[12px] text-text-muted">{{ $exam->courseSection->name ?? $exam->courseSection->code }}</p>
                    </div>

                    <x-button variant="primary" size="sm" class="w-full" href="{{ route('student.exams.show', $exam->id) }}">
                        Vào Phòng Thi
                    </x-button>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        <x-card padding="true">
            <x-slot name="header">
                <h3 class="text-[17px] font-semibold text-navy-900">Lớp học phần của tôi</h3>
            </x-slot>

            @if($enrolledSections->isEmpty())
            <div class="text-center py-10">
                <p class="text-text-muted text-[13px] mb-4">Bạn chưa tham gia lớp nào.</p>
                <x-button variant="primary" onclick="document.getElementById('join-class-modal').classList.remove('hidden')">
                    Tham gia lớp ngay
                </x-button>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($enrolledSections as $section)
                @php
                $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                @endphp
                <div class="border-[0.5px] border-border-clean bg-surface-0 rounded-[8px] p-4 flex items-center justify-between gap-4"
                    x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                    <div>
                        <p class="font-semibold text-[14px] text-navy-900 leading-tight">{{ $section->name ?? $section->code }}</p>
                        <p class="text-[12px] text-text-muted mt-1 font-mono">{{ $section->code }}</p>
                        @if($section->lecturer)
                        <p class="text-[12px] text-text-muted mt-0.5">Giảng viên: {{ $section->lecturer->name }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('student.leave-class', $section) }}" onsubmit="return confirm('Bạn có chắc muốn rời lớp này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[12px] font-medium text-red-600 hover:text-red-700 hover:underline">
                            Rời lớp
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>
    </div>

    <div id="join-class-modal" class="hidden fixed inset-0 bg-navy-950/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <x-card padding="true" class="w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[17px] font-semibold text-navy-900">Tham gia lớp học</h3>
                <button onclick="document.getElementById('join-class-modal').classList.add('hidden')" class="text-text-muted hover:text-navy-900">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if(!auth()->user()->student_code)
            <div class="bg-amber-50 border-[0.5px] border-amber-600 rounded-[8px] p-4 text-center">
                <p class="text-[12px] text-amber-600 font-medium mb-3">Trước tiên hãy hoàn tất hồ sơ sinh viên (nhập MSSV).</p>
                <x-button variant="primary" class="w-full" href="{{ route('onboarding.show') }}">
                    Nhập thông tin sinh viên
                </x-button>
            </div>
            @else
            <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1">Mã lớp học</label>
                    <x-text-input name="invite_code" type="text" required placeholder="VD: ABC123" class="font-mono" />
                    @error('invite_code')
                    <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button variant="ghost" onclick="document.getElementById('join-class-modal').classList.add('hidden')">Hủy bỏ</x-button>
                    <x-button type="submit" variant="primary">Tham gia</x-button>
                </div>
            </form>
            @endif
        </x-card>
    </div>

    @if($errors->has('invite_code') || session('error'))
    <script>
        document.getElementById('join-class-modal').classList.remove('hidden');
    </script>
    @endif
</x-app-layout>