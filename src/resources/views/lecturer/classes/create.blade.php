<x-app-layout>
    @section('title', 'Tạo lớp học phần — EMS')
    @section('page-title', 'Tạo lớp học phần')

    <div class="max-w-2xl space-y-6">

        {{-- Validation Errors --}}
        @if ($errors->any())
        <div class="rounded-[8px] border border-red-200 bg-red-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[13px] font-semibold text-red-700">Vui lòng kiểm tra lại thông tin</span>
            </div>
            <ul class="list-disc list-inside text-[12px] text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <x-card padding="true" variant="featured">
            <div class="mb-6">
                <h2 class="text-[22px] font-bold text-navy-900 leading-tight">Khởi tạo lớp học phần mới</h2>
                <p class="text-[13px] text-text-muted mt-2 leading-relaxed">
                    Điền đầy đủ các thông tin bên dưới để tạo một lớp học phần. Sau khi tạo, bạn có thể quản lý sinh viên, lịch thi và điểm số trong Class Workspace.
                </p>
            </div>

            <form action="{{ route('lecturer.classes.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Tên lớp học phần --}}
                <div class="flex flex-col gap-1.5">
                    <label for="class-name" class="text-[12px] font-medium text-navy-900">
                        Tên lớp học phần <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="class-name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="VD: Toán cao cấp — Nhóm 3"
                        required
                        class="w-full border-[1.5px] border-border-clean rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white placeholder:text-text-muted/50 focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors"
                    >
                    <p class="text-[11px] text-text-muted">Đặt tên rõ ràng giúp sinh viên dễ nhận diện lớp.</p>
                </div>

                {{-- Môn học --}}
                <div class="flex flex-col gap-1.5">
                    <label for="subject-select" class="text-[12px] font-medium text-navy-900">
                        Môn học <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="subject-select"
                        name="subject_id"
                        required
                        class="w-full border-[1.5px] border-border-clean rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors"
                    >
                        <option value="" disabled {{ old('subject_id') ? '' : 'selected' }}>— Chọn môn học —</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->code }} — {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Học kỳ --}}
                <div class="flex flex-col gap-1.5">
                    <label for="semester-select" class="text-[12px] font-medium text-navy-900">
                        Học kỳ <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="semester-select"
                        name="semester_id"
                        required
                        class="w-full border-[1.5px] border-border-clean rounded-[6px] px-3 py-2.5 text-[13px] text-navy-900 bg-white focus:border-primary-500 focus:ring-2 focus:ring-blue-50 outline-none transition-colors"
                    >
                        <option value="" disabled {{ old('semester_id') ? '' : 'selected' }}>— Chọn học kỳ —</option>
                        @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                            {{ $semester->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t border-border-clean/50">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[6px] bg-primary-500 hover:bg-primary-600 text-white text-[13px] font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Khởi tạo Lớp học
                    </button>
                    <a
                        href="{{ route('lecturer.classes.index') }}"
                        class="inline-flex items-center px-4 py-2.5 rounded-[6px] border border-border-clean text-[13px] font-medium text-navy-900 hover:bg-surface-1 transition-colors"
                    >
                        Huỷ bỏ
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>