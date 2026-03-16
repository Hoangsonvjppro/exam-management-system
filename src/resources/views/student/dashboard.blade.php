<x-app-layout>
    @section('title', 'Dashboard - Sinh viên')
    @section('page-title', 'Tổng quan học tập')

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-100 brutal-border font-semibold text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-100 brutal-border font-semibold text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <section class="bg-background-light brutal-border shadow-brutal-lg p-6 md:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-600">Student Hub</p>
                    <h2 class="mt-2 text-3xl md:text-4xl font-black uppercase tracking-tight">Xin chào, {{ auth()->user()->name }}</h2>
                    <p class="mt-3 text-sm md:text-base font-semibold text-slate-700">
                        @if (auth()->user()->student_code)
                            MSSV: <span class="font-black">{{ auth()->user()->student_code }}</span>
                            @if(auth()->user()->class_name)
                                - Lớp: <span class="font-black">{{ auth()->user()->class_name }}</span>
                            @endif
                        @else
                            Bạn chưa cập nhật MSSV.
                            <a href="{{ route('onboarding.show') }}" class="underline text-ems-primary font-black">Hoàn tất hồ sơ ngay</a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="document.getElementById('join-class-modal').classList.remove('hidden')"
                            class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        + Tham gia lớp
                    </button>
                    <a href="{{ route('profile.edit') }}"
                       class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Hồ sơ
                    </a>
                </div>
            </div>
        </section>

        @php
            $enrolledSections = auth()->user()->enrolledSections()->with('lecturer')->get();
        @endphp

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Học phần đang theo</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $enrolledSections->count() }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Lớp học phần đã tham gia</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Bài thi</p>
                <p class="mt-3 text-4xl font-black leading-none">-</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Số bài thi đã hoàn thành</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Chuyên cần</p>
                <p class="mt-3 text-4xl font-black leading-none">-%</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Tỉ lệ tham gia buổi học</p>
            </article>
        </section>

        <section class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black uppercase">Lớp học phần của tôi</h3>
            </div>

            @if($enrolledSections->isEmpty())
                <div class="text-center py-10">
                    <p class="text-slate-400 font-semibold">Bạn chưa tham gia lớp học phần nào.</p>
                    <button onclick="document.getElementById('join-class-modal').classList.remove('hidden')"
                            class="mt-4 h-10 px-6 inline-flex items-center bg-ems-primary text-white brutal-border font-black text-sm uppercase brutal-btn brutal-shadow">
                        Tham gia lớp ngay
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($enrolledSections as $section)
                        <div class="brutal-border p-4 bg-background-light flex items-center justify-between gap-4">
                            <div>
                                <p class="font-black text-sm uppercase tracking-wide">{{ $section->name ?? $section->code }}</p>
                                <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $section->code }}</p>
                                @if($section->lecturer)
                                    <p class="text-xs font-semibold text-slate-400 mt-0.5">GV: {{ $section->lecturer->name }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('student.leave-class', $section) }}"
                                  onsubmit="return confirm('Rời khỏi lớp này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-black text-red-500 hover:underline uppercase">
                                    Rời lớp
                                </button>
                            </form>
                        </div>
                    @endforeach
{ _ble_edit_exec_gexec__save_lastarg "$@"; } 4>&1 5>&2 &>/dev/null
