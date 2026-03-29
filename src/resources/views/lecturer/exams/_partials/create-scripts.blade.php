<script>
// ═══════════════════════════════════════════════════════════════
// GLOBAL STATE
// ═══════════════════════════════════════════════════════════════
const chapters = JSON.parse(document.getElementById('chapters-data')?.textContent || '[]');
const difficulties = JSON.parse(document.getElementById('difficulties-data')?.textContent || '[]');
const availabilityMap = JSON.parse(document.getElementById('availability-data')?.textContent || '{}');

const selectedQuestionIds = new Set();
let matrixRowIndex = 0;
let currentPage = 1;
let lastPage = 1;
let searchTimeout = null;
let expandedPreviews = new Set();
let activePreset = null;

const DIFFICULTY_LABELS = {};
difficulties.forEach(d => DIFFICULTY_LABELS[d.code] = d.name);

const API_QUESTIONS_URL = @json(route('lecturer.api.exam-form.questions'));
const API_QUICK_QUESTION_URL = @json(route('lecturer.api.exam-form.quick-question'));
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// ═══════════════════════════════════════════════════════════════
// TAB SWITCHING
// ═══════════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════════
// SUBJECT CHANGE (with confirmation)
// ═══════════════════════════════════════════════════════════════
let lastSubjectId = document.getElementById('subject_id')?.value || '';

document.getElementById('subject_id')?.addEventListener('change', function() {
    const newSubjectId = this.value;
    const hasSelections = selectedQuestionIds.size > 0;
    const hasMatrixRows = document.getElementById('matrix-body')?.children.length > 0;

    if ((hasSelections || hasMatrixRows) && lastSubjectId && lastSubjectId !== newSubjectId) {
        if (!confirm('Bạn có chắc muốn đổi môn học? Các cài đặt đề thi hiện tại (câu hỏi đã chọn, ma trận) sẽ bị xóa.')) {
            this.value = lastSubjectId;
            return;
        }
    }

    lastSubjectId = newSubjectId;
    onSubjectChange();
});

function onSubjectChange() {
    const subjectId = document.getElementById('subject_id')?.value;

    // Sync quick question modal
    const qqs = document.getElementById('quick-question-subject');
    if (qqs && subjectId) qqs.value = subjectId;

    // Update chapter filter dropdown
    updateChapterFilters(subjectId);

    // Reset selections
    selectedQuestionIds.clear();
    updateSelectedDisplay();

    // Reset matrix
    clearMatrix();
    addMatrixRow();

    // Load questions via AJAX
    if (subjectId) {
        searchQuestions(1);
    } else {
        document.getElementById('questions-list').innerHTML = '';
        showEmptyState('Vui lòng chọn môn học để xem danh sách câu hỏi.');
    }
}

function updateChapterFilters(subjectId) {
    const filtered = chapters.filter(c => c.subject_id == subjectId);
    const manualFilter = document.getElementById('manual-chapter-filter');
    if (manualFilter) {
        manualFilter.innerHTML = '<option value="">Tất cả chương</option>' +
            filtered.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    }
}

// ═══════════════════════════════════════════════════════════════
// MANUAL TAB: AJAX QUESTION SEARCH
// ═══════════════════════════════════════════════════════════════
function debouncedSearchQuestions() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => searchQuestions(1), 300);
}

async function searchQuestions(page) {
    const subjectId = document.getElementById('subject_id')?.value;
    if (!subjectId) return;

    currentPage = page;
    if (page === 1) {
        document.getElementById('questions-list').innerHTML = '';
        expandedPreviews.clear();
    }

    showLoading(true);
    hideEmptyState();

    const params = new URLSearchParams({
        subject_id: subjectId,
        page: page,
        per_page: 20
    });

    const chapterId = document.getElementById('manual-chapter-filter')?.value;
    const difficulty = document.getElementById('manual-difficulty-filter')?.value;
    const keyword = document.getElementById('manual-search')?.value?.trim();

    if (chapterId) params.append('chapter_id', chapterId);
    if (difficulty) params.append('difficulty', difficulty);
    if (keyword) params.append('keyword', keyword);

    try {
        const response = await fetch(`${API_QUESTIONS_URL}?${params}`);
        if (!response.ok) throw new Error('Failed to load questions');

        const data = await response.json();
        lastPage = data.last_page;

        if (data.data.length === 0 && page === 1) {
            showEmptyState('Không tìm thấy câu hỏi phù hợp với bộ lọc.');
        } else {
            renderQuestions(data.data);
        }

        // Load more button
        const loadMoreBtn = document.getElementById('load-more-btn');
        const pageInfo = document.getElementById('page-info');
        if (currentPage < lastPage) {
            loadMoreBtn.style.display = 'block';
            loadMoreBtn.textContent = `Tải thêm câu hỏi... (trang ${currentPage}/${lastPage})`;
        } else {
            loadMoreBtn.style.display = 'none';
        }
        if (data.total > 0) {
            pageInfo.style.display = 'block';
            pageInfo.textContent = `Hiển thị ${document.querySelectorAll('.q-item').length} / ${data.total} câu hỏi`;
        } else {
            pageInfo.style.display = 'none';
        }
    } catch (err) {
        console.error(err);
        showEmptyState('Lỗi khi tải câu hỏi. Vui lòng thử lại.');
    } finally {
        showLoading(false);
    }
}

