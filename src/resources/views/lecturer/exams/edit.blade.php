<x-app-layout>
    <style>
        /* THAM KHAO STYLES */
        .ds-section {
            margin-bottom: 36px;
        }

        .ds-label {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6B7C99;
            margin: 0 0 14px;
        }

        .ca-card {
            background: #fff;
            border: 0.5px solid #D6E2F0;
            border-radius: 10px;
            padding: 16px;
        }

        .ca-card-accent {
            border-top: 3px solid #1A3A6B;
        }

        .ca-input,
        .ca-select {
            border: 1.5px solid #D6E2F0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            color: #1A3A6B;
            background: #fff;
            font-family: inherit;
            outline: none;
            width: 100%;
            transition: all 0.2s ease;
        }

        .ca-input:focus,
        .ca-select:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px #E6F1FB;
        }

        .ca-input.error {
            border-color: #DC2626;
            background: #FEF2F2;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: opacity .15s;
        }

        .btn-primary {
            background: #1A3A6B;
            color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-ghost {
            background: transparent;
            color: #1A3A6B;
            border: 1.5px solid #D6E2F0;
        }

        .btn-ghost:hover {
            background: #F4F7FC;
        }

        .ca-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .ca-table thead th {
            background: #F4F7FC;
            color: #1A3A6B;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1.5px solid #D6E2F0;
            border-top: 1.5px solid #D6E2F0;
            font-size: 12px;
        }

        .ca-table tbody td {
            padding: 12px;
            color: #374151;
            border-bottom: 1px solid #EBF2FA;
            background: #fff;
        }

        .ca-table tbody tr:hover td {
            background: #F8FAFD;
        }

        .ca-table thead th:first-child {
            border-top-left-radius: 8px;
            border-left: 1.5px solid #D6E2F0;
        }

        .ca-table thead th:last-child {
            border-top-right-radius: 8px;
            border-right: 1.5px solid #D6E2F0;
        }

        .ca-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
            border-left: 1.5px solid #D6E2F0;
            border-bottom: 1.5px solid #D6E2F0;
        }

        .ca-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
            border-right: 1.5px solid #D6E2F0;
            border-bottom: 1.5px solid #D6E2F0;
        }

        .ca-table tbody td:first-child {
            border-left: 1.5px solid #D6E2F0;
        }

        .ca-table tbody td:last-child {
            border-right: 1.5px solid #D6E2F0;
        }

        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .s-upcoming {
            background: #EBF2FA;
            color: #1A3A6B;
        }

        .text-error {
            color: #DC2626;
            font-size: 11px;
            margin-top: 4px;
            display: block;
        }
    </style>

    <div class="py-8 bg-[#F8FAFD] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Sửa Đề Thi</h2>
                    <div class="flex items-center gap-2 text-[13px] text-[#6B7C99]">
                        <a href="{{ route('lecturer.exams.show', $exam->id) }}" class="text-[#185FA5] hover:underline">{{ $exam->title }}</a>
                        <span class="text-[#B0BECE]">•</span>
                        <span>Cập nhật thông tin</span>
                    </div>
                </div>
                @if(! $exam->canEditStructure())
                <div class="badge s-upcoming py-1.5 border border-amber-300 bg-amber-50 text-amber-700">
                    ⚠️ Đã có sinh viên thi — chỉ sửa được tên & mô tả
                </div>
                @endif
            </div>

            <form method="POST" action="{{ route('lecturer.exams.update', $exam->id) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Cột Trái: Cấu hình chung -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="ca-card ca-card-accent">
                            <h3 class="text-[14px] font-bold text-[#1A3A6B] mb-4 uppercase tracking-wider">Thông tin kỳ thi</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Tên bài kiểm tra <span class="text-[#DC2626]">*</span></label>
                                    <input id="title" type="text" name="title" value="{{ old('title', $exam->title) }}" required autofocus class="ca-input @error('title') error @enderror" />
                                    @error('title') <span class="text-error">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Hướng dẫn làm bài</label>
                                    <textarea id="description" name="description" class="ca-input" rows="3">{{ old('description', $exam->description) }}</textarea>
                                    @error('description') <span class="text-error">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4 {{ $exam->canEditStructure() ? '' : 'opacity-50 pointer-events-none' }}">
                                    <div>
                                        <label for="subject_id" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Môn học <span class="text-[#DC2626]">*</span></label>
                                        <select id="subject_id" name="subject_id" class="ca-select @error('subject_id') error @enderror" required onchange="onSubjectChange()">
                                            <option value="">-- Chọn môn học --</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="duration_minutes" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Thời gian (Phút) <span class="text-[#DC2626]">*</span></label>
                                        <input id="duration_minutes" type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required class="ca-input @error('duration_minutes') error @enderror" />
                                        @error('duration_minutes') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 {{ $exam->canEditStructure() ? '' : 'opacity-50 pointer-events-none' }}">
                                    <div>
                                        <label for="exam_type" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Loại đề thi <span class="text-[#DC2626]">*</span></label>
                                        @php $selectedExamType = old('exam_type', $exam->exam_type?->value); @endphp
                                        <select id="exam_type" name="exam_type" class="ca-select @error('exam_type') error @enderror" required>
                                            <option value="official" {{ $selectedExamType === 'official' ? 'selected' : '' }}>Chính thức</option>
                                            <option value="practice" {{ $selectedExamType === 'practice' ? 'selected' : '' }}>Luyện tập</option>
                                        </select>
                                        @error('exam_type') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ca-card">
                            <h3 class="text-[14px] font-bold text-[#1A3A6B] mb-4 uppercase tracking-wider">Hiển thị kết quả</h3>
                            <div class="space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="hidden" name="show_score_after_submit" value="0">
                                    <input type="checkbox" name="show_score_after_submit" value="1"
                                        class="mt-0.5 rounded border-[#D6E2F0] text-[#185FA5] shadow-sm focus:ring-[#E6F1FB] w-4 h-4 transition-colors group-hover:border-[#185FA5]"
                                        {{ old('show_score_after_submit', $exam->show_score_after_submit) ? 'checked' : '' }}>
                                    <div>
                                        <span class="block text-[13px] font-semibold text-[#1A3A6B]">Cho xem điểm tổng</span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="hidden" name="show_answers_after_submit" value="0">
                                    <input type="checkbox" name="show_answers_after_submit" value="1"
                                        class="mt-0.5 rounded border-[#D6E2F0] text-[#185FA5] shadow-sm focus:ring-[#E6F1FB] w-4 h-4 transition-colors group-hover:border-[#185FA5]"
                                        {{ old('show_answers_after_submit', $exam->show_answers_after_submit) ? 'checked' : '' }}>
                                    <div>
                                        <span class="block text-[13px] font-semibold text-[#1A3A6B]">Xem chi tiết đáp án</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-2">
                            <a href="{{ route('lecturer.exams.show', $exam->id) }}" class="btn btn-ghost flex-1">Hủy</a>
                            <button type="submit" class="btn btn-primary flex-1">Cập nhật</button>
                        </div>
                    </div>

                    <!-- Cột Phải: Chọn câu hỏi -->
                    <div class="lg:col-span-8 flex flex-col {{ $exam->canEditStructure() ? '' : 'opacity-70 grayscale-[0.5]' }}">
                        <div class="bg-white rounded-[10px] border-[0.5px] border-[#D6E2F0] flex flex-col h-full shadow-sm overflow-hidden">
                            <div class="p-5 border-b border-[#EBF2FA] flex justify-between items-center bg-[#F8FAFD]">
                                <div>
                                    <h3 class="text-[16px] font-bold text-[#1A3A6B]">Danh sách câu hỏi</h3>
                                    <p class="text-[12.5px] text-[#6B7C99] mt-1">Sửa đổi tập hợp câu hỏi của đề thi.</p>
                                </div>
                                <div class="badge s-ongoing">Đã chọn: <span id="selectedCount">0</span>/{{ count($questions) }}</div>
                            </div>

                            @if(! $exam->canEditStructure())
                            <div class="px-5 py-3 bg-amber-50 text-amber-800 text-[12px] font-medium border-b border-amber-200">
                                Vì đề thi đã có lượt làm bài, bạn không thể thay đổi danh sách câu hỏi.
                            </div>
                            @endif

                            <div class="flex-1 overflow-y-auto px-5 py-4 max-h-[750px] min-h-[400px]">
                                <table class="ca-table text-left">
                                    <thead class="sticky top-0 shadow-sm z-10 bg-[#F4F7FC]">
                                        <tr>
                                            <th class="w-12 text-center rounded-tl-[8px]">
                                                <input type="checkbox" id="selectAll" class="rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#E6F1FB] w-4 h-4 cursor-pointer" {{ $exam->canEditStructure() ? '' : 'disabled' }}>
                                            </th>
                                            <th class="rounded-tr-[8px]">Nội dung câu hỏi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($questions as $question)
                                        <tr class="question-row group cursor-pointer hover:bg-[#F8FAFD] transition-colors" data-subject="{{ $question->subject_id }}" @if($exam->canEditStructure()) onclick="toggleCheckbox(this, event)" @endif>
                                            <td class="text-center w-12">
                                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}"
                                                    class="question-checkbox rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#E6F1FB] w-4 h-4 cursor-pointer"
                                                    onclick="event.stopPropagation()"
                                                    {{ in_array($question->id, $selectedQuestionIds) ? 'checked' : '' }}
                                                    {{ $exam->canEditStructure() ? '' : 'disabled' }}>
                                            </td>
                                            <td>
                                                <div class="text-[13.5px] text-[#374151] line-clamp-2">
                                                    {!! strip_tags($question->content) !!}
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <script>
                function toggleCheckbox(row, event) {
                    if (event.target.tagName.toLowerCase() === 'input') return;
                    const checkbox = row.querySelector('.question-checkbox');
                    if (checkbox && !checkbox.disabled) {
                        checkbox.checked = !checkbox.checked;
                        updateCounter();
                        checkSelectAllState();
                    }
                }

                function updateCounter() {
                    const count = document.querySelectorAll('.question-checkbox:checked').length;
                    const counterEl = document.getElementById('selectedCount');
                    if (counterEl) counterEl.textContent = count;
                }

                function checkSelectAllState() {
                    const selectAll = document.getElementById('selectAll');
                    const checkboxes = document.querySelectorAll('.question-checkbox:not(:disabled)');
                    if (selectAll && checkboxes.length > 0) {
                        selectAll.checked = document.querySelectorAll('.question-checkbox:checked').length === document.querySelectorAll('.question-checkbox').length;
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const selectAll = document.getElementById('selectAll');
                    const checkboxes = document.querySelectorAll('.question-checkbox');

                    if (selectAll) {
                        selectAll.addEventListener('change', function() {
                            checkboxes.forEach(cb => {
                                if (!cb.disabled) cb.checked = selectAll.checked;
                            });
                            updateCounter();
                        });
                    }

                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', function() {
                            checkSelectAllState();
                            updateCounter();
                        });
                    });

                    updateCounter();
                    checkSelectAllState();
                    onSubjectChange(); // Filter questions on initial load
                });

                function onSubjectChange() {
                    const subjectId = document.getElementById('subject_id')?.value;
                    let visibleCount = 0;
                    document.querySelectorAll('.question-row').forEach(row => {
                        if(!subjectId || row.dataset.subject == subjectId) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                            const cb = row.querySelector('.question-checkbox');
                            if(cb && !cb.disabled) cb.checked = false; // uncheck if hidden and not disabled
                        }
                    });
                    
                    updateCounter();
                    checkSelectAllState();
                    
                    // Update the denominator
                    const selectedCountEl = document.getElementById('selectedCount');
                    if (selectedCountEl) {
                        selectedCountEl.parentElement.innerHTML = `Đã chọn: <span id="selectedCount">${document.querySelectorAll('.question-checkbox:checked').length}</span>/${visibleCount}`;
                    }
                }
            </script>
        </div>
    </div>
</x-app-layout>