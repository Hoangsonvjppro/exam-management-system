@php
$isLecturerUser = auth()->check() && auth()->user()->hasAnyRole(['lecturer', 'teaching_assistant']);
$lecturerSidebarSections = collect();
$lecturerSidebarSubjects = collect();

if ($isLecturerUser) {
$lecturerSidebarSections = \App\Models\CourseSection::query()
->where('lecturer_id', auth()->id())
->with([
'subject:id,name,code',
'semester:id,name,start_date,end_date',
])
->orderByDesc('updated_at')
->orderByDesc('id')
->get();

$lecturerSidebarSubjects = $lecturerSidebarSections
->pluck('subject')
->filter()
->unique('id')
->sortBy('name')
->values();

if ($lecturerSidebarSubjects->isEmpty()) {
$lecturerSidebarSubjects = \App\Models\Subject::query()
->orderBy('name')
->limit(20)
->get(['id', 'name', 'code']);
}
}

$isStudentUser = auth()->check() && auth()->user()->hasRole('student');
$studentSidebarSections = collect();

if ($isStudentUser) {
$studentSidebarSections = auth()->user()->enrolledSections()
->with(['subject:id,name,code', 'lecturer:id,name'])
->orderByDesc('updated_at')
->get();
}
@endphp

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
            openClassMenu: true,
            openQuestionBank: true,
            openExamBank: false,
            get isExpanded() {
                return (this.isSidebarPinned || this.isSidebarHovered) || (window.innerWidth < 1024 && this.isSidebarPinned);
            },
            togglePin() {
                this.isSidebarPinned = !this.isSidebarPinned;
                localStorage.setItem('ems_sidebar_pinned', this.isSidebarPinned);
                if (!this.isSidebarPinned) {
                    this.isSidebarHovered = false;
                }
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
        @resize.window="if (window.innerWidth < 1024) isSidebarPinned = false">

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

            <div class="w-64 h-full flex flex-col flex-shrink-0">
                <div class="flex items-center h-[52px] border-b-[0.5px] border-border-clean flex-shrink-0 pl-[12px] w-full">
                    <div class="flex items-center justify-center flex-shrink-0">
                        <button class="w-10 h-10 rounded-full hover:bg-surface-1 flex items-center justify-center text-text-muted transition-colors outline-none"
                            @click="togglePin()">
                            <svg class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-3 whitespace-nowrap pl-[12px]">
                        <div class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></div>
                        <div class="font-semibold text-navy-900 text-[15px] leading-tight uppercase tracking-wider">EduPortal</div>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 space-y-1 relative">
                    @if($isLecturerUser)
                    <div class="px-3">
                        <a href="{{ route('lecturer.classes.index') }}"
                            class="sidebar-link {{ request()->routeIs('lecturer.classes.index') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.25L12 3l9 7.25v9.25a1.5 1.5 0 01-1.5 1.5h-4.25v-6.5h-6.5V21H4.5A1.5 1.5 0 013 19.5v-9.25z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-4" x-show="isExpanded" x-cloak>Màn hình chính</span>
                        </a>
                    </div>

                    <div class="px-2 py-1 space-y-1">
                        <button type="button"
                            class="sidebar-link w-full"
                            @click="openClassMenu = !openClassMenu">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <span class="sidebar-label flex-1 text-left transition-opacity duration-300 pr-2" x-show="isExpanded" x-cloak>Lớp học phần</span>
                            <svg x-show="isExpanded" x-cloak class="w-4 h-4 mr-3 text-text-muted transition-transform" :class="openClassMenu ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openClassMenu" x-transition.opacity.duration.150ms class="space-y-1 pl-2 pr-1" style="display:none;">
                            @forelse($lecturerSidebarSections as $section)
                            @php
                            $sectionRouteModel = request()->route('section');
                            $sectionActive = request()->routeIs('lecturer.classes.show')
                            && ((int) optional($sectionRouteModel)->id === (int) $section->id);
                            @endphp
                            <a href="{{ route('lecturer.classes.show', $section) }}"
                                class="sidebar-link {{ $sectionActive ? 'active' : '' }}">
                                <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                </div>
                                <span class="sidebar-label flex-1 min-w-0 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>
                                    <span class="block truncate">{{ $section->name ?? $section->code }}</span>
                                    <span class="block text-[10px] text-text-muted truncate">{{ $section->code }}</span>
                                </span>
                            </a>
                            @empty
                            <p class="text-[11px] text-text-muted px-3 py-2" x-show="isExpanded" x-cloak>Chưa có lớp học phần được phân công.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="px-2 py-1 space-y-1 border-t border-border-clean/60 mt-2 pt-2">
                        <button type="button"
                            class="sidebar-link w-full"
                            @click="openQuestionBank = !openQuestionBank">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="sidebar-label flex-1 text-left transition-opacity duration-300 pr-2" x-show="isExpanded" x-cloak>Ngân hàng câu hỏi</span>
                            <svg x-show="isExpanded" x-cloak class="w-4 h-4 mr-3 text-text-muted transition-transform" :class="openQuestionBank ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openQuestionBank" x-transition.opacity.duration.150ms class="space-y-1 pl-2 pr-1" style="display:none;">
                            @foreach($lecturerSidebarSubjects as $subject)
                            @php
                            $questionActive = request()->routeIs('lecturer.questions.*')
                            && request()->query('sub-sel-ques') === $subject->code;
                            @endphp
                            <a href="{{ route('lecturer.questions.index', ['sub-sel-ques' => $subject->code]) }}"
                                class="sidebar-link {{ $questionActive ? 'active' : '' }}">
                                <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-teal-500"></div>
                                </div>
                                <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>{{ $subject->name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-2 py-1 space-y-1">
                        <button type="button"
                            class="sidebar-link w-full"
                            @click="openExamBank = !openExamBank">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="sidebar-label flex-1 text-left transition-opacity duration-300 pr-2" x-show="isExpanded" x-cloak>Quản lý Đề thi</span>
                            <svg x-show="isExpanded" x-cloak class="w-4 h-4 mr-3 text-text-muted transition-transform" :class="openExamBank ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openExamBank" x-transition.opacity.duration.150ms class="space-y-1 pl-2 pr-1" style="display:none;">
                            @foreach($lecturerSidebarSubjects as $subject)
                            @php
                            $examActive = request()->routeIs('lecturer.exams.*')
                            && (string) request()->query('subject_id') === (string) $subject->id;
                            @endphp
                            <a href="{{ route('lecturer.exams.index', ['subject_id' => $subject->id]) }}"
                                class="sidebar-link {{ $examActive ? 'active' : '' }}">
                                <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                                </div>
                                <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>{{ $subject->name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-2 py-1 space-y-1 border-t border-border-clean/60 mt-1 pt-2">
                        <a href="{{ route('lecturer.schedules.index') }}"
                            class="sidebar-link {{ request()->routeIs('lecturer.schedules.*') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>Lịch thi</span>
                        </a>
                    </div>
                    @endif

                    @role('student')
                    <div class="px-3">
                        <a href="{{ route('student.dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-4" x-show="isExpanded" x-cloak>Tổng quan</span>
                        </a>
                    </div>

                    <div class="px-2 py-1 space-y-1">
                        <button type="button"
                            class="sidebar-link w-full"
                            @click="openClassMenu = !openClassMenu">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <span class="sidebar-label flex-1 text-left transition-opacity duration-300 pr-2" x-show="isExpanded" x-cloak>Lớp học của tôi</span>
                            <svg x-show="isExpanded" x-cloak class="w-4 h-4 mr-3 text-text-muted transition-transform" :class="openClassMenu ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openClassMenu" x-transition.opacity.duration.150ms class="space-y-1 pl-2 pr-1" style="display:none;">
                            @forelse($studentSidebarSections as $section)
                            @php
                            $sectionRouteModel = request()->route('section');
                            $sectionActive = request()->routeIs('student.classes.show')
                            && ((int) optional($sectionRouteModel)->id === (int) $section->id);
                            @endphp
                            <a href="{{ route('student.classes.show', $section) }}"
                                class="sidebar-link {{ $sectionActive ? 'active' : '' }}">
                                <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                </div>
                                <span class="sidebar-label flex-1 min-w-0 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>
                                    <span class="block truncate">{{ $section->name ?? $section->code }}</span>
                                    <span class="block text-[10px] text-text-muted truncate">{{ $section->subject->name ?? $section->code }}</span>
                                </span>
                            </a>
                            @empty
                            <p class="text-[11px] text-text-muted px-3 py-2" x-show="isExpanded" x-cloak>Chưa có lớp học phần nào.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="px-2 py-1 space-y-1 border-t border-border-clean/60 mt-2 pt-2">
                        <a href="{{ route('student.results.index') }}"
                            class="sidebar-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>Kết quả học tập</span>
                        </a>

                        <a href="{{ route('student.complaints.index') }}"
                            class="sidebar-link {{ request()->routeIs('student.complaints.*') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>Khiếu nại</span>
                        </a>
                    </div>

                    <div class="px-2 py-1 space-y-1 border-t border-border-clean/60 mt-1 pt-2">
                        <a href="{{ route('profile.edit') }}"
                            class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <div class="w-[48px] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span class="sidebar-label truncate flex-1 transition-opacity duration-300 pr-3" x-show="isExpanded" x-cloak>Hồ sơ cá nhân</span>
                        </a>
                    </div>
                    @endrole
                </nav>
            </div>
        </aside>

        {{-- ─── MAIN CONTENT ─────────────────────────────────────── --}}
        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="sticky top-0 z-30 h-[52px] bg-navy-900 text-white">
                <div class="flex items-center justify-between h-full px-4 sm:px-6">
                    <div class="flex items-center gap-4">
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

                    <div class="hidden md:flex flex-1 max-w-md mx-8"></div>

                    <div class="flex items-center gap-2 sm:gap-3">
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

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <x-toast />
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>