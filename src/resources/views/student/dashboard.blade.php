<x-app-layout>
    @section('title', 'Dashboard - Sinh vien')
    @section('page-title', 'Tong quan hoc tap')

    <div class="space-y-6">
        <section class="bg-background-light brutal-border shadow-brutal-lg p-6 md:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-600">Student hub</p>
                    <h2 class="mt-2 text-3xl md:text-4xl font-black uppercase tracking-tight">Xin chao, {{ auth()->user()->name }}</h2>
                    <p class="mt-3 text-sm md:text-base font-semibold text-slate-700">
                        @if (auth()->user()->student_code)
                            MSSV: <span class="font-black">{{ auth()->user()->student_code }}</span> - Lop: <span class="font-black">{{ auth()->user()->class_name ?? 'Dang cap nhat' }}</span>
                        @else
                            Ban chua cap nhat MSSV. Hoan tat onboarding de tham gia lop hoc phan.
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="#" class="h-12 px-5 flex items-center justify-center bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Vao lich thi
                    </a>
                    <a href="#" class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Tham gia lop
                    </a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Bai thi</p>
                <p class="mt-3 text-4xl font-black leading-none">--</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">So bai thi da hoan thanh</p>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Hoc phan</p>
                <p class="mt-3 text-4xl font-black leading-none">--</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">So hoc phan dang theo hoc</p>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Chuyen can</p>
                <p class="mt-3 text-4xl font-black leading-none">--%</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Ty le tham gia buoi hoc</p>
            </article>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <article class="bg-white brutal-border brutal-shadow p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xl font-black uppercase">Lich sap toi</h3>
                    <span class="px-3 py-1 bg-blue-100 brutal-border text-xs font-black uppercase">2 ngay toi</span>
                </div>
                <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-700">
                    <li class="p-3 bg-slate-50 brutal-border">Thi Thu nghiem he thong - 08:00 Thu 4.</li>
                    <li class="p-3 bg-slate-50 brutal-border">Nop bai tap chuong 3 - 23:59 Thu 5.</li>
                    <li class="p-3 bg-slate-50 brutal-border">Diem danh buoi hoc phan CSDL - 13:00 Thu 6.</li>
                </ul>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-6">
                <h3 class="text-xl font-black uppercase">Trang thai he thong</h3>
                <p class="mt-3 text-sm font-semibold text-slate-700">
                    Dashboard sinh vien da duoc thiet ke theo phong cach action-first neobrutalism.
                    Du lieu tong hop se duoc dong bo khi module exam/attendance/report hoan tat.
                </p>
                <div class="mt-4 h-2 w-full bg-black"></div>
            </article>
        </section>
    </div>
</x-app-layout>
