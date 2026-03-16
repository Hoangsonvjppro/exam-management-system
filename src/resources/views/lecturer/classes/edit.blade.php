<x-app-layout>
    @section('title', 'Chỉnh sửa lớp — EMS')
    @section('page-title', 'Chỉnh sửa lớp học phần')

    <div class="max-w-xl space-y-6">

        <div>
            <a href="{{ route('lecturer.classes.show', $section) }}"
               class="inline-flex items-center gap-1 text-sm font-bold text-slate-500 hover:text-ems-primary mb-4">
                ← Quay lại chi tiết lớp
            </a>
            <h2 class="text-2xl font-black uppercase">Chỉnh sửa: {{ $section->name ?? $section->code }}</h2>
        </div>

        @if(session('error'))
            <div class="p-4 bg-red-100 brutal-border font-semibold text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('lecturer.classes.update', $section) }}"
              class="bg-white brutal-border brutal-shadow p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="name">
                    Tên lớp học phần <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text" value="{{ old('name', $section->name) }}" required
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold focus:ring-0 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="code">
                    Mã lớp (nội bộ) <span class="text-red-500">*</span>
                </label>
                <input id="code" name="code" type="text" value="{{ old('code', $section->code) }}" required
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold uppercase tracking-widest focus:ring-0 @error('code') border-red-500 @enderror">
                @error('code')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="max_students">Sĩ số tối đa</label>
                <input id="max_students" name="max_students" type="number"
                       value="{{ old('max_students', $section->max_students) }}"
                       min="1" max="500"
                       class="w-full h-12 px-4 brutal-border bg-white font-semibold focus:ring-0">
                @error('max_students')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-black uppercase mb-2 tracking-wide" for="status">Trạng thái</label>
                <select id="status" name="status"
                        class="w-full h-12 px-4 brutal-border bg-white font-semibold focus:ring-0">
                    <option value="active"    @selected(old('status', $section->status) === 'active')>Đang mở</option>
                    <option value="archived"  @selected(old('status', $section->status) === 'archived')>Lưu trữ</option>
                    <option value="cancelled" @selected(old('status', $section->status) === 'cancelled')>Đã huỷ</option>
                </select>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="h-12 px-8 bg-ems-primary text-white brutal-border brutal-shadow font-black uppercase tracking-widest brutal-btn">
                    Lưu thay đổi
                </button>
                <a href="{{ route('lecturer.classes.show', $section) }}"
                   class="h-12 px-6 flex items-center bg-white brutal-border font-black uppercase tracking-wide brutal-btn text-sm">
                    Huỷ
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
