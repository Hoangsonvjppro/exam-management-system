<x-app-layout>
    @section('title', 'Sửa đề thi - ' . $exam->title)
    @section('page-title', 'Sửa đề thi')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold">Sửa Đề Thi</h2>
                    @if(! $exam->canEditStructure())
                    <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border-[0.5px] border-amber-300">
                        ⚠️ Đã có SV thi — chỉ sửa được tên, mô tả, cấu hình
                    </span>
                    @endif
                </div>
                <p class="text-text-muted mb-6">Lớp học: {{ $courseSection->name ?? 'Lớp đang chọn' }}</p>

                <form method="POST" action="{{ route('lecturer.exams.update', $exam->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-input-label for="title" value="Tên bài kiểm tra" />
                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" value="{{ old('title', $exam->title) }}" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Mô tả / Hướng dẫn làm bài" />
                        <textarea id="description" name="description" class="border-border-clean focus:border-navy-600 focus:ring-blue-200 rounded-[6px] shadow-sm block mt-1 w-full" rows="3">{{ old('description', $exam->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-4 {{ $exam->canEditStructure() ? '' : 'opacity-50 pointer-events-none' }}">
                        <x-input-label for="duration_minutes" value="Thời gian làm bài (Phút)" />
                        <x-text-input id="duration_minutes" class="block mt-1 w-full" type="number" name="duration_minutes"
                            value="{{ old('duration_minutes', $exam->duration_minutes) }}" required />
                        <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                    </div>

                    <div class="mb-4 {{ $exam->canEditStructure() ? '' : 'opacity-50 pointer-events-none' }}">
                        <x-input-label for="exam_type" value="Loại đề thi" />
                        <select id="exam_type" name="exam_type" class="border-border-clean focus:border-navy-600 focus:ring-blue-200 rounded-[6px] shadow-sm block mt-1 w-full" required>
                            <option value="official" {{ old('exam_type', $exam->exam_type) === 'official' ? 'selected' : '' }}>Chính thức (Chỉ thi 1 lần)</option>
                            <option value="practice" {{ old('exam_type', $exam->exam_type) === 'practice' ? 'selected' : '' }}>Luyện tập (Cho phép thi lại nhiều lần)</option>
                        </select>
                        <x-input-error :messages="$errors->get('exam_type')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 {{ $exam->canEditStructure() ? '' : 'opacity-50 pointer-events-none' }}">
                        <div>
                            <x-input-label for="start_time" value="Thời gian Mở đề (Tuỳ chọn)" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="datetime-local" name="start_time"
                                value="{{ old('start_time', $exam->start_time?->format('Y-m-d\TH:i')) }}" />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_time" value="Thời gian Đóng đề (Tuỳ chọn)" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="datetime-local" name="end_time"
                                value="{{ old('end_time', $exam->end_time?->format('Y-m-d\TH:i')) }}" />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-6 p-4 bg-surface-1 rounded-[10px] border border-border-clean">
                        <p class="text-sm font-semibold text-text-muted mb-3">Cấu hình hiển thị kết quả cho sinh viên</p>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="show_score_after_submit" value="0">
                                <input type="checkbox" name="show_score_after_submit" value="1"
                                    class="rounded border-border-clean text-navy-600 shadow-sm focus:ring-blue-200 h-4 w-4"
                                    {{ old('show_score_after_submit', $exam->show_score_after_submit) ? 'checked' : '' }}>
                                <div>
                                    <span class="text-sm font-medium text-text-muted">Cho phép xem điểm tổng</span>
                                    <p class="text-xs text-text-muted">Sinh viên sẽ thấy điểm số và trạng thái đạt/không đạt sau khi nộp bài</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="show_answers_after_submit" value="0">
                                <input type="checkbox" name="show_answers_after_submit" value="1"
                                    class="rounded border-border-clean text-navy-600 shadow-sm focus:ring-blue-200 h-4 w-4"
                                    {{ old('show_answers_after_submit', $exam->show_answers_after_submit) ? 'checked' : '' }}>
                                <div>
                                    <span class="text-sm font-medium text-text-muted">Cho phép xem chi tiết đáp án</span>
                                    <p class="text-xs text-text-muted">Sinh viên sẽ thấy đáp án đúng/sai của từng câu hỏi</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <a href="{{ route('lecturer.exams.show', $exam->id) }}" class="text-sm text-text-muted hover:underline">← Quay lại</a>
                        <x-primary-button class="ml-4">
                            Lưu thay đổi
                        </x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
