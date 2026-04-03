<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EMS - Hệ thống đánh giá và quản lý học vụ trực tuyến. Cổng dành cho sinh viên và giảng viên.">
    <title>{{ config('app.name', 'EMS') }} — Cổng Thông Tin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Background ── */
        .gateway-bg {
            background-image: url('/img/SGU.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .gateway-overlay {
            background: linear-gradient(
                160deg,
                rgba(8, 24, 52, 0.92) 0%,
                rgba(15, 42, 82, 0.87) 35%,
                rgba(20, 68, 130, 0.82) 70%,
                rgba(30, 90, 160, 0.78) 100%
            );
            backdrop-filter: blur(3px);
        }

        /* ── Animations ── */
        .gateway-logo-pulse {
            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { opacity: 0.85; }
            50% { opacity: 1; }
        }

        .animate-hero {
            animation: heroIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .animate-hero-delay-1 {
            animation: heroIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
            opacity: 0;
        }

        .animate-hero-delay-2 {
            animation: heroIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
            opacity: 0;
        }

        .animate-hero-delay-3 {
            animation: heroIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
            opacity: 0;
        }

        .animate-hero-delay-4 {
            animation: heroIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.7s forwards;
            opacity: 0;
        }

        @keyframes heroIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Student CTA Button ── */
        .cta-student {
            position: relative;
            background: rgba(255, 255, 255, 0.97);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .cta-student::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4285F4, #34A853, #FBBC05, #EA4335);
            transform: scaleX(0);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: left;
        }

        .cta-student:hover {
            transform: translateY(-3px);
            box-shadow:
                0 12px 40px rgba(66, 133, 244, 0.2),
                0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .cta-student:hover::before {
            transform: scaleX(1);
        }

        .cta-student:active {
            transform: translateY(-1px);
        }

        .cta-student .google-icon-wrap {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cta-student:hover .google-icon-wrap {
            transform: scale(1.1) rotate(-4deg);
        }

        .cta-student .cta-arrow {
            opacity: 0;
            transform: translateX(-8px);
            transition: all 0.3s ease;
        }

        .cta-student:hover .cta-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        /* ── Header lecturer link ── */
        .header-lecturer-link {
            transition: all 0.25s ease;
        }

        .header-lecturer-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }

        /* ── Floating particles ── */
        .floating-particle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            animation: floatUp 14s ease-in-out infinite;
        }

        .floating-particle:nth-child(2) {
            animation-delay: 3s;
            animation-duration: 18s;
        }

        .floating-particle:nth-child(3) {
            animation-delay: 6s;
            animation-duration: 16s;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.05; }
            50% { transform: translateY(-50px) scale(1.05); opacity: 0.1; }
        }


    </style>
</head>

<body class="font-sans text-white">
    <div class="gateway-bg min-h-screen relative">
        <div class="gateway-overlay absolute inset-0"></div>

        {{-- Floating decorative particles --}}
        <div class="floating-particle" style="width:240px;height:240px;top:8%;left:3%;"></div>
        <div class="floating-particle" style="width:320px;height:320px;bottom:8%;right:3%;"></div>
        <div class="floating-particle" style="width:160px;height:160px;top:45%;left:65%;"></div>

        <div class="relative z-10 min-h-screen flex flex-col">

            {{-- ===== HEADER ===== --}}
            <header class="px-6 md:px-16 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3 gateway-logo-pulse">
                    <div class="w-10 h-10 bg-white/10 rounded-[8px] flex items-center justify-center backdrop-blur-md border-[0.5px] border-white/20">
                        <span class="material-symbols-outlined text-white text-xl font-bold">school</span>
                    </div>
                    <span class="text-[17px] font-semibold tracking-wider uppercase text-white/90">EMS</span>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="text-[13px] font-medium text-white/70 hover:text-white transition-colors">
                            Dashboard
                        </a>
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-9 h-9 rounded-full border-[1.5px] border-white/30 object-cover"
                             referrerpolicy="no-referrer">
                    @else
                        <a href="{{ route('login') }}"
                           id="btn-gateway-lecturer"
                           class="header-lecturer-link flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.07] border-[0.5px] border-white/15 text-[13px] font-medium text-white/70">
                            <span class="material-symbols-outlined text-[16px]">shield_person</span>
                            Giảng viên
                        </a>
                    @endauth
                </div>
            </header>

            {{-- ===== MAIN CONTENT ===== --}}
            <main class="flex-grow flex flex-col items-center justify-center px-6 text-center pb-16">

                {{-- Badge --}}
                <div class="animate-hero mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/[0.07] backdrop-blur-sm border-[0.5px] border-white/15">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-[11px] font-semibold tracking-[0.18em] uppercase text-blue-200/90">Trường Đại học Sài Gòn</span>
                    </div>
                </div>

                {{-- Heading --}}
                <h2 class="animate-hero-delay-1 text-3xl sm:text-4xl md:text-5xl lg:text-[54px] font-bold leading-[1.15] tracking-tight text-white max-w-4xl">
                    Hệ Thống Đánh Giá Và<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-white to-blue-300">Quản Lý Học Vụ Trực Tuyến</span>
                </h2>

                {{-- Description --}}
                <p class="animate-hero-delay-2 mt-5 text-[15px] text-white/50 max-w-lg mx-auto leading-relaxed">
                    Nền tảng quản lý giảng dạy, khảo thí và đánh giá học vụ hiện đại dành cho sinh viên và giảng viên.
                </p>

                {{-- ===== CTA BUTTONS ===== --}}
                <div class="animate-hero-delay-3 mt-12 flex flex-col items-center gap-6 w-full max-w-md">

                    {{-- Primary: Sinh viên (Google OAuth trực tiếp) --}}
                    <a href="{{ route('google.redirect') }}"
                       id="btn-gateway-student"
                       class="cta-student group w-full flex items-center gap-4 px-7 py-5 rounded-2xl">
                        {{-- Google Icon --}}
                        <div class="google-icon-wrap w-11 h-11 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                        </div>
                        {{-- Label --}}
                        <div class="flex-grow text-left">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-[0.12em] mb-0.5">Sinh viên</p>
                            <p class="text-[16px] font-bold text-gray-800">Đăng nhập bằng Google</p>
                        </div>
                        {{-- Arrow --}}
                        <span class="cta-arrow material-symbols-outlined text-gray-400 text-[20px]">arrow_forward</span>
                    </a>

                </div>

            </main>

            {{-- ===== FOOTER ===== --}}
            <footer class="px-6 md:px-16 py-5 text-center animate-hero-delay-4">
                <p class="text-[11px] font-medium text-white/25 tracking-wider">
                    © {{ date('Y') }} EMS — Trường Đại học Sài Gòn. All Rights Reserved.
                </p>
            </footer>

        </div>
    </div>
</body>

</html>