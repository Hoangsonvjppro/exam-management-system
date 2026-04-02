@php
$totalQuestions = count($questions);
@endphp
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} — Phòng thi</title>
    @vite(['resources/js/pages/student/exams-room.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .answer-textarea {
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
        }

        .answer-checkbox {
            border-radius: 4px !important;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #F4F7FC;
            color: #1A3A6B;
            overflow: hidden;
            height: 100vh;
        }

        /* ── Slim Top Bar ── */
        .zen-bar {
            height: 44px;
            background: #1A3A6B;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            flex-shrink: 0;
        }

        .zen-bar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zen-bar-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #34D399;
        }

        .zen-bar-title {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 400px;
        }

        .zen-bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .zen-bar-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #2A5298;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #93BAE8;
            font-size: 10px;
            font-weight: 700;
            border: 1.5px solid #3A6AC8;
            text-transform: uppercase;
        }

        /* ── Main Layout: 2 Columns ── */
        .zen-layout {
            display: flex;
            height: calc(100vh - 44px);
            overflow: hidden;
        }

        /* Left Column — Questions (large) */
        .zen-left {
            flex: 1;
            min-width: 0;
            overflow-y: auto;
            padding: 32px 48px;
        }

        /* Right Column — Controls (fixed, narrow) */
        .zen-right {
            width: 300px;
            flex-shrink: 0;
            background: #fff;
            border-left: 1px solid #D6E2F0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        /* ── Timer ── */
        .timer-block {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #D6E2F0;
            background: #F8FAFD;
        }

        .timer-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6B7C99;
            margin-bottom: 6px;
        }

        .timer-display {
            font-size: 42px;
            font-weight: 700;
            color: #1A3A6B;
            letter-spacing: 2px;
            font-variant-numeric: tabular-nums;
            transition: color 0.3s;
        }

        .timer-display.urgent {
            color: #DC2626;
        }

        .timer-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 20px;
            background: #D1FAE5;
            color: #065F46;
            margin-top: 8px;
        }

        .timer-status-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #10B981;
        }

        /* ── Question Map ── */
        .qmap-block {
            padding: 16px 20px;
            flex: 1;
            overflow-y: auto;
        }

        .qmap-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .qmap-title {
            font-size: 13px;
            font-weight: 700;
            color: #1A3A6B;
        }

        .qmap-count {
            font-size: 11px;
            color: #6B7C99;
        }

        .qmap-count strong {
            color: #1A3A6B;
        }

        .qmap-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .q-btn {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            border: 1.5px solid #D6E2F0;
            background: #fff;
            color: #6B7C99;
            cursor: pointer;
            transition: all 0.15s;
            position: relative;
            font-family: inherit;
        }

        .q-btn:hover {
            border-color: #185FA5;
            color: #1A3A6B;
            background: #F8FAFD;
        }

        .q-btn.answered {
            background: #E1F5EE;
            color: #085041;
            border-color: #1D9E75;
        }

        .q-btn.current {
            background: #1A3A6B;
            color: #fff;
            border-color: #1A3A6B;
        }

        .q-btn.flagged {
            border-color: #D97706;
        }

        .q-btn.flagged::after {
            content: '';
            position: absolute;
            top: -1px;
            right: -1px;
            border-top: 12px solid #D97706;
            border-left: 12px solid transparent;
            border-top-right-radius: 5px;
        }

        .qmap-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #D6E2F0;
            font-size: 11px;
            color: #6B7C99;
        }

        .qmap-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-box {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            border: 1.5px solid #D6E2F0;
            background: #fff;
        }

        .legend-box.answered {
            border-color: #1D9E75;
            background: #E1F5EE;
        }

        .legend-box.current {
            border-color: #1A3A6B;
            background: #1A3A6B;
        }

        .legend-box.flagged {
            border-color: #D97706;
        }

        /* ── Submit Button ── */
        .submit-block {
            padding: 16px 20px;
            border-top: 1px solid #D6E2F0;
            background: #F8FAFD;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #1A3A6B;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-submit:hover {
            background: #0F2A53;
        }

        .submit-note {
            text-align: center;
            font-size: 11px;
            color: #6B7C99;
            margin-top: 8px;
        }

        /* ── Question Content ── */
        .question-container {
            display: none;
        }

        .question-container.active {
            display: flex;
            flex-direction: column;
            animation: fadeSlide 0.25s ease-out;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 28px;
            border-bottom: 1px solid #D6E2F0;
        }

        .question-number {
            font-size: 22px;
            font-weight: 700;
            color: #1A3A6B;
        }

        .question-badge {
            font-size: 12px;
            color: #6B7C99;
            font-weight: 500;
            background: #F4F7FC;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .btn-flag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            color: #6B7C99;
            background: transparent;
            border: 1.5px solid #D6E2F0;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s;
        }

        .btn-flag:hover {
            border-color: #D97706;
            color: #D97706;
        }

        .btn-flag.active {
            border-color: #D97706;
            color: #D97706;
            background: #FEF3C7;
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            line-height: 1.7;
            color: #1A3A6B;
            margin-bottom: 32px;
        }

        /* ── Option Cards ── */
        .option-card {
            border: 1.5px solid #D6E2F0;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            cursor: pointer;
            background: #fff;
            transition: all 0.2s;
            min-height: 52px;
        }

        .option-card:hover {
            border-color: #185FA5;
            background: #F8FAFD;
        }

        .option-card.selected {
            background: #E6F1FB;
            border-color: #185FA5;
        }

        .custom-radio {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 1.5px solid #D6E2F0;
            border-radius: 50%;
            margin-right: 14px;
            outline: none;
            flex-shrink: 0;
            cursor: pointer;
            background: #fff;
            transition: all 0.15s;
        }

        .option-card:hover .custom-radio {
            border-color: #185FA5;
        }

        .custom-radio:checked {
            border-color: #185FA5;
            border-width: 5px;
        }

        .custom-radio:focus {
            box-shadow: 0 0 0 3px #E6F1FB;
            border-color: #185FA5;
        }

        .option-text {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }

        /* ── Navigation Buttons ── */
        .nav-buttons {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #D6E2F0;
        }

        .btn-nav {
            flex: 1;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s;
            border: 1.5px solid #D6E2F0;
            background: transparent;
            color: #1A3A6B;
        }

        .btn-nav:hover {
            background: #F4F7FC;
            border-color: #185FA5;
        }

        .btn-nav:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .btn-nav.primary {
            background: #1A3A6B;
            color: #fff;
            border-color: #1A3A6B;
        }

        .btn-nav.primary:hover {
            background: #0F2A53;
        }

        /* ── Toast ── */
        .toast {
            position: fixed;
            top: 56px;
            right: 20px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
            transition: all 0.3s;
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            border: 0.5px solid #059669;
            background: #D1FAE5;
            color: #065F46;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.error {
            border-color: #DC2626;
            background: #FEF2F2;
            color: #991B1B;
        }

        .toast.warning {
            border-color: #D97706;
            background: #FEF3C7;
            color: #78350F;
        }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            .zen-layout {
                flex-direction: column;
            }

            .zen-left {
                padding: 20px 16px;
                flex: 1;
            }

            .zen-right {
                width: 100%;
                border-left: none;
                border-top: 1px solid #D6E2F0;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: auto;
                max-height: 60vh;
                z-index: 50;
                border-radius: 16px 16px 0 0;
                box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.1);
                transform: translateY(calc(100% - 60px));
                transition: transform 0.3s ease;
            }

            .zen-right.expanded {
                transform: translateY(0);
            }

            .zen-right-handle {
                display: flex;
                justify-content: center;
                padding: 8px;
                cursor: pointer;
            }

            .zen-right-handle-bar {
                width: 36px;
                height: 4px;
                border-radius: 2px;
                background: #D6E2F0;
            }

            .qmap-grid {
                grid-template-columns: repeat(7, 1fr);
            }
        }

        @media (min-width: 769px) {
            .zen-right-handle {
                display: none;
            }
        }
    </style>
