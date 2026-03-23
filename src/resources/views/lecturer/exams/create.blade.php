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
                    <h2 class="text-[22px] font-bold text-[#1A3A6B] mb-1">Tạo Bài Kiểm Tra Mới</h2>
                    <div class="flex items-center gap-2 text-[13px] text-[#6B7C99]">
                        <a href="{{ route('lecturer.exams.index') }}" class="text-[#185FA5] hover:underline">Quản lý Đề thi</a>
                        <span class="text-[#B0BECE]">•</span>
                        <span>Đề thi mới</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('lecturer.exams.store') }}">
                @csrf
                <input type="hidden" name="creation_mode" id="creation_mode" value="{{ old('creation_mode', 'manual') }}">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Cột Trái: Cấu hình chung (Lớn hơn chút) -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="ca-card ca-card-accent">
                            <h3 class="text-[14px] font-bold text-[#1A3A6B] mb-4 uppercase tracking-wider">Thông tin kỳ thi</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Tên bài kiểm tra <span class="text-[#DC2626]">*</span></label>
                                    <input id="title" type="text" name="title" value="{{ old('title') }}" required autofocus class="ca-input @error('title') error @enderror" placeholder="VD: Thi giữa kỳ" />
                                    @error('title') <span class="text-error">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Hướng dẫn làm bài</label>
                                    <textarea id="description" name="description" class="ca-input" rows="3" placeholder="Ghi chú cho sinh viên...">{{ old('description') }}</textarea>
                                    @error('description') <span class="text-error">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="subject_id" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Môn học <span class="text-[#DC2626]">*</span></label>
                                        <select id="subject_id" name="subject_id" class="ca-select @error('subject_id') error @enderror" required onchange="onSubjectChange()">
                                            <option value="">-- Chọn môn học --</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="duration_minutes" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Thời gian (Phút) <span class="text-[#DC2626]">*</span></label>
                                        <input id="duration_minutes" type="number" name="duration_minutes" value="{{ old('duration_minutes', 45) }}" required class="ca-input @error('duration_minutes') error @enderror" />
                                        @error('duration_minutes') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="exam_type" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Loại đề thi <span class="text-[#DC2626]">*</span></label>
                                        <select id="exam_type" name="exam_type" class="ca-select @error('exam_type') error @enderror" required>
                                            <option value="official" {{ old('exam_type', 'official') === 'official' ? 'selected' : '' }}>Chính thức</option>
                                            <option value="practice" {{ old('exam_type') === 'practice' ? 'selected' : '' }}>Luyện tập</option>
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
                                        {{ old('show_score_after_submit', true) ? 'checked' : '' }}>
                                    <div>
                                        <span class="block text-[13px] font-semibold text-[#1A3A6B]">Cho xem điểm tổng</span>
                                        <p class="text-[11.5px] text-[#6B7C99] leading-tight mt-1">Sinh viên thấy điểm ngay khi nộp</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="hidden" name="show_answers_after_submit" value="0">
                                    <input type="checkbox" name="show_answers_after_submit" value="1"
                                        class="mt-0.5 rounded border-[#D6E2F0] text-[#185FA5] shadow-sm focus:ring-[#E6F1FB] w-4 h-4 transition-colors group-hover:border-[#185FA5]"
                                        {{ old('show_answers_after_submit', false) ? 'checked' : '' }}>
                                    <div>
                                        <span class="block text-[13px] font-semibold text-[#1A3A6B]">Xem chi tiết đáp án</span>
                                        <p class="text-[11.5px] text-[#6B7C99] leading-tight mt-1">Sinh viên thấy đúng/sai từng câu</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Chọn câu hỏi / Ma trận -->
                    <div class="lg:col-span-8 flex flex-col">
                        <!-- Tab toggle -->
                        <div class="flex mb-4 border-b border-[#D6E2F0]">
                            <button type="button" id="tab-manual" onclick="switchCreationMode('manual')" class="px-5 py-2.5 text-[13px] font-semibold border-b-2 transition-colors border-[#1A3A6B] text-[#1A3A6B]">
                                Chọn thủ công
                            </button>
                            <button type="button" id="tab-matrix" onclick="switchCreationMode('matrix')" class="px-5 py-2.5 text-[13px] font-semibold border-b-2 transition-colors border-transparent text-[#6B7C99] hover:text-[#1A3A6B]">
                                Sinh từ ma trận
                            </button>
                        </div>

                        @error('creation_mode')
                        <div class="mb-3 px-4 py-2 bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] text-[13px] rounded-lg">{{ $message }}</div>
                        @enderror

                        <!-- TAB 1: Chọn thủ công -->
                        <div id="panel-manual" class="flex-1 flex flex-col">
                            <div class="bg-white rounded-[10px] border-[0.5px] border-[#D6E2F0] flex flex-col h-full shadow-sm">
                                <div class="p-5 border-b border-[#EBF2FA] flex justify-between items-center bg-[#F8FAFD] rounded-t-[10px]">
                                    <div>
                                        <h3 class="text-[16px] font-bold text-[#1A3A6B]">Chọn câu hỏi cho đề thi</h3>
                                        <p class="text-[12.5px] text-[#6B7C99] mt-1">Tick chọn những câu hỏi muốn sử dụng từ ngân hàng môn học.</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="badge s-upcoming">Tổng: <span id="selectedCount">0</span>/{{ count($questions) }} đã chọn</div>
                                        <button type="submit" class="btn btn-primary ml-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Lưu Đề Thi
                                        </button>
                                    </div>
                                </div>

                                @error('question_ids')
                                <div class="px-5 py-3 bg-[#FEF2F2] border-b border-[#FCA5A5] text-[#991B1B] text-[13px] font-medium">
                                    {{ $message }}
                                </div>
                                @enderror

                                <div class="flex-1 overflow-y-auto px-5 py-4 max-h-[700px] min-h-[400px]">
                                    @if($questions->isEmpty())
                                    <div class="h-full flex flex-col items-center justify-center text-center py-12">
                                        <div class="w-16 h-16 bg-[#F4F7FC] rounded-full flex items-center justify-center mb-4 border border-[#D6E2F0]">
                                            <svg class="w-8 h-8 text-[#6B7C99]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-[15px] font-bold text-[#1A3A6B] mb-2">Ngân hàng trống</h4>
                                        <p class="text-[13px] text-[#6B7C99] max-w-sm">Môn học này hiện chưa có câu hỏi nào được phê duyệt.</p>
                                    </div>
                                    @else
                                    <table class="ca-table text-left">
                                        <thead class="sticky top-0 shadow-sm z-10 bg-[#F4F7FC]">
                                            <tr>
                                                <th class="w-12 text-center rounded-tl-[8px]">
                                                    <input type="checkbox" id="selectAll" class="rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#E6F1FB] w-4 h-4 cursor-pointer">
                                                </th>
                                                <th class="rounded-tr-[8px]">Nội dung câu hỏi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($questions as $question)
                                            <tr class="question-row group cursor-pointer hover:bg-[#F8FAFD] transition-colors" data-subject="{{ $question->subject_id }}" onclick="toggleCheckbox(this, event)">
                                                <td class="text-center w-12">
                                                    <input type="checkbox" name="question_ids[]" value="{{ $question->id }}"
                                                        class="question-checkbox rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#E6F1FB] w-4 h-4 cursor-pointer"
                                                        onclick="event.stopPropagation()"
                                                        {{ (is_array(old('question_ids')) && in_array($question->id, old('question_ids'))) ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    <div class="text-[13.5px] text-[#374151] line-clamp-3">
                                                        {!! strip_tags($question->content) !!}
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Sinh từ ma trận -->
                        <div id="panel-matrix" class="flex-1 flex-col" style="display: none;">
                            <div class="bg-white rounded-[10px] border-[0.5px] border-[#D6E2F0] flex flex-col shadow-sm">
                                <div class="p-5 border-b border-[#EBF2FA] flex justify-between items-center bg-[#F8FAFD] rounded-t-[10px]">
                                    <div>
                                        <h3 class="text-[16px] font-bold text-[#1A3A6B]">Cấu trúc ma trận đề thi</h3>
                                        <p class="text-[12.5px] text-[#6B7C99] mt-1">Định nghĩa số câu theo chương và độ khó, hệ thống sẽ tự động chọn ngẫu nhiên.</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="badge s-upcoming">Tổng: <span id="matrixTotalQuestions">0</span> câu • <span id="matrixTotalPoints">0</span> điểm</div>
                                        <button type="submit" class="btn btn-primary ml-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Tạo Đề Từ Ma Trận
                                        </button>
                                    </div>
                                </div>

                                @error('matrix')
                                <div class="px-5 py-3 bg-[#FEF2F2] border-b border-[#FCA5A5] text-[#991B1B] text-[13px] font-medium">{{ $message }}</div>
                                @enderror

                                <div class="p-5">
                                    <table class="ca-table text-left" id="matrix-table">
                                        <thead>
                                            <tr>
                                                <th>Chương</th>
                                                <th>Độ khó</th>
                                                <th>Số câu</th>
                                                <th>Điểm/câu</th>
                                                <th class="w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="matrix-body">
                                            {{-- JS sẽ thêm rows --}}
                                        </tbody>
                                    </table>

                                    <button type="button" onclick="addMatrixRow()" class="btn btn-ghost mt-4 w-full">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Thêm hàng ma trận
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <script id="chapters-data" type="application/json">
                @json($chapters)
            </script>

            <script>
                const chapters = JSON.parse(document.getElementById('chapters-data')?.textContent || '[]');
                let matrixRowIndex = 0;

                // === Tab switching ===
                function switchCreationMode(mode) {
                    document.getElementById('creation_mode').value = mode;
                    document.getElementById('panel-manual').style.display = mode === 'manual' ? 'flex' : 'none';
                    document.getElementById('panel-matrix').style.display = mode === 'matrix' ? 'flex' : 'none';

                    const tabManual = document.getElementById('tab-manual');
                    const tabMatrix = document.getElementById('tab-matrix');
                    if (mode === 'manual') {
                        tabManual.classList.add('border-[#1A3A6B]', 'text-[#1A3A6B]');
                        tabManual.classList.remove('border-transparent', 'text-[#6B7C99]');
                        tabMatrix.classList.remove('border-[#1A3A6B]', 'text-[#1A3A6B]');
                        tabMatrix.classList.add('border-transparent', 'text-[#6B7C99]');
                    } else {
                        tabMatrix.classList.add('border-[#1A3A6B]', 'text-[#1A3A6B]');
                        tabMatrix.classList.remove('border-transparent', 'text-[#6B7C99]');
                        tabManual.classList.remove('border-[#1A3A6B]', 'text-[#1A3A6B]');
                        tabManual.classList.add('border-transparent', 'text-[#6B7C99]');
                    }
                }

                // === Matrix row management ===
                function addMatrixRow() {
                    const i = matrixRowIndex++;
                    const subjectId = document.getElementById('subject_id')?.value;
                    const filteredChapters = chapters.filter(c => c.subject_id == subjectId);
                    const chapterOptions = filteredChapters.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <select name="matrix[${i}][chapter_id]" class="ca-select" style="min-width:140px">
                                <option value="">Tất cả</option>
                                ${chapterOptions}
                            </select>
                        </td>
                        <td>
                            <select name="matrix[${i}][difficulty]" class="ca-select" required style="min-width:120px">
                                <option value="remember">Nhớ</option>
                                <option value="understand">Hiểu</option>
                                <option value="apply">Áp dụng</option>
                                <option value="analyze">Phân tích</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="matrix[${i}][question_count]" class="ca-input" value="5" min="1" required style="width:70px" oninput="updateMatrixSummary()">
                        </td>
                        <td>
                            <input type="number" name="matrix[${i}][points_each]" class="ca-input" value="1.00" min="0.01" step="0.01" style="width:80px" oninput="updateMatrixSummary()">
                        </td>
                        <td class="text-center">
                            <button type="button" onclick="this.closest('tr').remove(); updateMatrixSummary();" class="text-[#DC2626] hover:text-[#991B1B] text-[14px] font-bold">&times;</button>
                        </td>
                    `;
                    document.getElementById('matrix-body').appendChild(row);
                    updateMatrixSummary();
                }

                function updateMatrixSummary() {
                    let totalQ = 0,
                        totalP = 0;
                    document.querySelectorAll('#matrix-body tr').forEach(row => {
                        const count = parseInt(row.querySelector('input[name*="question_count"]')?.value || 0);
                        const points = parseFloat(row.querySelector('input[name*="points_each"]')?.value || 1);
                        totalQ += count;
                        totalP += count * points;
                    });
                    document.getElementById('matrixTotalQuestions').textContent = totalQ;
                    document.getElementById('matrixTotalPoints').textContent = totalP.toFixed(2);
                }

                // === Manual mode: checkbox logic ===
                function toggleCheckbox(row, event) {
                    if (event.target.tagName.toLowerCase() === 'input') return;
                    const checkbox = row.querySelector('.question-checkbox');
                    if (checkbox) {
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
                    const checkboxes = document.querySelectorAll('.question-checkbox');
                    if (selectAll && checkboxes.length > 0) {
                        selectAll.checked = document.querySelectorAll('.question-checkbox:checked').length === checkboxes.length;
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    // Restore tab from old input
                    const savedMode = document.getElementById('creation_mode').value;
                    if (savedMode === 'matrix') switchCreationMode('matrix');

                    const selectAll = document.getElementById('selectAll');
                    const checkboxes = document.querySelectorAll('.question-checkbox');

                    if (selectAll) {
                        selectAll.addEventListener('change', function() {
                            checkboxes.forEach(cb => cb.checked = selectAll.checked);
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

                    // Add initial matrix row
                    if (document.getElementById('matrix-body').children.length === 0) {
                        addMatrixRow();
                    }

                    // Trigger initial subject filter
                    onSubjectChange();
                });

                function onSubjectChange() {
                    const subjectId = document.getElementById('subject_id')?.value;
                    // Filter questions
                    let visibleQuestions = 0;
                    document.querySelectorAll('.question-row').forEach(row => {
                        if(row.dataset.subject == subjectId) {
                            row.style.display = '';
                            visibleQuestions++;
                        } else {
                            row.style.display = 'none';
                            const cb = row.querySelector('.question-checkbox');
                            if(cb) cb.checked = false;
                        }
                    });
                    
                    // Reset matrix
                    document.getElementById('matrix-body').innerHTML = '';
                    addMatrixRow();
                    updateCounter();
                    checkSelectAllState();
                }
            </script>
        </div>
    </div>
</x-app-layout>