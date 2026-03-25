<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'EMS'))</title>
    <meta name="description" content="@yield('description', 'EMS - Hệ thống Quản lý Thi trắc nghiệm')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="font-sans antialiased bg-surface-0 text-navy-900">

    <div class="flex h-screen overflow-hidden" 
         x-data="{ 
            isSidebarPinned: false, 
            isSidebarHovered: false, 
            get isExpanded() { return (this.isSidebarPinned || this.isSidebarHovered) || window.innerWidth < 1024 && this.isSidebarPinned; },
            togglePin() {
                this.isSidebarPinned = !this.isSidebarPinned;
                localStorage.setItem('ems_sidebar_pinned', this.isSidebarPinned);
                if (!this.isSidebarPinned) this.isSidebarHovered = false;
            },
            init() {
                const stored = localStorage.getItem('ems_sidebar_pinned');
                if (stored !== null) {
                    this.isSidebarPinned = stored === 'true';
                } else {
                    this.isSidebarPinned = window.innerWidth >= 1024;
                }
            }
         }"
         @resize.window="if(window.innerWidth < 1024) isSidebarPinned = false">

        {{-- ─── SIDEBAR ─────────────────────────────────────────── --}}
        <!-- Overlay (mobile only) -->
        <div x-show="isSidebarPinned && window.innerWidth < 1024" x-on:click="isSidebarPinned = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            style="display:none">
        </div>

        <!-- Spacer for Main Content (Desktop Only) -->
        <div class="transition-all duration-300 ease-in-out flex-shrink-0 hidden lg:block"
             :class="isSidebarPinned ? 'w-64' : 'w-16'"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 bg-white border-r-[0.5px] border-border-clean flex flex-col transition-all duration-300 ease-in-out overflow-hidden"
            :class="[
                isExpanded ? 'w-64 translate-x-0' : 'w-16 -translate-x-full lg:translate-x-0',
                (!isSidebarPinned && isSidebarHovered) ? 'shadow-[4px_0_24px_rgba(0,0,0,0.06)] lg:border-r-transparent' : ''
            ]"
            @mouseenter="if(!isSidebarPinned && window.innerWidth >= 1024) isSidebarHovered = true"
            @mouseleave="if(!isSidebarPinned && window.innerWidth >= 1024) isSidebarHovered = false">

            <!-- Sidebar Inner Container (fixed width 256px so contents don't squish during transition) -->
            <div class="w-64 h-full flex flex-col flex-shrink-0">
                <!-- Logo & Hamburger -->
                <div class="flex items-center h-[52px] border-b-[0.5px] border-border-clean flex-shrink-0 pl-[12px] w-full">
                    <!-- Hamburger inside Sidebar -->
                    <div class="flex items-center justify-center flex-shrink-0">
                        <button class="w-10 h-10 rounded-full hover:bg-surface-1 flex items-center justify-center text-text-muted transition-colors outline-none"
                            @click="togglePin()">
                            <svg class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
    
                    <!-- Logo Text -->
                    <div class="flex items-center gap-3 whitespace-nowrap pl-[12px]">
                        <div class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></div>
                        <div class="font-semibold text-navy-900 text-[15px] leading-tight uppercase tracking-wider">EduPortal</div>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 space-y-1 relative">

                @role('lecturer|teaching_assistant')
                <x-sidebar-section label="Giảng dạy">
                    <x-sidebar-link route="lecturer.dashboard" icon="grid">Tổng quan</x-sidebar-link>
                    <x-sidebar-link route="lecturer.classes.index" icon="book-open">Lớp học phần</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Ngân hàng đề">
                    {{-- Đã sửa route và bỏ :active="false" để menu tự sáng lên khi click vào --}}
                    <x-sidebar-link route="lecturer.questions.index" icon="question-mark-circle">Câu hỏi</x-sidebar-link>
                    <x-sidebar-link route="lecturer.exams.index" icon="document-text">Đề thi</x-sidebar-link>
                    <x-sidebar-link route="lecturer.schedules.index" icon="clock">Quản lý Lịch Thi</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Lớp học">
                    <x-sidebar-link route="lecturer.attendance.index" icon="check-circle">Điểm danh</x-sidebar-link>
                </x-sidebar-section>
                @endrole

                @role('student')
                <x-sidebar-section label="Menu chính">
                    <x-sidebar-link route="student.dashboard" icon="grid">Tổng quan</x-sidebar-link>
                    <x-sidebar-link route="student.classes.index" icon="book-open">Học phần của tôi</x-sidebar-link>

                    {{-- Thêm badge nhắc nhở (tuỳ chọn) cho sinh viên ở mục Bài tập --}}
                    <x-sidebar-link route="student.exams.index" icon="clipboard-list">Kỳ thi & Bài tập</x-sidebar-link>
                    <x-sidebar-link route="student.schedules.index" icon="clock">Lịch thi</x-sidebar-link>

                    <x-sidebar-link route="student.results.index" icon="chart-bar">Kết quả học tập</x-sidebar-link>
                    <x-sidebar-link route="student.attendance.index" icon="check-circle">Điểm danh</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Cài đặt">
                    <x-sidebar-link route="profile.edit" icon="user">Hồ sơ cá nhân</x-sidebar-link>
                </x-sidebar-section>
                @endrole

            </nav>
        </div>
    </aside>

        {{-- ─── MAIN CONTENT ─────────────────────────────────────── --}}
        <div class="flex flex-col flex-1 overflow-hidden">

            <!-- Top Navbar -->
            <header class="sticky top-0 z-30 h-[52px] bg-navy-900 text-white">
                <div class="flex items-center justify-between h-full px-4 sm:px-6">

                    <!-- Left: Page title (Hamburger removed from here) -->
                    <div class="flex items-center gap-4">
                        <!-- Hamburger only for mobile (header) -->
                        <button class="lg:hidden p-1.5 rounded-[5px] bg-navy-700 text-blue-200 hover:bg-navy-600 transition-colors"
                            x-on:click="isSidebarPinned = !isSidebarPinned">
                            <svg class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <h1 class="text-[15px] font-semibold text-white">
                            @yield('page-title', 'Dashboard')
                        </h1>
                    </div>

                    <!-- Center: Spacer or Global Search placeholder -->
                    <div class="hidden md:flex flex-1 max-w-md mx-8"></div>

                    <!-- Right side -->
                    <div class="flex items-center gap-2 sm:gap-3">

                        <!-- Notifications -->
                        @role('student')
                        <a href="{{ route('student.notifications.index') }}" class="relative p-1.5 rounded-[5px] text-blue-200 hover:text-white transition-colors block" title="Thông báo">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(($unreadNotificationCount ?? 0) > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-600 rounded-full" title="Có thông báo mới"></span>
                            @endif
                        </a>
                        @else
                        <button class="relative p-1.5 rounded-[5px] text-blue-200 hover:text-white transition-colors" title="Thông báo">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(($unreadNotificationCount ?? 0) > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-600 rounded-full" title="Có thông báo mới"></span>
                            @endif
                        </button>
                        @endrole

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button class="flex items-center gap-2" x-on:click="open = !open">
                                <div class="bg-white/10 rounded-[5px] px-2.5 py-1 hidden sm:flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-teal-300"></div>
                                    <span class="text-blue-200 text-[11px] font-medium">{{ auth()->user()->primary_role ?? 'SV' }}</span>
                                </div>
                                @if(auth()->user()->google_avatar)
                                <img src="{{ auth()->user()->google_avatar }}" alt="Avatar" class="w-[30px] h-[30px] rounded-full border-[1.5px] border-blue-400 object-cover" referrerpolicy="no-referrer">
                                @else
                                <div class="w-[30px] h-[30px] rounded-full bg-navy-700 flex items-center justify-center text-blue-200 font-semibold text-[11px] border-[1.5px] border-blue-400">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                </div>
                                @endif
                            </button>

                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-56 bg-white border-[0.5px] border-border-clean rounded-[10px] py-1 shadow-card z-50 overflow-hidden"
                                x-show="open"
                                x-on:click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                style="display:none">
                                <div class="px-4 py-3 border-b-[0.5px] border-border-clean bg-surface-0">
                                    <p class="text-[13px] font-semibold text-navy-900 leading-none mb-1">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-text-muted leading-none">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center px-4 py-2 text-[12px] font-medium text-navy-900 hover:bg-surface-1">
                                    <svg class="w-4 h-4 mr-2.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Hồ sơ cá nhân
                                </a>
                                <div class="border-t-[0.5px] border-border-clean my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full px-4 py-2 text-[12px] font-semibold text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                {{-- Toast messages --}}
                <x-toast />

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>