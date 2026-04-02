<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoàn tất hồ sơ sinh viên</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-1">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-border-clean">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-navy-900">Hoàn tất hồ sơ sinh viên</h1>
            </div>

            <p class="mt-3 text-sm text-text-muted leading-relaxed">
                Hệ thống cần <strong>họ tên thật</strong> và <strong>mã số sinh viên (MSSV)</strong> của bạn
                để đảm bảo hồ sơ học vụ chính xác. Tên này sẽ được sử dụng trong bảng điểm, danh sách lớp và
                các tài liệu chính thức.
            </p>

            {{-- Warning --}}
            <div class="mt-4 flex items-start gap-2 rounded-[10px] border border-amber-200 bg-amber-50 p-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-xs text-amber-800">
                    <strong>Lưu ý:</strong> Vui lòng nhập đúng họ tên theo giấy tờ tùy thân, không sử dụng biệt danh
                    hoặc tên mạng xã hội. Tên Google của bạn hiện tại là
                    "<span class="font-semibold">{{ auth()->user()->name }}</span>".
                </p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mt-5 rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('onboarding.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-text-muted">
                        Họ và tên đầy đủ <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', auth()->user()->name) }}"
                        required
                        class="w-full rounded-[10px] border border-border-clean px-4 py-2.5 text-navy-900 outline-none transition focus:border-navy-600 focus:ring-2 focus:ring-blue-200"
                        placeholder="VD: Nguyễn Văn An">
                </div>

                <div>
                    <label for="student_code" class="mb-2 block text-sm font-medium text-text-muted">
                        Mã số sinh viên (MSSV) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="student_code"
                        name="student_code"
                        type="text"
                        value="{{ old('student_code') }}"
                        required
                        class="w-full rounded-[10px] border border-border-clean px-4 py-2.5 text-navy-900 outline-none transition focus:border-navy-600 focus:ring-2 focus:ring-blue-200"
                        placeholder="VD: 22123456">
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-[10px] bg-navy-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-navy-950 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    Xác nhận & Tiếp tục
                </button>
            </form>
        </div>
    </div>
</body>

</html>