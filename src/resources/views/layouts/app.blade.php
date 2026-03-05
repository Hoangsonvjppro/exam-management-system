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
<body class="font-sans antialiased bg-background text-gray-900">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        {{-- ─── SIDEBAR ─────────────────────────────────────────── --}}
        <!-- Overlay (mobile) -->
        <div x-show="sidebarOpen" x-on:click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             style="display:none">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-sidebar shadow-sidebar transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:z-auto flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : ''">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 flex-shrink-0">
                <div class="flex items-center justify-center w-10 h-10 bg-primary-500 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-white text-base leading-tight">EMS</div>
                    <div class="text-sidebar-text text-xs">Quản lý Thi trắc nghiệm</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-1">

                @role('admin|department_admin')
                <x-sidebar-section label="Quản trị">
                    <x-sidebar-link route="admin.dashboard" icon="grid">Tổng quan</x-sidebar-link>
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
                    <x-sidebar-link route="lecturer.dashboard" icon="grid">Tổng quan</x-sidebar-link>
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
                <x-sidebar-section label="Menu chính">
                    <x-sidebar-link route="student.dashboard" icon="grid">Tổng quan</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="book-open">Học phần của tôi</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="clipboard-list">Kỳ thi & Bài tập</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="chart-bar">Kết quả học tập</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="calendar">Lịch biểu</x-sidebar-link>
                </x-sidebar-section>

                <x-sidebar-section label="Cài đặt">
                    <x-sidebar-link route="student.dashboard" icon="user">Hồ sơ cá nhân</x-sidebar-link>
                    <x-sidebar-link route="student.dashboard" icon="cog">Cài đặt hệ thống</x-sidebar-link>
                </x-sidebar-section>
                @endrole

            </nav>

            <!-- Help/Support Card -->
            <div class="flex-shrink-0 px-4 pb-4">
                <div class="bg-sidebar-light/80 rounded-xl p-4 text-center">
                    <div class="w-10 h-10 bg-primary-500/20 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-white text-sm font-semibold mb-1">Cần hỗ trợ?</p>
                    <p class="text-sidebar-text text-xs mb-3">Liên hệ phòng đào tạo nếu bạn gặp sự cố.</p>
                    <button class="w-full bg-white/10 hover:bg-white/20 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors">
                        Gửi yêu cầu
                    </button>
                </div>
            </div>
        </aside>

        {{-- ─── MAIN CONTENT ─────────────────────────────────────── --}}
        <div class="flex flex-col flex-1 overflow-hidden">

            <!-- Top Navbar -->
            <header class="sticky top-0 z-30 h-16 bg-white border-b border-border shadow-sm">
                <div class="flex items-center justify-between h-full px-4 sm:px-6">

                    <!-- Left: Hamburger + Page title -->
                    <div class="flex items-center gap-4">
                        <button class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-surface-hover hover:text-gray-700 transition-colors"
                                x-on:click="sidebarOpen = !sidebarOpen">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <h1 class="text-lg font-bold text-gray-900 leading-tight">
                            @yield('page-title', 'Dashboard')
                        </h1>
                    </div>

                    <!-- Center: Search Bar -->
                    <div class="hidden md:flex flex-1 max-w-md mx-8">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="search"
                                   placeholder="Tìm kiếm khóa học..."
                                   class="block w-full pl-10 pr-4 py-2 bg-surface-muted border border-border rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center gap-2 sm:gap-3">

                        <!-- Notifications -->
                        <button class="relative p-2 rounded-lg text-gray-500 hover:bg-surface-hover hover:text-gray-700 transition-colors" title="Thông báo">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            {{-- Unread badge --}}
                            @php $unreadCount = auth()->user() ? \App\Models\Notification::where('user_id', auth()->id())->unread()->count() : 0; @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-danger-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>

                        <!-- Divider -->
                        <div class="hidden sm:block w-px h-8 bg-border"></div>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-surface-hover transition-colors"
                                    x-on:click="open = !open">
                                <div class="hidden sm:block text-right">
                                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->primary_role ?? 'Người dùng' }}</p>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </button>

                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-border py-1 z-50"
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
                                   class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-surface-hover">
                                    <svg class="w-4 h-4 mr-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Hồ sơ cá nhân
                                </a>
                                <div class="border-t border-border my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center w-full px-4 py-2.5 text-sm text-danger-500 hover:bg-danger-50">
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
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 bg-success-50 border border-success-500/20 text-success-600 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-500/20 text-danger-600 rounded-lg text-sm flex items-center gap-2">
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
