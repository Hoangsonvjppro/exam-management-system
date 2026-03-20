<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập thông tin sinh viên</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-1">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-border-clean">
            <h1 class="text-2xl font-bold text-navy-900">Hoàn tất hồ sơ sinh viên</h1>
            <p class="mt-2 text-sm text-text-muted">Vui lòng cung cấp MSSV và tên lớp trước khi tiếp tục đến bảng điều khiển.</p>

            @if ($errors->any())
            <div class="mt-6 rounded-[10px] border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('onboarding.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="student_code" class="mb-2 block text-sm font-medium text-text-muted">Mã số sinh viên</label>
                    <input
                        id="student_code"
                        name="student_code"
                        type="text"
                        value="{{ old('student_code') }}"
                        required
                        class="w-full rounded-[10px] border border-border-clean px-4 py-2.5 text-navy-900 outline-none transition focus:border-navy-600 focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. 22123456">
                </div>

                <div>
                    <label for="class_name" class="mb-2 block text-sm font-medium text-text-muted">Tên lớp </label>
                    <input
                        id="class_name"
                        name="class_name"
                        type="text"
                        value="{{ old('class_name') }}"
                        required
                        class="w-full rounded-[10px] border border-border-clean px-4 py-2.5 text-navy-900 outline-none transition focus:border-navy-600 focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. D21CQCN01-N">
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-[10px] bg-navy-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-navy-950 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    Lưu thông tin
                </button>
            </form>
        </div>
    </div>
</body>

</html>