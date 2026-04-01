<x-app-layout>
    @php
    $quickSubjectIds = $courseSections->pluck('subject_id')->filter()->unique()->values();
    $quickSubjects = \App\Models\Subject::query()
    ->whereIn('id', $quickSubjectIds)
    ->orderBy('name')
    ->get(['id', 'name', 'code']);

    $quickChaptersBySubject = \App\Models\Chapter::query()
    ->whereIn('subject_id', $quickSubjectIds)
    ->orderBy('order')
    ->get(['id', 'subject_id', 'name'])
    ->groupBy('subject_id')
    ->map(fn($items) => $items->map(fn($chapter) => [
    'id' => (int) $chapter->id,
    'name' => $chapter->name,
    ])->values())
    ->toArray();

    $quickDifficulties = \App\Models\Difficulty::query()
    ->orderedForQuestionBank()
    ->get(['code', 'name'])
    ->map(fn($difficulty) => [
    'code' => $difficulty->code,
    'name' => $difficulty->name,
    ])
    ->values()
    ->toArray();

    $quickQuestionTypes = \App\Models\QuestionType::query()
    ->active()
    ->orderedForQuestionBank()
    ->get(['id', 'name', 'code'])
    ->map(fn($type) => [
    'id' => (int) $type->id,
    'name' => $type->name,
    'code' => $type->code,
    ])
    ->values()
    ->toArray();

    $preSelectedSubjectId = (string) ($preSelectedSection?->subject_id ?? '');
    @endphp

    <div class="py-8 bg-[#F8FAFD] min-h-screen"
        data-pre-selected-subject-id="{{ $preSelectedSubjectId }}"
        x-data="scheduleCreateManager($el.dataset.preSelectedSubjectId)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Tạo Lịch Thi Mới</h2>
                    <p class="text-[13px] text-[#6B7C99]">
                        @if($preSelectedSection)
                        Thiết lập ca thi cho lớp <strong>{{ $preSelectedSection->name }}</strong>.
                        @else
                        Thiết lập ca thi cho một hoặc nhiều lớp học phần.
                        @endif
                    </p>
                </div>
                <a href="{{ $preSelectedSection ? route('lecturer.classes.show', $preSelectedSection) : route('lecturer.schedules.index') }}" class="inline-flex items-center gap-2 text-[13px] text-[#185FA5] hover:underline">← Quay lại</a>
            </div>

            <div class="bg-white rounded-[10px] border border-[#D6E2F0] p-6 max-w-3xl shadow-sm">
                <form method="POST" action="{{ route('lecturer.schedules.store') }}" id="schedule-form">
                    @csrf

                    <div class="space-y-6">
                        @if($preSelectedSection)
                        <div class="p-4 bg-[#F0F7FF] border border-[#D1E5FF] rounded-lg flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-[#185FA5] uppercase tracking-wider">Đang lên lịch cho lớp</p>
                                <p class="text-[15px] font-bold text-[#1A3A6B] mt-0.5">{{ $preSelectedSection->name }} ({{ $preSelectedSection->code }})</p>
                            </div>
                            <input type="hidden" name="course_section_ids[]" value="{{ $preSelectedSection->id }}">
                            <div class="text-[12px] text-[#6B7C99] italic">Môn học: {{ $preSelectedSection->subject->name }}</div>
                        </div>
                        @endif

                        @if(!$preSelectedSection)
                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-2">Bước 1: Chọn môn học <span class="text-[#DC2626]">*</span></label>
                            <select x-model="selectedSubjectId" @change="onSubjectChange()" required
                                class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                <option value="">-- Chọn môn học --</option>
                                @foreach($quickSubjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="selectedSubjectId" x-transition>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-3">Bước 2: Áp dụng cho các lớp học phần <span class="text-[#DC2626]">*</span></label>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[300px] overflow-y-auto p-4 border border-[#D6E2F0] rounded-lg bg-[#F9FBFF]" id="sections-list">
                                @foreach($courseSections as $cs)
                                <div class="section-item flex items-start gap-3 p-2 hover:bg-white rounded transition-colors border border-transparent hover:border-[#D6E2F0]"
                                    data-subject-id="{{ $cs->subject_id }}"
                                    x-show="'{{ $cs->subject_id }}' == selectedSubjectId">
                                    <input type="checkbox"
                                        name="course_section_ids[]"
                                        id="cs-{{ $cs->id }}"
                                        value="{{ $cs->id }}"
                                        @change="onSectionChange()"
                                        class="mt-0.5 rounded border-[#D6E2F0] text-[#1A3A6B] focus:ring-[#185FA5]"
                                        {{ is_array(old('course_section_ids')) && in_array($cs->id, old('course_section_ids')) ? 'checked' : '' }}>
                                    <label for="cs-{{ $cs->id }}" class="cursor-pointer">
                                        <p class="text-[13px] font-semibold text-[#1A3A6B] leading-tight">{{ $cs->name }}</p>
                                        <p class="text-[11px] text-[#6B7C99] mt-0.5">{{ $cs->code }}</p>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <p id="no-sections-msg" class="hidden text-[13px] text-[#DC2626] italic py-4">Không tìm thấy lớp học phần nào đang dạy môn này.</p>
                            @error('course_section_ids') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>
                        @else
                        {{-- Trạng thái đã chọn lớp từ trước (ví dụ từ trang chi tiết lớp) --}}
                        <div x-init="hasSelectedSection = true; selectedSubjectId = '{{ $preSelectedSubjectId }}'"></div>
                        @endif

                        <div x-show="selectedSubjectId && hasSelectedSection" x-transition>
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <label class="text-[12px] font-semibold text-[#1A3A6B]">Bước {{ $preSelectedSection ? '2' : '3' }}: Chọn đề thi <span class="text-[#DC2626]">*</span></label>
                                <button type="button" class="text-[12px] font-semibold text-[#185FA5] hover:underline" @click="$dispatch('open-modal', 'quick-create-exam-modal')">
                                    + Tạo đề thi mới
                                </button>
                            </div>
                            <select name="exam_id" id="exam_id" x-model="selectedExamId" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                <option value="">-- Chọn đề thi môn hiện tại --</option>
                                @foreach($exams as $ex)
                                <option value="{{ $ex->id }}"
                                    data-subject-id="{{ $ex->subject_id }}"
                                    data-preview-url="{{ route('lecturer.exams.quick-preview', $ex) }}"
                                    data-quick-update-url="{{ route('lecturer.exams.quick-update', $ex) }}"
                                    data-edit-url="{{ route('lecturer.exams.edit', $ex) }}"
                                    x-show="'{{ $ex->subject_id }}' == selectedSubjectId"
                                    {{ old('exam_id') == $ex->id ? 'selected' : '' }}>
                                    [{{ $ex->subject->code }}] {{ $ex->title }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-[#6B7C99] mt-1">Danh sách được lọc theo môn học bạn đã chọn.</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    @click="openExamPreviewModal()"
                                    :disabled="!selectedExamId"
                                    class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-[#BFD4EA] text-[#1A3A6B] hover:bg-[#F3F8FD] disabled:opacity-50 disabled:cursor-not-allowed">
                                    Xem chi tiết đề
                                </button>
                                <button
                                    type="button"
                                    @click="openExamEditModal()"
                                    :disabled="!selectedExamId"
                                    class="px-3 py-1.5 rounded-lg text-[12px] font-semibold border border-[#185FA5] text-[#185FA5] hover:bg-[#EAF4FD] disabled:opacity-50 disabled:cursor-not-allowed">
                                    Sửa nhanh đề
                                </button>
                                <a
                                    x-show="selectedExamId"
                                    :href="selectedExamEditUrl()"
                                    class="text-[12px] font-semibold text-[#185FA5] hover:underline">
                                    Mở trang sửa đầy đủ
                                </a>
                            </div>
                            @error('exam_id') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>

                        <div id="main-form-fields" x-show="selectedSubjectId && hasSelectedSection && selectedExamId" x-transition class="space-y-4 transition-all duration-300">
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1 uppercase tracking-wider">Bước {{ $preSelectedSection ? '3' : '4' }}: Chi tiết ca thi</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày thi <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="exam_date" value="{{ old('exam_date') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                    @error('exam_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Số SV tối đa mỗi lớp</label>
                                    <input type="number" name="max_students" value="{{ old('max_students') }}" min="1" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]" placeholder="Để trống = không giới hạn">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ bắt đầu <span class="text-[#DC2626]">*</span></label>
                                    <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('start_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ kết thúc <span class="text-[#DC2626]">*</span></label>
                                    <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('end_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ghi chú</label>
                                <textarea name="notes" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]" rows="3" placeholder="Lưu ý cho các ca thi...">{{ old('notes') }}</textarea>
                            </div>

                            <div class="mt-4 p-4 border border-indigo-100 rounded-lg bg-indigo-50/50">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="link_grade_column" value="1" class="mt-0.5 rounded border-indigo-300 text-[#185FA5] focus:ring-[#185FA5]" checked>
                                    <div>
                                        <p class="text-[13px] font-bold text-[#1A3A6B]">Tự động thêm vào bảng Điểm quá trình</p>
                                        <p class="text-[11px] text-[#6B7C99] mt-0.5">Hệ thống sẽ tạo tự động một cột Điểm Bài Thi trong bảng điểm của (các) lớp học phần được chọn.</p>
                                    </div>
                                </label>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="bg-[#1A3A6B] text-white px-6 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-[#0F2A53] transition-colors">
                                    Tạo lịch thi
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <x-modal name="quick-create-exam-modal" maxWidth="2xl">
            <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0">
                <h3 class="text-[17px] font-bold text-navy-900">Tạo đề thi nhanh</h3>
                <button @click="$dispatch('close-modal', 'quick-create-exam-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[80vh]">
                <form @submit.prevent="submitQuickExamForm($el)" class="space-y-5">
                    @csrf

                    <input type="hidden" name="creation_mode" value="manual">
                    <input type="hidden" name="exam_type" value="official">
                    <input type="hidden" name="allow_late_entrance" value="1">
                    <input type="hidden" name="late_entrance_limit_minutes" value="15">
                    <input type="hidden" name="late_entrance_behavior" value="fixed_end">
                    <input type="hidden" name="min_duration_before_submit" value="0">
                    <input type="hidden" name="show_score_after_submit" value="1">
                    <input type="hidden" name="show_answers_after_submit" value="0">
                    <input type="hidden" name="multiple_choice_scoring_method" value="all_or_nothing">
                    <div id="quick-selected-questions-container"></div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Tên đề thi <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="VD: Thi giữa kỳ lớp K22">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Môn học <span class="text-red-500">*</span></label>
                            <select name="subject_id" x-model="quickSubjectId" @change="onQuickSubjectChange()" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                                <option value="">-- Chọn môn học --</option>
                                @foreach($quickSubjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Thời lượng (phút) <span class="text-red-500">*</span></label>
                            <input type="number" name="duration_minutes" min="1" value="45" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Mô tả</label>
                        <input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Ghi chú ngắn cho đề thi">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[12px] font-semibold text-navy-900">Chọn câu hỏi <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-[12px] font-semibold text-emerald-700 hover:text-emerald-800" @click="openQuickQuestionCreateModal()">+ Tạo câu hỏi nhanh</button>
                                <a href="{{ route('lecturer.exams.create') }}" class="text-[12px] font-semibold text-[#185FA5] hover:underline">Mở trình tạo đầy đủ</a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            <input type="text" x-model="quickQuestionKeyword" @input="debouncedQuickQuestionSearch()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] md:col-span-2" placeholder="Tìm theo nội dung câu hỏi...">
                            <select x-model="quickQuestionChapterId" @change="loadQuickQuestions({ page: 1 })" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                                <option value="">Tất cả chương</option>
                                <template x-for="chapter in quickChapterOptions()" :key="chapter.id">
                                    <option :value="String(chapter.id)" x-text="chapter.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex items-center justify-between mb-2 gap-3">
                            <select x-model="quickQuestionDifficulty" @change="loadQuickQuestions({ page: 1 })" class="w-full md:w-[220px] border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                                <option value="">Tất cả độ khó</option>
                                <template x-for="difficulty in quickDifficultyOptions" :key="difficulty.code">
                                    <option :value="difficulty.code" x-text="difficulty.name"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-text-muted whitespace-nowrap" x-text="`Đã chọn ${quickQuestionSelectedIds.length} câu`"></p>
                        </div>

                        <div class="max-h-[320px] overflow-y-auto border border-gray-200 rounded-lg bg-surface-0 divide-y divide-border-clean/70 px-2 mt-1">
                            <div x-show="quickQuestionLoading" class="p-5 text-center text-[12px] text-text-muted">Đang tải câu hỏi...</div>

                            <template x-if="!quickQuestionLoading && !quickSubjectId">
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-[12px] text-blue-800 m-2">
                                    Chọn môn học trước để tải câu hỏi phù hợp.
                                </div>
                            </template>

                            <template x-if="!quickQuestionLoading && quickSubjectId && quickQuestions.length === 0">
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-[12px] text-amber-800 m-2">
                                    Không có câu hỏi phù hợp với bộ lọc hiện tại.
                                </div>
                            </template>

                            <template x-for="question in quickQuestions" :key="question.id">
                                <div class="p-3 rounded-md transition-colors" :class="isQuickQuestionSelected(question.id) ? 'bg-blue-50/60' : 'hover:bg-white'">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-navy-900 focus:ring-indigo-500" :checked="isQuickQuestionSelected(question.id)" @change="toggleQuickQuestionSelection(question.id, $event.target.checked)">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-show="question.chapter" x-text="question.chapter?.name || ''"></span>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-text="difficultyLabel(question.difficulty)"></span>
                                                <span class="text-[10px] text-text-muted" x-text="`ID: ${question.id}`"></span>
                                            </div>
                                            <p class="text-[12px] text-navy-900 leading-relaxed" x-text="stripHtml(question.content)"></p>
                                            <button type="button" class="mt-2 text-[11px] font-semibold text-blue-700 hover:text-blue-800" @click="toggleQuickQuestionPreview(question.id)">
                                                <span x-text="isQuickQuestionPreviewOpen(question.id) ? 'Ẩn đáp án chi tiết' : 'Xem đáp án chi tiết'"></span>
                                            </button>

                                            <div class="mt-2 space-y-1" x-show="isQuickQuestionPreviewOpen(question.id)">
                                                <template x-if="(question.options || []).length === 0">
                                                    <p class="text-[11px] text-text-muted italic">Câu hỏi chưa có phương án trả lời.</p>
                                                </template>
                                                <template x-for="option in (question.options || [])" :key="`${question.id}-${option.label}`">
                                                    <div class="text-[11px] rounded-md border px-2 py-1 flex items-start gap-2" :class="option.is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-50 text-slate-700'">
                                                        <span class="font-semibold" x-text="`${option.label}.`"></span>
                                                        <span class="flex-1" x-text="option.content"></span>
                                                        <span x-show="option.is_correct" class="font-semibold text-[10px]">Đúng</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div class="p-2" x-show="quickQuestionHasMore()">
                                <button type="button" class="w-full text-[12px] font-semibold text-blue-700 border border-blue-200 rounded-md py-2 hover:bg-blue-50" :disabled="quickQuestionLoading" @click="loadQuickQuestions({ page: quickQuestionPage + 1, append: true })">
                                    Tải thêm câu hỏi
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 text-[11px] text-text-muted" x-show="quickQuestionTotal > 0" x-text="`Hiển thị ${quickQuestions.length}/${quickQuestionTotal} câu hỏi`"></div>
                        <div class="mt-2 text-[11px] text-red-600" x-show="quickQuestionFormError" x-text="quickQuestionFormError"></div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'quick-create-exam-modal')">Huỷ</x-button>
                        <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingQuickExam || quickQuestionSelectedIds.length === 0">
                            <span x-show="!isSubmittingQuickExam">Tạo đề và tự chọn</span>
                            <span x-show="isSubmittingQuickExam" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Đang tạo...
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal>

        <x-modal name="quick-question-modal" maxWidth="xl">
            <div class="p-6 md:p-7 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[17px] font-bold text-navy-900">Tạo câu hỏi nhanh</h3>
                        <p class="text-[12px] text-text-muted mt-1">Câu hỏi mới sẽ tự động xuất hiện trong danh sách chọn.</p>
                    </div>
                    <button type="button" class="text-text-muted hover:text-navy-900" @click="$dispatch('close-modal', 'quick-question-modal')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Môn học</label>
                        <select x-model="quickQuestionCreator.subject_id" @change="syncQuickQuestionCreatorChapterOptions()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" required>
                            <option value="">-- Chọn môn học --</option>
                            @foreach($quickSubjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Chương</label>
                        <select x-model="quickQuestionCreator.chapter_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                            <option value="">-- Tất cả chương --</option>
                            <template x-for="chapter in quickQuestionCreatorChapterOptions" :key="chapter.id">
                                <option :value="String(chapter.id)" x-text="chapter.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Độ khó</label>
                        <select x-model="quickQuestionCreator.difficulty" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" required>
                            <template x-for="difficulty in quickDifficultyOptions" :key="difficulty.code">
                                <option :value="difficulty.code" x-text="difficulty.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1">Loại câu hỏi</label>
                    <select x-model="quickQuestionCreator.question_type_id" @change="onQuickQuestionTypeChanged()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" required>
                        <option value="">-- Chọn loại câu hỏi --</option>
                        <template x-for="questionType in quickQuestionTypes" :key="questionType.id">
                            <option :value="String(questionType.id)" x-text="questionType.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-[11px] text-text-muted" x-text="quickQuestionCorrectHint()"></p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1">Nội dung câu hỏi</label>
                    <textarea x-model="quickQuestionCreator.content" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Nhập nội dung câu hỏi..."></textarea>
                </div>

                <div class="space-y-2 border border-border-clean rounded-lg p-3 bg-surface-0">
                    <div class="flex items-center justify-between">
                        <label class="text-[12px] font-semibold text-navy-900">Phương án trả lời</label>
                        <button type="button" class="text-[12px] font-semibold text-blue-700 hover:text-blue-800" @click="addQuickQuestionCreatorOption()">+ Thêm phương án</button>
                    </div>
                    <template x-for="(option, index) in quickQuestionCreator.options" :key="index">
                        <div class="flex items-start gap-2">
                            <input
                                :type="quickQuestionCreatorUsesCheckbox() ? 'checkbox' : 'radio'"
                                :name="quickQuestionCreatorUsesCheckbox() ? `quick-correct-${index}` : 'quick-correct-one'"
                                class="mt-2 rounded border-gray-300 text-navy-900 focus:ring-indigo-500"
                                :checked="quickQuestionCreator.correct_options.includes(index)"
                                @change="toggleQuickQuestionCreatorCorrect(index, $event.target.checked)">
                            <input type="text" x-model="quickQuestionCreator.options[index]" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-[13px]" :placeholder="`Phương án ${String.fromCharCode(65 + index)}`">
                            <button type="button" class="text-red-600 text-[18px] leading-none px-2 py-1" @click="removeQuickQuestionCreatorOption(index)">&times;</button>
                        </div>
                    </template>
                </div>

                <div class="text-[12px] text-red-600" x-show="quickQuestionCreatorError" x-text="quickQuestionCreatorError"></div>

                <div class="flex justify-end gap-3 pt-3 border-t border-border-clean">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'quick-question-modal')">Huỷ</x-button>
                    <x-button type="button" variant="primary" x-bind:disabled="isSubmittingQuickQuestion" @click="submitQuickQuestionCreator()">
                        <span x-show="!isSubmittingQuickQuestion">Lưu và thêm vào danh sách</span>
                        <span x-show="isSubmittingQuickQuestion">Đang lưu...</span>
                    </x-button>
                </div>
            </div>
        </x-modal>

        <x-modal name="exam-preview-modal" maxWidth="3xl">
            <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0">
                <h3 class="text-[17px] font-bold text-navy-900">Chi tiết đề thi</h3>
                <button @click="$dispatch('close-modal', 'exam-preview-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[80vh] space-y-4">
                <div x-show="isLoadingExamPreview" class="py-10 text-center text-[13px] text-[#6B7C99]">
                    Đang tải thông tin đề thi...
                </div>

                <div x-show="!isLoadingExamPreview && examPreviewError" class="p-4 rounded-lg border border-[#FECACA] bg-[#FEF2F2] text-[13px] text-[#991B1B]" x-text="examPreviewError"></div>

                <div x-show="!isLoadingExamPreview && examPreviewData" class="space-y-4">
                    <div>
                        <h4 class="text-[18px] font-bold text-[#1A3A6B]" x-text="examPreviewData?.title || ''"></h4>
                        <p class="text-[12px] text-[#6B7C99] mt-1" x-text="(examPreviewData?.subject?.code || '') + ' - ' + (examPreviewData?.subject?.name || '')"></p>
                        <p class="text-[13px] text-[#334155] mt-2" x-show="examPreviewData?.description" x-text="examPreviewData?.description"></p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="p-3 rounded-lg border border-[#D6E2F0] bg-[#F8FAFD]">
                            <p class="text-[11px] text-[#6B7C99]">Thời lượng</p>
                            <p class="text-[16px] font-bold text-[#1A3A6B]" x-text="(examPreviewData?.duration_minutes || 0) + ' phút'"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-[#D6E2F0] bg-[#F8FAFD]">
                            <p class="text-[11px] text-[#6B7C99]">Số câu hỏi</p>
                            <p class="text-[16px] font-bold text-[#1A3A6B]" x-text="examPreviewData?.question_count || 0"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-[#D6E2F0] bg-[#F8FAFD]">
                            <p class="text-[11px] text-[#6B7C99]">Đã gán lịch</p>
                            <p class="text-[16px] font-bold text-[#1A3A6B]" x-text="examPreviewData?.schedule_count || 0"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-[#D6E2F0] bg-[#F8FAFD]">
                            <p class="text-[11px] text-[#6B7C99]">Lượt làm bài</p>
                            <p class="text-[16px] font-bold text-[#1A3A6B]" x-text="examPreviewData?.attempt_count || 0"></p>
                        </div>
                    </div>

                    <div x-show="examPreviewData && !examPreviewData.can_edit_structure" class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-[12px] text-amber-800">
                        Đề thi đã có sinh viên làm bài, bạn chỉ có thể chỉnh sửa tên và mô tả.
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[12px] font-semibold text-[#1A3A6B] uppercase tracking-wider">Câu hỏi trong đề</p>
                            <p class="text-[11px] text-[#6B7C99]">Hiển thị tối đa 8 câu đầu</p>
                        </div>

                        <div class="space-y-2" x-show="examPreviewData?.questions_preview?.length">
                            <template x-for="question in (examPreviewData?.questions_preview || [])" :key="question.order">
                                <div class="p-3 rounded-lg border border-[#E2E8F0] bg-white">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <p class="text-[12px] font-semibold text-[#1A3A6B]" x-text="'Câu ' + question.order"></p>
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-[#EAF4FD] text-[#185FA5]" x-show="question.difficulty" x-text="difficultyLabel(question.difficulty)"></span>
                                    </div>
                                    <p class="text-[12px] text-[#334155]" x-text="question.content"></p>
                                </div>
                            </template>
                        </div>

                        <p x-show="!(examPreviewData?.questions_preview?.length)" class="text-[12px] text-[#6B7C99] italic py-2">
                            Chưa có dữ liệu câu hỏi cho đề này.
                        </p>

                        <p x-show="(examPreviewData?.question_count || 0) > (examPreviewData?.questions_preview?.length || 0)" class="text-[11px] text-[#6B7C99] mt-2">
                            Còn <span x-text="(examPreviewData?.question_count || 0) - (examPreviewData?.questions_preview?.length || 0)"></span> câu chưa hiển thị trong preview.
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-border-clean flex items-center justify-end gap-2 bg-surface-0">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-[#D6E2F0] text-[12px] font-semibold text-[#1A3A6B] hover:bg-[#F8FAFD]" @click="$dispatch('close-modal', 'exam-preview-modal')">
                    Đóng
                </button>
                <button type="button" class="px-3 py-1.5 rounded-lg border border-[#185FA5] text-[12px] font-semibold text-[#185FA5] hover:bg-[#EAF4FD]" @click="$dispatch('close-modal', 'exam-preview-modal'); openExamEditModal()" :disabled="!selectedExamId">
                    Sửa nhanh
                </button>
                <a :href="selectedExamEditUrl()" x-show="selectedExamId" class="px-3 py-1.5 rounded-lg bg-[#1A3A6B] text-white text-[12px] font-semibold hover:bg-[#0F2A53]">
                    Mở trang sửa đầy đủ
                </a>
            </div>
        </x-modal>

        <x-modal name="quick-edit-exam-modal" maxWidth="2xl">
            <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0">
                <h3 class="text-[17px] font-bold text-navy-900">Sửa nhanh đề thi</h3>
                <button @click="$dispatch('close-modal', 'quick-edit-exam-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form class="p-6 space-y-4" @submit.prevent="submitQuickExamEdit()">
                <div x-show="isLoadingExamPreview" class="py-6 text-center text-[13px] text-[#6B7C99]">
                    Đang tải dữ liệu đề thi...
                </div>

                <div x-show="!isLoadingExamPreview" class="space-y-4">
                    <div x-show="quickExamEditWarning" class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-[12px] text-amber-800" x-text="quickExamEditWarning"></div>
                    <div x-show="quickExamEditError" class="p-3 rounded-lg border border-[#FECACA] bg-[#FEF2F2] text-[12px] text-[#991B1B]" x-text="quickExamEditError"></div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Tên đề thi <span class="text-red-500">*</span></label>
                        <input type="text" x-model="quickExamEditForm.title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" maxlength="255">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Mô tả</label>
                        <textarea x-model="quickExamEditForm.description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Ghi chú ngắn cho đề thi"></textarea>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Thời lượng (phút) <span class="text-red-500">*</span></label>
                        <input type="number" min="1" x-model.number="quickExamEditForm.duration_minutes" :disabled="examPreviewData && !examPreviewData.can_edit_structure" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] disabled:bg-gray-100 disabled:text-gray-500">
                        <p x-show="examPreviewData && !examPreviewData.can_edit_structure" class="text-[11px] text-amber-700 mt-1">
                            Đề đã có sinh viên làm bài nên không thể sửa thời lượng.
                        </p>
                    </div>
                </div>

                <div class="pt-4 border-t border-border-clean flex items-center justify-end gap-2">
                    <button type="button" class="px-3 py-1.5 rounded-lg border border-[#D6E2F0] text-[12px] font-semibold text-[#1A3A6B] hover:bg-[#F8FAFD]" @click="$dispatch('close-modal', 'quick-edit-exam-modal')">
                        Huỷ
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#1A3A6B] text-white text-[12px] font-semibold hover:bg-[#0F2A53] disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isSavingQuickExamEdit || !selectedExamId">
                        <span x-show="!isSavingQuickExamEdit">Lưu thay đổi</span>
                        <span x-show="isSavingQuickExamEdit">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    @php
    $quickExamConfig = [
    'chaptersBySubject' => $quickChaptersBySubject,
    'difficulties' => $quickDifficulties,
    'questionTypes' => $quickQuestionTypes,
    ];
    @endphp
    <script id="quick-exam-config-data" type="application/json">
        @json($quickExamConfig)
    </script>

    <script>
        const quickExamConfig = JSON.parse(document.getElementById('quick-exam-config-data')?.textContent || '{}');

        function scheduleCreateManager(initialSubjectId) {
            return {
                selectedSubjectId: initialSubjectId || '',
                selectedExamId: '',
                hasSelectedSection: false,
                quickSubjectId: initialSubjectId || '',
                quickQuestionApiUrl: "{{ route('lecturer.api.exam-form.questions') }}",
                quickQuestionCreateUrl: "{{ route('lecturer.api.exam-form.quick-question') }}",
                quickChaptersBySubject: quickExamConfig.chaptersBySubject || {},
                quickDifficultyOptions: quickExamConfig.difficulties || [],
                quickQuestionTypes: quickExamConfig.questionTypes || [],
                quickQuestions: [],
                quickQuestionLoading: false,
                quickQuestionPage: 1,
                quickQuestionLastPage: 1,
                quickQuestionTotal: 0,
                quickQuestionKeyword: '',
                quickQuestionChapterId: '',
                quickQuestionDifficulty: '',
                quickQuestionSelectedIds: [],
                quickQuestionExpandedIds: [],
                quickQuestionSearchDebounce: null,
                quickQuestionFormError: '',
                quickQuestionCreatorChapterOptions: [],
                quickQuestionCreatorError: '',
                isSubmittingQuickQuestion: false,
                quickQuestionCreator: {
                    subject_id: initialSubjectId || '',
                    chapter_id: '',
                    difficulty: (quickExamConfig.difficulties || [])[0]?.code || 'remember',
                    question_type_id: '',
                    content: '',
                    options: ['', '', '', ''],
                    correct_options: [0],
                },
                isSubmittingQuickExam: false,
                isLoadingExamPreview: false,
                examPreviewError: '',
                examPreviewData: null,
                isSavingQuickExamEdit: false,
                quickExamEditError: '',
                quickExamEditWarning: '',
                quickExamEditForm: {
                    title: '',
                    description: '',
                    duration_minutes: 45,
                },
                quickPreviewUrlTemplate: "{{ route('lecturer.exams.quick-preview', ['exam' => '__EXAM_ID__']) }}",
                quickUpdateUrlTemplate: "{{ route('lecturer.exams.quick-update', ['exam' => '__EXAM_ID__']) }}",
                examEditUrlTemplate: "{{ route('lecturer.exams.edit', ['exam' => '__EXAM_ID__']) }}",
                csrfToken: "{{ csrf_token() }}",

                buildExamUrl(template, examId) {
                    if (!template || !examId) {
                        return '';
                    }

                    return template.replace('__EXAM_ID__', String(examId));
                },

                selectedExamOption() {
                    const examSelect = document.getElementById('exam_id');
                    if (!examSelect || examSelect.selectedIndex < 0) {
                        return null;
                    }

                    return examSelect.options[examSelect.selectedIndex] || null;
                },

                selectedExamRoutes() {
                    const selectedOption = this.selectedExamOption();
                    const examId = this.selectedExamId || selectedOption?.value || '';

                    if (!examId) {
                        return {
                            previewUrl: '',
                            updateUrl: '',
                            editUrl: '',
                        };
                    }

                    return {
                        previewUrl: selectedOption?.getAttribute('data-preview-url') || this.buildExamUrl(this.quickPreviewUrlTemplate, examId),
                        updateUrl: selectedOption?.getAttribute('data-quick-update-url') || this.buildExamUrl(this.quickUpdateUrlTemplate, examId),
                        editUrl: selectedOption?.getAttribute('data-edit-url') || this.buildExamUrl(this.examEditUrlTemplate, examId),
                    };
                },

                selectedExamEditUrl() {
                    return this.selectedExamRoutes().editUrl || '#';
                },

                difficultyLabel(level) {
                    const labels = {
                        remember: 'Nhận biết',
                        understand: 'Thông hiểu',
                        apply: 'Vận dụng',
                        analyze: 'Phân tích',
                    };

                    return labels[level] || level || '';
                },

                resetExamMetaState() {
                    this.examPreviewError = '';
                    this.quickExamEditError = '';
                    this.quickExamEditWarning = '';
                },

                onSubjectChange() {
                    this.hasSelectedSection = false;
                    this.selectedExamId = '';
                    this.examPreviewData = null;
                    this.resetExamMetaState();
                    document.querySelectorAll('input[name="course_section_ids[]"]').forEach(cb => cb.checked = false);
                    this.quickSubjectId = this.selectedSubjectId;
                    this.onQuickSubjectChange();
                },

                onSectionChange() {
                    const checked = document.querySelectorAll('input[name="course_section_ids[]"]:checked');
                    this.hasSelectedSection = checked.length > 0;
                },

                onQuickSubjectChange() {
                    this.quickQuestionFormError = '';
                    this.quickQuestionKeyword = '';
                    this.quickQuestionChapterId = '';
                    this.quickQuestionDifficulty = '';
                    this.quickQuestions = [];
                    this.quickQuestionPage = 1;
                    this.quickQuestionLastPage = 1;
                    this.quickQuestionTotal = 0;
                    this.quickQuestionSelectedIds = [];
                    this.quickQuestionExpandedIds = [];
                    this.syncQuickQuestionHiddenInputs();

                    this.quickQuestionCreator.subject_id = this.quickSubjectId ? String(this.quickSubjectId) : '';
                    this.syncQuickQuestionCreatorChapterOptions();

                    if (this.quickSubjectId) {
                        this.loadQuickQuestions({
                            page: 1
                        });
                    }
                },

                quickChapterOptions() {
                    if (!this.quickSubjectId) {
                        return [];
                    }

                    return this.quickChaptersBySubject[String(this.quickSubjectId)] || [];
                },

                stripHtml(input) {
                    const temp = document.createElement('div');
                    temp.innerHTML = String(input || '');
                    return (temp.textContent || temp.innerText || '').trim();
                },

                debouncedQuickQuestionSearch() {
                    clearTimeout(this.quickQuestionSearchDebounce);
                    this.quickQuestionSearchDebounce = setTimeout(() => {
                        this.loadQuickQuestions({
                            page: 1
                        });
                    }, 300);
                },

                quickQuestionHasMore() {
                    return this.quickQuestionPage < this.quickQuestionLastPage;
                },

                isQuickQuestionSelected(questionId) {
                    return this.quickQuestionSelectedIds.includes(Number(questionId));
                },

                toggleQuickQuestionSelection(questionId, checked) {
                    const numericId = Number(questionId);
                    const nextIds = new Set(this.quickQuestionSelectedIds);

                    if (checked) {
                        nextIds.add(numericId);
                    } else {
                        nextIds.delete(numericId);
                    }

                    this.quickQuestionSelectedIds = Array.from(nextIds);
                    this.quickQuestionFormError = '';
                    this.syncQuickQuestionHiddenInputs();
                },

                toggleQuickQuestionPreview(questionId) {
                    const numericId = Number(questionId);
                    const next = new Set(this.quickQuestionExpandedIds);

                    if (next.has(numericId)) {
                        next.delete(numericId);
                    } else {
                        next.add(numericId);
                    }

                    this.quickQuestionExpandedIds = Array.from(next);
                },

                isQuickQuestionPreviewOpen(questionId) {
                    return this.quickQuestionExpandedIds.includes(Number(questionId));
                },

                syncQuickQuestionHiddenInputs() {
                    const container = document.getElementById('quick-selected-questions-container');
                    if (!container) {
                        return;
                    }

                    container.innerHTML = '';
                    this.quickQuestionSelectedIds.forEach((questionId) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'question_ids[]';
                        input.value = String(questionId);
                        container.appendChild(input);
                    });
                },

                async loadQuickQuestions({
                    page = 1,
                    append = false
                } = {}) {
                    if (!this.quickSubjectId) {
                        return;
                    }

                    this.quickQuestionLoading = true;

                    if (!append) {
                        this.quickQuestions = [];
                        this.quickQuestionExpandedIds = [];
                    }

                    const params = new URLSearchParams({
                        subject_id: String(this.quickSubjectId),
                        page: String(page),
                        per_page: '20',
                    });

                    if (this.quickQuestionChapterId) {
                        params.append('chapter_id', this.quickQuestionChapterId);
                    }

                    if (this.quickQuestionDifficulty) {
                        params.append('difficulty', this.quickQuestionDifficulty);
                    }

                    const keyword = this.quickQuestionKeyword.trim();
                    if (keyword) {
                        params.append('keyword', keyword);
                    }

                    try {
                        const response = await fetch(`${this.quickQuestionApiUrl}?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Không thể tải danh sách câu hỏi.');
                        }

                        const payload = await response.json();
                        const incomingItems = Array.isArray(payload?.data) ? payload.data : [];

                        this.quickQuestionPage = Number(payload?.current_page || page);
                        this.quickQuestionLastPage = Number(payload?.last_page || page);
                        this.quickQuestionTotal = Number(payload?.total || incomingItems.length);

                        if (append) {
                            this.quickQuestions = [...this.quickQuestions, ...incomingItems];
                        } else {
                            this.quickQuestions = incomingItems;
                        }
                    } catch (error) {
                        this.quickQuestions = [];
                        this.quickQuestionPage = 1;
                        this.quickQuestionLastPage = 1;
                        this.quickQuestionTotal = 0;

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: error?.message || 'Không thể tải danh sách câu hỏi.',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.quickQuestionLoading = false;
                    }
                },

                quickQuestionTypeCodeById(typeId) {
                    const numericId = Number(typeId);
                    const found = this.quickQuestionTypes.find((type) => Number(type.id) === numericId);
                    return found?.code || '';
                },

                quickQuestionCreatorUsesCheckbox() {
                    return this.quickQuestionTypeCodeById(this.quickQuestionCreator.question_type_id) === 'multiple_choice';
                },

                quickQuestionCorrectHint() {
                    return this.quickQuestionCreatorUsesCheckbox() ?
                        'Loại nhiều đáp án: có thể chọn nhiều phương án đúng.' :
                        'Loại một đáp án: chỉ được chọn 1 phương án đúng.';
                },

                syncQuickQuestionCreatorChapterOptions() {
                    const subjectId = String(this.quickQuestionCreator.subject_id || '');
                    this.quickQuestionCreatorChapterOptions = this.quickChaptersBySubject[subjectId] || [];

                    if (
                        this.quickQuestionCreator.chapter_id &&
                        !this.quickQuestionCreatorChapterOptions.some((chapter) => String(chapter.id) === String(this.quickQuestionCreator.chapter_id))
                    ) {
                        this.quickQuestionCreator.chapter_id = '';
                    }
                },

                onQuickQuestionTypeChanged() {
                    if (this.quickQuestionCreatorUsesCheckbox()) {
                        if (!Array.isArray(this.quickQuestionCreator.correct_options) || this.quickQuestionCreator.correct_options.length === 0) {
                            this.quickQuestionCreator.correct_options = [0];
                        }
                        return;
                    }

                    const firstSelected = Array.isArray(this.quickQuestionCreator.correct_options) && this.quickQuestionCreator.correct_options.length > 0 ?
                        this.quickQuestionCreator.correct_options[0] :
                        0;
                    this.quickQuestionCreator.correct_options = [Number(firstSelected)];
                },

                openQuickQuestionCreateModal() {
                    if (!this.quickSubjectId) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Hãy chọn môn học trước khi tạo câu hỏi nhanh.',
                                type: 'error'
                            }
                        }));
                        return;
                    }

                    this.resetQuickQuestionCreatorForm();
                    this.quickQuestionCreator.subject_id = String(this.quickSubjectId);
                    this.syncQuickQuestionCreatorChapterOptions();
                    this.quickQuestionCreatorError = '';
                    this.$dispatch('open-modal', 'quick-question-modal');
                },

                addQuickQuestionCreatorOption() {
                    if (this.quickQuestionCreator.options.length >= 12) {
                        return;
                    }

                    this.quickQuestionCreator.options.push('');
                },

                removeQuickQuestionCreatorOption(index) {
                    if (this.quickQuestionCreator.options.length <= 2) {
                        return;
                    }

                    this.quickQuestionCreator.options.splice(index, 1);
                    this.quickQuestionCreator.correct_options = this.quickQuestionCreator.correct_options
                        .filter((selectedIndex) => selectedIndex !== index)
                        .map((selectedIndex) => selectedIndex > index ? selectedIndex - 1 : selectedIndex);

                    if (this.quickQuestionCreator.correct_options.length === 0) {
                        this.quickQuestionCreator.correct_options = [0];
                    }
                },

                toggleQuickQuestionCreatorCorrect(index, checked) {
                    const numericIndex = Number(index);

                    if (this.quickQuestionCreatorUsesCheckbox()) {
                        const next = new Set(this.quickQuestionCreator.correct_options);
                        if (checked) {
                            next.add(numericIndex);
                        } else {
                            next.delete(numericIndex);
                        }
                        this.quickQuestionCreator.correct_options = Array.from(next);
                        return;
                    }

                    this.quickQuestionCreator.correct_options = [numericIndex];
                },

                resetQuickQuestionCreatorForm() {
                    this.quickQuestionCreator = {
                        subject_id: this.quickSubjectId ? String(this.quickSubjectId) : '',
                        chapter_id: '',
                        difficulty: this.quickDifficultyOptions[0]?.code || 'remember',
                        question_type_id: '',
                        content: '',
                        options: ['', '', '', ''],
                        correct_options: [0],
                    };
                    this.quickQuestionCreatorError = '';
                    this.syncQuickQuestionCreatorChapterOptions();
                },

                validateQuickQuestionCreator() {
                    if (!this.quickQuestionCreator.subject_id) {
                        return 'Môn học là bắt buộc.';
                    }

                    if (!this.quickQuestionCreator.question_type_id) {
                        return 'Loại câu hỏi là bắt buộc.';
                    }

                    if (!String(this.quickQuestionCreator.content || '').trim()) {
                        return 'Nội dung câu hỏi là bắt buộc.';
                    }

                    const normalizedOptions = this.quickQuestionCreator.options
                        .map((value) => String(value || '').trim())
                        .filter((value) => value.length > 0);

                    if (normalizedOptions.length < 2) {
                        return 'Cần ít nhất 2 phương án trả lời có nội dung.';
                    }

                    if (!Array.isArray(this.quickQuestionCreator.correct_options) || this.quickQuestionCreator.correct_options.length === 0) {
                        return 'Vui lòng chọn ít nhất một đáp án đúng.';
                    }

                    const hasValidCorrectOption = this.quickQuestionCreator.correct_options.some((optionIndex) => {
                        const optionValue = this.quickQuestionCreator.options[Number(optionIndex)];
                        return String(optionValue || '').trim().length > 0;
                    });

                    if (!hasValidCorrectOption) {
                        return 'Vui lòng chọn đáp án đúng hợp lệ (không để trống).';
                    }

                    return '';
                },

                async submitQuickQuestionCreator() {
                    this.quickQuestionCreatorError = '';
                    const validationMessage = this.validateQuickQuestionCreator();
                    if (validationMessage) {
                        this.quickQuestionCreatorError = validationMessage;
                        return;
                    }

                    const payload = new FormData();
                    payload.append('subject_id', String(this.quickQuestionCreator.subject_id));
                    payload.append('chapter_id', this.quickQuestionCreator.chapter_id ? String(this.quickQuestionCreator.chapter_id) : '');
                    payload.append('question_type_id', String(this.quickQuestionCreator.question_type_id));
                    payload.append('difficulty', String(this.quickQuestionCreator.difficulty || 'remember'));
                    payload.append('content', String(this.quickQuestionCreator.content || '').trim());

                    const normalizedOptions = this.quickQuestionCreator.options
                        .map((value, index) => ({
                            originalIndex: index,
                            content: String(value || '').trim(),
                        }))
                        .filter((option) => option.content.length > 0);

                    normalizedOptions.forEach((option, index) => {
                        payload.append(`options[${index}][content]`, option.content);
                    });

                    const normalizedIndexMap = new Map(
                        normalizedOptions.map((option, normalizedIndex) => [option.originalIndex, normalizedIndex])
                    );
                    const normalizedCorrectOptions = Array.from(new Set(
                        this.quickQuestionCreator.correct_options
                        .map((optionIndex) => normalizedIndexMap.get(Number(optionIndex)))
                        .filter((optionIndex) => Number.isInteger(optionIndex))
                    ));

                    if (normalizedCorrectOptions.length === 0) {
                        this.quickQuestionCreatorError = 'Vui lòng chọn đáp án đúng hợp lệ (không để trống).';
                        return;
                    }

                    normalizedCorrectOptions.forEach((optionIndex) => {
                        payload.append('correct_options[]', String(optionIndex));
                    });

                    this.isSubmittingQuickQuestion = true;
                    try {
                        const response = await fetch(this.quickQuestionCreateUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: payload,
                        });

                        const responseData = await response.json();
                        if (!response.ok) {
                            const firstError = Object.values(responseData?.errors || {})[0];
                            this.quickQuestionCreatorError = Array.isArray(firstError) ?
                                firstError[0] :
                                (responseData?.error || responseData?.message || 'Không thể tạo câu hỏi nhanh.');
                            return;
                        }

                        const newQuestionId = Number(responseData?.id || 0);
                        if (newQuestionId > 0 && !this.quickQuestionSelectedIds.includes(newQuestionId)) {
                            this.quickQuestionSelectedIds = [...this.quickQuestionSelectedIds, newQuestionId];
                            this.syncQuickQuestionHiddenInputs();
                        }

                        this.$dispatch('close-modal', 'quick-question-modal');
                        this.resetQuickQuestionCreatorForm();
                        await this.loadQuickQuestions({
                            page: 1
                        });

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Đã tạo câu hỏi mới và thêm vào đề nhanh.',
                                type: 'success'
                            }
                        }));
                    } catch (error) {
                        this.quickQuestionCreatorError = error?.message || 'Không thể tạo câu hỏi nhanh.';
                    } finally {
                        this.isSubmittingQuickQuestion = false;
                    }
                },

                async loadExamPreview() {
                    const {
                        previewUrl
                    } = this.selectedExamRoutes();
                    if (!previewUrl) {
                        this.examPreviewError = 'Vui lòng chọn đề thi trước khi xem chi tiết.';
                        this.examPreviewData = null;
                        return;
                    }

                    this.isLoadingExamPreview = true;
                    this.examPreviewError = '';
                    this.quickExamEditError = '';

                    try {
                        const response = await fetch(previewUrl, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        let responseData = null;
                        try {
                            responseData = await response.json();
                        } catch (_) {
                            responseData = null;
                        }

                        if (!response.ok) {
                            throw new Error(responseData?.message || 'Không thể tải dữ liệu đề thi.');
                        }

                        this.examPreviewData = responseData;
                        this.quickExamEditForm = {
                            title: responseData?.title || '',
                            description: responseData?.description || '',
                            duration_minutes: responseData?.duration_minutes || 45,
                        };

                        this.quickExamEditWarning = responseData?.can_edit_structure ?
                            '' :
                            'Đề thi đã có sinh viên làm bài, chỉ chỉnh sửa được tên và mô tả.';
                    } catch (error) {
                        this.examPreviewData = null;
                        this.examPreviewError = error?.message || 'Không thể tải dữ liệu đề thi.';
                    } finally {
                        this.isLoadingExamPreview = false;
                    }
                },

                async openExamPreviewModal() {
                    if (!this.selectedExamId) {
                        return;
                    }

                    this.$dispatch('open-modal', 'exam-preview-modal');
                    await this.loadExamPreview();
                },

                async openExamEditModal() {
                    if (!this.selectedExamId) {
                        return;
                    }

                    this.$dispatch('open-modal', 'quick-edit-exam-modal');
                    await this.loadExamPreview();
                },

                async submitQuickExamEdit() {
                    if (!this.selectedExamId) {
                        this.quickExamEditError = 'Vui lòng chọn đề thi cần chỉnh sửa.';
                        return;
                    }

                    const {
                        updateUrl
                    } = this.selectedExamRoutes();
                    if (!updateUrl) {
                        this.quickExamEditError = 'Không xác định được đường dẫn cập nhật đề thi.';
                        return;
                    }

                    this.isSavingQuickExamEdit = true;
                    this.quickExamEditError = '';

                    const payload = {
                        title: this.quickExamEditForm.title,
                        description: this.quickExamEditForm.description,
                    };

                    if (this.examPreviewData?.can_edit_structure !== false) {
                        payload.duration_minutes = this.quickExamEditForm.duration_minutes;
                    }

                    try {
                        const response = await fetch(updateUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify(payload),
                        });

                        let responseData = null;
                        try {
                            responseData = await response.json();
                        } catch (_) {
                            responseData = null;
                        }

                        if (response.status === 422) {
                            const firstError = Object.values(responseData?.errors || {})[0];
                            this.quickExamEditError = Array.isArray(firstError) ?
                                firstError[0] :
                                'Dữ liệu cập nhật chưa hợp lệ.';
                            return;
                        }

                        if (!response.ok) {
                            throw new Error(responseData?.message || 'Không thể cập nhật đề thi.');
                        }

                        const updatedExam = responseData?.exam || null;
                        const selectedOption = this.selectedExamOption();

                        if (selectedOption && updatedExam) {
                            const subjectCode = updatedExam.subject_code || 'SUB';
                            selectedOption.textContent = `[${subjectCode}] ${updatedExam.title}`;
                            selectedOption.setAttribute('data-preview-url', this.buildExamUrl(this.quickPreviewUrlTemplate, updatedExam.id));
                            selectedOption.setAttribute('data-quick-update-url', this.buildExamUrl(this.quickUpdateUrlTemplate, updatedExam.id));
                            selectedOption.setAttribute('data-edit-url', this.buildExamUrl(this.examEditUrlTemplate, updatedExam.id));
                        }

                        if (this.examPreviewData && updatedExam) {
                            this.examPreviewData.title = updatedExam.title;
                            this.examPreviewData.description = updatedExam.description;
                            this.examPreviewData.duration_minutes = updatedExam.duration_minutes;
                            this.examPreviewData.can_edit_structure = updatedExam.can_edit_structure;
                            this.examPreviewData.subject = this.examPreviewData.subject || {};
                            this.examPreviewData.subject.code = this.examPreviewData.subject.code || updatedExam.subject_code;
                        }

                        this.quickExamEditWarning = responseData?.warning || '';
                        this.$dispatch('close-modal', 'quick-edit-exam-modal');

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: responseData?.message || 'Đã cập nhật đề thi thành công.',
                                type: 'success'
                            }
                        }));
                    } catch (error) {
                        this.quickExamEditError = error?.message || 'Không thể cập nhật đề thi.';
                    } finally {
                        this.isSavingQuickExamEdit = false;
                    }
                },

                async submitQuickExamForm(formElement) {
                    this.isSubmittingQuickExam = true;
                    this.quickQuestionFormError = '';
                    this.syncQuickQuestionHiddenInputs();

                    if (this.quickQuestionSelectedIds.length === 0) {
                        this.isSubmittingQuickExam = false;
                        this.quickQuestionFormError = 'Vui lòng chọn ít nhất một câu hỏi để tạo đề nhanh.';
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: this.quickQuestionFormError,
                                type: 'error'
                            }
                        }));
                        return;
                    }

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.exams.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        let responseData = null;
                        try {
                            responseData = await response.json();
                        } catch (_) {
                            responseData = null;
                        }

                        if (response.status === 422) {
                            const firstError = Object.values(responseData?.errors || {})[0];
                            const message = Array.isArray(firstError) ?
                                firstError[0] :
                                (responseData?.message || 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.');
                            throw new Error(message);
                        }

                        if (!response.ok || !responseData?.success || !responseData?.exam?.id) {
                            throw new Error(responseData?.message || 'Không thể tạo đề thi nhanh. Vui lòng dùng trình tạo đầy đủ.');
                        }

                        const examData = responseData.exam;
                        const examId = examData.id;

                        const selectedSubject = formElement.querySelector('select[name="subject_id"] option:checked');
                        const subjectId = examData.subject_id || (selectedSubject ? selectedSubject.value : '');
                        const subjectCode = examData.subject_code || (selectedSubject ? selectedSubject.textContent.split(' - ')[0] : 'SUB');
                        const title = examData.title || String(formData.get('title') || 'Đề thi mới');

                        const examSelect = document.getElementById('exam_id');
                        if (examSelect) {
                            const existing = Array.from(examSelect.options).some(opt => String(opt.value) === String(examId));
                            if (!existing) {
                                const option = document.createElement('option');
                                option.value = examId;
                                option.setAttribute('data-subject-id', subjectId);
                                option.setAttribute('data-preview-url', examData.preview_url || this.buildExamUrl(this.quickPreviewUrlTemplate, examId));
                                option.setAttribute('data-quick-update-url', examData.quick_update_url || this.buildExamUrl(this.quickUpdateUrlTemplate, examId));
                                option.setAttribute('data-edit-url', examData.edit_url || this.buildExamUrl(this.examEditUrlTemplate, examId));
                                option.textContent = `[${subjectCode}] ${title}`;
                                examSelect.appendChild(option);
                            }
                            examSelect.value = examId;
                            examSelect.dispatchEvent(new Event('change'));
                        }

                        this.$dispatch('close-modal', 'quick-create-exam-modal');
                        formElement.reset();
                        this.quickSubjectId = '';
                        this.onQuickSubjectChange();
                        this.resetQuickQuestionCreatorForm();

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Đã tạo đề thi và tự động chọn vào lịch thi.',
                                type: 'success'
                            }
                        }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: error?.message || 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.isSubmittingQuickExam = false;
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const examSelect = document.getElementById('exam_id');
            const examOptions = Array.from(examSelect.options);
            const sectionContainer = document.getElementById('section-selection-container');
            const mainFields = document.getElementById('main-form-fields');
            const sectionItems = document.querySelectorAll('.section-item');
            const noSectionsMsg = document.getElementById('no-sections-msg');
            const sectionsList = document.getElementById('sections-list');

            const rootEl = document.querySelector('[data-pre-selected-subject-id]');
            const preSelectedSubjectId = rootEl ? rootEl.dataset.preSelectedSubjectId : '';

            function handleExamChange() {
                const selectedExam = examSelect.options[examSelect.selectedIndex];
                const subjectId = selectedExam ? selectedExam.getAttribute('data-subject-id') : null;

                if (!subjectId) {
                    if (sectionContainer) sectionContainer.classList.add('hidden');
                    mainFields.classList.add('opacity-50', 'pointer-events-none');
                    return;
                }

                if (sectionContainer) sectionContainer.classList.remove('hidden');
                mainFields.classList.remove('opacity-50', 'pointer-events-none');

                if (sectionItems.length > 0) {
                    let visibleCount = 0;
                    sectionItems.forEach(item => {
                        const itemSubjectId = item.getAttribute('data-subject-id');
                        const checkbox = item.querySelector('input[type="checkbox"]');

                        if (itemSubjectId === subjectId) {
                            item.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            item.classList.add('hidden');
                            checkbox.checked = false;
                        }
                    });

                    if (visibleCount === 0) {
                        if (sectionsList) sectionsList.classList.add('hidden');
                        if (noSectionsMsg) noSectionsMsg.classList.remove('hidden');
                    } else {
                        if (sectionsList) sectionsList.classList.remove('hidden');
                        if (noSectionsMsg) noSectionsMsg.classList.add('hidden');
                    }
                }
            }

            if (preSelectedSubjectId) {
                examSelect.innerHTML = '<option value="">-- Chọn đề thi --</option>';
                examOptions.forEach(option => {
                    const optionSubjectId = option.getAttribute('data-subject-id');
                    if (!optionSubjectId || optionSubjectId == preSelectedSubjectId) {
                        examSelect.appendChild(option);
                    }
                });
            }

            examSelect.addEventListener('change', handleExamChange);

            if (examSelect.value) {
                handleExamChange();
            }

            const alpineRoot = document.querySelector('[x-data^="scheduleCreateManager"]');
            if (alpineRoot && alpineRoot.__x && typeof alpineRoot.__x.$data.onQuickSubjectChange === 'function') {
                alpineRoot.__x.$data.onQuickSubjectChange();
            }
        });
    </script>
</x-app-layout>