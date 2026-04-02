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
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày bắt đầu <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="exam_date" value="{{ old('exam_date') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                    @error('exam_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Ngày kết thúc <span class="text-[#DC2626]">*</span></label>
                                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                    @error('end_date') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ bắt đầu <span class="text-[#DC2626]">*</span></label>
                                    <input type="text" name="start_time" value="{{ old('start_time') }}" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 08:30" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
                                    @error('start_time') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giờ kết thúc <span class="text-[#DC2626]">*</span></label>
                                    <input type="text" name="end_time" value="{{ old('end_time') }}" required inputmode="numeric" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:mm" title="Nhập giờ theo định dạng 24h, ví dụ 17:45" class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px]">
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


    @php
    $scheduleCreateConfig = [
    'quickQuestionApiUrl' => route('lecturer.api.exam-form.questions'),
    'quickQuestionCreateUrl' => route('lecturer.api.exam-form.quick-question'),
    'quickPreviewUrlTemplate' => route('lecturer.exams.quick-preview', ['exam' => '__EXAM_ID__']),
    'quickUpdateUrlTemplate' => route('lecturer.exams.quick-update', ['exam' => '__EXAM_ID__']),
    'examEditUrlTemplate' => route('lecturer.exams.edit', ['exam' => '__EXAM_ID__']),
    'examStoreUrl' => route('lecturer.exams.store'),
    'csrfToken' => csrf_token(),
    ];
    @endphp
    <script id="schedule-create-config" type="application/json">
        @json($scheduleCreateConfig)
    </script>

    @push('scripts')
    @vite(['resources/js/pages/lecturer/schedules-create.js'])
    @endpush
</x-app-layout>