function loadMoreQuestions() {
    searchQuestions(currentPage + 1);
}

function renderQuestions(questions) {
    const container = document.getElementById('questions-list');
    questions.forEach(q => {
        const isSelected = selectedQuestionIds.has(q.id);
        const div = document.createElement('div');
        div.className = `q-item ${isSelected ? 'selected' : ''}`;
        div.dataset.questionId = q.id;

        const plainContent = stripHtml(q.content).substring(0, 200);
        const chapterLabel = q.chapter ? q.chapter.name : '';
        const diffLabel = DIFFICULTY_LABELS[q.difficulty] || q.difficulty;

        div.innerHTML = `
            <div class="flex items-start gap-3">
                <input type="checkbox" class="mt-1 rounded border-[#D6E2F0] text-[#185FA5] focus:ring-[#E6F1FB] w-4 h-4 cursor-pointer flex-shrink-0"
                    ${isSelected ? 'checked' : ''} onchange="toggleQuestion(${q.id}, this.checked)">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        ${chapterLabel ? `<span class="badge s-upcoming">${chapterLabel}</span>` : ''}
                        <span class="badge" style="background:#F0F7FF;color:#185FA5">${diffLabel}</span>
                        <span class="text-[11px] text-[#B0BECE]">ID: ${q.id}</span>
                    </div>
                    <div class="text-[13px] text-[#374151] line-clamp-2 cursor-pointer" onclick="togglePreview(${q.id})">${plainContent}${q.content.length > 200 ? '...' : ''}</div>
                </div>
                <button type="button" class="text-[#6B7C99] hover:text-[#1A3A6B] flex-shrink-0 p-1" onclick="togglePreview(${q.id})" title="Xem chi tiết">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            <div class="q-preview" id="preview-${q.id}" style="display:none">
                <div class="text-[13px] text-[#374151] mb-3">${q.content}</div>
                ${q.options.length > 0 ? `<div class="space-y-1">${q.options.map(opt =>
                    `<div class="q-option ${opt.is_correct ? 'correct' : 'incorrect'}">
                        <span class="q-option-label">${opt.label}.</span>
                        <span>${opt.content}</span>
                        ${opt.is_correct ? '<span class="ml-auto text-[10px] font-bold text-emerald-600">✓ Đúng</span>' : ''}
                    </div>`
                ).join('')}</div>` : '<p class="text-[12px] text-[#6B7C99] italic">Không có đáp án</p>'}
            </div>
        `;
        container.appendChild(div);
    });

    updateCollapseAllBtn();
}

function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

function toggleQuestion(id, checked) {
    if (checked) {
        selectedQuestionIds.add(id);
    } else {
        selectedQuestionIds.delete(id);
    }
    // Update visual state
    const item = document.querySelector(`.q-item[data-question-id="${id}"]`);
    if (item) item.classList.toggle('selected', checked);
    updateSelectedDisplay();
}

function togglePreview(id) {
    const el = document.getElementById(`preview-${id}`);
    if (!el) return;
    const isShown = el.style.display !== 'none';
    el.style.display = isShown ? 'none' : 'block';
    if (isShown) expandedPreviews.delete(id);
    else expandedPreviews.add(id);
    updateCollapseAllBtn();
}

function collapseAllPreviews() {
    expandedPreviews.forEach(id => {
        const el = document.getElementById(`preview-${id}`);
        if (el) el.style.display = 'none';
    });
    expandedPreviews.clear();
    updateCollapseAllBtn();
}

function updateCollapseAllBtn() {
    const btn = document.getElementById('collapse-all-btn');
    if (btn) btn.style.display = expandedPreviews.size > 0 ? 'inline-flex' : 'none';
}

function updateSelectedDisplay() {
    const count = selectedQuestionIds.size;
    document.getElementById('selectedCount').textContent = count;
    updateManualTotal();
    syncSelectedHiddenInputs();
}

