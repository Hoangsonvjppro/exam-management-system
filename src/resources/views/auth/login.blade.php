<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Đăng nhập vào hệ thống EMS - Cổng dành cho giảng viên.">
    <title>Đăng nhập Giảng viên — {{ config('app.name', 'EMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-card {
            animation: cardFadeIn 0.6s ease-out forwards;
            opacity: 0;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


    </style>
</head>
<body class="bg-surface-0 font-sans text-navy-900 min-h-screen flex flex-col">
{{-- ===== HEADER ===== --}}
<header class="w-full bg-white border-b-[0.5px] border-border-clean px-6 md:px-20 py-4 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
        <h1 class="text-[17px] font-semibold tracking-wider uppercase">EMS</h1>
    </div>
    <a href="{{ route('landing') }}" class="text-[13px] font-medium text-text-muted hover:text-navy-900 transition-colors flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Về trang chủ
    </a>
</header>

{{-- ===== MAIN ===== --}}
<main class="flex-grow flex items-center justify-center p-6 md:p-12">
    <div class="login-card max-w-lg w-full">

        {{-- Error hiển thị chung --}}
        @if ($errors->has('google_auth'))
            <div class="mb-4 p-3 bg-red-50 border-[0.5px] border-red-200 rounded-[6px] text-[13px] font-medium text-red-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px] text-red-500">error</span>
                {{ $errors->first('google_auth') }}
            </div>
        @endif

        {{-- Login Card --}}
        <div class="bg-white border-[0.5px] border-border-clean rounded-[10px] shadow-sm overflow-hidden">

            {{-- ============================== --}}
            {{-- PHẦN 1: DÀNH CHO GIẢNG VIÊN   --}}
            {{-- ============================== --}}
            <div class="p-8 md:p-10">
                <div class="mb-6">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-[6px] bg-navy-900 flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-[16px]">shield_person</span>
                        </div>
                        <h3 class="text-[18px] font-bold text-navy-900">Dành cho Giảng viên</h3>
                    </div>
                    <p class="text-[12px] text-text-muted ml-[42px]">Đăng nhập bằng tài khoản nội bộ do quản trị viên cấp.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Email / Mã đăng nhập --}}
                    <div>
                        <label for="login_id" class="block text-[12px] font-medium text-navy-900 mb-1.5">Mã đăng nhập</label>
                        <x-text-input id="login_id" name="login_id" type="text" value="{{ old('login_id') }}" required autofocus placeholder="Mã đăng nhập" />
                        @error('login_id')
                            <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mật khẩu --}}
                    <div>
                        <label for="password" class="block text-[12px] font-medium text-navy-900 mb-1.5">Mật khẩu</label>
                        <x-password-input id="password" name="password" required placeholder="••••••••" />
                        @error('password')
                            <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="remember" name="remember" class="rounded-[4px] border-[1.5px] border-border-clean text-navy-600 focus:ring-navy-50 w-4 h-4">
                        <label for="remember" class="text-[12px] font-medium text-navy-900">Ghi nhớ đăng nhập</label>
                    </div>

                    {{-- Submit --}}
                    <x-button type="submit" variant="primary" class="w-full">
                        Đăng nhập
                    </x-button>
                </form>
            </div>

            {{-- ============================== --}}
            {{-- GHI CHÚ CHO SINH VIÊN          --}}
            {{-- ============================== --}}
            <div class="px-8 md:px-10 pb-8 md:pb-10">
                <div class="p-4 bg-blue-50/60 border-[0.5px] border-blue-200/60 rounded-[8px] flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-500 text-[18px] mt-0.5">info</span>
                    <div>
                        <p class="text-[12px] font-semibold text-navy-900 mb-0.5">Bạn là sinh viên?</p>
                        <p class="text-[11px] text-text-muted leading-relaxed">
                            Vui lòng quay lại <a href="{{ route('landing') }}" class="text-blue-600 hover:underline font-medium">trang chủ</a> và nhấn nút <strong>"Cổng dành cho Sinh viên"</strong> để đăng nhập bằng Google.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

{{-- ===== FOOTER ===== --}}
<footer class="w-full bg-white border-t-[0.5px] border-border-clean px-6 md:px-20 py-6 mt-auto">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-[12px] font-medium text-text-muted tracking-wider">© {{ date('Y') }} EMS — Trường Đại học Sài Gòn</p>
        <div class="flex gap-6">
            <a class="text-[12px] font-medium text-text-muted hover:text-navy-900 transition-colors" href="#">Điều khoản</a>
            <a class="text-[12px] font-medium text-text-muted hover:text-navy-900 transition-colors" href="#">Bảo mật</a>
            <a class="text-[12px] font-medium text-text-muted hover:text-navy-900 transition-colors" href="#">Hỗ trợ</a>
        </div>
    </div>
</footer>
<x-toast />
</body>
</html>
