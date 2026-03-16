<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập - EMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background-light font-display text-slate-900 min-h-screen flex flex-col">
<header class="w-full bg-white border-b-[3px] border-black px-6 md:px-20 py-4 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-3">
        <div class="bg-ems-primary brutal-border p-1 flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-3xl">database</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tighter italic">EMS</h1>
    </div>
    <a href="{{ route('landing') }}" class="text-lg font-bold border-b-[4px] border-black hover:bg-ems-primary/10 px-2 py-1 transition-colors">
        Về trang chủ
    </a>
</header>

<main class="flex-grow flex items-center justify-center p-6 md:p-12">
    <div class="max-w-5xl w-full bg-white brutal-border shadow-brutal-lg flex flex-col md:flex-row overflow-hidden">
        <div class="md:w-1/2 bg-ems-primary/10 border-b-[3px] md:border-b-0 md:border-r-[3px] border-black p-12 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="relative z-10 text-center">
                <div class="mb-8 flex justify-center gap-4">
                    <div class="w-24 h-24 bg-ems-primary brutal-border flex items-center justify-center transform -rotate-6 shadow-brutal">
                        <span class="material-symbols-outlined text-white text-5xl">school</span>
                    </div>
                    <div class="w-24 h-24 bg-white brutal-border flex items-center justify-center transform rotate-12 shadow-brutal">
                        <span class="material-symbols-outlined text-ems-primary text-5xl">calendar_month</span>
                    </div>
                </div>
                <h2 class="text-3xl font-black text-ems-primary leading-none uppercase mb-2">Hệ thống</h2>
                <p class="text-xl font-bold bg-black text-white px-3 py-1 inline-block">Quản lý Khảo thí</p>
            </div>
        </div>

        <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <div class="mb-8">
                <h3 class="text-4xl font-black uppercase tracking-tight mb-2">Đăng nhập</h3>
                <div class="h-2 w-20 bg-ems-primary mb-4"></div>
                <p class="text-slate-600 font-medium">Giảng viên đăng nhập bằng email và mật khẩu. Sinh viên đăng nhập bằng Google bên dưới.</p>
            </div>

            @if (session('warning'))
                <div class="mb-4 p-3 bg-yellow-100 brutal-border text-sm font-semibold text-yellow-900">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 brutal-border text-sm font-semibold text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->has('google_auth'))
                <div class="mb-4 p-3 bg-red-100 brutal-border text-sm font-semibold text-red-800">
                    {{ $errors->first('google_auth') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-black uppercase mb-2 tracking-wide">Email giảng viên</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full h-14 px-4 bg-white brutal-border font-bold text-lg placeholder:text-slate-400 focus:ring-0"
                        placeholder="lecturer@ems.local">
                    @error('email')
                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-black uppercase mb-2 tracking-wide">Mật khẩu</label>
                    <input id="password" name="password" type="password" required
                        class="w-full h-14 px-4 bg-white brutal-border font-bold text-lg placeholder:text-slate-400 focus:ring-0"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-black text-ems-primary focus:ring-0">
                    <label for="remember" class="text-sm font-semibold">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit"
                    class="w-full h-14 bg-ems-primary text-white brutal-border shadow-brutal font-black uppercase tracking-widest hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all">
                    Đăng nhập
                </button>
            </form>

            <div class="my-6 flex items-center gap-3">
                <div class="h-[2px] flex-1 bg-black"></div>
                <span class="text-xs font-black uppercase tracking-widest">Hoặc</span>
                <div class="h-[2px] flex-1 bg-black"></div>
            </div>

            <div class="bg-blue-50 brutal-border p-4 mb-2">
                <p class="text-xs font-black uppercase tracking-wider text-blue-700 mb-3">Dành cho Sinh viên</p>
                <a href="{{ route('google.redirect') }}"
                    class="w-full h-14 bg-white brutal-border shadow-brutal font-black uppercase tracking-wider flex items-center justify-center gap-2 hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all">
                    <span class="material-symbols-outlined">account_circle</span>
                    Đăng nhập với Google
                </a>
            </div>

            <div class="mt-8 pt-6 border-t-2 border-dashed border-slate-300">
                <p class="text-sm font-bold text-slate-600 italic">
                    Quên mật khẩu giảng viên? Liên hệ quản trị viên để được cấp lại mật khẩu tạm.
                </p>
            </div>
        </div>
    </div>
</main>

<footer class="w-full bg-white border-t-[3px] border-black px-6 md:px-20 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-sm font-black uppercase tracking-widest">© {{ date('Y') }} EMS - EXAM MANAGEMENT SYSTEM</p>
        <div class="flex gap-6">
            <a class="text-sm font-bold hover:underline" href="#">Điều khoản</a>
            <a class="text-sm font-bold hover:underline" href="#">Bảo mật</a>
            <a class="text-sm font-bold hover:underline" href="#">Hỗ trợ kỹ thuật</a>
        </div>
    </div>
</footer>
</body>
</html>
