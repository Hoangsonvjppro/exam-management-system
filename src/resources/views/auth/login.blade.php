<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập — EMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-900 via-indigo-800 to-violet-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/15 backdrop-blur rounded-2xl mb-4">
                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white">EMS</h1>
            <p class="text-indigo-200 text-sm mt-1">Hệ thống Quản lý Thi trắc nghiệm</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl shadow-black/25 p-8">

            <h2 class="text-xl font-semibold text-gray-900 mb-1">Đăng nhập</h2>
            <p class="text-gray-500 text-sm mb-6">Nhập thông tin tài khoản được cấp</p>

            {{-- Session status --}}
            @if (session('status'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error from EnsureUserIsActive --}}
            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email
                    </label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="email"
                           placeholder="email@truong.edu.vn"
                           class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-500 focus:border-red-400' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500' }}
                                  focus:outline-none focus:ring-2">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Mật khẩu
                    </label>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition
                                  {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-500 focus:border-red-400' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500' }}
                                  focus:outline-none focus:ring-2">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               name="remember"
                               id="remember_me"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">Ghi nhớ đăng nhập</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            Quên mật khẩu?
                        </a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-sm">
                    Đăng nhập
                </button>
            </form>

        </div>

        {{-- Demo hint for dev --}}
        @if(config('app.debug'))
        <div class="mt-4 text-center text-indigo-300 text-xs space-y-0.5">
            <p>Demo: <code class="bg-white/10 px-1 rounded">admin@ems.local</code> / <code class="bg-white/10 px-1 rounded">password</code></p>
        </div>
        @endif

    </div>

</body>
</html>
