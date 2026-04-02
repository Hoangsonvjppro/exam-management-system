<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Đăng nhập vào hệ thống EMS - Cổng dành cho giảng viên và sinh viên.">
    <title>Đăng nhập — {{ config('app.name', 'EMS') }}</title>
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

        .google-btn {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .google-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(66, 133, 244, 0.08) 0%,
                rgba(52, 168, 83, 0.08) 33%,
                rgba(251, 188, 5, 0.08) 66%,
                rgba(234, 67, 53, 0.08) 100%
            );
            opacity: 0;
            transition: opacity 0.3s;
        }

        .google-btn:hover::before {
            opacity: 1;
        }

        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 58, 107, 0.15);
        }

        .google-btn:active {
            transform: translateY(0);
        }

        .google-rainbow-bar {
            background: linear-gradient(90deg, #4285F4 0%, #34A853 33%, #FBBC05 66%, #EA4335 100%);
        }

        .divider-line {
            background: linear-gradient(90deg, transparent 0%, #D6E2F0 30%, #D6E2F0 70%, transparent 100%);
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
            {{-- DIVIDER: HOẶC                 --}}
            {{-- ============================== --}}
            <div class="px-8 md:px-10">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full h-[0.5px] divider-line"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-4 text-[11px] text-text-muted font-semibold uppercase tracking-[0.15em]">HOẶC</span>
                    </div>
                </div>
            </div>

            {{-- ============================== --}}
            {{-- PHẦN 2: DÀNH CHO SINH VIÊN    --}}
            {{-- ============================== --}}
            <div class="p-8 md:p-10">
                <div class="mb-5">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-[6px] bg-surface-2 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                        </div>
                        <h3 class="text-[18px] font-bold text-navy-900">Dành cho Sinh viên</h3>
                    </div>
                    <p class="text-[12px] text-text-muted ml-[42px]">Sử dụng email trường để đăng nhập nhanh chóng và bảo mật.</p>
                </div>

                {{-- NÚT ĐĂNG NHẬP GOOGLE --}}
                <a href="{{ route('google.redirect') }}"
                   id="btn-google-login"
                   class="google-btn relative block w-full text-center bg-white border-[1.5px] border-border-clean rounded-[8px] overflow-hidden">
                    {{-- Google rainbow bar on top --}}
                    <div class="h-[3px] google-rainbow-bar"></div>

                    <div class="px-6 py-5 flex items-center justify-center gap-3">
                        <svg class="w-6 h-6 relative z-10 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span class="relative z-10 text-[15px] font-semibold text-navy-900">Đăng nhập bằng Email Trường (Google)</span>
                    </div>
                </a>
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
