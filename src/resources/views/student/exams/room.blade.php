@php
$totalQuestions = count($questions);
@endphp
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ $exam->title }} - EduPortal</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style data-purpose="custom-styles">
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #F8FAFD;
            font-family: 'Be Vietnam Pro', 'Inter', system-ui, sans-serif;
            color: #374151;
            margin: 0;
        }

        .text-h1 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            color: #1A3A6B;
        }

        .text-h2 {
            font-size: 22px;
            font-weight: 600;
            line-height: 1.3;
            color: #1A3A6B;
        }

        .text-h3 {
            font-size: 17px;
            font-weight: 600;
            color: #1A3A6B;
        }

        .text-body {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }

        .text-caption {
            font-size: 12px;
            color: #6B7C99;
        }

        .text-mono {
            font-size: 13px;
            color: #1A3A6B;
            font-family: monospace;
        }

        /* Navbar */
        .nav-bar {
            background: #1A3A6B;
            display: flex;
            align-items: center;
            padding: 0 20px;
            height: 52px;
            gap: 8px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 24px;
        }

        .nav-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #34D399;
        }

        .nav-logo-text {
            color: #fff;
            font-size: 15px;
            font-weight: 600;
        }

        .nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-user-role {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            padding: 4px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-role-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #34D399;
        }

        .nav-role-text {
            color: #93BAE8;
            font-size: 11px;
        }

        .nav-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #2A5298;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #93BAE8;
            font-size: 11px;
            font-weight: 600;
            border: 1.5px solid #3A6AC8;
            text-transform: uppercase;
        }

        /* Cards */
        .ca-card {
            background: #fff;
            border: 0.5px solid #D6E2F0;
            border-radius: 10px;
            padding: 16px;
        }

        .ca-card-accent {
            background: #fff;
            border: 0.5px solid #D6E2F0;
            border-radius: 10px;
            padding: 16px;
            border-top: 3px solid #1A3A6B;
        }

        .ca-card-featured {
            background: #F4F7FC;
            border: 0.5px solid #B5D4F4;
            border-radius: 10px;
            padding: 16px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: opacity .15s;
            outline: none;
        }

        .btn-primary {
            background: #1A3A6B;
            color: #fff;
        }

        .btn-secondary {
            background: #E6F1FB;
            color: #1A3A6B;
        }

        .btn-outline {
            background: transparent;
            color: #1A3A6B;
            border: 1.5px solid #1A3A6B;
        }

        .btn-ghost {
            background: transparent;
            color: #1A3A6B;
            border: 1.5px solid #D6E2F0;
        }

        .btn-danger {
            background: #FEE2E2;
            color: #991B1B;
        }

        .btn-sm {
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 5px;
        }

        .btn-lg {
            font-size: 15px;
            padding: 11px 24px;
            border-radius: 8px;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Form inputs */
        .ca-input {
            border: 1.5px solid #D6E2F0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            color: #1A3A6B;
            background: #fff;
            font-family: inherit;
            outline: none;
            width: 100%;
            transition: all 0.2s;
            resize: none;
        }

        .ca-input:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px #E6F1FB;
        }

        /* Status */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .status-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .s-ongoing {
            background: #D1FAE5;
            color: #065F46;
        }

        .s-ongoing .status-dot {
            background: #10B981;
        }

        /* Question Navigator */
        .question-btn {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            border: 1.5px solid #D6E2F0;
            background-color: #fff;
            color: #6B7C99;
            position: relative;
            transition: all 0.2s;
            cursor: pointer;
            padding: 0;
            font-family: inherit;
        }

        .question-btn:hover {
            background-color: #F8FAFD;
            border-color: #185FA5;
            color: #1A3A6B;
        }

        .question-btn.answered {
            background-color: #E1F5EE;
            color: #085041;
            border-color: #1D9E75;
        }

        .question-btn.current {
            background-color: #1A3A6B;
            color: #fff;
            border-color: #1A3A6B;
            font-weight: 600;
        }

        .question-btn.flagged {
            border-color: #D97706;
        }

        .flag-indicator {
            position: absolute;
            top: -1px;
            right: -1px;
            width: 0;
            height: 0;
            border-top: 14px solid #D97706;
            border-left: 14px solid transparent;
            border-top-right-radius: 5px;
            display: none;
        }

        .question-btn.flagged .flag-indicator {
            display: block;
        }

        .flag-icon-xs {
            font-size: 7px;
            color: #fff;
            position: absolute;
            top: 1px;
            right: 2px;
            z-index: 10;
            display: none;
        }

        .question-btn.flagged .flag-icon-xs {
            display: block;
        }

        /* Option Cards */
        .option-card {
            border: 1.5px solid #D6E2F0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            cursor: pointer;
            background-color: #fff;
            transition: all 0.2s;
            min-height: 48px;
        }

        .option-card:hover {
            border-color: #185FA5;
            background-color: #F8FAFD;
        }

        .option-card.selected {
            background-color: #E6F1FB;
            border-color: #185FA5;
        }

        .custom-radio {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 1.5px solid #D6E2F0;
            border-radius: 50%;
            margin-right: 12px;
            outline: none;
            position: relative;
            flex-shrink: 0;
            cursor: pointer;
            background-color: #fff;
            transition: all 0.15s;
        }

        .option-card:hover .custom-radio {
            border-color: #185FA5;
        }

        .custom-radio:checked {
            border-color: #185FA5;
            border-width: 5px;
        }

        .question-container {
            display: none;
        }

        .question-container.active {
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Layout */
        .main-layout {
            display: flex;
            gap: 24px;
            max-width: 1400px;
            margin: 32px auto;
            padding: 0 24px;
            align-items: flex-start;
        }

        .col-left {
            width: 320px;
            flex-shrink: 0;
        }

        .col-center {
            flex: 1;
            min-width: 0;
        }

        .col-right {
            width: 280px;
            flex-shrink: 0;
        }

        .timer-display {
            font-size: 40px;
            font-weight: 700;
            color: #DC2626;
            text-align: center;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        .toast {
            position: absolute;
            top: 24px;
            right: 24px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 50;
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

        .grid-nav {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        @media (max-width: 1100px) {
            .grid-nav {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>

<body>

    <div class="nav-bar">
        <div class="nav-logo">
            <div class="nav-dot"></div>
            <span class="nav-logo-text">EduPortal</span>
        </div>
        <div class="nav-right">
            <div class="nav-user-role">
                <div class="nav-role-dot"></div>
                <span class="nav-role-text">Sinh viên</span>
            </div>
            <div class="nav-avatar" title="{{ Auth::user()->name ?? 'Sinh viên' }}">
                {{ strtoupper(substr(Auth::user()->name ?? 'SV', 0, 2)) }}
            </div>
        </div>
    </div>

    <main class="main-layout" id="main-content">
        <div id="exam-config" data-total-questions="{{ $totalQuestions }}" data-time-left="{{ $timeLeftSeconds }}" hidden></div>

        <!-- Left Column: Navigation -->
        <div class="col-left">
            <div class="ca-card">
                <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
                    <h2 class="text-h3" style="margin: 0;">Danh sách câu hỏi</h2>
                    <span class="text-caption">Đã làm: <span id="answered-count" style="color: #1A3A6B; font-weight: 600;">0</span>/{{ $totalQuestions }}</span>
                </div>

                <div class="grid-nav" id="question-navigator">
                    @foreach($questions as $index => $question)
                    <button type="button"
                        class="question-btn {{ $index === 0 ? 'current' : '' }} {{ isset($savedAnswers[$question->id]) ? 'answered' : '' }}"
                        data-question-index="{{ $index }}"
                        id="nav-btn-{{ $index }}">
                        {{ $index + 1 }}
                        <div class="flag-indicator"></div>
                        <i class="fa-solid fa-flag flag-icon-xs"></i>
                    </button>
                    @endforeach
                </div>

                <hr style="border: none; border-top: 0.5px solid #D6E2F0; margin: 24px 0;">

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 12px; color: #4A5F7A;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 14px; height: 14px; border: 1.5px solid #D6E2F0; border-radius: 3px; background: #fff;"></div>
                        <span>Chưa trả lời</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 14px; height: 14px; border: 1.5px solid #1D9E75; border-radius: 3px; background: #E1F5EE;"></div>
                        <span>Đã trả lời</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 14px; height: 14px; border: 1.5px solid #D97706; border-radius: 3px; background: #fff;"></div>
                        <span>Đã gắn cờ</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 14px; height: 14px; border: 1.5px solid #1A3A6B; border-radius: 3px; background: #1A3A6B;"></div>
                        <span>Câu hiện tại</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center Column -->
        <div class="col-center">
            <div class="ca-card ca-card-accent" style="min-height: 60vh; display: flex; flex-direction: column; position: relative;">

                <form id="exam-form" action="{{ route('student.exams.submit', $exam->id) }}" method="POST" style="flex: 1; display: flex; flex-direction: column;">
                    @csrf

                    @foreach($questions as $index => $question)
                    <div class="question-container {{ $index === 0 ? 'active' : '' }}" id="question-{{ $index }}" style="flex: 1;">

                        <!-- Header -->
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 0.5px solid #D6E2F0; padding-bottom: 20px; margin-bottom: 24px;">
                            <h2 class="text-h2" style="margin: 0;">Câu {{ $index + 1 }}</h2>
                            <button type="button" data-flag-index="{{ $index }}" id="flag-btn-{{ $index }}" class="btn btn-ghost btn-sm" style="display: flex; gap: 6px;">
                                <i class="fa-regular fa-flag"></i> Đặt cờ
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="question-text-content" style="margin-bottom: 32px; font-size: 15px; color: #1A3A6B; font-weight: 500; line-height: 1.6;">
                            {!! $question->content !!}
                        </div>

                        <!-- Options -->
                        <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                            @foreach($question->options as $option)
                            @php
                            $isChecked = isset($savedAnswers[$question->id]) && $savedAnswers[$question->id] == $option->id;
                            @endphp
                            <label class="option-card {{ $isChecked ? 'selected' : '' }}" data-option-index="{{ $index }}">
                                <input type="radio"
                                    name="answers[{{ $question->id }}]"
                                    value="{{ $option->id }}"
                                    data-question-id="{{ $question->id }}"
                                    class="answer-radio custom-radio"
                                    {{ $isChecked ? 'checked' : '' }}>
                                <span style="font-size: 14px; color: #374151;">{!! $option->content !!}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </form>

                <!-- Navigation Controls -->
                <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 0.5px solid #D6E2F0;">
                    <button type="button" data-action="prev-question" id="btn-prev" class="btn btn-outline" style="flex: 1; height: 44px;">
                        <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Câu trước
                    </button>
                    <button type="button" data-action="next-question" id="btn-next" class="btn btn-primary" style="flex: 1; height: 44px;">
                        Câu tiếp theo <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>
                    </button>
                </div>

                <!-- Toast Positioned Relative to Card -->
                <div id="save-toast" class="toast">
                    <i class="fa-solid fa-check-circle"></i> <span id="toast-msg">Đã lưu tự động</span>
                </div>

            </div>
        </div>

        <!-- Right Column: Info & Action -->
        <div class="col-right">
            <!-- Exam Meta -->
            <div class="ca-card-featured" style="margin-bottom: 24px; text-align: center;">
                <p class="text-caption" style="text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 8px;">Thời gian còn lại</p>
                <div id="countdown-timer" class="timer-display">--:--</div>
                <div style="margin-top: 12px; display: inline-flex;">
                    <span class="status s-ongoing"><span class="status-dot"></span>Đang thi</span>
                </div>
            </div>

            <!-- Submit -->
            <div class="ca-card" style="margin-bottom: 24px;">
                <p class="text-h3" style="text-align: center; margin: 0 0 16px;">Hoàn tất bài làm?</p>
                <button type="button" data-action="submit-exam" class="btn btn-primary btn-lg" style="width: 100%;">
                    Nộp bài thi
                </button>
                <p class="text-caption" style="text-align: center; margin: 12px 0 0;">(Không thể sửa sau khi nộp)</p>
            </div>

            <!-- Tools -->
            <div class="ca-card">
                <h3 class="text-h3" style="margin: 0 0 16px;">Tiện ích</h3>

                <p class="text-caption" style="margin: 0 0 8px;">Cỡ chữ</p>
                <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                    <button type="button" data-action="font-minus" class="btn btn-outline btn-sm" style="flex: 1;">A-</button>
                    <button type="button" data-action="font-plus" class="btn btn-outline btn-sm" style="flex: 1;">A+</button>
                </div>

                <p class="text-caption" style="margin: 0 0 8px;">Giấy nháp</p>
                <textarea class="ca-input" rows="5" placeholder="Ghi chú nhanh tại đây..."></textarea>
            </div>
        </div>
    </main>

    <script>
        const configEl = document.getElementById('exam-config');
        const totalQuestions = parseInt(configEl?.dataset.totalQuestions || '0', 10);
        let currentQuestionIndex = 0;
        let baseFontSize = 15;

        const saveUrl = "{{ route('student.exams.save-answer', $exam->id) }}";
        const csrfToken = "{{ csrf_token() }}";
        const toastEl = document.getElementById('save-toast');
        const toastMsg = document.getElementById('toast-msg');

        document.addEventListener("DOMContentLoaded", function() {
            updateNavigationButtons();
            updateAnsweredCount();

            let timeLeft = parseInt(configEl?.dataset.timeLeft || '0', 10);
            const timerDisplay = document.getElementById('countdown-timer');
            const examForm = document.getElementById('exam-form');

            document.querySelectorAll('[data-question-index]').forEach(btn => {
                btn.addEventListener('click', () => {
                    goToQuestion(parseInt(btn.dataset.questionIndex || '0', 10));
                });
            });

            document.querySelectorAll('[data-flag-index]').forEach(btn => {
                btn.addEventListener('click', () => {
                    toggleFlag(parseInt(btn.dataset.flagIndex || '0', 10));
                });
            });

            document.querySelectorAll('[data-option-index]').forEach(label => {
                label.addEventListener('click', () => {
                    selectOption(label, parseInt(label.dataset.optionIndex || '0', 10));
                });
            });

            document.querySelector('[data-action="prev-question"]')?.addEventListener('click', prevQuestion);
            document.querySelector('[data-action="next-question"]')?.addEventListener('click', nextQuestion);
            document.querySelector('[data-action="submit-exam"]')?.addEventListener('click', () => examForm.submit());
            document.querySelector('[data-action="font-minus"]')?.addEventListener('click', () => changeFontSize(-1));
            document.querySelector('[data-action="font-plus"]')?.addEventListener('click', () => changeFontSize(1));

            const countdown = setInterval(function() {
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerDisplay.innerHTML = "00:00";
                    examForm.submit();
                } else {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    timerDisplay.innerHTML =
                        (minutes < 10 ? "0" : "") + minutes + ":" +
                        (seconds < 10 ? "0" : "") + seconds;
                    timeLeft -= 1;

                    if (timeLeft < 300) {
                        timerDisplay.style.color = '#991B1B';
                    }
                }
            }, 1000);

            document.querySelectorAll('.answer-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const questionIdx = this.closest('.question-container').id.split('-')[1];
                    const btn = document.getElementById(`nav-btn-${questionIdx}`);
                    btn.classList.add('answered');
                    updateAnsweredCount();
                    autoSave(this.getAttribute('data-question-id'), this.value);
                });
            });
        });

        function updateAnsweredCount() {
            const answered = document.querySelectorAll('.question-btn.answered').length;
            document.getElementById('answered-count').innerText = answered;
        }

        let toastTimeout;

        function showToast(message, type = 'success') {
            toastMsg.innerText = message;
            toastEl.className = 'toast show';
            if (type === 'error') toastEl.classList.add('error');
            if (type === 'warning') toastEl.classList.add('warning');

            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toastEl.classList.remove('show');
            }, 2500);
        }

        function autoSave(questionId, optionId) {
            showToast('Đang lưu...', 'warning');

            fetch(saveUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        question_option_id: optionId,
                        tab_switch_count: tabSwitchCount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Đã lưu bài tự động', 'success');
                    } else {
                        showToast('Không thể lưu!', 'error');
                    }
                })
                .catch(() => {
                    showToast('Lỗi kết nối!', 'error');
                });
        }

        // ── Anti-cheat: Tab switch tracking ──────────────────
        let tabSwitchCount = 0;
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                tabSwitchCount++;
                console.warn(`[Anti-cheat] Tab switch #${tabSwitchCount}`);
            }
        });

        function goToQuestion(index) {
            if (index < 0 || index >= totalQuestions) return;

            document.getElementById(`question-${currentQuestionIndex}`).classList.remove('active');
            document.getElementById(`nav-btn-${currentQuestionIndex}`).classList.remove('current');

            currentQuestionIndex = index;
            document.getElementById(`question-${currentQuestionIndex}`).classList.add('active');
            document.getElementById(`nav-btn-${currentQuestionIndex}`).classList.add('current');

            updateNavigationButtons();
        }

        function prevQuestion() {
            goToQuestion(currentQuestionIndex - 1);
        }

        function nextQuestion() {
            goToQuestion(currentQuestionIndex + 1);
        }

        function updateNavigationButtons() {
            document.getElementById('btn-prev').disabled = (currentQuestionIndex === 0);
            document.getElementById('btn-next').disabled = (currentQuestionIndex === totalQuestions - 1);
        }

        function selectOption(label, index) {
            const container = document.getElementById(`question-${index}`);
            container.querySelectorAll('.option-card').forEach(card => card.classList.remove('selected'));
            label.classList.add('selected');
        }

        function toggleFlag(index) {
            const navBtn = document.getElementById(`nav-btn-${index}`);
            const flagBtn = document.getElementById(`flag-btn-${index}`);

            const isFlagged = navBtn.classList.toggle('flagged');

            if (isFlagged) {
                flagBtn.innerHTML = '<i class="fa-solid fa-flag" style="color: #D97706;"></i> <span style="color: #D97706;">Bỏ cờ</span>';
            } else {
                flagBtn.innerHTML = '<i class="fa-regular fa-flag"></i> Đặt cờ';
            }
        }

        function changeFontSize(direction) {
            baseFontSize += (direction * 2);
            if (baseFontSize < 13) baseFontSize = 13;
            if (baseFontSize > 24) baseFontSize = 24;

            document.querySelectorAll('.question-text-content').forEach(el => {
                el.style.fontSize = `${baseFontSize}px`;
            });
        }
    </script>
</body>

</html>