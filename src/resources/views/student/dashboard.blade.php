<x-app-layout>
    @section('title', 'Dashboard - Sinh vien')
    @section('page-title', 'Tong quan hoc tap')

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
                        Bạn chưa cập nhật thông tin sinh viên.
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
        // Lấy danh sách lớp học đã tham gia
        $enrolledSections = auth()->user()->enrolledSections()->with('lecturer')->get();
        $sectionIds = $enrolledSections->pluck('id');

        // Lấy toàn bộ bài kiểm tra thuộc về các lớp sinh viên đã tham gia
        $exams = \App\Models\Exam::whereIn('course_section_id', $sectionIds)
        ->with('courseSection') // Load kèm thông tin lớp để hiển thị tên môn
        ->orderBy('created_at', 'desc')
        ->get();
        @endphp

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Học phần đang theo</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $enrolledSections->count() }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Lớp học phần đã tham gia</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Bài thi</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $exams->count() }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Tổng số bài kiểm tra</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Chuyên cần</p>
                <p class="mt-3 text-4xl font-black leading-none">-%</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Tỷ lệ tham gia buổi học</p>
            </article>
        </section>

        <section class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black uppercase">Bài Kiểm Tra & Kỳ Thi</h3>
            </div>

            @if($exams->isEmpty())
            <div class="text-center py-10 bg-gray-50 brutal-border border-dashed">
                <p class="text-slate-500 font-semibold">Chưa có bài kiểm tra nào được giao cho bạn.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($exams as $exam)
                <div class="brutal-border p-5 bg-yellow-50 flex flex-col justify-between relative group hover:-translate-y-1 hover:shadow-brutal-lg transition-all">
                    <div class="absolute -top-3 -right-3 bg-ems-primary text-white text-xs font-black px-3 py-1 brutal-border shadow-brutal rotate-3">
                        {{ $exam->duration_minutes }} PHÚT
                    </div>

                    <div class="mb-6 mt-2">
                        <h4 class="font-black text-xl uppercase leading-tight mb-2">{{ $exam->title }}</h4>
                        <p class="text-sm font-bold text-slate-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            {{ $exam->courseSection->name ?? $exam->courseSection->code }}
                        </p>
                    </div>

                    <a href="{{ route('student.exams.show', $exam->id) }}" class="w-full h-10 flex items-center justify-center bg-black text-white brutal-border font-black text-sm uppercase brutal-btn brutal-shadow group-hover:bg-ems-primary">
                        Vào Phòng Thi
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </section>

        <section class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black uppercase">Lớp học phần của tôi</h3>
            </div>

            @if($enrolledSections->isEmpty())
            <div class="text-center py-10">
                <p class="text-slate-400 font-semibold">Bạn chưa tham gia lớp nào</p>
                <button onclick="document.getElementById('join-class-modal').classList.remove('hidden')"
                    class="mt-4 h-10 px-6 inline-flex items-center bg-ems-primary text-white brutal-border font-black text-sm uppercase brutal-btn brutal-shadow">
                    Tham gia lớp ngay
                </button>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($enrolledSections as $section)
                @php
                $searchableText = strtolower(($section->name ?? '') . ' ' . $section->code);
                @endphp
                <div class="brutal-border p-4 bg-background-light flex items-center justify-between gap-4"
                    x-show="searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase())">
                    <div>
                        <p class="font-black text-sm uppercase tracking-wide">{{ $section->name ?? $section->code }}</p>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $section->code }}</p>
                        @if($section->lecturer)
                        <p class="text-xs font-semibold text-slate-400 mt-0.5">GV: {{ $section->lecturer->name }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('student.leave-class', $section) }}" onsubmit="return confirm('Roi khoi lop nay?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-black text-red-500 hover:underline uppercase">
                            Rời lớp
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </section>
    </div>

    <div id="join-class-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white brutal-border brutal-shadow-lg w-full max-w-md p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black uppercase">Tham gia lớp học</h3>
                <button onclick="document.getElementById('join-class-modal').classList.add('hidden')" class="font-black text-2xl leading-none hover:text-ems-primary">&times;</button>
            </div>

            @if(!auth()->user()->student_code)
            <p class="mb-4 font-semibold text-slate-600">Trước tiên hãy hoàn tất hồ sơ sinh viên (nhập MSSV).</p>
            <a href="{{ route('onboarding.show') }}" class="w-full block text-center h-12 bg-ems-primary text-white brutal-border font-black uppercase tracking-wider leading-[3rem]">
                Nhập thông tin sinh viên
            </a>
            @else
            <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-black uppercase mb-2">Mã lớp học</label>
                    <input name="invite_code" type="text" required placeholder="Nhập mã lớp (VD: ABC123)" class="w-full h-12 px-4 bg-white brutal-border font-bold text-lg focus:ring-0 uppercase tracking-widest">
                    @error('invite_code')
                    <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full h-12 bg-ems-primary text-white brutal-border font-black uppercase tracking-widest hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all shadow-brutal">
                    Tham gia
                </button>
            </form>
            @endif
        </div>
    </div>

    @if($errors->has('invite_code') || session('error'))
    <script>
        document.getElementById('join-class-modal').classList.remove('hidden');
    </script>
    @endif
</x-app-layout>