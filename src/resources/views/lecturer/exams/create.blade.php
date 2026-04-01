<x-app-layout>
    @php
    $quickQuestionTypes = \App\Models\QuestionType::query()
    ->active()
    ->orderedForQuestionBank()
    ->get(['id', 'name', 'code']);
    @endphp
    <style>
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

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-ghost {
            background: transparent;
            color: #1A3A6B;
            border: 1.5px solid #D6E2F0;
        }

        .btn-ghost:hover {
            background: #F4F7FC;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
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

        .availability-hint {
            font-size: 11px;
            color: #6B7C99;
            margin-top: 2px;
        }

        .availability-hint.warn {
            color: #DC2626;
            font-weight: 600;
        }

        .preset-btn {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 6px;
            border: 1.5px solid #D6E2F0;
            background: #F8FAFD;
            color: #1A3A6B;
            cursor: pointer;
            transition: all .15s;
            font-family: inherit;
            font-weight: 500;
        }

        .preset-btn:hover {
            background: #E6F1FB;
            border-color: #185FA5;
        }

        .preset-btn.active {
            background: #1A3A6B;
            color: #fff;
            border-color: #1A3A6B;
        }

        .q-item {
            border: 1px solid #EBF2FA;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 8px;
            transition: all .15s;
            background: #fff;
        }

        .q-item:hover {
            border-color: #D6E2F0;
            background: #FCFDFE;
        }

        .q-item.selected {
            border-color: #185FA5;
            background: #F0F7FF;
        }

        .q-preview {
            background: #F8FAFD;
            border-top: 1px solid #EBF2FA;
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 0 0 6px 6px;
        }

        .q-option {
            padding: 6px 10px;
            margin: 4px 0;
            border-radius: 4px;
            font-size: 12.5px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .q-option.correct {
            background: #ECFDF5;
            border: 1px solid #6EE7B7;
        }

        .q-option.incorrect {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
        }

        .q-option-label {
            font-weight: 700;
            min-width: 20px;
            color: #1A3A6B;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar .ca-input,
        .filter-bar .ca-select {
            width: auto;
            min-width: 150px;
            flex: 1;
        }

        .filter-bar .search-input {
            flex: 2;
            min-width: 200px;
        }

        .load-more-btn {
            width: 100%;
            padding: 10px;
            text-align: center;
            background: #F4F7FC;
            border: 1.5px dashed #D6E2F0;
            border-radius: 8px;
            color: #185FA5;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .load-more-btn:hover {
            background: #E6F1FB;
            border-color: #185FA5;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #D6E2F0;
            border-top-color: #1A3A6B;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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

            <form method="POST" action="{{ route('lecturer.exams.store') }}" id="exam-form">
                @csrf
                <input type="hidden" name="creation_mode" id="creation_mode" value="{{ old('creation_mode', 'manual') }}">
                {{-- Hidden container for selected question IDs (tab thủ công) --}}
                <div id="selected-questions-container"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- ═══ CỘT TRÁI: Cấu hình chung ═══ --}}
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
                                        <select id="subject_id" name="subject_id" class="ca-select @error('subject_id') error @enderror" required>
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
                                <div class="grid grid-cols-1 gap-4 mt-4">
                                    <div>
                                        <label for="multiple_choice_scoring_method" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Chấm điểm Chọn nhiều (Multiple Choice) <span class="text-[#DC2626]">*</span></label>
                                        <select id="multiple_choice_scoring_method" name="multiple_choice_scoring_method" class="ca-select @error('multiple_choice_scoring_method') error @enderror" required>
                                            <option value="all_or_nothing" {{ old('multiple_choice_scoring_method', 'all_or_nothing') === 'all_or_nothing' ? 'selected' : '' }}>Tuyệt đối (All or Nothing - Khuyên dùng)</option>
                                            <option value="partial_credit" {{ old('multiple_choice_scoring_method') === 'partial_credit' ? 'selected' : '' }}>Theo phần (Partial Credit)</option>
                                        </select>
                                        @error('multiple_choice_scoring_method') <span class="text-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ca-card">
                            <h3 class="text-[14px] font-bold text-[#1A3A6B] mb-4 uppercase tracking-wider">Cấu hình thời gian</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-start gap-3 cursor-pointer group">
                                        <input type="hidden" name="allow_late_entrance" value="0">
                                        <input type="checkbox" name="allow_late_entrance" id="allow_late_entrance" value="1"
                                            class="mt-0.5 rounded border-[#D6E2F0] text-[#185FA5] shadow-sm focus:ring-[#E6F1FB] w-4 h-4 transition-colors group-hover:border-[#185FA5]"
                                            {{ old('allow_late_entrance', true) ? 'checked' : '' }} onchange="toggleLateSettings()">
                                        <div><span class="block text-[13px] font-semibold text-[#1A3A6B]">Cho phép vào thi muộn</span></div>
                                    </label>
                                </div>
                                <div id="late_settings" class="pl-7 space-y-4 {{ old('allow_late_entrance', true) ? '' : 'hidden' }}">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="late_entrance_limit_minutes" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Giới hạn muộn (Phút)</label>
                                            <input id="late_entrance_limit_minutes" type="number" name="late_entrance_limit_minutes" value="{{ old('late_entrance_limit_minutes', 15) }}" class="ca-input @error('late_entrance_limit_minutes') error @enderror" />
                                            @error('late_entrance_limit_minutes') <span class="text-error">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="late_entrance_behavior" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Chế độ <span class="text-[#DC2626]">*</span></label>
                                            <select id="late_entrance_behavior" name="late_entrance_behavior" class="ca-select @error('late_entrance_behavior') error @enderror">
                                                <option value="fixed_end" {{ old('late_entrance_behavior', 'fixed_end') === 'fixed_end' ? 'selected' : '' }}>Thu bài đúng giờ</option>
                                                <option value="flexible_duration" {{ old('late_entrance_behavior') === 'flexible_duration' ? 'selected' : '' }}>Làm đủ thời gian</option>
                                            </select>
                                            @error('late_entrance_behavior') <span class="text-error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="min_duration_before_submit" class="block text-[12px] font-semibold text-[#1A3A6B] mb-1">Thời gian làm tối thiểu trước khi nộp (Phút)</label>
                                    <input id="min_duration_before_submit" type="number" name="min_duration_before_submit" value="{{ old('min_duration_before_submit', 0) }}" class="ca-input @error('min_duration_before_submit') error @enderror" />
                                    <p class="text-[11px] text-[#6B7C99] mt-1">Để 0 nếu không giới hạn.</p>
                                    @error('min_duration_before_submit') <span class="text-error">{{ $message }}</span> @enderror
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

                    {{-- ═══ CỘT PHẢI: Chọn câu hỏi / Ma trận ═══ --}}
                    <div class="lg:col-span-8 flex flex-col">
                        {{-- Tab toggle --}}
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

                        {{-- ═══ TAB 1: CHỌN THỦ CÔNG (AJAX) ═══ --}}
                        @include('lecturer.exams._partials.manual-tab')

                        {{-- ═══ TAB 2: SINH TỪ MA TRẬN ═══ --}}
                        @include('lecturer.exams._partials.matrix-tab')
                    </div>
                </div>
            </form>

            {{-- Modal thêm câu hỏi nhanh --}}
            @include('lecturer.exams._partials.quick-question-modal')

            {{-- Data injection --}}
            <script id="chapters-data" type="application/json">
                @json($chapters)
            </script>
            <script id="difficulties-data" type="application/json">
                @json($difficulties)
            </script>
            <script id="availability-data" type="application/json">
                @json($availabilityMap)
            </script>

            @php
            $createFormContext = [
            'selectedQuestionIds' => array_values(array_map('intval', old('question_ids', []))),
            'initialMode' => old('creation_mode', 'manual'),
            'initialMatrixRows' => old('matrix', []),
            ];

            $examFormEndpoints = [
            'questionsUrl' => route('lecturer.api.exam-form.questions'),
            'quickQuestionUrl' => route('lecturer.api.exam-form.quick-question'),
            'csrfToken' => csrf_token(),
            ];
            @endphp
            <script id="exam-form-endpoints-data" type="application/json">
                @json($examFormEndpoints)
            </script>
            <script id="exam-form-context-data" type="application/json">
                @json($createFormContext)
            </script>

            {{-- Core JS --}}
            @include('lecturer.exams._partials.create-scripts')
        </div>
    </div>
</x-app-layout>