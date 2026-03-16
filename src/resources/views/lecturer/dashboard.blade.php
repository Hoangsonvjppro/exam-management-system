<x-app-layout>
    @section('title', 'Dashboard - Giang vien')
    @section('page-title', 'Tong quan giang day')

    <div class="space-y-6">
        <section class="bg-background-light brutal-border shadow-brutal-lg p-6 md:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-600">Lecturer cockpit</p>
                    <h2 class="mt-2 text-3xl md:text-4xl font-black uppercase tracking-tight">Xin chao, {{ auth()->user()->name }}</h2>
                    <p class="mt-3 text-sm md:text-base font-semibold text-slate-700">Trang nay uu tien hanh dong nhanh: tao de thi, cap nhat cau hoi, theo doi lop hoc phan.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="#" class="h-12 px-5 flex items-center justify-center bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Tao de thi moi
                    </a>
                    <a href="#" class="h-12 px-5 flex items-center justify-center bg-white brutal-border brutal-shadow font-black uppercase tracking-wide brutal-btn">
                        Quan ly cau hoi
                    </a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Hoc phan</p>
                <p class="mt-3 text-4xl font-black leading-none">--</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">So lop hoc phan dang phu trach</p>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">De thi</p>
                <p class="mt-3 text-4xl font-black leading-none">--</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Tong de thi da cau hinh</p>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Cau hoi</p>
                <p class="mt-3 text-4xl font-black leading-none">--</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Ngan hang cau hoi ca nhan</p>
            </article>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <article class="bg-white brutal-border brutal-shadow p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xl font-black uppercase">Viec can xu ly hom nay</h3>
                    <span class="px-3 py-1 bg-red-100 brutal-border text-xs font-black uppercase">Uu tien</span>
                </div>
                <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-700">
                    <li class="p-3 bg-slate-50 brutal-border">Kiem tra lich thi cua cac lop trong tuan nay.</li>
                    <li class="p-3 bg-slate-50 brutal-border">Duyet bo cau hoi sap su dung cho de thi giua ky.</li>
                    <li class="p-3 bg-slate-50 brutal-border">Thong bao cho sinh vien ve han nop bai tap.</li>
                </ul>
            </article>

            <article class="bg-white brutal-border brutal-shadow p-6">
                <h3 class="text-xl font-black uppercase">Dong bo he thong</h3>
                <p class="mt-3 text-sm font-semibold text-slate-700">
                    Dashboard giang vien da duoc chuyen sang style neobrutalist action-first theo DESIGN.md.
                    So lieu thuc se duoc noi vao cac card sau khi module exam/report hoan tat.
                </p>
                <div class="mt-4 h-2 w-full bg-black"></div>
            </article>
        </section>
    </div>
</x-app-layout>
