<x-app-layout>
    @section('title', 'Tạo lớp học phần — EMS')
    @section('page-title', 'Tạo lớp học phần')

    <div class="max-w-xl space-y-6">

        <div>
            <a href="{{ route('lecturer.classes.index') }}"
               class="inline-flex items-center gap-1 text-sm font-bold text-slate-500 hover:text-ems-primary mb-4">
                ← Quay lại danh sách lớp
            </a>
            <h2 class="text-2xl font-black uppercase">Tạo lớp học phần mới</h2>
            <p class="text-sm text-slate-500 font-semibold mt-1">
                Sau khi tạo, mã tham gia sẽ được tự động sinh. Chia sẻ mã này cho sinh viên để họ tham gia.
            </p>
        </div>

        @if(session('error'))
            <div class="p-4 bg-red-100 brutal-border font-semibold text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('lecturer.classes.store') }}" class="bg-white brutal-border brutal-shadow p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="name">
                    Tên lớp học phần <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       placeholder="VD: Lập trình Java — Nhóm 1"
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold focus:ring-0 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="code">
                    Mã lớp (nội bộ) <span class="text-red-500">*</span>
                </label>
                <input id="code" name="code" type="text" value="{{ old('code') }}" required
                       placeholder="VD: CS101-01-HK1-2526"
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold uppercase tracking-widest focus:ring-0 @error('code') border-red-500 @enderror">
                <p class="mt-1 text-xs text-slate-500 font-semibold">Mã duy nhất, dùng để phân biệt lớp trong hệ thống.</p>
                @error('code')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="max_students">
                    Sĩ số tối đa
                </label>
                <input id="max_students" name="max_students" type="number" value="{{ old('max_students', 100) }}"
                       min="1" max="500"
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold focus:ring-0">
                @error('max_students')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="h-12 px-8 bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-widest brutal-btn">
                    Tạo lớp
                </button>
                <a href="{{ route('lecturer.classes.index') }}"
                   class="h-12 px-6 flex items-center bg-white brutal-border font-black uppercase tracking-wide brutal-btn text-sm">
                    Huỷ
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