</head>

<body>
    {{-- ─── Slim Top Bar (Zen Mode) ─── --}}
    <div class="zen-bar">
        <div class="zen-bar-left">
            <div class="zen-bar-dot"></div>
            <span class="zen-bar-title">{{ $exam->title }}</span>
        </div>
        <div class="zen-bar-right">
            <span style="color:#93BAE8; font-size:11px; font-weight:500;">{{ $exam->subject->name ?? '' }}</span>
            <div class="zen-bar-avatar" title="{{ Auth::user()->name ?? 'SV' }}">
                {{ strtoupper(substr(Auth::user()->name ?? 'SV', 0, 2)) }}
            </div>
        </div>
    </div>

    {{-- ─── 2-Column Layout ─── --}}
    <div class="zen-layout" id="main-content">
        <div id="exam-config"
            data-total-questions="{{ $totalQuestions }}"
            data-time-left="{{ $timeLeftSeconds }}"
            data-min-submit-remaining="{{ $minSubmitRemainingSeconds }}"
            data-min-submit-duration="{{ (int) ($exam->min_duration_before_submit ?? 0) }}"
            hidden></div>

        {{-- ═══ Left Column: Questions ═══ --}}
        <div class="zen-left">
            <form id="exam-form" action="{{ route('student.exams.submit', $schedule->id) }}" method="POST">
                @csrf

                @foreach($questions as $index => $question)
                <div class="question-container {{ $index === 0 ? 'active' : '' }}" id="question-{{ $index }}" data-question-id="{{ $question->id }}">

                    <div class="question-header">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="question-number">Câu {{ $index + 1 }}</span>
                            <span class="question-badge">/ {{ $totalQuestions }}</span>
                        </div>
                        <button type="button" data-flag-index="{{ $index }}" id="flag-btn-{{ $index }}" class="btn-flag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                                <line x1="4" y1="22" x2="4" y2="15" />
                            </svg>
                            <span class="flag-label">Đặt cờ</span>
                        </button>
                    </div>

                    <div class="question-text">{!! $question->content !!}</div>

                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @php $savedAnswer = $savedAnswers[$question->id] ?? null; @endphp

                        @if($question->questionType->code === 'short_answer')
                        <div class="p-2">
                            <textarea
                                name="answers[{{ $question->id }}][text]"
                                data-question-id="{{ $question->id }}"
                                class="answer-textarea w-full p-4 border-1.5 border-[#D6E2F0] rounded-xl focus:border-[#185FA5] focus:ring-4 focus:ring-[#E6F1FB] outline-none transition-all"
                                rows="4"
                                placeholder="Nhập câu trả lời của bạn tại đây...">{{ $savedAnswer->answer_text ?? '' }}</textarea>
                        </div>
                        @else
                        @foreach($question->options as $option)
                        @php
                        $isSelected = false;
                        if ($savedAnswer) {
                        if ($question->questionType->code === 'multiple_choice') {
                        $isSelected = $savedAnswer->selectedOptions->contains('question_option_id', $option->id);
                        } else {
                        $isSelected = $savedAnswer->question_option_id == $option->id;
                        }
                        }
                        @endphp
                        <label class="option-card {{ $isSelected ? 'selected' : '' }}" data-option-index="{{ $index }}">
                            <input type="{{ $question->questionType->code === 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                name="answers[{{ $question->id }}]{{ $question->questionType->code === 'multiple_choice' ? '[]' : '' }}"
                                value="{{ $option->id }}"
                                data-question-id="{{ $question->id }}"
                                class="{{ $question->questionType->code === 'multiple_choice' ? 'answer-checkbox' : 'answer-radio' }} custom-radio"
                                {{ $isSelected ? 'checked' : '' }}
                                {{ $question->questionType->code === 'multiple_choice' ? 'style=border-radius:4px' : '' }}>
                            <span class="option-text">{!! $option->content !!}</span>
                        </label>
                        @endforeach
                        @endif
                    </div>
                </div>
                @endforeach
            </form>

            {{-- Navigation Controls --}}
            <div class="nav-buttons">
                <button type="button" data-action="prev-question" id="btn-prev" class="btn-nav">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Câu trước
                </button>
                <button type="button" data-action="next-question" id="btn-next" class="btn-nav primary">
                    Câu tiếp
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ═══ Right Column: Timer + Map + Submit ═══ --}}
        <div class="zen-right" id="zen-panel">
            {{-- Mobile handle --}}
            <div class="zen-right-handle" id="panel-handle">
                <div class="zen-right-handle-bar"></div>
            </div>

            {{-- Timer --}}
            <div class="timer-block">
                <div class="timer-label">Thời gian còn lại</div>
                <div id="countdown-timer" class="timer-display">--:--</div>
                <div class="timer-status">
                    <span class="timer-status-dot"></span>
                    Đang thi
                </div>
            </div>

            {{-- Question Map --}}
            <div class="qmap-block">
                <div class="qmap-header">
                    <span class="qmap-title">Bản đồ câu hỏi</span>
                    <span class="qmap-count">Đã làm: <strong id="answered-count">0</strong>/{{ $totalQuestions }}</span>
                </div>

                <div class="qmap-grid" id="question-navigator">
                    @foreach($questions as $index => $question)
                    <button type="button"
                        class="q-btn {{ $index === 0 ? 'current' : '' }} {{ isset($savedAnswers[$question->id]) ? 'answered' : '' }}"
                        data-question-index="{{ $index }}"
                        id="nav-btn-{{ $index }}">
                        {{ $index + 1 }}
                    </button>
                    @endforeach
                </div>

                <div class="qmap-legend">
                    <div class="qmap-legend-item">
                        <div class="legend-box"></div> Chưa làm
                    </div>
                    <div class="qmap-legend-item">
                        <div class="legend-box answered"></div> Đã làm
                    </div>
                    <div class="qmap-legend-item">
                        <div class="legend-box current"></div> Đang chọn
                    </div>
                    <div class="qmap-legend-item">
                        <div class="legend-box flagged"></div> Gắn cờ
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="submit-block">
                <button type="button" data-action="submit-exam" class="btn-submit">
                    Nộp bài thi
                </button>
                <p class="submit-note">Không thể sửa sau khi nộp</p>
                @if(($exam->min_duration_before_submit ?? 0) > 0)
                <p class="submit-note" id="min-submit-note"></p>
                @endif
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="save-toast" class="toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
            <path d="M22 4L12 14.01l-3-3" />
        </svg>
        <span id="toast-msg">Đã lưu tự động</span>
    </div>

    @php
    $flashMessage = session('warning') ?? session('error');
    $flashType = session('warning') ? 'warning' : 'error';

    $studentExamRoomConfig = [
    'saveUrl' => route('student.exams.save-answer', $schedule->id),
    'csrfToken' => csrf_token(),
    'flash' => [
    'message' => $flashMessage,
    'type' => $flashMessage ? $flashType : null,
    ],
    ];
    @endphp
    <script id="student-exam-room-config" type="application/json">
        @json($studentExamRoomConfig)
    </script>
</body>

</html>