<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EMS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-surface-0 text-navy-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="w-full border-b-[0.5px] border-border-clean bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-[52px] flex items-center justify-between">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                    <span class="text-[15px] font-semibold tracking-wide uppercase">EduPortal</span>
                </a>
                <a href="{{ route('login') }}" class="text-[12px] font-medium text-text-muted hover:text-navy-900 transition-colors">Đăng nhập</a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="w-full max-w-md bg-white border-[0.5px] border-border-clean rounded-[10px] shadow-card p-6 sm:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>