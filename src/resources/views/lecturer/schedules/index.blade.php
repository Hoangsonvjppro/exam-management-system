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
            <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-schedule-slide')" class="flex items-center gap-2 text-sm">
                <x-ui-icon name="play" class="w-4 h-4" />
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
                <x-button variant="primary" @click="$dispatch('open-slide-over', 'create-schedule-slide')">
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

        {{-- Slide-over: Tạo lịch thi mới --}}
        <x-slide-over name="create-schedule-slide" title="Tạo Lịch Thi Mới">
            <form @submit.prevent="submitCreateForm($el)" class="space-y-5">
                @csrf

                {{-- Chọn đề thi --}}
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <label class="text-[12px] font-semibold text-navy-900">Đề thi <span class="text-red-500">*</span></label>
                        <button type="button" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700" @click="$dispatch('open-slide-over', 'quick-create-exam-slide')">
                            + Tạo đề thi mới
                        </button>
                    </div>
                    <select name="exam_id" x-model="selectedExamId" @change="onExamChange()" required 
                            id="schedule-slide-exam-id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                        <option value="">-- Chọn đề thi --</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" data-subject-id="{{ $ex->subject_id }}">
                                [{{ $ex->subject->code }}] {{ $ex->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-text-muted">Bạn có thể tạo đề mới ngay tại đây và chọn tự động vào lịch thi đang nhập.</p>
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="exam_id"></p>
                </div>

                {{-- Chọn lớp học phần bằng Checkbox --}}
                <div x-show="selectedExamId" x-transition>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-3">Áp dụng cho các lớp <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto p-3 border border-gray-200 rounded-lg bg-surface-0">
                        @foreach($courseSections as $cs)
                            <div class="section-checkbox flex items-start gap-3 p-2 hover:bg-white rounded transition-colors"
                                 data-subject-id="{{ $cs->subject_id }}"
                                 x-show="!selectedSubjectId || '{{ $cs->subject_id }}' == selectedSubjectId">
                                <input type="checkbox" name="course_section_ids[]" id="slide-cs-{{ $cs->id }}" value="{{ $cs->id }}"
                                       class="mt-0.5 rounded border-gray-300 text-navy-900 focus:ring-indigo-500">
                                <label for="slide-cs-{{ $cs->id }}" class="cursor-pointer">
                                    <p class="text-[13px] font-semibold text-navy-900 leading-tight">{{ $cs->name }}</p>
                                    <p class="text-[11px] text-text-muted mt-0.5">{{ $cs->code }}</p>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-[11px] font-medium text-red-600 hidden" data-error="course_section_ids"></p>
                </div>

                {{-- Ngày thi + Số SV --}}
                <div class="grid grid-cols-2 gap-4" x-show="selectedExamId" x-transition>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ngày thi <span class="text-red-500">*</span></label>
                        <input type="date" name="exam_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="exam_date"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Số SV tối đa</label>
                        <input type="number" name="max_students" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" placeholder="Không giới hạn">
                    </div>
                </div>

                {{-- Giờ --}}
                <div class="grid grid-cols-2 gap-4" x-show="selectedExamId" x-transition>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ bắt đầu <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="start_time"></p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-900 mb-1">Giờ kết thúc <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]">
                        <p class="mt-1 text-[11px] font-medium text-red-600 hidden" data-error="end_time"></p>
                    </div>
                </div>

                {{-- Ghi chú --}}
                <div x-show="selectedExamId" x-transition>
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1">Ghi chú</label>
                    <textarea name="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px]" rows="3" placeholder="Lưu ý cho các ca thi..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-border-clean" x-show="selectedExamId" x-transition>
                    <x-button type="button" variant="ghost" @click="$dispatch('close-slide-over', 'create-schedule-slide')">Huỷ</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="isSubmitting">
                        <span x-show="!isSubmitting">Tạo lịch thi</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Đang tạo...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>

        <x-slide-over name="quick-create-exam-slide" title="Tạo đề thi nhanh" maxWidth="2xl">
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
                        <div class="max-h-[280px] overflow-y-auto border border-gray-200 rounded-lg bg-surface-0 divide-y divide-border-clean/70">
                            @foreach($quickQuestionPool as $question)
                                <label class="quick-question-item flex items-start gap-3 p-3 hover:bg-white cursor-pointer" data-subject-id="{{ $question->subject_id }}">
                                    <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="mt-0.5 rounded border-gray-300 text-navy-900 focus:ring-indigo-500">
                                    <span class="text-[12px] text-navy-900 leading-relaxed">{{ \Illuminate\Support\Str::limit(trim(strip_tags($question->content)), 180) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border-clean">
                    <x-button type="button" variant="ghost" @click="$dispatch('close-slide-over', 'quick-create-exam-slide')">Huỷ</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="isSubmittingQuickExam || {{ $quickQuestionPool->isEmpty() ? 'true' : 'false' }}">
                        <span x-show="!isSubmittingQuickExam">Tạo đề và tự chọn</span>
                        <span x-show="isSubmittingQuickExam" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Đang tạo...
                        </span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>

    </div>

    <script>
        function scheduleManager() {
            return {
                isSubmitting: false,
                selectedExamId: '',
                selectedSubjectId: '',
                quickSubjectId: '',
                isSubmittingQuickExam: false,

                onExamChange() {
                    const select = document.querySelector('select[name="exam_id"]');
                    const option = select.options[select.selectedIndex];
                    this.selectedSubjectId = option ? option.getAttribute('data-subject-id') : '';
                    
                    // Uncheck lớp không khớp môn học
                    document.querySelectorAll('.section-checkbox input[type="checkbox"]').forEach(cb => {
                        const subjectId = cb.closest('.section-checkbox').getAttribute('data-subject-id');
                        if (this.selectedSubjectId && subjectId !== this.selectedSubjectId) {
                            cb.checked = false;
                        }
                    });
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

                            this.$dispatch('close-slide-over', 'create-schedule-slide');
                            formElement.reset();
                            this.selectedExamId = '';
                            this.selectedSubjectId = '';

                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: result.message, type: 'success' }
                            }));
                        } else if (response.status === 422 && result.errors) {
                            this.showErrors(formElement, result.errors);
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
                                detail: { message: 'Không thể tạo đề nhanh. Vui lòng dùng trình tạo đầy đủ.', type: 'error' }
                            }));
                            return;
                        }

                        const selectedSubject = formElement.querySelector('select[name="subject_id"] option:checked');
                        const subjectId = selectedSubject ? selectedSubject.value : '';
                        const subjectCode = selectedSubject ? selectedSubject.textContent.split(' - ')[0] : 'SUB';
                        const title = String(formData.get('title') || 'Đề thi mới');

                        const examSelect = document.getElementById('schedule-slide-exam-id');
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

                        this.$dispatch('close-slide-over', 'quick-create-exam-slide');
                        formElement.reset();
                        this.quickSubjectId = '';
                        this.onQuickSubjectChange();

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Đã tạo đề thi và tự động chọn vào lịch thi.', type: 'success' }
                        }));
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.', type: 'error' }
                        }));
                    } finally {
                        this.isSubmittingQuickExam = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>