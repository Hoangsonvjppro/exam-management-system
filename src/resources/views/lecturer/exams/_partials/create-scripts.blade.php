<script>
    // ═══════════════════════════════════════════════════════════════
    // GLOBAL STATE
    // ═══════════════════════════════════════════════════════════════
    const chapters = JSON.parse(document.getElementById('chapters-data')?.textContent || '[]');
    const difficulties = JSON.parse(document.getElementById('difficulties-data')?.textContent || '[]');
    const availabilityMap = JSON.parse(document.getElementById('availability-data')?.textContent || '{}');

    const formContextEl = document.getElementById('exam-form-context-data');
    const FORM_CONTEXT = formContextEl ? JSON.parse(formContextEl.textContent || '{}') : (window.EXAM_FORM_CONTEXT || {});
    const initialSelectedQuestionIds = Array.isArray(FORM_CONTEXT.selectedQuestionIds) ?
        FORM_CONTEXT.selectedQuestionIds
        .map(id => parseInt(id, 10))
        .filter(id => Number.isInteger(id) && id > 0) : [];
    const initialMatrixRows = Array.isArray(FORM_CONTEXT.initialMatrixRows) ? FORM_CONTEXT.initialMatrixRows : [];
    const initialCreationMode = FORM_CONTEXT.initialMode === 'matrix' ? 'matrix' :
        (FORM_CONTEXT.initialMode === 'manual' ? 'manual' :
            (document.getElementById('creation_mode')?.value === 'matrix' ? 'matrix' : 'manual'));

    const selectedQuestionIds = new Set(initialSelectedQuestionIds);
    let matrixRowIndex = 0;
    let currentPage = 1;
    let lastPage = 1;
    let searchTimeout = null;
    let expandedPreviews = new Set();
    let activePreset = null;
    const QUICK_OPTION_LABELS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const DIFFICULTY_LABELS = {};
    difficulties.forEach(d => DIFFICULTY_LABELS[d.code] = d.name);

    const endpointEl = document.getElementById('exam-form-endpoints-data');
    const endpointPayload = endpointEl ? JSON.parse(endpointEl.textContent || '{}') : {};
    const API_QUESTIONS_URL = endpointPayload.questionsUrl || '';
    const API_QUICK_QUESTION_URL = endpointPayload.quickQuestionUrl || '';
    const CSRF_TOKEN = endpointPayload.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

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
        syncQuickQuestionSubjectAndChapters(subjectId);

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

    function showLoading(show) {
        document.getElementById('questions-loading').style.display = show ? 'flex' : 'none';
    }

    function showEmptyState(text) {
        const el = document.getElementById('questions-empty');
        el.style.display = 'flex';
        document.getElementById('questions-empty-text').textContent = text;
    }

    function hideEmptyState() {
        document.getElementById('questions-empty').style.display = 'none';
    }

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
        <td><input type="number" name="matrix[${i}][question_count]" class="ca-input matrix-count" value="${prefill?.question_count ?? prefill?.count ?? 5}" min="1" required style="width:70px" oninput="updateMatrixSummary();updateRowAvailability(this.closest('tr'))"></td>
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

    function hydrateInitialMatrixRows() {
        clearMatrix();

        if (initialMatrixRows.length === 0) {
            addMatrixRow();
            return;
        }

        initialMatrixRows.forEach(row => {
            addMatrixRow({
                chapter_id: row.chapter_id ?? null,
                difficulty: row.difficulty ?? (difficulties[0]?.code || 'remember'),
                question_count: row.question_count ?? row.count ?? 1,
                count: row.count ?? row.question_count ?? 1,
            });
        });
    }

    function applyPreset(type) {
        const total = parseInt(document.getElementById('preset-total')?.value || 20);
        const presets = {
            easy: [{
                code: 'remember',
                pct: 60
            }, {
                code: 'understand',
                pct: 20
            }, {
                code: 'apply',
                pct: 15
            }, {
                code: 'analyze',
                pct: 5
            }],
            standard: [{
                code: 'remember',
                pct: 40
            }, {
                code: 'understand',
                pct: 30
            }, {
                code: 'apply',
                pct: 20
            }, {
                code: 'analyze',
                pct: 10
            }],
            hard: [{
                code: 'remember',
                pct: 20
            }, {
                code: 'understand',
                pct: 20
            }, {
                code: 'apply',
                pct: 30
            }, {
                code: 'analyze',
                pct: 30
            }],
        };

        const config = presets[type];
        if (!config) return;

        // Calculate counts with rounding correction
        let counts = config.map(c => ({
            ...c,
            count: Math.round(total * c.pct / 100)
        }));
        let sum = counts.reduce((a, c) => a + c.count, 0);
        // Adjust first item to ensure total matches
        if (sum !== total) counts[0].count += (total - sum);

        clearMatrix();
        counts.forEach(c => {
            if (c.count > 0) addMatrixRow({
                difficulty: c.code,
                count: c.count
            });
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
                const avail = row.querySelector('[data-avail]')?.textContent.replace('⚠️', '').trim();
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
    function syncQuickQuestionSubjectAndChapters(subjectId, preferredChapterId = '') {
        const subjectSelect = document.getElementById('quick-question-subject');
        const chapterSelect = document.getElementById('quick-question-chapter');
        if (!subjectSelect || !chapterSelect) return;

        if (subjectId !== undefined && subjectId !== null) {
            subjectSelect.value = subjectId ? String(subjectId) : '';
        }

        const activeSubjectId = subjectSelect.value;
        const filtered = chapters.filter(c => String(c.subject_id) === String(activeSubjectId));
        const previousChapterId = preferredChapterId || chapterSelect.value;

        chapterSelect.innerHTML = '<option value="">-- Chọn chương --</option>' +
            filtered.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

        if (previousChapterId && filtered.some(c => String(c.id) === String(previousChapterId))) {
            chapterSelect.value = String(previousChapterId);
        }
    }

    function getQuickQuestionTypeCode() {
        const typeSelect = document.getElementById('quick-question-type');
        if (!typeSelect) return 'single_choice';

        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        return selectedOption?.dataset?.code || 'single_choice';
    }

    function updateQuickQuestionCorrectInputMode() {
        const typeCode = getQuickQuestionTypeCode();
        const isMultiple = typeCode === 'multiple_choice';
        const modeHint = document.getElementById('quick-correct-mode-hint');

        document.querySelectorAll('#quick-question-options .quick-correct-input').forEach(input => {
            input.type = isMultiple ? 'checkbox' : 'radio';
            input.name = isMultiple ? 'quick_option_selector[]' : 'quick_option_selector';
        });

        if (modeHint) {
            modeHint.textContent = isMultiple ?
                'Trắc nghiệm nhiều đáp án: có thể chọn nhiều đáp án đúng.' :
                'Trắc nghiệm 1 lựa chọn: chọn đúng 1 đáp án.';
        }
    }

    function syncQuickQuestionOptionIndexes() {
        const rows = document.querySelectorAll('#quick-question-options .quick-option-row');
        const canRemove = rows.length > 2;

        rows.forEach((row, index) => {
            const label = row.querySelector('.quick-option-label');
            const correctInput = row.querySelector('.quick-correct-input');
            const optionContentInput = row.querySelector('.quick-option-content');
            const removeBtn = row.querySelector('[title="Xóa phương án"]');

            if (label) label.textContent = `${QUICK_OPTION_LABELS[index]}.`;
            if (correctInput) correctInput.value = String(index);
            if (optionContentInput) optionContentInput.name = `options[${index}][content]`;

            if (removeBtn) {
                removeBtn.disabled = !canRemove;
                removeBtn.classList.toggle('opacity-40', !canRemove);
                removeBtn.classList.toggle('cursor-not-allowed', !canRemove);
            }
        });

        updateQuickQuestionCorrectInputMode();
    }

    function addQuickQuestionOption(initialContent = '', isCorrect = false) {
        const container = document.getElementById('quick-question-options');
        const template = document.getElementById('quick-question-option-template');
        if (!container || !template) return;

        const currentCount = container.querySelectorAll('.quick-option-row').length;
        if (currentCount >= QUICK_OPTION_LABELS.length) return;

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.quick-option-row');
        if (!row) return;

        const optionContentInput = row.querySelector('.quick-option-content');
        const correctInput = row.querySelector('.quick-correct-input');
        if (optionContentInput) optionContentInput.value = initialContent;
        if (correctInput) correctInput.checked = isCorrect;

        container.appendChild(row);
        syncQuickQuestionOptionIndexes();
    }

    function removeQuickQuestionOption(button) {
        const container = document.getElementById('quick-question-options');
        if (!container) return;

        const rows = container.querySelectorAll('.quick-option-row');
        if (rows.length <= 2) return;

        button.closest('.quick-option-row')?.remove();
        syncQuickQuestionOptionIndexes();
    }

    function showQuickQuestionErrors(messages) {
        const errorsDiv = document.getElementById('quick-question-errors');
        if (!errorsDiv) return;

        const list = Array.isArray(messages) ? messages : [messages];
        errorsDiv.innerHTML = list.map(message => `<div>• ${message}</div>`).join('');
        errorsDiv.style.display = 'block';
    }

    function validateQuickQuestionForm(form) {
        const rows = form.querySelectorAll('#quick-question-options .quick-option-row');
        if (rows.length < 2) {
            return ['Cần ít nhất 2 phương án trả lời.'];
        }

        const emptyOptions = [];
        rows.forEach((row, index) => {
            const optionContentInput = row.querySelector('.quick-option-content');
            if (!optionContentInput || !optionContentInput.value.trim()) {
                emptyOptions.push(QUICK_OPTION_LABELS[index]);
            }
        });

        if (emptyOptions.length > 0) {
            return [`Phương án ${emptyOptions.join(', ')} chưa có nội dung.`];
        }

        const selectedCorrectInputs = form.querySelectorAll('#quick-question-options .quick-correct-input:checked');
        if (selectedCorrectInputs.length < 1) {
            return ['Vui lòng chọn ít nhất 1 đáp án đúng.'];
        }

        return [];
    }

    function syncQuickQuestionCorrectOptionsPayload(form) {
        form.querySelectorAll('.quick-hidden-correct-option').forEach(el => el.remove());

        form.querySelectorAll('#quick-question-options .quick-correct-input:checked').forEach(input => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'correct_options[]';
            hidden.value = input.value;
            hidden.className = 'quick-hidden-correct-option';
            form.appendChild(hidden);
        });
    }

    function resetQuickQuestionFormState() {
        const form = document.getElementById('quick-question-form');
        const errorsDiv = document.getElementById('quick-question-errors');
        const optionsContainer = document.getElementById('quick-question-options');
        if (!form || !optionsContainer) return;

        form.reset();
        optionsContainer.innerHTML = '';
        for (let i = 0; i < 4; i++) addQuickQuestionOption();

        const selectedSubject = document.getElementById('subject_id')?.value;
        syncQuickQuestionSubjectAndChapters(selectedSubject);
        updateQuickQuestionCorrectInputMode();

        if (errorsDiv) {
            errorsDiv.style.display = 'none';
            errorsDiv.innerHTML = '';
        }
    }

    function initializeQuickQuestionForm() {
        const subjectSelect = document.getElementById('quick-question-subject');
        const typeSelect = document.getElementById('quick-question-type');
        const optionsContainer = document.getElementById('quick-question-options');
        if (!subjectSelect || !typeSelect || !optionsContainer) return;

        if (optionsContainer.children.length === 0) {
            for (let i = 0; i < 4; i++) addQuickQuestionOption();
        } else {
            syncQuickQuestionOptionIndexes();
        }

        const selectedSubject = document.getElementById('subject_id')?.value || subjectSelect.value;
        syncQuickQuestionSubjectAndChapters(selectedSubject);
        updateQuickQuestionCorrectInputMode();

        subjectSelect.addEventListener('change', () => {
            syncQuickQuestionSubjectAndChapters(subjectSelect.value);
        });

        typeSelect.addEventListener('change', () => {
            updateQuickQuestionCorrectInputMode();
        });
    }

    async function submitQuickQuestionForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitBtn = document.getElementById('quick-question-submit');
        const errorsDiv = document.getElementById('quick-question-errors');

        const validationErrors = validateQuickQuestionForm(form);
        if (validationErrors.length > 0) {
            showQuickQuestionErrors(validationErrors);
            return;
        }

        syncQuickQuestionCorrectOptionsPayload(form);
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang lưu...';
        errorsDiv.innerHTML = '';
        errorsDiv.style.display = 'none';

        try {
            const response = await fetch(API_QUICK_QUESTION_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) {
                const messages = data.errors ? Object.values(data.errors).flat() : [data.error || 'Lỗi không xác định'];
                showQuickQuestionErrors(messages);
                return;
            }

            // Auto-select the new question
            selectedQuestionIds.add(data.id);
            updateSelectedDisplay();

            // Reload questions list to include new one
            searchQuestions(1);

            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: 'quick-question-modal'
            }));
            resetQuickQuestionFormState();

            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    message: 'Đã thêm câu hỏi mới và tự động đưa vào đề thi.',
                    type: 'success'
                }
            }));
        } catch (error) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    message: 'Không thể thêm câu hỏi. Vui lòng thử lại.',
                    type: 'error'
                }
            }));
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Lưu và thêm vào đề';
        }
    }

    function openQuickQuestionModal() {
        const subjectId = document.getElementById('subject_id')?.value;
        syncQuickQuestionSubjectAndChapters(subjectId);
        updateQuickQuestionCorrectInputMode();
        window.dispatchEvent(new CustomEvent('open-modal', {
            detail: 'quick-question-modal'
        }));
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
        const creationModeInput = document.getElementById('creation_mode');
        if (creationModeInput) {
            creationModeInput.value = initialCreationMode;
        }
        switchCreationMode(initialCreationMode);

        initializeQuickQuestionForm();
        updateSelectedDisplay();

        const subjectId = document.getElementById('subject_id')?.value;
        if (subjectId) {
            lastSubjectId = subjectId;
            syncQuickQuestionSubjectAndChapters(subjectId);
            updateChapterFilters(subjectId);
            hydrateInitialMatrixRows();
            searchQuestions(1);
        } else {
            showEmptyState('Vui lòng chọn môn học để xem danh sách câu hỏi.');
            hydrateInitialMatrixRows();
        }
    });
</script>