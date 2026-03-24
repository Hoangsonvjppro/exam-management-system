<x-app-layout>
    <div class="py-8 bg-[#F8FAFD] min-h-screen">
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
                        {{-- Hiển thị thông tin lớp nếu đã chọn sẵn --}}
                        @if($preSelectedSection)
                            <div class="p-4 bg-[#F0F7FF] border border-[#D1E5FF] rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-bold text-[#185FA5] uppercase tracking-wider">Đang lên lịch cho lớp</p>
                                    <p class="text-[15px] font-bold text-[#1A3A6B] mt-0.5">{{ $preSelectedSection->name }} ({{ $preSelectedSection->code }})</p>
                                </div>
                                <input type="hidden" name="course_section_ids[]" value="{{ $preSelectedSection->id }}">
                                <div class="text-[12px] text-[#6B7C99] italic">
                                    Môn học: {{ $preSelectedSection->subject->name }}
                                </div>
                            </div>
                        @endif

                        {{-- Chọn đề thi --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-1.5">Đề thi <span class="text-[#DC2626]">*</span></label>
                            <select name="exam_id" id="exam_id" required class="w-full border border-[#D6E2F0] rounded-lg px-3 py-2 text-[13px] focus:border-[#185FA5] focus:ring-1 focus:ring-[#E6F1FB]">
                                <option value="">-- Chọn đề thi --</option>
                                @foreach($exams as $ex)
                                    <option value="{{ $ex->id }}" 
                                        data-subject-id="{{ $ex->subject_id }}"
                                        {{ old('exam_id') == $ex->id ? 'selected' : '' }}>
                                        [{{ $ex->subject->code }}] {{ $ex->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_id') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Chọn lớp học phần bằng Checkbox (Chỉ hiện khi không có lớp chọn sẵn) --}}
                        @if(!$preSelectedSection)
                        <div id="section-selection-container" class="hidden">
                            <label class="block text-[12px] font-semibold text-[#1A3A6B] mb-3">Áp dụng cho các lớp học phần <span class="text-[#DC2626]">*</span></label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[300px] overflow-y-auto p-4 border border-[#D6E2F0] rounded-lg bg-[#F9FBFF]" id="sections-list">
                                @foreach($courseSections as $cs)
                                    <div class="section-item flex items-start gap-3 p-2 hover:bg-white rounded transition-colors border border-transparent hover:border-[#D6E2F0]" 
                                         data-subject-id="{{ $cs->subject_id }}">
                                        <input type="checkbox" 
                                               name="course_section_ids[]" 
                                               id="cs-{{ $cs->id }}" 
                                               value="{{ $cs->id }}"
                                               class="mt-0.5 rounded border-[#D6E2F0] text-[#1A3A6B] focus:ring-[#185FA5]"
                                               {{ is_array(old('course_section_ids')) && in_array($cs->id, old('course_section_ids')) ? 'checked' : '' }}>
                                        <label for="cs-{{ $cs->id }}" class="cursor-pointer">
                                            <p class="text-[13px] font-semibold text-[#1A3A6B] leading-tight">{{ $cs->name }}</p>
                                            <p class="text-[11px] text-[#6B7C99] mt-0.5">{{ $cs->code }}</p>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p id="no-sections-msg" class="hidden text-[13px] text-[#DC2626] italic py-4">Không tìm thấy lớp học phần nào đang học môn này.</p>
                            @error('course_section_ids') <span class="text-[11px] text-[#DC2626]">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div id="main-form-fields" class="{{ $preSelectedSection ? '' : 'opacity-50 pointer-events-none' }} space-y-4 transition-all duration-300">
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const examSelect = document.getElementById('exam_id');
            const examOptions = Array.from(examSelect.options);
            const sectionContainer = document.getElementById('section-selection-container');
            const mainFields = document.getElementById('main-form-fields');
            const sectionItems = document.querySelectorAll('.section-item');
            const noSectionsMsg = document.getElementById('no-sections-msg');
            const sectionsList = document.getElementById('sections-list');

            // Lấy subject_id của lớp được chọn sẵn (nếu có)
            const preSelectedSubjectId = @json($preSelectedSection ? $preSelectedSection->subject_id : null);

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

            // Nếu đi từ trang chi tiết lớp, lọc danh sách Đề thi ngay lập tức
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
        });
    </script>
</x-app-layout>
