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
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="subject_id">
                        Môn học <span class="text-red-500">*</span>
                    </label>
                    <select id="subject_id" name="subject_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $errors->has('subject_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}">
                        <option value="">-- Chọn môn học --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->code }} - {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1.5 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-navy-900 mb-1.5" for="semester_id">
                        Học kỳ <span class="text-red-500">*</span>
                    </label>
                    <select id="semester_id" name="semester_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $errors->has('semester_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-100/50' : '' }}">
                        <option value="">-- Chọn học kỳ --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <p class="mt-1.5 text-[11px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <p class="mt-1.5 text-[12px] font-medium text-text-muted">Mã lớp (nội bộ) sẽ được tự sinh theo môn học, nhóm, học kỳ và năm học.</p>
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
