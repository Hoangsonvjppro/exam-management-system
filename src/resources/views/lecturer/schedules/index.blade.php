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
    @endphp
    @section('title', 'Quản lý Lịch Thi — EMS')
    @section('page-title', 'Lịch thi')

    <div class="space-y-6" x-data="scheduleManager()">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-navy-900 mb-1">Quản lý Lịch Thi</h2>
                <p class="text-sm text-text-muted">Tất cả ca thi bạn đã lên lịch.</p>
            </div>
            <x-button variant="primary" @click="$dispatch('open-modal', 'create-schedule-modal')" class="flex items-center gap-2 text-sm">
                <x-ui-icon name="plus" class="w-4 h-4" />
                Thêm lịch thi mới
            </x-button>
        </div>

        {{-- Search & Filter --}}
        <form action="{{ route('lecturer.schedules.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm theo tên đề thi..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-border-clean rounded-[10px] text-sm font-medium text-navy-900 placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all shadow-sm" />
            </div>
            <div class="w-full sm:w-56">
                <select name="subject_id" data-auto-submit="form"
                    class="w-full px-4 py-2.5 bg-white border border-border-clean rounded-[10px] text-sm font-medium text-navy-900 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all shadow-sm cursor-pointer appearance-none"
                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236B7C99%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem;">
                    <option value="">Tất cả môn học</option>
                    @foreach($filterSubjects as $subject)
                    <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-navy-900 text-white rounded-[10px] text-sm font-semibold hover:bg-navy-950 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Tìm kiếm
            </button>
            @if(request('search') || request('subject_id'))
            <a href="{{ route('lecturer.schedules.index') }}" class="px-4 py-2.5 bg-white text-text-muted border border-border-clean rounded-[10px] text-sm font-semibold hover:bg-surface-1 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Xóa lọc
            </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-[10px] border border-border-clean overflow-visible shadow-sm">
            @if($schedules->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-surface-1 rounded-full flex items-center justify-center mx-auto mb-4 border border-border-clean">
                    <x-ui-icon name="academic-cap" class="w-8 h-8 text-text-muted" />
                </div>
                <h4 class="text-base font-bold text-navy-900 mb-2">Chưa có lịch thi nào</h4>
                <p class="text-sm text-text-muted mb-4">Nhấn "Thêm lịch thi mới" để bắt đầu.</p>
                <x-button variant="primary" @click="$dispatch('open-modal', 'create-schedule-modal')">
                    Tạo lịch thi
                </x-button>
            </div>
            @else
            <div class="overflow-x-auto overflow-y-visible relative">
                <table class="w-full text-[14px] lg:text-[15px]">
                    <thead>
                        <tr class="bg-surface-1 text-navy-900">
                            <th class="px-5 py-4 text-left font-semibold">Đề thi</th>
                            <th class="px-5 py-4 text-left font-semibold">Lớp</th>
                            <th class="px-5 py-4 text-center font-semibold">Ngày</th>
                            <th class="px-5 py-4 text-center font-semibold">Giờ</th>
                            <th class="px-5 py-4 text-center font-semibold">SV</th>
                            <th class="px-5 py-4 text-center font-semibold">Trạng thái</th>
                            <th class="px-5 py-4 text-center font-semibold">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="schedule-table-body">
                        @foreach($schedules as $schedule)
                        @include('lecturer.schedules.partials._schedule_row', ['schedule' => $schedule])
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Modal: Tạo lịch thi mới --}}
        <x-modal name="create-schedule-modal" maxWidth="2xl">
            <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0">
                <h3 class="text-[17px] font-bold text-navy-900">Thêm lịch thi mới</h3>
                <button @click="$dispatch('close-modal', 'create-schedule-modal')" class="text-text-muted hover:text-navy-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[85vh]">
                <form @submit.prevent="submitCreateForm($el)" class="space-y-6">
                    @csrf

                    {{-- 1. Chọn môn học --}}
                    <div>
                        <label class="block text-[12px] font-bold text-navy-900 uppercase tracking-wider mb-2">Bước 1: Chọn môn học <span class="text-red-500">*</span></label>
                        <select x-model="selectedSubjectId" @change="onSubjectChange()" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-[14px] font-medium focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 bg-white">
                            <option value="">-- Click để chọn môn học --</option>
                            @foreach($quickSubjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Chọn lớp học phần --}}
                    <div x-show="selectedSubjectId" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2">
                        <label class="block text-[12px] font-bold text-navy-900 uppercase tracking-wider mb-3">Bước 2: Các lớp áp dụng <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[220px] overflow-y-auto p-4 border border-gray-200 rounded-xl bg-surface-0 shadow-inner">
                            @foreach($courseSections as $cs)
                            <div class="section-checkbox flex items-start gap-3 p-2.5 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-border-clean"
                                data-subject-id="{{ $cs->subject_id }}"
                                x-show="'{{ $cs->subject_id }}' == selectedSubjectId">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="course_section_ids[]" id="modal-cs-{{ $cs->id }}" value="{{ $cs->id }}"
                                        @change="onSectionChange()"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                </div>
                                <label for="modal-cs-{{ $cs->id }}" class="cursor-pointer">
                                    <p class="text-[13px] font-bold text-navy-900 leading-tight">{{ $cs->name }}</p>
                                    <p class="text-[11px] text-text-muted mt-0.5 font-mono">{{ $cs->code }}</p>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="course_section_ids"></p>
                    </div>

                    {{-- 3. Chọn đề thi --}}
                    <div x-show="selectedSubjectId && hasSelectedSection" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <label class="text-[12px] font-bold text-navy-900 uppercase tracking-wider">Bước 3: Chọn đề thi <span class="text-red-500">*</span></label>
                            <button type="button" class="text-[12px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1" @click="$dispatch('open-modal', 'quick-create-exam-modal')">
                                <x-ui-icon name="plus" class="w-3.5 h-3.5" />
                                Tạo nhanh đề mới
                            </button>
                        </div>
                        <select name="exam_id" x-model="selectedExamId" required
                            id="schedule-modal-exam-id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-[14px] font-medium focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200 bg-white">
                            <option value="">-- Chọn đề thi môn hiện tại --</option>
                            @foreach($exams as $ex)
                            <option
                                value="{{ $ex->id }}"
                                data-subject-id="{{ $ex->subject_id }}"
                                data-preview-url="{{ route('lecturer.exams.quick-preview', $ex) }}"
                                data-quick-update-url="{{ route('lecturer.exams.quick-update', $ex) }}"
                                data-edit-url="{{ route('lecturer.exams.edit', $ex) }}"
                                x-show="'{{ $ex->subject_id }}' == selectedSubjectId">
                                [{{ $ex->subject->code }}] {{ $ex->title }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[11px] text-text-muted italic flex items-start gap-1">
                            <x-ui-icon name="information-circle" class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                            Danh sách đề thi được lọc tự động theo môn học bạn đã chọn ở Bước 1.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="openExamPreviewModal()"
                                :disabled="!selectedExamId"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold border border-[#BFD4EA] text-navy-900 hover:bg-[#F3F8FD] disabled:opacity-50 disabled:cursor-not-allowed">
                                Xem chi tiết đề
                            </button>
                            <button
                                type="button"
                                @click="openExamEditModal()"
                                :disabled="!selectedExamId"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold border border-indigo-500 text-indigo-600 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Sửa nhanh đề
                            </button>
                            <a
                                x-show="selectedExamId"
                                :href="selectedExamEditUrl()"
                                class="text-[12px] font-bold text-indigo-600 hover:text-indigo-700">
                                Mở trang sửa đầy đủ
                            </a>
                        </div>
                        <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="exam_id"></p>
                    </div>

                    {{-- 4. Thông tin chi tiết --}}
                    <div x-show="selectedSubjectId && hasSelectedSection && selectedExamId" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" class="space-y-5 pt-2">
                        <label class="block text-[12px] font-bold text-navy-900 uppercase tracking-wider mb-1">Bước 4: Thời gian & Cấu hình</label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1.5">Ngày thi <span class="text-red-500">*</span></label>
                                <input type="date" name="exam_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                                <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="exam_date"></p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1.5">Số SV tối đa</label>
                                <input type="number" name="max_students" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Không giới hạn">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1.5">Giờ bắt đầu <span class="text-red-500">*</span></label>
                                <input type="time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                                <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="start_time"></p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1.5">Giờ kết thúc <span class="text-red-500">*</span></label>
                                <input type="time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                                <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="end_time"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-text-muted mb-1.5">Ghi chú cho sinh viên (vị trí phòng, thiết bị...)</label>
                            <textarea name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" rows="3" placeholder="Nhập ghi chú..."></textarea>
                        </div>

                        <div class="mt-4 p-4 border border-indigo-100 rounded-[8px] bg-indigo-50/50">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="link_grade_column" value="1" class="mt-0.5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" checked>
                                <div>
                                    <p class="text-[13px] font-bold text-navy-900">Tự động thêm vào bảng Điểm quá trình</p>
                                    <p class="text-[11px] text-text-muted mt-0.5">Hệ thống sẽ tạo tự động một cột Điểm Bài Thi trong bảng điểm của (các) lớp học phần được chọn.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-6 border-t border-border-clean" x-show="selectedSubjectId && hasSelectedSection && selectedExamId" x-transition>
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'create-schedule-modal')">Hủy bỏ</x-button>
                        <x-button type="submit" variant="primary" x-bind:disabled="isSubmitting" class="px-8">
                            <span x-show="!isSubmitting">Lưu lịch thi</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Đang xử lý...
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal>

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
                        <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="VD: Kiểm tra nhanh tuần 5">
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
                                <a href="{{ route('lecturer.exams.create') }}" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700">Mở trình tạo đầy đủ</a>
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
                <div x-show="isLoadingExamPreview" class="py-10 text-center text-[13px] text-text-muted">
                    Đang tải thông tin đề thi...
                </div>

                <div x-show="!isLoadingExamPreview && examPreviewError" class="p-4 rounded-lg border border-red-200 bg-red-50 text-[13px] text-red-700" x-text="examPreviewError"></div>

                <div x-show="!isLoadingExamPreview && examPreviewData" class="space-y-4">
                    <div>
                        <h4 class="text-[18px] font-bold text-navy-900" x-text="examPreviewData?.title || ''"></h4>
                        <p class="text-[12px] text-text-muted mt-1" x-text="(examPreviewData?.subject?.code || '') + ' - ' + (examPreviewData?.subject?.name || '')"></p>
                        <p class="text-[13px] text-slate-700 mt-2" x-show="examPreviewData?.description" x-text="examPreviewData?.description"></p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="p-3 rounded-lg border border-border-clean bg-surface-0">
                            <p class="text-[11px] text-text-muted">Thời lượng</p>
                            <p class="text-[16px] font-bold text-navy-900" x-text="(examPreviewData?.duration_minutes || 0) + ' phút'"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-border-clean bg-surface-0">
                            <p class="text-[11px] text-text-muted">Số câu hỏi</p>
                            <p class="text-[16px] font-bold text-navy-900" x-text="examPreviewData?.question_count || 0"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-border-clean bg-surface-0">
                            <p class="text-[11px] text-text-muted">Đã gán lịch</p>
                            <p class="text-[16px] font-bold text-navy-900" x-text="examPreviewData?.schedule_count || 0"></p>
                        </div>
                        <div class="p-3 rounded-lg border border-border-clean bg-surface-0">
                            <p class="text-[11px] text-text-muted">Lượt làm bài</p>
                            <p class="text-[16px] font-bold text-navy-900" x-text="examPreviewData?.attempt_count || 0"></p>
                        </div>
                    </div>

                    <div x-show="examPreviewData && !examPreviewData.can_edit_structure" class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-[12px] text-amber-800">
                        Đề thi đã có sinh viên làm bài, bạn chỉ có thể chỉnh sửa tên và mô tả.
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[12px] font-bold text-navy-900 uppercase tracking-wider">Câu hỏi trong đề</p>
                            <p class="text-[11px] text-text-muted">Hiển thị tối đa 8 câu đầu</p>
                        </div>

                        <div class="space-y-2" x-show="examPreviewData?.questions_preview?.length">
                            <template x-for="question in (examPreviewData?.questions_preview || [])" :key="question.order">
                                <div class="p-3 rounded-lg border border-border-clean bg-white">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <p class="text-[12px] font-bold text-navy-900" x-text="'Câu ' + question.order"></p>
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-show="question.difficulty" x-text="difficultyLabel(question.difficulty)"></span>
                                    </div>
                                    <p class="text-[12px] text-slate-700" x-text="question.content"></p>
                                </div>
                            </template>
                        </div>

                        <p x-show="!(examPreviewData?.questions_preview?.length)" class="text-[12px] text-text-muted italic py-2">
                            Chưa có dữ liệu câu hỏi cho đề này.
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-border-clean flex items-center justify-end gap-2 bg-surface-0">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-border-clean text-[12px] font-semibold text-navy-900 hover:bg-surface-1" @click="$dispatch('close-modal', 'exam-preview-modal')">
                    Đóng
                </button>
                <button type="button" class="px-3 py-1.5 rounded-lg border border-indigo-500 text-[12px] font-semibold text-indigo-600 hover:bg-indigo-50" @click="$dispatch('close-modal', 'exam-preview-modal'); openExamEditModal()" :disabled="!selectedExamId">
                    Sửa nhanh
                </button>
                <a :href="selectedExamEditUrl()" x-show="selectedExamId" class="px-3 py-1.5 rounded-lg bg-navy-900 text-white text-[12px] font-semibold hover:bg-navy-950">
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
                <div x-show="isLoadingExamPreview" class="py-6 text-center text-[13px] text-text-muted">
                    Đang tải dữ liệu đề thi...
                </div>

                <div x-show="!isLoadingExamPreview" class="space-y-4">
                    <div x-show="quickExamEditWarning" class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-[12px] text-amber-800" x-text="quickExamEditWarning"></div>
                    <div x-show="quickExamEditError" class="p-3 rounded-lg border border-red-200 bg-red-50 text-[12px] text-red-700" x-text="quickExamEditError"></div>

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
                    <button type="button" class="px-3 py-1.5 rounded-lg border border-border-clean text-[12px] font-semibold text-navy-900 hover:bg-surface-1" @click="$dispatch('close-modal', 'quick-edit-exam-modal')">
                        Huỷ
                    </button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-navy-900 text-white text-[12px] font-semibold hover:bg-navy-950 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isSavingQuickExamEdit || !selectedExamId">
                        <span x-show="!isSavingQuickExamEdit">Lưu thay đổi</span>
                        <span x-show="isSavingQuickExamEdit">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- Modal: Phân sinh viên vào ca thi --}}
        <div x-show="assignModalOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-black/40" @click="assignModalOpen = false"></div>

            {{-- Modal Content --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-border-clean flex items-center justify-between bg-surface-0 flex-shrink-0">
                    <div>
                        <h3 class="text-[17px] font-bold text-navy-900">Phân sinh viên vào ca thi</h3>
                        <p class="text-[12px] text-text-muted mt-0.5" x-text="assignModalExamTitle + ' — ' + assignModalClassName"></p>
                    </div>
                    <button @click="assignModalOpen = false" class="text-text-muted hover:text-navy-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Search & Actions --}}
                <div class="px-6 py-3 border-b border-border-clean/60 flex-shrink-0 space-y-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="assignSearchQuery" placeholder="Tìm kiếm sinh viên..."
                            class="w-full pl-9 pr-4 py-2 bg-surface-0 border border-border-clean rounded-lg text-[13px] font-medium text-navy-900 placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all" />
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAllStudents()" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 px-2 py-1 rounded hover:bg-blue-50 transition-colors">Chọn tất cả</button>
                            <button type="button" @click="deselectAllStudents()" class="text-[11px] font-bold text-text-muted hover:text-navy-900 px-2 py-1 rounded hover:bg-surface-1 transition-colors">Bỏ chọn tất cả</button>
                        </div>
                        <span class="text-[11px] font-bold text-text-muted">
                            Đã chọn <span class="text-navy-900" x-text="assignSelectedIds.length"></span> / <span x-text="assignStudents.length"></span> SV
                        </span>
                    </div>
                </div>

                {{-- Loading state --}}
                <div x-show="assignLoading" class="flex-1 flex items-center justify-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-[13px] text-text-muted">Đang tải danh sách sinh viên...</span>
                    </div>
                </div>

                {{-- Student List --}}
                <div x-show="!assignLoading" class="flex-1 overflow-y-auto px-3 py-2" style="max-height: 400px;">
                    <template x-if="filteredStudents().length === 0 && !assignLoading">
                        <div class="text-center py-8">
                            <p class="text-[13px] text-text-muted" x-text="assignSearchQuery ? 'Không tìm thấy sinh viên phù hợp.' : 'Lớp chưa có sinh viên nào.'"></p>
                        </div>
                    </template>

                    <template x-for="student in filteredStudents()" :key="student.id">
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-surface-1 cursor-pointer transition-colors group">
                            <input type="checkbox"
                                :value="student.id"
                                :checked="assignSelectedIds.includes(student.id)"
                                @change="toggleStudent(student.id)"
                                class="rounded border-gray-300 text-navy-900 focus:ring-blue-500 w-4 h-4 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-semibold text-navy-900 truncate" x-text="student.name"></p>
                                <p class="text-[11px] text-text-muted truncate">
                                    <span x-text="student.student_id || ''"></span>
                                    <span x-show="student.student_id && student.email"> · </span>
                                    <span x-text="student.email || ''"></span>
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span x-show="assignSelectedIds.includes(student.id)" class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                            </div>
                        </label>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-border-clean flex items-center justify-between bg-surface-0 flex-shrink-0">
                    <p class="text-[11px] text-text-muted">
                        <span x-show="assignSelectedIds.length === assignStudents.length && assignStudents.length > 0" class="text-green-600 font-bold">✓ Toàn bộ sinh viên được chọn</span>
                        <span x-show="assignSelectedIds.length !== assignStudents.length || assignStudents.length === 0" x-text="assignSelectedIds.length + ' sinh viên được chọn'"></span>
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="assignModalOpen = false"
                            class="px-4 py-2 text-[13px] font-semibold text-text-muted hover:text-navy-900 hover:bg-surface-1 rounded-lg transition-all">Hủy</button>
                        <button type="button" @click="submitAssign()"
                            :disabled="assignSaving || assignSelectedIds.length === 0"
                            class="px-5 py-2 bg-navy-900 text-white rounded-lg text-[13px] font-semibold hover:bg-navy-950 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <span x-show="!assignSaving">Lưu phân công</span>
                            <span x-show="assignSaving" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Đang lưu...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
    $scheduleIndexConfig = [
    'quickQuestionApiUrl' => route('lecturer.api.exam-form.questions'),
    'quickQuestionCreateUrl' => route('lecturer.api.exam-form.quick-question'),
    'quickPreviewUrlTemplate' => route('lecturer.exams.quick-preview', ['exam' => '__EXAM_ID__']),
    'quickUpdateUrlTemplate' => route('lecturer.exams.quick-update', ['exam' => '__EXAM_ID__']),
    'examEditUrlTemplate' => route('lecturer.exams.edit', ['exam' => '__EXAM_ID__']),
    'scheduleStoreUrl' => route('lecturer.schedules.store'),
    'examStoreUrl' => route('lecturer.exams.store'),
    'csrfToken' => csrf_token(),
    ];
    @endphp
    <script id="schedule-index-config" type="application/json">
        @json($scheduleIndexConfig)
    </script>

    @push('scripts')
    @vite(['resources/js/pages/lecturer/schedules-index.js'])
    @endpush
</x-app-layout>