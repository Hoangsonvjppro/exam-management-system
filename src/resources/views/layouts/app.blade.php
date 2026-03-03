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
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        {{-- ─── SIDEBAR ─────────────────────────────────────────── --}}
        <!-- Overlay (mobile) -->
        <div class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             x-show="sidebarOpen"
             x-on:click="sidebarOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display:none">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-indigo-900 text-white transition-transform duration-300 lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               style="transform: translateX(-100%);">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-indigo-700/50">
                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm leading-tight">EMS</div>
                    <div class="text-indigo-300 text-xs">Quản lý Thi trắc nghiệm</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                @role('admin|department_admin')
                <x-sidebar-section label="Quản trị">
                    <x-sidebar-link route="admin.dashboard" icon="chart-bar">Dashboard</x-sidebar-link>
                    <x-sidebar-link route="admin.dashboard" icon="users">Người dùng</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Danh mục">
                    <x-sidebar-link route="admin.dashboard" icon="academic-cap">Môn học</x-sidebar-link>
                    <x-sidebar-link route="admin.dashboard" icon="calendar">Học kỳ</x-sidebar-link>
                    <x-sidebar-link route="admin.dashboard" icon="book-open">Lớp học phần</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Hệ thống">
                    <x-sidebar-link route="admin.dashboard" icon="cog">Cài đặt</x-sidebar-link>
                </x-sidebar-section>
                @endrole

                @role('lecturer|teaching_assistant')
                <x-sidebar-section label="Giảng dạy">
                    <x-sidebar-link route="lecturer.dashboard" icon="chart-bar">Dashboard</x-sidebar-link>
                    <x-sidebar-link route="lecturer.dashboard" icon="book-open">Lớp học phần</x-sidebar-link>
                    <x-sidebar-link route="lecturer.dashboard" icon="users">Sinh viên</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Ngân hàng đề">
                    <x-sidebar-link route="lecturer.dashboard" icon="question-mark-circle">Câu hỏi</x-sidebar-link>
                    <x-sidebar-link route="lecturer.dashboard" icon="document-text">Đề thi</x-sidebar-link>
                    <x-sidebar-link route="lecturer.dashboard" icon="clock">Lịch thi</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Lớp học">
                    <x-sidebar-link route="lecturer.dashboard" icon="check-circle">Điểm danh</x-sidebar-link>
                    <x-sidebar-link route="lecturer.dashboard" icon="paper-clip">Tài liệu</x-sidebar-link>
                </x-sidebar-section>
                @endrole

                @role('student')
                <x-sidebar-section label="Học tập">
                    <x-sidebar-link route="student.dashboard" icon="chart-bar">Dashboard</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="document-text">Bài thi của tôi</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="check-circle">Điểm danh</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="paper-clip">Tài liệu</x-sidebar-link>
                </x-sidebar-section>
                @endrole

            </nav>

            <!-- User info bottom -->
            <div class="border-t border-indigo-700/50 px-4 py-3">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-400">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                        <div class="text-indigo-300 text-xs truncate">{{ auth()->user()->primary_role }}</div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ─── MAIN CONTENT ─────────────────────────────────────── --}}
        <div class="flex flex-col flex-1 overflow-hidden">

            <!-- Top Navbar -->
            <header class="z-10 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">

                    <!-- Hamburger (mobile) -->
                    <button class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100 transition"
                            x-on:click="sidebarOpen = !sidebarOpen">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page title -->
                    <h1 class="text-lg font-semibold text-gray-800 hidden lg:block">
                        @yield('page-title', 'Dashboard')
                    </h1>

                    <!-- Right side -->
                    <div class="flex items-center gap-3">

                        <!-- Notifications -->
                        <button class="relative p-2 rounded-full text-gray-500 hover:bg-gray-100 transition" title="Thông báo">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            {{-- Unread badge --}}
                            @php $unreadCount = auth()->user() ? \App\Models\Notification::where('user_id', auth()->id())->unread()->count() : 0; @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button class="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 transition"
                                    x-on:click="open = !open">
                                <img src="{{ auth()->user()->avatar_url }}"
                                     alt="{{ auth()->user()->name }}"
                                     class="w-8 h-8 rounded-full object-cover">
                                <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                                 x-show="open"
                                 x-on:click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 style="display:none">
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Hồ sơ cá nhân
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
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
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">

                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
