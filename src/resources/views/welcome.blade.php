<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EMS - Hệ thống quản lý học tập toàn diện cho cơ sở giáo dục hiện đại. Cập nhật thông báo, lịch học và kỳ thi nhanh chóng.">
    <title>{{ config('app.name', 'EMS') }} — Hệ thống Quản lý Học tập</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background-light font-display text-slate-900">
<div class="min-h-screen flex flex-col">

    {{-- ===== HEADER ===== --}}
    <header class="sticky top-0 z-50 bg-background-light border-b-4 border-black px-6 md:px-20 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="bg-ems-primary p-2 brutal-border">
                <span class="material-symbols-outlined text-white font-bold">menu_book</span>
            </div>
            <h1 class="text-2xl font-black tracking-tighter uppercase">EMS</h1>
        </div>

        <nav class="hidden md:flex items-center gap-8 font-bold uppercase text-sm">
            <a class="hover:underline decoration-4 underline-offset-4" href="#">Trang chủ</a>
            <a class="hover:underline decoration-4 underline-offset-4" href="#notifications">Thông báo</a>
            <a class="hover:underline decoration-4 underline-offset-4" href="#activity">Lịch học</a>
            <a class="hover:underline decoration-4 underline-offset-4" href="#exams">Kỳ thi</a>
            <a class="hover:underline decoration-4 underline-offset-4" href="#footer">Hỗ trợ</a>
        </nav>

        @auth
            <a href="{{ url('/dashboard') }}"
               class="bg-ems-primary text-white px-6 py-2 brutal-border brutal-shadow font-black uppercase tracking-wider brutal-btn inline-block">
                Dashboard
            </a>
        @endauth
        @guest
            <a href="{{ route('google.redirect') }}"
               class="bg-ems-primary text-white px-6 py-2 brutal-border brutal-shadow font-black uppercase tracking-wider brutal-btn inline-block">
                Đăng nhập
            </a>
        @endguest
    </header>

    {{-- ===== HERO SECTION ===== --}}
    <section class="px-6 md:px-20 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center border-b-4 border-black">
        <div class="flex flex-col gap-8">
            <h2 class="text-6xl md:text-7xl font-black leading-none uppercase tracking-tighter">
                Hệ thống <br> quản lý <br>
                <span class="text-ems-primary bg-white px-2 brutal-border">học tập</span> EMS
            </h2>
            <p class="text-xl font-bold max-w-md border-l-8 border-ems-primary pl-4">
                Cập nhật thông báo, lịch học và kỳ thi nhanh chóng. Hệ thống tối ưu cho sinh viên và giảng viên.
            </p>
            <div class="flex flex-wrap gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="bg-ems-primary text-white px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Vào Dashboard
                    </a>
                @endauth
                @guest
                    <a href="{{ route('google.redirect') }}"
                       class="bg-ems-primary text-white px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Bắt đầu ngay
                    </a>
                @endguest
                <a href="#notifications"
                   class="bg-white text-black px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                    Tìm hiểu thêm
                </a>
            </div>
        </div>

        <div class="relative">
            <div class="aspect-square bg-ems-primary brutal-border brutal-shadow-lg flex items-center justify-center relative overflow-hidden">
                <span class="material-symbols-outlined text-[12rem] text-white opacity-20 absolute -bottom-10 -right-10">description</span>
                <span class="material-symbols-outlined text-[10rem] text-white">quiz</span>
            </div>
            <div class="absolute -top-6 -left-6 bg-yellow-400 brutal-border p-4 brutal-shadow font-black">
                MỚI CẬP NHẬT!
            </div>
        </div>
    </section>

    {{-- ===== STATS SECTION ===== --}}
    <section class="px-6 md:px-20 py-12 grid grid-cols-2 md:grid-cols-4 gap-6 bg-white border-b-4 border-black">
        <div class="p-6 brutal-border bg-background-light">
            <p class="text-4xl font-black">12k+</p>
            <p class="font-bold uppercase text-xs text-ems-primary">Số sinh viên</p>
        </div>
        <div class="p-6 brutal-border bg-background-light">
            <p class="text-4xl font-black">850</p>
            <p class="font-bold uppercase text-xs text-ems-primary">Số giảng viên</p>
        </div>
        <div class="p-6 brutal-border bg-background-light">
            <p class="text-4xl font-black">150</p>
            <p class="font-bold uppercase text-xs text-ems-primary">Số môn học</p>
        </div>
        <div class="p-6 brutal-border bg-background-light">
            <p class="text-4xl font-black">45</p>
            <p class="font-bold uppercase text-xs text-ems-primary">Số kỳ thi</p>
        </div>
    </section>

    {{-- ===== NOTIFICATIONS SECTION ===== --}}
    <section id="notifications" class="px-6 md:px-20 py-16">
        <div class="flex items-center justify-between mb-10">
            <h3 class="text-4xl font-black uppercase italic whitespace-nowrap">Thông báo mới nhất</h3>
            <div class="h-2 flex-grow mx-8 bg-black hidden md:block"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Card 1 --}}
            <div class="bg-white brutal-border brutal-shadow-lg p-6 flex flex-col gap-4">
                <span class="bg-red-500 text-white px-3 py-1 text-xs font-black uppercase brutal-border w-fit">Khẩn cấp</span>
                <h4 class="text-xl font-black">Thông báo lịch thi học kỳ phụ năm 2024</h4>
                <p class="font-medium text-slate-600">Thời gian đăng ký kéo dài đến hết ngày 20/11...</p>
                <div class="flex items-center justify-between mt-auto pt-4 border-t-2 border-dashed border-black">
                    <span class="font-bold text-sm">15/10/2023</span>
                    <button class="font-black uppercase text-ems-primary hover:underline">Xem chi tiết</button>
                </div>
            </div>
            {{-- Card 2 --}}
            <div class="bg-white brutal-border brutal-shadow-lg p-6 flex flex-col gap-4">
                <span class="bg-blue-500 text-white px-3 py-1 text-xs font-black uppercase brutal-border w-fit">Học vụ</span>
                <h4 class="text-xl font-black">Cập nhật quy chế thi trắc nghiệm trực tuyến</h4>
                <p class="font-medium text-slate-600">Yêu cầu cài đặt phần mềm giám sát thi mới nhất...</p>
                <div class="flex items-center justify-between mt-auto pt-4 border-t-2 border-dashed border-black">
                    <span class="font-bold text-sm">12/10/2023</span>
                    <button class="font-black uppercase text-ems-primary hover:underline">Xem chi tiết</button>
                </div>
            </div>
            {{-- Card 3 --}}
            <div class="bg-white brutal-border brutal-shadow-lg p-6 flex flex-col gap-4">
                <span class="bg-green-500 text-white px-3 py-1 text-xs font-black uppercase brutal-border w-fit">Sự kiện</span>
                <h4 class="text-xl font-black">Lịch nghỉ lễ Quốc khánh chính thức</h4>
                <p class="font-medium text-slate-600">Toàn bộ sinh viên được nghỉ từ ngày 01/09 đến hết...</p>
                <div class="flex items-center justify-between mt-auto pt-4 border-t-2 border-dashed border-black">
                    <span class="font-bold text-sm">10/10/2023</span>
                    <button class="font-black uppercase text-ems-primary hover:underline">Xem chi tiết</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== ACTIVITY & EXAMS SPLIT SECTION ===== --}}
    <section id="activity" class="px-6 md:px-20 py-16 grid lg:grid-cols-2 gap-16 border-t-4 border-black bg-white">
        {{-- Calendar Side --}}
        <div>
            <h3 class="text-3xl font-black uppercase mb-8 underline decoration-ems-primary decoration-8 underline-offset-8">Lịch hoạt động</h3>
            <div class="grid grid-cols-2 gap-6">
                <div class="brutal-border brutal-shadow p-4 bg-background-light">
                    <div class="border-b-4 border-black pb-2 mb-4 flex justify-between items-center">
                        <span class="font-black uppercase">Tháng 10</span>
                        <span class="material-symbols-outlined">calendar_today</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold">
                        <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        <span class="p-1">1</span><span class="p-1">2</span><span class="p-1">3</span><span class="p-1">4</span><span class="p-1">5</span><span class="p-1">6</span><span class="p-1">7</span>
                        <span class="p-1">8</span><span class="p-1">9</span><span class="p-1">10</span><span class="p-1">11</span><span class="p-1 bg-ems-primary text-white">12</span><span class="p-1">13</span><span class="p-1">14</span>
                        <span class="p-1">15</span><span class="p-1">16</span><span class="p-1">17</span><span class="p-1">18</span><span class="p-1">19</span><span class="p-1">20</span><span class="p-1">21</span>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="p-4 brutal-border bg-yellow-300 font-bold text-sm">
                        Họp hội đồng thi <br> <span class="text-xs opacity-70">08:00 - Phòng A102</span>
                    </div>
                    <div class="p-4 brutal-border bg-ems-primary text-white font-bold text-sm">
                        Thi Giải tích 1 <br> <span class="text-xs opacity-70">13:30 - Hội trường</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Exams Side --}}
        <div id="exams">
            <h3 class="text-3xl font-black uppercase mb-8 underline decoration-ems-primary decoration-8 underline-offset-8">Kỳ thi sắp tới</h3>
            <div class="flex flex-col gap-4">
                <div class="brutal-border p-4 flex items-center justify-between hover:bg-background-light transition-colors">
                    <div>
                        <p class="font-black text-lg">Cấu trúc dữ liệu &amp; Giải thuật</p>
                        <p class="text-sm font-bold opacity-60">Phòng 402 - 14/11/2023</p>
                    </div>
                    <span class="bg-blue-200 text-blue-800 px-3 py-1 font-black text-xs brutal-border uppercase">Sắp diễn ra</span>
                </div>
                <div class="brutal-border p-4 flex items-center justify-between bg-ems-primary/10">
                    <div>
                        <p class="font-black text-lg">Lập trình Java nâng cao</p>
                        <p class="text-sm font-bold opacity-60">Phòng 105 - Hôm nay</p>
                    </div>
                    <span class="bg-red-500 text-white px-3 py-1 font-black text-xs brutal-border uppercase">Đang diễn ra</span>
                </div>
                <div class="brutal-border p-4 flex items-center justify-between opacity-50 grayscale">
                    <div>
                        <p class="font-black text-lg">Kinh tế chính trị</p>
                        <p class="text-sm font-bold opacity-60">Phòng 201 - 10/10/2023</p>
                    </div>
                    <span class="bg-slate-300 text-slate-800 px-3 py-1 font-black text-xs brutal-border uppercase">Hoàn thành</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer id="footer" class="mt-auto bg-black text-white px-6 md:px-20 py-12">
        <div class="grid md:grid-cols-3 gap-12">
            <div class="flex flex-col gap-6">
                <div class="flex items-center gap-3">
                    <div class="bg-ems-primary p-2 border-2 border-white">
                        <span class="material-symbols-outlined text-white font-bold">menu_book</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tighter uppercase">EMS</h2>
                </div>
                <p class="font-bold opacity-70">Hệ thống quản lý học tập toàn diện cho cơ sở giáo dục hiện đại. Nhanh chóng, chính xác và minh bạch.</p>
            </div>
            <div class="flex flex-col gap-4">
                <h5 class="text-xl font-black uppercase italic text-ems-primary">Liên hệ</h5>
                <ul class="font-bold flex flex-col gap-2">
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">mail</span> contact@ems-edu.vn</li>
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">call</span> +84 24 123 4567</li>
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">location_on</span> 123 Đường Học Thuật, Hà Nội</li>
                </ul>
            </div>
            <div class="flex flex-col gap-4">
                <h5 class="text-xl font-black uppercase italic text-ems-primary">Theo dõi</h5>
                <div class="flex gap-4">
                    <a class="bg-white text-black p-3 brutal-border hover:bg-ems-primary hover:text-white transition-colors" href="#">
                        <span class="material-symbols-outlined">public</span>
                    </a>
                    <a class="bg-white text-black p-3 brutal-border hover:bg-ems-primary hover:text-white transition-colors" href="#">
                        <span class="material-symbols-outlined">share</span>
                    </a>
                    <a class="bg-white text-black p-3 brutal-border hover:bg-ems-primary hover:text-white transition-colors" href="#">
                        <span class="material-symbols-outlined">groups</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-12 pt-8 border-t border-white/20 text-center font-bold opacity-50 text-sm">
            © {{ date('Y') }} EMS Project. All Rights Reserved.
        </div>
    </footer>

</div>
</body>
</html>
