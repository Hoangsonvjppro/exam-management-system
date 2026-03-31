<x-app-layout>
    @php
    $quickSubjectIds = $courseSections->pluck('subject_id')->filter()->unique()->values();
    $quickSubjects = \App\Models\Subject::query()
    ->whereIn('id', $quickSubjectIds)
    ->orderBy('name')
    ->get(['id', 'name', 'code']);

    $quickQuestionPool = \App\Models\Question::query()
    ->whereIn('subject_id', $quickSubjectIds)
    ->orderByDesc('updated_at')
    ->limit(300)
    ->get(['id', 'subject_id', 'content']);

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
                                <option value="{{ $ex->id }}" data-subject-id="{{ $ex->subject_id }}" 
                                    x-show="'{{ $ex->subject_id }}' == selectedSubjectId"
                                    {{ old('exam_id') == $ex->id ? 'selected' : '' }}>
                                    [{{ $ex->subject->code }}] {{ $ex->title }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-[#6B7C99] mt-1">Danh sách được lọc theo môn học bạn đã chọn.</p>
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
                            <a href="{{ route('lecturer.exams.create') }}" class="text-[12px] font-semibold text-[#185FA5] hover:underline">Mở trình tạo đầy đủ</a>
                        </div>

                        @if($quickQuestionPool->isEmpty())
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-[12px] text-amber-800">
                            Chưa có câu hỏi đã duyệt để tạo đề nhanh. Vui lòng tạo câu hỏi ở Ngân hàng câu hỏi trước.
                        </div>
                        @else
                        <div class="max-h-[280px] overflow-y-auto border border-gray-200 rounded-lg bg-surface-0 divide-y divide-border-clean/70 px-2">
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
        function scheduleCreateManager(initialSubjectId) {
            return {
                selectedSubjectId: initialSubjectId || '',
                selectedExamId: '',
                hasSelectedSection: false,
                quickSubjectId: initialSubjectId || '',
                isSubmittingQuickExam: false,

                onSubjectChange() {
                    this.hasSelectedSection = false;
                    this.selectedExamId = '';
                    document.querySelectorAll('input[name="course_section_ids[]"]').forEach(cb => cb.checked = false);
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

                        const examSelect = document.getElementById('exam_id');
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
                            examSelect.dispatchEvent(new Event('change'));
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