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