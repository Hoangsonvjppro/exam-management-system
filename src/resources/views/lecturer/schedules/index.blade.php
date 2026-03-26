<x-app-layout>
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
                    <label class="block text-[12px] font-semibold text-navy-900 mb-1.5">Đề thi <span class="text-red-500">*</span></label>
                    <select name="exam_id" x-model="selectedExamId" @change="onExamChange()" required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                        <option value="">-- Chọn đề thi --</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" data-subject-id="{{ $ex->subject_id }}">
                                [{{ $ex->subject->code }}] {{ $ex->title }}
                            </option>
                        @endforeach
                    </select>
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

    </div>

    <script>
        function scheduleManager() {
            return {
                isSubmitting: false,
                selectedExamId: '',
                selectedSubjectId: '',

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
                }
            }
        }
    </script>
</x-app-layout>