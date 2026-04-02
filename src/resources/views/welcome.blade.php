<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EMS - Hệ thống quản lý học tập toàn diện cho cơ sở giáo dục hiện đại. Cập nhật thông báo, lịch học và kỳ thi nhanh chóng.">
    <title>{{ config('app.name', 'EMS') }} — Hệ thống Quản lý Học tập</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/pages/common/welcome.js'])
</head>

<body class="bg-background-light font-display text-navy-900" data-open-join-class-modal="{{ ($errors->has('invite_code') || session('error')) ? '1' : '0' }}">
    <div class="min-h-screen flex flex-col">

        {{-- ===== HEADER ===== --}}
        <header class="sticky top-0 z-50 bg-background-light border-b-4 border-black px-6 md:px-20 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-navy-900 p-2 brutal-border">
                    <span class="material-symbols-outlined text-white font-bold">menu_book</span>
                </div>
                <h1 class="text-2xl font-black tracking-tighter uppercase">EMS</h1>
            </div>

            <nav class="hidden md:flex items-center gap-8 font-bold uppercase text-sm">
                <a class="hover:underline decoration-4 underline-offset-4" href="#">Trang chủ</a>
                <a class="hover:underline decoration-4 underline-offset-4" href="#notifications">Thông báo</a>
                <a class="hover:underline decoration-4 underline-offset-4" href="#footer">Hỗ trợ</a>
            </nav>

            @auth
            <div class="relative" x-data="dropdownState()">
                <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                    <img src="{{ auth()->user()->avatar_url }}"
                        alt="{{ auth()->user()->name }}"
                        class="w-10 h-10 rounded-full brutal-border object-cover"
                        referrerpolicy="no-referrer">
                    <span class="hidden md:inline font-bold text-sm">{{ auth()->user()->name }}</span>
                </button>
                <div x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white brutal-border brutal-shadow z-50">
                    <a href="{{ route('dashboard') }}"
                        class="block px-4 py-3 font-bold text-sm hover:bg-background-light border-b-2 border-black">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-3 font-bold text-sm text-red-600 hover:bg-red-50">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
            @endauth
            @guest
            <a href="{{ route('login') }}"
                class="bg-navy-900 text-white px-6 py-2 brutal-border brutal-shadow font-black uppercase tracking-wider brutal-btn inline-block">
                Đăng nhập
            </a>
            @endguest
        </header>

        {{-- ===== HERO SECTION ===== --}}
        <section class="px-6 md:px-20 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center border-b-4 border-black">
            <div class="flex flex-col gap-8">
                <h2 class="text-6xl md:text-7xl font-black leading-none uppercase tracking-tighter">
                    Hệ thống <br> quản lý <br>
                    <span class="text-navy-600 bg-white px-2 brutal-border">học tập</span> EMS
                </h2>
                <p class="text-xl font-bold max-w-md border-l-8 border-navy-900 pl-4">
                    Cập nhật thông báo, lịch học và kỳ thi nhanh chóng. Hệ thống tối ưu cho sinh viên và giảng viên.
                </p>
                <div class="flex flex-wrap gap-4">
                    @auth
                    @if(auth()->user()->hasRole('lecturer') || auth()->user()->hasRole('student'))
                    <a href="{{ route('dashboard') }}"
                        class="bg-navy-900 text-white px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Vào Dashboard
                    </a>
                    @else
                    {{-- Student logged in but no class yet: show join class CTA --}}
                    <button data-open-target="#join-class-modal"
                        class="bg-navy-900 text-white px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Tham gia lớp học
                    </button>
                    <a href="{{ route('profile.edit') }}"
                        class="bg-white text-black px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Hồ sơ cá nhân
                    </a>
                    @endif
                    @endauth
                    @guest
                    <a href="{{ route('login') }}"
                        class="bg-navy-900 text-white px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Đăng nhập
                    </a>
                    @endguest
                    <a href="#notifications"
                        class="bg-white text-black px-8 py-4 brutal-border brutal-shadow-lg font-black uppercase text-lg brutal-btn inline-block">
                        Xem thông báo
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="aspect-square bg-navy-900 brutal-border brutal-shadow-lg flex items-center justify-center relative overflow-hidden">
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
                <p class="text-4xl font-black">{{ $studentCount }}</p>
                <p class="font-bold uppercase text-xs text-navy-600">Số sinh viên</p>
            </div>
            <div class="p-6 brutal-border bg-background-light">
                <p class="text-4xl font-black">{{ $lecturerCount }}</p>
                <p class="font-bold uppercase text-xs text-navy-600">Số giảng viên</p>
            </div>
            <div class="p-6 brutal-border bg-background-light">
                <p class="text-4xl font-black">{{ $subjectCount }}</p>
                <p class="font-bold uppercase text-xs text-navy-600">Số môn học</p>
            </div>
            <div class="p-6 brutal-border bg-background-light">
                <p class="text-4xl font-black">{{ $sectionCount }}</p>
                <p class="font-bold uppercase text-xs text-navy-600">Số lớp học phần</p>
            </div>
        </section>

        {{-- ===== NOTIFICATIONS SECTION ===== --}}
        <section id="notifications" class="px-6 md:px-20 py-16">
            <div class="flex items-center justify-between mb-10">
                <h3 class="text-4xl font-black uppercase italic whitespace-nowrap">Thông báo mới nhất</h3>
                <div class="h-2 flex-grow mx-8 bg-black hidden md:block"></div>
            </div>

            @if($announcements->isEmpty())
            <div class="text-center py-16 brutal-border bg-white">
                <span class="material-symbols-outlined text-6xl text-blue-200">notifications_none</span>
                <p class="mt-4 font-bold text-text-muted uppercase">Chưa có thông báo nào</p>
            </div>
            @else
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($announcements as $item)
                @php
                $colorMap = [
                'urgent' => ['bg' => 'bg-red-500', 'label' => 'Khẩn cấp'],
                'warning' => ['bg' => 'bg-yellow-500', 'label' => 'Cảnh báo'],
                'event' => ['bg' => 'bg-green-500', 'label' => 'Sự kiện'],
                'info' => ['bg' => 'bg-blue-500', 'label' => 'Học vụ'],
                ];
                $color = $colorMap[$item->type] ?? $colorMap['info'];
                @endphp
                <div class="bg-white brutal-border brutal-shadow-lg p-6 flex flex-col gap-4">
                    <span class="{{ $color['bg'] }} text-white px-3 py-1 text-xs font-black uppercase brutal-border w-fit">{{ $color['label'] }}</span>
                    <h4 class="text-xl font-black">{{ $item->title }}</h4>
                    @if($item->body)
                    <p class="font-medium text-text-muted">{{ Str::limit($item->body, 80) }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-auto pt-4 border-t-2 border-dashed border-black">
                        <span class="font-bold text-sm">{{ $item->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>

        {{-- ===== FOOTER ===== --}}
        <footer id="footer" class="mt-auto bg-black text-white px-6 md:px-20 py-12">
            <div class="grid md:grid-cols-3 gap-12">
                <div class="flex flex-col gap-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-navy-900 p-2 border-2 border-white">
                            <span class="material-symbols-outlined text-white font-bold">menu_book</span>
                        </div>
                        <h2 class="text-2xl font-black tracking-tighter uppercase">EMS</h2>
                    </div>
                    <p class="font-bold opacity-70">Hệ thống quản lý học tập toàn diện cho cơ sở giáo dục hiện đại. Nhanh chóng, chính xác và minh bạch.</p>
                </div>
                <div class="flex flex-col gap-4">
                    <h5 class="text-xl font-black uppercase italic text-blue-200">Liên hệ</h5>
                    <ul class="font-bold flex flex-col gap-2">
                        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">mail</span> Hoangsonle1805@gmail.com</li>
                        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">call</span> +84 934191038 </li>
                        <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">location_on</span> 273 An Dương Vương, Tp.HCMl</li>
                    </ul>
                </div>
                <div class="flex flex-col gap-4">
                    <h5 class="text-xl font-black uppercase italic text-blue-200">Theo dõi</h5>
                    <div class="flex gap-4">
                        <a class="bg-white text-black p-3 brutal-border hover:bg-navy-900 hover:text-white transition-colors" href="#">
                            <span class="material-symbols-outlined">public</span>
                        </a>
                        <a class="bg-white text-black p-3 brutal-border hover:bg-navy-900 hover:text-white transition-colors" href="#">
                            <span class="material-symbols-outlined">share</span>
                        </a>
                        <a class="bg-white text-black p-3 brutal-border hover:bg-navy-900 hover:text-white transition-colors" href="#">
                            <span class="material-symbols-outlined">groups</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-white/20 text-center font-bold opacity-50 text-sm">
                © {{ date('Y') }} EMS Project. All Rights Reserved.
            </div>
        </footer>

        {{-- ===== JOIN CLASS MODAL (for students without a class) ===== --}}
        @auth
        @if(!auth()->user()->hasRole('lecturer') && !auth()->user()->hasRole('student'))
        <div id="join-class-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-white brutal-border brutal-shadow-lg w-full max-w-md p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-black uppercase">Tham gia lớp học</h3>
                    <button type="button" data-close-target="#join-class-modal"
                        class="font-black text-2xl leading-none hover:text-navy-600">&times;</button>
                </div>


                @if(!auth()->user()->student_code)
                <p class="mb-4 font-semibold text-text-muted">Trước tiên hãy hoàn tất hồ sơ sinh viên (nhập MSSV).</p>
                <a href="{{ route('onboarding.show') }}"
                    class="w-full block text-center h-12 bg-navy-900 text-white brutal-border font-black uppercase tracking-wider leading-[3rem]">
                    Nhập thông tin sinh viên
                </a>
                @else
                <form method="POST" action="{{ route('student.join-class') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-black uppercase mb-2">Mã lớp học</label>
                        <input name="invite_code" type="text" required placeholder="Nhập mã lớp (VD: ABC123)"
                            value="{{ old('invite_code') }}"
                            class="w-full h-12 px-4 bg-white brutal-border font-bold text-lg focus:ring-0 uppercase tracking-widest">
                        @error('invite_code')
                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full h-12 bg-navy-900 text-white brutal-border font-black uppercase tracking-widest hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all shadow-brutal">
                        Tham gia
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
        @endauth

        <x-toast />
    </div>
</body>

</html>