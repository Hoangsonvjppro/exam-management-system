<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-background">

            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main Content Wrapper --}}
            <div class="flex-1 flex flex-col min-h-screen lg:ml-0 w-full">

                {{-- Top Header Bar --}}
                <header class="sticky top-0 z-30 h-16 bg-white border-b border-border flex items-center justify-between px-4 sm:px-6 lg:px-8">
                    {{-- Left: Hamburger + Page Title --}}
                    <div class="flex items-center gap-4">
                        {{-- Mobile menu toggle --}}
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-surface-hover hover:text-gray-700 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- Page Heading + Subtitle --}}
                        <div>
                            @isset($header)
                                <h1 class="text-lg font-bold text-gray-900 leading-tight">
                                    {{ $header }}
                                </h1>
                            @endisset
                            @isset($subtitle)
                                <p class="text-sm text-gray-500">{{ $subtitle }}</p>
                            @endisset
                        </div>
                    </div>

                    {{-- Center: Search Bar --}}
                    <div class="hidden md:flex flex-1 max-w-md mx-8">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="search"
                                   placeholder="{{ __('Tìm kiếm khóa học...') }}"
                                   class="block w-full pl-10 pr-4 py-2 bg-surface-muted border border-border rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        </div>
                    </div>

                    {{-- Right: Actions --}}
                    <div class="flex items-center gap-2 sm:gap-3">
                        {{-- Mobile search toggle --}}
                        <button class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-surface-hover hover:text-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>

                        {{-- Notifications --}}
                        <button class="relative p-2 rounded-lg text-gray-500 hover:bg-surface-hover hover:text-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            {{-- Notification badge --}}
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-danger-500 rounded-full ring-2 ring-white"></span>
                        </button>

                        {{-- Divider --}}
                        <div class="hidden sm:block w-px h-8 bg-border"></div>

                        {{-- Profile Dropdown --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-surface-hover transition-colors">
                                    <div class="hidden sm:block text-right">
                                        <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name ?? 'Nguyễn Văn A' }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Sinh viên CNTT') }}</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ __('Hồ sơ cá nhân') }}
                                    </span>
                                </x-dropdown-link>

                                <x-dropdown-link href="#">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ __('Cài đặt') }}
                                    </span>
                                </x-dropdown-link>

                                <div class="border-t border-border my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        <span class="flex items-center gap-2 text-danger-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ __('Đăng xuất') }}
                                        </span>
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
