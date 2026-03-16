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
                    <h2 class="mt-2 text-3xl md:text-4xl font-black uppercase tracking-tight">Xin chao, {{ auth()->user()->name }}</h2>
                    <p class="mt-3 text-sm md:text-base font-semibold text-slate-700">
                        @if (auth()->user()->student_code)
                            MSSV: <span class="font-black">{{ auth()->user()->student_code }}</span>
                            @if(auth()->user()->class_name)
                                - Lop: <span class="font-black">{{ auth()->user()->class_name }}</span>
                            @endif
                        @else
                            Ban chua cap nhat MSSV.
                            <a href="{{ route('onboarding.show') }}" class="underline text-ems-primary font-black">Hoan tat ho so ngay</a>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="document.getElementById('join-class-modal').classList.remove('hidden')"
                            class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        + Tham gia lop
                    </button>
                    <a href="{{ route('profile.edit') }}"
                       class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Ho so
                    </a>
                </div>
            </div>
        </section>

        @php
            $enrolledSections = auth()->user()->enrolledSections()->with('lecturer')->get();
        @endphp

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Hoc phan dang theo</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $enrolledSections->count() }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Lop hoc phan da tham gia</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Bai thi</p>
                <p class="mt-3 text-4xl font-black leading-none">-</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">So bai thi da hoan thanh</p>
            </article>
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Chuyen can</p>
                <p class="mt-3 text-4xl font-black leading-none">-%</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Ti le tham gia buoi hoc</p>
            </article>
        </section>

        <section class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black uppercase">Lop hoc phan cua toi</h3>
            </div>

            @if($enrolledSections->isEmpty())
                <div class="text-center py-10">
                    <p class="text-slate-400 font-semibold">Ban chua tham gia lop hoc phan nao.</p>
                    <button onclick="document.getElementById('join-class-modal').classList.remove('hidden')"
                            class="mt-4 h-10 px-6 inline-flex items-center bg-ems-primary text-white brutal-border font-black text-sm uppercase brutal-btn brutal-shadow">
                        Tham gia lop ngay
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
                            <form method="POST" action="{{ route('student.leave-class', $section) }}" onsubmit="return confirm('Roi khoi lop nay?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-black text-red-500 hover:underline uppercase">
                                    Roi lop
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
                <h3 class="text-2xl font-black uppercase">Tham gia lop hoc</h3>
                <button onclick="document.getElementById('join-class-modal').classList.add('hidden')" class="font-black text-2xl leading-none hover:text-ems-primary">&times;</button>
            </div>

            @if(!auth()->user()->student_code)
                <p class="mb-4 font-semibold text-slate-600">Truoc tien hay hoan tat ho so sinh vien (nhap MSSV).</p>
                <a href="{{ route('onboarding.show') }}" class="w-full block text-center h-12 bg-ems-primary text-white brutal-border font-black uppercase tracking-wider leading-[3rem]">
                    Nhap thong tin sinh vien
                </a>
            @else
                <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-black uppercase mb-2">Ma lop hoc</label>
                        <input name="invite_code" type="text" required placeholder="Nhap ma lop (VD: ABC123)" class="w-full h-12 px-4 bg-white brutal-border font-bold text-lg focus:ring-0 uppercase tracking-widest">
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
        <script>document.getElementById('join-class-modal').classList.remove('hidden');</script>
    @endif
</x-app-layout>
