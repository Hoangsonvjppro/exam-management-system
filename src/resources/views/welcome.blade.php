<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EMS - Hệ thống đánh giá và quản lý học vụ trực tuyến. Cổng dành cho sinh viên và giảng viên.">
    <title>{{ config('app.name', 'EMS') }} — Cổng Thông Tin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gateway-bg {
            background-image: url('/img/SGU.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .gateway-overlay {
            background: linear-gradient(
                135deg,
                rgba(11, 35, 71, 0.88) 0%,
                rgba(26, 58, 107, 0.82) 40%,
                rgba(24, 95, 165, 0.78) 100%
            );
            backdrop-filter: blur(4px);
        }

        .gateway-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gateway-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .gateway-btn:active {
            transform: translateY(-1px);
        }

        .gateway-logo-pulse {
            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        .gateway-slogan {
            animation: sloganFadeIn 1s ease-out forwards;
            opacity: 0;
        }

        .gateway-buttons {
            animation: buttonsFadeUp 1s ease-out 0.3s forwards;
            opacity: 0;
        }

        @keyframes sloganFadeIn {
            to { opacity: 1; }
        }

        @keyframes buttonsFadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .floating-particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            animation: floatUp 12s ease-in-out infinite;
        }

        .floating-particle:nth-child(2) {
            animation-delay: 2s;
            animation-duration: 16s;
        }

        .floating-particle:nth-child(3) {
            animation-delay: 5s;
            animation-duration: 14s;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.06; }
            50% { transform: translateY(-60px) scale(1.1); opacity: 0.12; }
        }
    </style>
</head>

<body class="font-sans text-white">
    <div class="gateway-bg min-h-screen relative">
        <div class="gateway-overlay absolute inset-0"></div>

        {{-- Floating decorative particles --}}
        <div class="floating-particle" style="width:200px;height:200px;top:10%;left:5%;"></div>
        <div class="floating-particle" style="width:300px;height:300px;bottom:10%;right:5%;"></div>
        <div class="floating-particle" style="width:150px;height:150px;top:50%;left:60%;"></div>

        <div class="relative z-10 min-h-screen flex flex-col">

            {{-- ===== HEADER ===== --}}
            <header class="px-6 md:px-16 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3 gateway-logo-pulse">
                    <div class="w-10 h-10 bg-white/10 rounded-[8px] flex items-center justify-center backdrop-blur-md border-[0.5px] border-white/20">
                        <span class="material-symbols-outlined text-white text-xl font-bold">school</span>
                    </div>
                    <span class="text-[17px] font-semibold tracking-wider uppercase text-white/90">EMS</span>
                </div>

                @auth
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}"
                       class="text-[13px] font-medium text-white/70 hover:text-white transition-colors">
                        Dashboard
                    </a>
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-9 h-9 rounded-full border-[1.5px] border-white/30 object-cover"
                         referrerpolicy="no-referrer">
                </div>
                @endauth
            </header>

            {{-- ===== MAIN CONTENT ===== --}}
            <main class="flex-grow flex flex-col items-center justify-center px-6 text-center pb-12">

                {{-- Slogan --}}
                <div class="gateway-slogan mb-16 max-w-4xl">
                    <div class="inline-block mb-6 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border-[0.5px] border-white/20">
                        <span class="text-[12px] font-medium tracking-widest uppercase text-blue-200">Trường Đại học Sài Gòn</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-[56px] font-bold leading-tight tracking-tight text-white">
                        HỆ THỐNG ĐÁNH GIÁ VÀ<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 via-white to-blue-200">QUẢN LÝ HỌC VỤ TRỰC TUYẾN</span>
                    </h2>
                    <p class="mt-6 text-[15px] text-white/60 max-w-2xl mx-auto leading-relaxed">
                        Nền tảng quản lý giảng dạy, khảo thí và đánh giá học vụ hiện đại. Đăng nhập để bắt đầu.
                    </p>
                </div>

                {{-- Gateway Buttons --}}
                <div class="gateway-buttons flex flex-col sm:flex-row gap-5 sm:gap-6 w-full max-w-xl">

                    {{-- Nút Sinh viên (Google) --}}
                    <a href="{{ route('login') }}"
                       id="btn-gateway-student"
                       class="gateway-btn flex-1 group relative bg-white text-navy-900 rounded-[10px] overflow-hidden border-[0.5px] border-white/80">
                        <div class="px-6 py-8 sm:py-10 flex flex-col items-center gap-4">
                            {{-- Google Icon --}}
                            <div class="w-14 h-14 rounded-full bg-surface-1 flex items-center justify-center border-[0.5px] border-border-clean group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium text-text-muted uppercase tracking-wider mb-1">Đăng nhập bằng Google</p>
                                <p class="text-[17px] font-bold text-navy-900">Cổng dành cho Sinh viên</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#4285F4] via-[#34A853] to-[#EA4335] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>

                    {{-- Nút Giảng viên --}}
                    <a href="{{ route('login') }}"
                       id="btn-gateway-lecturer"
                       class="gateway-btn flex-1 group relative bg-white/10 backdrop-blur-md text-white rounded-[10px] overflow-hidden border-[0.5px] border-white/20 hover:bg-white/15">
                        <div class="px-6 py-8 sm:py-10 flex flex-col items-center gap-4">
                            {{-- App Icon --}}
                            <div class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center border-[0.5px] border-white/20 group-hover:scale-110 transition-transform duration-300">
                                <span class="material-symbols-outlined text-white text-2xl">shield_person</span>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium text-white/50 uppercase tracking-wider mb-1">Tài khoản nội bộ</p>
                                <p class="text-[17px] font-bold text-white">Cổng dành cho Giảng viên</p>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-navy-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>

                </div>
            </main>

            {{-- ===== FOOTER ===== --}}
            <footer class="px-6 md:px-16 py-5 text-center">
                <p class="text-[11px] font-medium text-white/30 tracking-wider">
                    © {{ date('Y') }} EMS — Trường Đại học Sài Gòn. All Rights Reserved.
                </p>
            </footer>

        </div>
    </div>
</body>

</html>