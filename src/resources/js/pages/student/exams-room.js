const configEl = document.getElementById('exam-config');
if (!configEl) {
    // Not on exam room page.
}

const examRoomConfigEl = document.getElementById('student-exam-room-config');
const examRoomConfig = examRoomConfigEl ? JSON.parse(examRoomConfigEl.textContent || '{}') : {};
const totalQuestions = parseInt(configEl?.dataset.totalQuestions || '0', 10);
let currentQuestionIndex = 0;

const saveUrl = examRoomConfig.saveUrl || '';
const csrfToken = examRoomConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
const toastEl = document.getElementById('save-toast');
const toastMsg = document.getElementById('toast-msg');

document.addEventListener("DOMContentLoaded", function () {
    updateNavigationButtons();
    updateAnsweredCount();

    let timeLeft = parseInt(configEl?.dataset.timeLeft || '0', 10);
    const timerDisplay = document.getElementById('countdown-timer');
    const examForm = document.getElementById('exam-form');

    // Question navigator
    document.querySelectorAll('[data-question-index]').forEach(btn => {
        btn.addEventListener('click', () => {
            goToQuestion(parseInt(btn.dataset.questionIndex || '0', 10));
        });
    });

    // Flag buttons
    document.querySelectorAll('[data-flag-index]').forEach(btn => {
        btn.addEventListener('click', () => {
            toggleFlag(parseInt(btn.dataset.flagIndex || '0', 10));
        });
    });

    // Option cards
    document.querySelectorAll('[data-option-index]').forEach(label => {
        label.addEventListener('click', () => {
            selectOption(label, parseInt(label.dataset.optionIndex || '0', 10));
        });
    });

    // Nav buttons
    document.querySelector('[data-action="prev-question"]')?.addEventListener('click', prevQuestion);
    document.querySelector('[data-action="next-question"]')?.addEventListener('click', nextQuestion);
    document.querySelector('[data-action="submit-exam"]')?.addEventListener('click', () => {
        if (confirm('Bạn chắc chắn muốn nộp bài? Không thể sửa sau khi nộp.')) {
            examForm.submit();
        }
    });

    // Countdown
    const countdown = setInterval(function () {
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
                timerDisplay.classList.add('urgent');
            }
        }
    }, 1000);

    // Answer change → auto-save (Radio/Checkbox)
    document.querySelectorAll('.answer-radio, .answer-checkbox').forEach(input => {
        input.addEventListener('change', function () {
            const questionIdx = this.closest('.question-container').id.split('-')[1];
            const btn = document.getElementById(`nav-btn-${questionIdx}`);

            const isCheckedByQuestion = Array.from(this.closest('.question-container').querySelectorAll('input:checked')).length > 0;
            if (isCheckedByQuestion) btn.classList.add('answered');
            else btn.classList.remove('answered');

            updateAnsweredCount();

            // Collect all checked values if multi-choice
            let payload = { question_id: this.getAttribute('data-question-id'), tab_switch_count: tabSwitchCount };
            if (this.type === 'checkbox') {
                const checked = Array.from(this.closest('.question-container').querySelectorAll('input:checked')).map(el => el.value);
                payload.option_ids = checked;
            } else {
                payload.question_option_id = this.value;
            }
            autoSave(payload);
        });
    });

    // Textarea change → auto-save (debounced)
    let debounceTimer;
    document.querySelectorAll('.answer-textarea').forEach(textarea => {
        textarea.addEventListener('input', function () {
            const questionIdx = this.closest('.question-container').id.split('-')[1];
            const btn = document.getElementById(`nav-btn-${questionIdx}`);

            if (this.value.trim().length > 0) btn.classList.add('answered');
            else btn.classList.remove('answered');

            updateAnsweredCount();

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                autoSave({
                    question_id: this.getAttribute('data-question-id'),
                    answer_text: this.value,
                    tab_switch_count: tabSwitchCount
                });
            }, 1000);
        });
    });

    // Mobile panel toggle
    const panelHandle = document.getElementById('panel-handle');
    const zenPanel = document.getElementById('zen-panel');
    if (panelHandle && zenPanel) {
        panelHandle.addEventListener('click', () => {
            zenPanel.classList.toggle('expanded');
        });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            prevQuestion();
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            nextQuestion();
        }
    });
});

function updateAnsweredCount() {
    const answered = document.querySelectorAll('.q-btn.answered').length;
    document.getElementById('answered-count').innerText = answered;
}

let toastTimeout;
function showToast(message, type = 'success') {
    toastMsg.innerText = message;
    toastEl.className = 'toast show';
    if (type === 'error') toastEl.classList.add('error');
    if (type === 'warning') toastEl.classList.add('warning');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2500);
}

function autoSave(payload) {
    fetch(saveUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showToast('Không thể lưu!', 'error');
            }
        })
        .catch(() => showToast('Lỗi kết nối!', 'error'));
}

// Anti-cheat: Tab switch tracking
let tabSwitchCount = 0;
let isForcedSubmit = false; // Flag to prevent multiple submissions
document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        tabSwitchCount++;
        console.warn(`[Anti-cheat] Tab switch #${tabSwitchCount}`);
    } else {
        if (tabSwitchCount === 1) {
            showToast('Cảnh báo: Bạn đã chuyển tab 1 lần. Vi phạm 3 lần bài thi sẽ tự động nộp.', 'error');
        } else if (tabSwitchCount === 2) {
            showToast('Cảnh báo: Bạn đã chuyển tab 2 lần. Lần chuyển tab tiếp theo bài thi sẽ tự động nộp.', 'warning');
        } else if (tabSwitchCount >= 3) {
            if (!isForcedSubmit) {
                isForcedSubmit = true;
                showToast('Vi phạm quy chế (chuyển tab quá 3 lần). Bài thi đang được tự động nộp.', 'error');
                const examForm = document.getElementById('exam-form');
                if (examForm) {
                    // Tạo một thẻ input ẩn để đánh dấu nộp bài do vi phạm
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'is_forced_submit';
                    input.value = '1';
                    examForm.appendChild(input);
                    examForm.submit();
                }
            }
        }
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
    // Scroll question nav button into view
    document.getElementById(`nav-btn-${currentQuestionIndex}`).scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}

function prevQuestion() { goToQuestion(currentQuestionIndex - 1); }
function nextQuestion() { goToQuestion(currentQuestionIndex + 1); }

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
    const label = flagBtn.querySelector('.flag-label');
    if (label) {
        label.textContent = isFlagged ? 'Bỏ cờ' : 'Đặt cờ';
    }
    flagBtn.classList.toggle('active', isFlagged);
}
