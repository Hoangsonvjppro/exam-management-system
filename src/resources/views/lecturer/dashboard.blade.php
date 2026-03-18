<x-app-layout>
    @section('title', 'Dashboard - Giảng viên')
    @section('page-title', 'Tổng quan giảng dạy')

        @php
            $lecturer       = auth()->user();
            $mySections     = $lecturer->courseSections()->withCount('students')->get();
            $activeCount    = $mySections->where('status', 'active')->count();
            $studentTotal   = $mySections->sum('students_count');
            $questionCount  = $lecturer->questions()->count();
        @endphp

        <div class="space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 brutal-border font-semibold text-green-800 text-sm">{{ session('success') }}</div>
            @endif

            <section class="bg-background-light brutal-border shadow-brutal-lg p-6 md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-600">Lecturer Cockpit</p>
                        <h2 class="mt-2 text-3xl md:text-4xl font-black uppercase tracking-tight">Xin chào, {{ $lecturer->name }}</h2>
                        <p class="mt-3 text-sm md:text-base font-semibold text-slate-700">
                            @if($lecturer->lecturer_code)
                                Mã GV: <span class="font-black">{{ $lecturer->lecturer_code }}</span>
                            @endif
                            @if($lecturer->department)
                                — {{ $lecturer->department }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('lecturer.classes.create') }}"
                           class="h-12 px-5 flex items-center justify-center bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                            + Tạo lớp mới
                        </a>
                        <a href="{{ route('lecturer.classes.index') }}"
                           class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                            Quản lý lớp
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <article class="bg-white brutal-border brutal-shadow p-5">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Lớp học phần</p>
                    <p class="mt-3 text-4xl font-black leading-none">{{ $activeCount }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-600">Số lớp đang mở</p>
                </article>

                <article class="bg-white brutal-border brutal-shadow p-5">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Sinh viên</p>
                    <p class="mt-3 text-4xl font-black leading-none">{{ $studentTotal }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-600">Tổng sinh viên đang theo học</p>
                </article>

                <article class="bg-white brutal-border brutal-shadow p-5">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Câu hỏi</p>
                    <p class="mt-3 text-4xl font-black leading-none">{{ $questionCount }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-600">Ngân hàng câu hỏi cá nhân</p>
                </article>
            </section>

            {{-- My class list preview --}}
            <section class="bg-white brutal-border brutal-shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-black uppercase">Lớp học phần của tôi</h3>
                    <a href="{{ route('lecturer.classes.index') }}" class="text-sm font-black text-ems-primary hover:underline">Xem tất cả →</a>
                </div>

                @if($mySections->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-slate-400 font-semibold">Chưa có lớp học phần nào.</p>
                        <a href="{{ route('lecturer.classes.create') }}"
                           class="mt-3 inline-flex items-center h-10 px-5 bg-ems-primary text-white brutal-border font-black text-sm uppercase brutal-btn brutal-shadow">
                            Tạo lớp ngay
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($mySections->take(6) as $section)
                            @php
                                $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                            @endphp
                            <a href="{{ route('lecturer.classes.show', $section) }}"
                               class="brutal-border p-4 bg-background-light hover:bg-ems-primary/10 transition-colors flex items-start justify-between gap-3"
                               x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                                <div>
                                    <p class="font-black text-sm uppercase">{{ $section->name ?? $section->code }}</p>
                                    <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $section->students_count }} sinh viên</p>
                                </div>
                                <span class="font-mono text-xs font-black bg-white brutal-border px-2 py-1 tracking-widest uppercase">
                                    {{ $section->invite_code ?? '—' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
</x-app-layout>
