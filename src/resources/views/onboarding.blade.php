<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Onboarding</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
            <h1 class="text-2xl font-bold text-slate-900">Complete Your Student Profile</h1>
            <p class="mt-2 text-sm text-slate-600">Please provide your MSSV and class name before continuing to the dashboard.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
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
                    <label for="student_code" class="mb-2 block text-sm font-medium text-slate-700">MSSV (Student Code)</label>
                    <input
                        id="student_code"
                        name="student_code"
                        type="text"
                        value="{{ old('student_code') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                        placeholder="e.g. 22123456"
                    >
                </div>

                <div>
                    <label for="class_name" class="mb-2 block text-sm font-medium text-slate-700">Class Name</label>
                    <input
                        id="class_name"
                        name="class_name"
                        type="text"
                        value="{{ old('class_name') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                        placeholder="e.g. D21CQCN01-N"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-300"
                >
                    Save and Continue
                </button>
            </form>
        </div>
    </div>
</body>
</html>