function updateManualTotal() {
    const count = selectedQuestionIds.size;
    document.getElementById('manual-total-display').textContent = `Tổng: ${count} câu`;
}

function syncSelectedHiddenInputs() {
    const container = document.getElementById('selected-questions-container');
    container.innerHTML = '';
    selectedQuestionIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'question_ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

function showLoading(show) { document.getElementById('questions-loading').style.display = show ? 'flex' : 'none'; }
function showEmptyState(text) { const el = document.getElementById('questions-empty'); el.style.display = 'flex'; document.getElementById('questions-empty-text').textContent = text; }
function hideEmptyState() { document.getElementById('questions-empty').style.display = 'none'; }

// ═══════════════════════════════════════════════════════════════
// MATRIX TAB
// ═══════════════════════════════════════════════════════════════
function addMatrixRow(prefill) {
    const i = matrixRowIndex++;
    const subjectId = document.getElementById('subject_id')?.value;
    const filtered = chapters.filter(c => c.subject_id == subjectId);
    const chapterOpts = filtered.map(c => `<option value="${c.id}" ${prefill?.chapter_id == c.id ? 'selected' : ''}>${c.name}</option>`).join('');
    const diffOpts = difficulties.map(d => `<option value="${d.code}" ${prefill?.difficulty === d.code ? 'selected' : ''}>${d.name}</option>`).join('');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td><select name="matrix[${i}][chapter_id]" class="ca-select" style="min-width:140px" onchange="updateRowAvailability(this.closest('tr'))"><option value="">Tất cả</option>${chapterOpts}</select></td>
        <td><select name="matrix[${i}][difficulty]" class="ca-select" required style="min-width:120px" onchange="updateRowAvailability(this.closest('tr'))">${diffOpts}</select></td>
        <td><input type="number" name="matrix[${i}][question_count]" class="ca-input matrix-count" value="${prefill?.count || 5}" min="1" required style="width:70px" oninput="updateMatrixSummary();updateRowAvailability(this.closest('tr'))"></td>
        <td class="text-center"><span class="availability-hint" data-avail>—</span></td>
        <td class="text-center"><button type="button" onclick="this.closest('tr').remove();updateMatrixSummary();checkMatrixAvailability()" class="text-[#DC2626] hover:text-[#991B1B] text-[14px] font-bold">&times;</button></td>
    `;
    document.getElementById('matrix-body').appendChild(row);
    updateRowAvailability(row);
    updateMatrixSummary();
}

function clearMatrix() {
    document.getElementById('matrix-body').innerHTML = '';
    matrixRowIndex = 0;
    activePreset = null;
    document.querySelectorAll('.preset-btn[data-preset]').forEach(b => b.classList.remove('active'));
    updateMatrixSummary();
    document.getElementById('matrix-availability-warning').style.display = 'none';
}

function applyPreset(type) {
    const total = parseInt(document.getElementById('preset-total')?.value || 20);
    const presets = {
        easy:     [{code:'remember',pct:60},{code:'understand',pct:20},{code:'apply',pct:15},{code:'analyze',pct:5}],
        standard: [{code:'remember',pct:40},{code:'understand',pct:30},{code:'apply',pct:20},{code:'analyze',pct:10}],
        hard:     [{code:'remember',pct:20},{code:'understand',pct:20},{code:'apply',pct:30},{code:'analyze',pct:30}],
    };

    const config = presets[type];
    if (!config) return;

    // Calculate counts with rounding correction
    let counts = config.map(c => ({ ...c, count: Math.round(total * c.pct / 100) }));
    let sum = counts.reduce((a, c) => a + c.count, 0);
    // Adjust first item to ensure total matches
    if (sum !== total) counts[0].count += (total - sum);

    clearMatrix();
    counts.forEach(c => {
        if (c.count > 0) addMatrixRow({ difficulty: c.code, count: c.count });
    });

    activePreset = type;
    document.querySelectorAll('.preset-btn[data-preset]').forEach(b => {
        b.classList.toggle('active', b.dataset.preset === type);
    });
}

function updateActivePreset() {
    if (activePreset) applyPreset(activePreset);
}

function updateMatrixSummary() {
    let totalQ = 0;
    document.querySelectorAll('#matrix-body tr').forEach(row => {
        const count = parseInt(row.querySelector('.matrix-count')?.value || 0);
        totalQ += count;
    });
    document.getElementById('matrixTotalQuestions').textContent = totalQ;
    checkMatrixAvailability();
}

function updateRowAvailability(row) {
    if (!row) return;
    const subjectId = document.getElementById('subject_id')?.value;
    const chapterId = row.querySelector('select[name*="chapter_id"]')?.value || '';
    const difficulty = row.querySelector('select[name*="difficulty"]')?.value || '';
    const countInput = row.querySelector('.matrix-count');
    const availSpan = row.querySelector('[data-avail]');
    if (!subjectId || !difficulty || !availSpan) return;

    // Look up availability: specific chapter first, then "all chapters" total
    let available = 0;
    if (chapterId) {
        const key = `${subjectId}|${chapterId}|${difficulty}`;
        available = availabilityMap[key] || 0;
    } else {
        // Sum all chapters for this difficulty
        const prefix = `${subjectId}|`;
        const suffix = `|${difficulty}`;
        for (const [k, v] of Object.entries(availabilityMap)) {
            if (k.startsWith(prefix) && k.endsWith(suffix)) available += v;
        }
    }

    availSpan.textContent = available;
    const requested = parseInt(countInput?.value || 0);
    if (requested > available) {
        availSpan.className = 'availability-hint warn';
        availSpan.textContent = `${available} ⚠️`;
        countInput?.classList.add('error');
    } else {
        availSpan.className = 'availability-hint';
        countInput?.classList.remove('error');
    }
    checkMatrixAvailability();
}

function checkMatrixAvailability() {
    const rows = document.querySelectorAll('#matrix-body tr');
    let hasWarning = false;
    const warnings = [];
    rows.forEach(row => {
        const countInput = row.querySelector('.matrix-count');
        if (countInput?.classList.contains('error')) {
            hasWarning = true;
            const diff = row.querySelector('select[name*="difficulty"]')?.value;
            const avail = row.querySelector('[data-avail]')?.textContent.replace('⚠️','').trim();
            warnings.push(`${DIFFICULTY_LABELS[diff] || diff}: cần ${countInput.value}, có ${avail}`);
        }
    });
    const warningEl = document.getElementById('matrix-availability-warning');
    const submitBtn = document.getElementById('btn-submit-matrix');
    if (hasWarning) {
        warningEl.style.display = 'block';
        document.getElementById('matrix-warning-text').textContent = warnings.join('; ');
        if (submitBtn) submitBtn.disabled = true;
    } else {
        warningEl.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;
    }
}

// ═══════════════════════════════════════════════════════════════
// QUICK QUESTION (JSON API)
// ═══════════════════════════════════════════════════════════════
async function submitQuickQuestionForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('quick-question-submit');
    const errorsDiv = document.getElementById('quick-question-errors');
    const formData = new FormData(form);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Đang lưu...';
    errorsDiv.style.display = 'none';

    try {
        const response = await fetch(API_QUICK_QUESTION_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: formData,
        });

        const data = await response.json();
        if (!response.ok) {
            const msgs = data.errors ? Object.values(data.errors).flat().join(', ') : (data.error || 'Lỗi không xác định');
            errorsDiv.textContent = msgs;
            errorsDiv.style.display = 'block';
            return;
        }

        // Auto-select the new question
        selectedQuestionIds.add(data.id);
        updateSelectedDisplay();

        // Reload questions list to include new one
        searchQuestions(1);

        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'quick-question-modal' }));
        form.reset();

        // Re-sync subject select
        const selectedSubject = document.getElementById('subject_id')?.value;
        const qqs = document.getElementById('quick-question-subject');
        if (qqs && selectedSubject) qqs.value = selectedSubject;

        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Đã thêm câu hỏi mới và tự động đưa vào đề thi.', type: 'success' } }));
    } catch (error) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Không thể thêm câu hỏi. Vui lòng thử lại.', type: 'error' } }));
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Lưu và thêm vào đề';
    }
}

function openQuickQuestionModal() {
    const subjectId = document.getElementById('subject_id')?.value;
    const qqs = document.getElementById('quick-question-subject');
    if (qqs && subjectId) qqs.value = subjectId;
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'quick-question-modal' }));
}

// ═══════════════════════════════════════════════════════════════
// UTILS
// ═══════════════════════════════════════════════════════════════
function toggleLateSettings() {
    const cb = document.getElementById('allow_late_entrance');
    const settings = document.getElementById('late_settings');
    if (cb && settings) settings.style.display = cb.checked ? 'block' : 'none';
}

// ═══════════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    const savedMode = document.getElementById('creation_mode').value;
    if (savedMode === 'matrix') switchCreationMode('matrix');

    const subjectId = document.getElementById('subject_id')?.value;
    if (subjectId) {
        lastSubjectId = subjectId;
        updateChapterFilters(subjectId);
        searchQuestions(1);
        addMatrixRow();
    } else {
        showEmptyState('Vui lòng chọn môn học để xem danh sách câu hỏi.');
        addMatrixRow();
    }
});
</script>
