<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'EMS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|plus-jakarta-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 [font-family:'Plus_Jakarta_Sans',sans-serif]">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-28 top-[-8rem] h-80 w-80 rounded-full bg-cyan-400/25 blur-3xl"></div>
        <div class="absolute right-[-6rem] top-[10rem] h-72 w-72 rounded-full bg-orange-500/20 blur-3xl"></div>
        <div class="absolute bottom-[-10rem] left-1/2 h-96 w-[42rem] -translate-x-1/2 rounded-full bg-fuchsia-500/10 blur-3xl"></div>
    </div>

    <main class="relative mx-auto flex min-h-screen w-full max-w-7xl items-center px-6 py-16 lg:px-12">
        <section class="grid w-full items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="mb-5 inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">
                    EMS SaaS Platform
                </p>

                <h1 class="[font-family:'Space_Grotesk',sans-serif] text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                    Hệ thống Quản lý Học tập & Thi cử Thông minh
                </h1>

                <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Trải nghiệm học tập mượt mà, thi cử minh bạch.
                </p>

                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-7 py-3.5 text-sm font-semibold text-slate-900 shadow-lg shadow-white/20 transition hover:-translate-y-0.5 hover:bg-slate-100"
                        >
                            Vào Dashboard
                        </a>
                    @endauth

                    @guest
                        <a
                            href="{{ route('google.redirect') }}"
                            class="inline-flex items-center justify-center gap-3 rounded-xl bg-[#4285F4] px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:-translate-y-0.5 hover:bg-[#2f76ea]"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <path d="M44.5 20H24V28.5H35.8C34.7 34 30 37 24 37C16.8 37 11 31.2 11 24C11 16.8 16.8 11 24 11C27.1 11 29.8 12.1 31.9 14L38 7.9C34.2 4.4 29.4 2.25 24 2.25C12 2.25 2.25 12 2.25 24C2.25 36 12 45.75 24 45.75C35 45.75 45 37.75 45 24C45 22.6 44.8 21.2 44.5 20Z" fill="white"/>
                            </svg>
                            Đăng nhập bằng Google (@edu.vn)
                        </a>
                    @endguest
                </div>
            </div>

            <div class="relative">
                <div class="rounded-3xl border border-white/15 bg-white/5 p-6 backdrop-blur-xl">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <article class="rounded-2xl border border-cyan-300/20 bg-cyan-300/10 p-5">
                            <p class="text-sm font-medium text-cyan-100">Thi trực tuyến</p>
                            <p class="mt-2 text-xs text-cyan-50/90">Giám sát tiến trình và kết quả minh bạch theo thời gian thực.</p>
                        </article>
                        <article class="rounded-2xl border border-orange-300/20 bg-orange-300/10 p-5">
                            <p class="text-sm font-medium text-orange-100">Quản lý lớp học</p>
                            <p class="mt-2 text-xs text-orange-50/90">Tham gia lớp nhanh bằng mã, đồng bộ dữ liệu tự động.</p>
                        </article>
                        <article class="rounded-2xl border border-emerald-300/20 bg-emerald-300/10 p-5">
                            <p class="text-sm font-medium text-emerald-100">Onboarding tự động</p>
                            <p class="mt-2 text-xs text-emerald-50/90">Hỗ trợ sinh viên cập nhật MSSV ngay sau khi đăng nhập Google.</p>
                        </article>
                        <article class="rounded-2xl border border-violet-300/20 bg-violet-300/10 p-5">
                            <p class="text-sm font-medium text-violet-100">Hệ thống mở</p>
                            <p class="mt-2 text-xs text-violet-50/90">Sẵn sàng mở rộng cho mô hình SaaS đa vai trò hiện đại.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
