<x-app-layout>
    @php
    $quickSubjectIds = $courseSections->pluck('subject_id')->filter()->unique()->values();
    $quickSubjects = \App\Models\Subject::query()
    ->whereIn('id', $quickSubjectIds)
    ->orderBy('name')
    ->get(['id', 'name', 'code']);

    $quickQuestionPool = \App\Models\Question::approved()
    ->whereIn('subject_id', $quickSubjectIds)
    ->orderByDesc('updated_at')
    ->limit(300)
    ->get(['id', 'subject_id', 'content']);
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
                <select name="subject_id" onchange="this.form.submit()"
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
        <div class="bg-white rounded-[10px] border border-border-clean overflow-hidden shadow-sm">
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
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-surface-1 text-navy-900">
                        <th class="px-5 py-3 text-left font-semibold">Đề thi</th>
                        <th class="px-5 py-3 text-left font-semibold">Lớp</th>
                        <th class="px-5 py-3 text-center font-semibold">Ngày</th>
                        <th class="px-5 py-3 text-center font-semibold">Giờ</th>
                        <th class="px-5 py-3 text-center font-semibold">SV</th>
                        <th class="px-5 py-3 text-center font-semibold">Trạng thái</th>
                        <th class="px-5 py-3 text-center font-semibold">Hành động</th>
                    </tr>
                </thead>
                <tbody id="schedule-table-body">
                    @foreach($schedules as $schedule)
                    @include('lecturer.schedules.partials._schedule_row', ['schedule' => $schedule])
                    @endforeach
                </tbody>
            </table>
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
                            <option value="{{ $ex->id }}" data-subject-id="{{ $ex->subject_id }}" x-show="'{{ $ex->subject_id }}' == selectedSubjectId">
                                [{{ $ex->subject->code }}] {{ $ex->title }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[11px] text-text-muted italic flex items-start gap-1">
                            <x-ui-icon name="information-circle" class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                            Danh sách đề thi được lọc tự động theo môn học bạn đã chọn ở Bước 1.
                        </p>
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
                            <a href="{{ route('lecturer.exams.create') }}" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700">Mở trình tạo đầy đủ</a>
                        </div>

                        @if($quickQuestionPool->isEmpty())
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-[12px] text-amber-800">
                            Chưa có câu hỏi đã duyệt để tạo đề nhanh. Vui lòng tạo câu hỏi trước trong Ngân hàng câu hỏi.
                        </div>
                        @else
                        <div class="max-h-[280px] overflow-y-auto border border-gray-200 rounded-lg bg-surface-0 divide-y divide-border-clean/70 px-2 mt-1">
                            @foreach($quickQuestionPool as $question)
                            <label class="quick-question-item flex items-start gap-3 p-3 hover:bg-white cursor-pointer rounded-md transition-colors" data-subject-id="{{ $question->subject_id }}">
                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="mt-0.5 rounded border-gray-300 text-navy-900 focus:ring-indigo-500">
                                <span class="text-[12px] text-navy-900 leading-relaxed">{{ \Illuminate\Support\Str::limit(trim(strip_tags($question->content)), 180) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                        <x-button type="button" variant="ghost" @click="$dispatch('close-modal', 'quick-create-exam-modal')">Huỷ</x-button>
                        <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingQuickExam || {{ $quickQuestionPool->isEmpty() ? 'true' : 'false' }}">
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

    <script>
        function scheduleManager() {
            return {
                isSubmitting: false,
                selectedExamId: '',
                selectedSubjectId: '',
                hasSelectedSection: false,
                quickSubjectId: '',
                isSubmittingQuickExam: false,

                // Assign Students Modal
                assignModalOpen: false,
                assignModalScheduleId: null,
                assignModalExamTitle: '',
                assignModalClassName: '',
                assignLoading: false,
                assignSaving: false,
                assignStudents: [],
                assignSelectedIds: [],
                assignSearchQuery: '',

                openAssignModal(scheduleId, examTitle, className) {
                    this.assignModalScheduleId = scheduleId;
                    this.assignModalExamTitle = examTitle;
                    this.assignModalClassName = className;
                    this.assignSearchQuery = '';
                    this.assignStudents = [];
                    this.assignSelectedIds = [];
                    this.assignModalOpen = true;
                    this.assignLoading = true;
                    this.loadStudents(scheduleId);
                },

                async loadStudents(scheduleId) {
                    try {
                        const response = await fetch(`/lecturer/schedules/${scheduleId}/students`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        this.assignStudents = data.students || [];
                        // Nếu đã có assignment thì tick những SV đã assign, ngược lại chọn tất cả
                        if (data.assigned_ids && data.assigned_ids.length > 0) {
                            this.assignSelectedIds = [...data.assigned_ids];
                        } else {
                            // Mặc định chọn tất cả
                            this.assignSelectedIds = this.assignStudents.map(s => s.id);
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Không thể tải danh sách sinh viên.', type: 'error' }
                        }));
                    } finally {
                        this.assignLoading = false;
                    }
                },

                filteredStudents() {
                    if (!this.assignSearchQuery) return this.assignStudents;
                    const q = this.assignSearchQuery.toLowerCase();
                    return this.assignStudents.filter(s =>
                        (s.name && s.name.toLowerCase().includes(q)) ||
                        (s.email && s.email.toLowerCase().includes(q)) ||
                        (s.student_id && s.student_id.toLowerCase().includes(q))
                    );
                },

                toggleStudent(id) {
                    const idx = this.assignSelectedIds.indexOf(id);
                    if (idx >= 0) {
                        this.assignSelectedIds.splice(idx, 1);
                    } else {
                        this.assignSelectedIds.push(id);
                    }
                },

                selectAllStudents() {
                    this.assignSelectedIds = this.assignStudents.map(s => s.id);
                },

                deselectAllStudents() {
                    this.assignSelectedIds = [];
                },

                async submitAssign() {
                    this.assignSaving = true;
                    try {
                        const response = await fetch(`/lecturer/schedules/${this.assignModalScheduleId}/assign-students`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                            },
                            body: JSON.stringify({ student_ids: this.assignSelectedIds })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.assignModalOpen = false;
                            // Cập nhật số SV trên bảng
                            window.location.reload();
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message, type: 'success' }
                            }));
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message || 'Có lỗi xảy ra.', type: 'error' }
                            }));
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Có lỗi hệ thống xảy ra!', type: 'error' }
                        }));
                    } finally {
                        this.assignSaving = false;
                    }
                },

                onSubjectChange() {
                    // Reset dependency selections
                    this.hasSelectedSection = false;
                    this.selectedExamId = '';
                    
                    // Uncheck all classes
                    document.querySelectorAll('input[name="course_section_ids[]"]').forEach(cb => {
                        cb.checked = false;
                    });
                    
                    // Trigger custom subject filter logic for Quick Create if needed
                    this.quickSubjectId = this.selectedSubjectId;
                    this.onQuickSubjectChange();
                },

                onSectionChange() {
                    const checked = document.querySelectorAll('input[name="course_section_ids[]"]:checked');
                    this.hasSelectedSection = checked.length > 0;
                },

                onQuickSubjectChange() {
                    document.querySelectorAll('.quick-question-item').forEach(item => {
                        const itemSubjectId = item.getAttribute('data-subject-id');
                        const checkbox = item.querySelector('input[type="checkbox"]');
                        const visible = !this.quickSubjectId || String(itemSubjectId) === String(this.quickSubjectId);
                        item.classList.toggle('hidden', !visible);
                        if (!visible && checkbox) {
                            checkbox.checked = false;
                        }
                    });
                },

                clearErrors(formElement) {
                    formElement.querySelectorAll('[data-error]').forEach(el => {
                        el.textContent = '';
                        el.classList.add('hidden');
                    });
                },

                showErrors(formElement, errors) {
                    for (const [field, messages] of Object.entries(errors)) {
                        const errorEl = formElement.querySelector(`[data-error="${field}"]`);
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.remove('hidden');
                        }
                    }
                },

                async submitCreateForm(formElement) {
                    this.isSubmitting = true;
                    this.clearErrors(formElement);

                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.schedules.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            const tbody = document.getElementById('schedule-table-body');
                            if (tbody) {
                                tbody.insertAdjacentHTML('afterbegin', result.html);
                            } else {
                                window.location.reload();
                                return;
                            }

                            this.$dispatch('close-modal', 'create-schedule-modal');
                            formElement.reset();
                            this.selectedExamId = '';
                            this.selectedSubjectId = '';

                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: result.message,
                                    type: 'success'
                                }
                            }));
                        } else if (response.status === 422 && result.errors) {
                            this.showErrors(formElement, result.errors);
                        } else {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: result.message || 'Có lỗi xảy ra.',
                                    type: 'error'
                                }
                            }));
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Có lỗi hệ thống xảy ra!',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickExamForm(formElement) {
                    this.isSubmittingQuickExam = true;
                    const formData = new FormData(formElement);

                    try {
                        const response = await fetch("{{ route('lecturer.exams.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                            },
                            body: formData,
                            redirect: 'follow',
                        });

                        if (!response.ok) {
                            throw new Error('Failed to create exam');
                        }

                        const createdUrl = response.url || '';
                        const match = createdUrl.match(/\/lecturer\/exams\/(\d+)/);
                        const examId = match ? match[1] : null;

                        if (!examId) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: 'Không thể tạo đề nhanh. Vui lòng dùng trình tạo đầy đủ.',
                                    type: 'error'
                                }
                            }));
                            return;
                        }

                        const selectedSubject = formElement.querySelector('select[name="subject_id"] option:checked');
                        const subjectId = selectedSubject ? selectedSubject.value : '';
                        const subjectCode = selectedSubject ? selectedSubject.textContent.split(' - ')[0] : 'SUB';
                        const title = String(formData.get('title') || 'Đề thi mới');

                        const examSelect = document.getElementById('schedule-modal-exam-id');
                        if (examSelect) {
                            const existing = Array.from(examSelect.options).some(opt => String(opt.value) === String(examId));
                            if (!existing) {
                                const option = document.createElement('option');
                                option.value = examId;
                                option.setAttribute('data-subject-id', subjectId);
                                option.textContent = `[${subjectCode}] ${title}`;
                                examSelect.appendChild(option);
                            }
                            examSelect.value = examId;
                            this.selectedExamId = examId;
                            this.onExamChange();
                        }

                        this.$dispatch('close-modal', 'quick-create-exam-modal');
                        formElement.reset();
                        this.quickSubjectId = '';
                        this.onQuickSubjectChange();

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Đã tạo đề thi và tự động chọn vào lịch thi.',
                                type: 'success'
                            }
                        }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.',
                                type: 'error'
                            }
                        }));
                    } finally {
                        this.isSubmittingQuickExam = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>