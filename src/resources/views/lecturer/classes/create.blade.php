<x-app-layout>
    @section('title', 'Tạo lớp học phần — EMS')
    @section('page-title', 'Tạo lớp học phần')

    <div class="max-w-xl space-y-6">

        <div>
            <a href="{{ route('lecturer.classes.index') }}"
               class="inline-flex items-center gap-1.5 text-[13px] font-medium text-text-muted hover:text-navy-900 mb-4 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Quay lại danh sách lớp
            </a>
            <h2 class="text-[24px] font-bold text-navy-900 leading-tight">Tạo lớp học phần mới</h2>
            <p class="text-[13px] font-medium text-text-muted mt-1">
                Sau khi tạo, mã tham gia sẽ được tự động sinh. Chia sẻ mã này cho sinh viên để họ tham gia.
            </p>
        </div>

        @if(session('error'))
            <div class="p-4 bg-red-50 border-[0.5px] border-red-200 rounded-[6px] font-medium text-red-800 text-[13px]">{{ session('error') }}</div>
        @endif

        <x-card padding="true">
            <form method="POST" action="{{ route('lecturer.classes.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="name">
                        Tên lớp học phần <span class="text-red-500">*</span>
                    </label>
                    <x-text-input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="VD: Lập trình Java — Nhóm 1" class="{{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}" />
                    @error('name')
                        <p class="mt-1.5 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="code">
                        Mã lớp (nội bộ) <span class="text-red-500">*</span>
                    </label>
                    <x-text-input id="code" name="code" type="text" value="{{ old('code') }}" required placeholder="VD: CS101-01-HK1-2526" class="uppercase {{ $errors->has('code') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}" />
                    <p class="mt-1.5 text-[11px] font-medium text-text-muted">Mã duy nhất, dùng để phân biệt lớp trong hệ thống.</p>
                    @error('code')
                        <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="max_students">
                        Sĩ số tối đa
                    </label>
                    <x-text-input id="max_students" name="max_students" type="number" value="{{ old('max_students', 100) }}" min="1" max="500" class="{{ $errors->has('max_students') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}" />
                    @error('max_students')
                        <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 flex gap-3">
                    <x-button type="submit" variant="primary">
                        Tạo lớp
                    </x-button>
                    <x-button variant="outline" href="{{ route('lecturer.classes.index') }}">
                        Huỷ
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
