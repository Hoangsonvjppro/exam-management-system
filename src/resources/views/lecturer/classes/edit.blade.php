<x-app-layout>
    @section('title', 'Chỉnh sửa lớp — EMS')
    @section('page-title', 'Chỉnh sửa lớp học phần')

    <div class="max-w-xl space-y-6">

        <div>
            <a href="{{ route('lecturer.classes.show', $section) }}"
               class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 mb-4 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Quay lại chi tiết lớp
            </a>
            <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Chỉnh sửa: {{ $section->name ?? $section->code }}</h2>
        </div>


        <x-card padding="true">
            <form method="POST" action="{{ route('lecturer.classes.update', $section) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="name">
                        Tên lớp học phần <span class="text-red-500">*</span>
                    </label>
                    <x-text-input id="name" name="name" type="text" value="{{ old('name', $section->name) }}" required class="{{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}" />
                    @error('name')
                        <p class="mt-1.5 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="code">
                        Mã lớp (nội bộ)
                    </label>
                    <x-text-input id="code" name="code" type="text" value="{{ $section->code }}" readonly class="bg-gray-50 cursor-not-allowed uppercase text-gray-500" />
                    <p class="mt-1.5 text-[11px] font-medium text-text-muted">Mã duy nhất tự sinh, không thể thay đổi.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="max_students">Sĩ số tối đa</label>
                    <x-text-input id="max_students" name="max_students" type="number" value="{{ old('max_students', $section->max_students) }}" min="1" max="500" class="{{ $errors->has('max_students') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}" />
                    @error('max_students')
                        <p class="mt-1.5 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="status">Trạng thái</label>
                    <select id="status" name="status"
                            class="w-full px-4 py-2 border-[1.5px] border-border-clean rounded-[6px] text-navy-900 font-medium text-[14px] bg-white hover:border-blue-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100/50 transition-all outline-none">
                        <option value="active"    @selected(old('status', $section->status) === 'active')>Đang mở</option>
                        <option value="archived"  @selected(old('status', $section->status) === 'archived')>Lưu trữ</option>
                        <option value="cancelled" @selected(old('status', $section->status) === 'cancelled')>Đã huỷ</option>
                    </select>
                </div>

                <div class="pt-4 flex gap-3">
                    <x-button type="submit" variant="primary">
                        Lưu thay đổi
                    </x-button>
                    <x-button variant="outline" href="{{ route('lecturer.classes.show', $section) }}">
                        Huỷ
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
