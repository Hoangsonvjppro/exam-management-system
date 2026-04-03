const quickExamConfig = JSON.parse(document.getElementById('quick-exam-config-data')?.textContent || '{}');
const scheduleCreateConfigEl = document.getElementById('schedule-create-config');
const scheduleCreateConfig = scheduleCreateConfigEl ? JSON.parse(scheduleCreateConfigEl.textContent || '{}') : {};

window.scheduleCreateManager = function scheduleCreateManager(initialSubjectId, initialScheduleMode = 'within_day', initialExamDate = '') {
    return {
        selectedSubjectId: initialSubjectId || '',
        selectedExamId: '',
        hasSelectedSection: false,
        scheduleMode: initialScheduleMode === 'in_range' ? 'in_range' : 'within_day',
        singleDayDate: initialExamDate || '',
        quickSubjectId: initialSubjectId || '',
        quickQuestionApiUrl: scheduleCreateConfig.quickQuestionApiUrl || '',
        quickQuestionCreateUrl: scheduleCreateConfig.quickQuestionCreateUrl || '',
        quickChaptersBySubject: quickExamConfig.chaptersBySubject || {},
        quickDifficultyOptions: quickExamConfig.difficulties || [],
        quickQuestionTypes: quickExamConfig.questionTypes || [],
        quickQuestions: [],
        quickQuestionLoading: false,
        quickQuestionPage: 1,
        quickQuestionLastPage: 1,
        quickQuestionTotal: 0,
        quickQuestionKeyword: '',
        quickQuestionChapterId: '',
        quickQuestionDifficulty: '',
        quickQuestionSelectedIds: [],
        quickQuestionExpandedIds: [],
        quickQuestionSearchDebounce: null,
        quickQuestionFormError: '',
        quickQuestionCreatorChapterOptions: [],
        quickQuestionCreatorError: '',
        isSubmittingQuickQuestion: false,
        quickQuestionCreator: {
            subject_id: initialSubjectId || '',
            chapter_id: '',
            difficulty: (quickExamConfig.difficulties || [])[0]?.code || 'remember',
            question_type_id: '',
            content: '',
            options: ['', '', '', ''],
            correct_options: [0],
        },
        isSubmittingQuickExam: false,
        isLoadingExamPreview: false,
        examPreviewError: '',
        examPreviewData: null,
        isSavingQuickExamEdit: false,
        quickExamEditError: '',
        quickExamEditWarning: '',
        quickExamEditForm: {
            title: '',
            description: '',
            duration_minutes: 45,
        },
        quickPreviewUrlTemplate: scheduleCreateConfig.quickPreviewUrlTemplate || '',
        quickUpdateUrlTemplate: scheduleCreateConfig.quickUpdateUrlTemplate || '',
        examEditUrlTemplate: scheduleCreateConfig.examEditUrlTemplate || '',
        csrfToken: scheduleCreateConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',

        buildExamUrl(template, examId) {
            if (!template || !examId) {
                return '';
            }

            return template.replace('__EXAM_ID__', String(examId));
        },

        selectedExamOption() {
            const examSelect = document.getElementById('exam_id');
            if (!examSelect || examSelect.selectedIndex < 0) {
                return null;
            }

            return examSelect.options[examSelect.selectedIndex] || null;
        },

        selectedExamRoutes() {
            const selectedOption = this.selectedExamOption();
            const examId = this.selectedExamId || selectedOption?.value || '';

            if (!examId) {
                return {
                    previewUrl: '',
                    updateUrl: '',
                    editUrl: '',
                };
            }

            return {
                previewUrl: selectedOption?.getAttribute('data-preview-url') || this.buildExamUrl(this.quickPreviewUrlTemplate, examId),
                updateUrl: selectedOption?.getAttribute('data-quick-update-url') || this.buildExamUrl(this.quickUpdateUrlTemplate, examId),
                editUrl: selectedOption?.getAttribute('data-edit-url') || this.buildExamUrl(this.examEditUrlTemplate, examId),
            };
        },

        selectedExamEditUrl() {
            return this.selectedExamRoutes().editUrl || '#';
        },

        difficultyLabel(level) {
            const labels = {
                remember: 'Nhận biết',
                understand: 'Thông hiểu',
                apply: 'Vận dụng',
                analyze: 'Phân tích',
            };

            return labels[level] || level || '';
        },

        resetExamMetaState() {
            this.examPreviewError = '';
            this.quickExamEditError = '';
            this.quickExamEditWarning = '';
        },

        onSubjectChange() {
            this.hasSelectedSection = false;
            this.selectedExamId = '';
            this.examPreviewData = null;
            this.resetExamMetaState();
            document.querySelectorAll('input[name="course_section_ids[]"]').forEach(cb => cb.checked = false);
            this.quickSubjectId = this.selectedSubjectId;
            this.onQuickSubjectChange();
        },

        onSectionChange() {
            const checked = document.querySelectorAll('input[name="course_section_ids[]"]:checked');
            this.hasSelectedSection = checked.length > 0;
        },

        onQuickSubjectChange() {
            this.quickQuestionFormError = '';
            this.quickQuestionKeyword = '';
            this.quickQuestionChapterId = '';
            this.quickQuestionDifficulty = '';
            this.quickQuestions = [];
            this.quickQuestionPage = 1;
            this.quickQuestionLastPage = 1;
            this.quickQuestionTotal = 0;
            this.quickQuestionSelectedIds = [];
            this.quickQuestionExpandedIds = [];
            this.syncQuickQuestionHiddenInputs();

            this.quickQuestionCreator.subject_id = this.quickSubjectId ? String(this.quickSubjectId) : '';
            this.syncQuickQuestionCreatorChapterOptions();

            if (this.quickSubjectId) {
                this.loadQuickQuestions({
                    page: 1
                });
            }
        },

        quickChapterOptions() {
            if (!this.quickSubjectId) {
                return [];
            }

            return this.quickChaptersBySubject[String(this.quickSubjectId)] || [];
        },

        stripHtml(input) {
            const temp = document.createElement('div');
            temp.innerHTML = String(input || '');
            return (temp.textContent || temp.innerText || '').trim();
        },

        debouncedQuickQuestionSearch() {
            clearTimeout(this.quickQuestionSearchDebounce);
            this.quickQuestionSearchDebounce = setTimeout(() => {
                this.loadQuickQuestions({
                    page: 1
                });
            }, 300);
        },

        quickQuestionHasMore() {
            return this.quickQuestionPage < this.quickQuestionLastPage;
        },

        isQuickQuestionSelected(questionId) {
            return this.quickQuestionSelectedIds.includes(Number(questionId));
        },

        toggleQuickQuestionSelection(questionId, checked) {
            const numericId = Number(questionId);
            const nextIds = new Set(this.quickQuestionSelectedIds);

            if (checked) {
                nextIds.add(numericId);
            } else {
                nextIds.delete(numericId);
            }

            this.quickQuestionSelectedIds = Array.from(nextIds);
            this.quickQuestionFormError = '';
            this.syncQuickQuestionHiddenInputs();
        },

        toggleQuickQuestionPreview(questionId) {
            const numericId = Number(questionId);
            const next = new Set(this.quickQuestionExpandedIds);

            if (next.has(numericId)) {
                next.delete(numericId);
            } else {
                next.add(numericId);
            }

            this.quickQuestionExpandedIds = Array.from(next);
        },

        isQuickQuestionPreviewOpen(questionId) {
            return this.quickQuestionExpandedIds.includes(Number(questionId));
        },

        syncQuickQuestionHiddenInputs() {
            const container = document.getElementById('quick-selected-questions-container');
            if (!container) {
                return;
            }

            container.innerHTML = '';
            this.quickQuestionSelectedIds.forEach((questionId) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question_ids[]';
                input.value = String(questionId);
                container.appendChild(input);
            });
        },

        async loadQuickQuestions({
            page = 1,
            append = false
        } = {}) {
            if (!this.quickSubjectId) {
                return;
            }

            this.quickQuestionLoading = true;

            if (!append) {
                this.quickQuestions = [];
                this.quickQuestionExpandedIds = [];
            }

            const params = new URLSearchParams({
                subject_id: String(this.quickSubjectId),
                page: String(page),
                per_page: '20',
            });

            if (this.quickQuestionChapterId) {
                params.append('chapter_id', this.quickQuestionChapterId);
            }

            if (this.quickQuestionDifficulty) {
                params.append('difficulty', this.quickQuestionDifficulty);
            }

            const keyword = this.quickQuestionKeyword.trim();
            if (keyword) {
                params.append('keyword', keyword);
            }

            try {
                const response = await fetch(`${this.quickQuestionApiUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Không thể tải danh sách câu hỏi.');
                }

                const payload = await response.json();
                const incomingItems = Array.isArray(payload?.data) ? payload.data : [];

                this.quickQuestionPage = Number(payload?.current_page || page);
                this.quickQuestionLastPage = Number(payload?.last_page || page);
                this.quickQuestionTotal = Number(payload?.total || incomingItems.length);

                if (append) {
                    this.quickQuestions = [...this.quickQuestions, ...incomingItems];
                } else {
                    this.quickQuestions = incomingItems;
                }
            } catch (error) {
                this.quickQuestions = [];
                this.quickQuestionPage = 1;
                this.quickQuestionLastPage = 1;
                this.quickQuestionTotal = 0;

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: error?.message || 'Không thể tải danh sách câu hỏi.',
                        type: 'error'
                    }
                }));
            } finally {
                this.quickQuestionLoading = false;
            }
        },

        quickQuestionTypeCodeById(typeId) {
            const numericId = Number(typeId);
            const found = this.quickQuestionTypes.find((type) => Number(type.id) === numericId);
            return found?.code || '';
        },

        quickQuestionCreatorUsesCheckbox() {
            return this.quickQuestionTypeCodeById(this.quickQuestionCreator.question_type_id) === 'multiple_choice';
        },

        quickQuestionCorrectHint() {
            return this.quickQuestionCreatorUsesCheckbox() ?
                'Loại nhiều đáp án: có thể chọn nhiều phương án đúng.' :
                'Loại một đáp án: chỉ được chọn 1 phương án đúng.';
        },

        syncQuickQuestionCreatorChapterOptions() {
            const subjectId = String(this.quickQuestionCreator.subject_id || '');
            this.quickQuestionCreatorChapterOptions = this.quickChaptersBySubject[subjectId] || [];

            if (
                this.quickQuestionCreator.chapter_id &&
                !this.quickQuestionCreatorChapterOptions.some((chapter) => String(chapter.id) === String(this.quickQuestionCreator.chapter_id))
            ) {
                this.quickQuestionCreator.chapter_id = '';
            }
        },

        onQuickQuestionTypeChanged() {
            if (this.quickQuestionCreatorUsesCheckbox()) {
                if (!Array.isArray(this.quickQuestionCreator.correct_options) || this.quickQuestionCreator.correct_options.length === 0) {
                    this.quickQuestionCreator.correct_options = [0];
                }
                return;
            }

            const firstSelected = Array.isArray(this.quickQuestionCreator.correct_options) && this.quickQuestionCreator.correct_options.length > 0 ?
                this.quickQuestionCreator.correct_options[0] :
                0;
            this.quickQuestionCreator.correct_options = [Number(firstSelected)];
        },

        openQuickQuestionCreateModal() {
            if (!this.quickSubjectId) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Hãy chọn môn học trước khi tạo câu hỏi nhanh.',
                        type: 'error'
                    }
                }));
                return;
            }

            this.resetQuickQuestionCreatorForm();
            this.quickQuestionCreator.subject_id = String(this.quickSubjectId);
            this.syncQuickQuestionCreatorChapterOptions();
            this.quickQuestionCreatorError = '';
            this.$dispatch('open-modal', 'quick-question-modal');
        },

        addQuickQuestionCreatorOption() {
            if (this.quickQuestionCreator.options.length >= 12) {
                return;
            }

            this.quickQuestionCreator.options.push('');
        },

        removeQuickQuestionCreatorOption(index) {
            if (this.quickQuestionCreator.options.length <= 2) {
                return;
            }

            this.quickQuestionCreator.options.splice(index, 1);
            this.quickQuestionCreator.correct_options = this.quickQuestionCreator.correct_options
                .filter((selectedIndex) => selectedIndex !== index)
                .map((selectedIndex) => selectedIndex > index ? selectedIndex - 1 : selectedIndex);

            if (this.quickQuestionCreator.correct_options.length === 0) {
                this.quickQuestionCreator.correct_options = [0];
            }
        },

        toggleQuickQuestionCreatorCorrect(index, checked) {
            const numericIndex = Number(index);

            if (this.quickQuestionCreatorUsesCheckbox()) {
                const next = new Set(this.quickQuestionCreator.correct_options);
                if (checked) {
                    next.add(numericIndex);
                } else {
                    next.delete(numericIndex);
                }
                this.quickQuestionCreator.correct_options = Array.from(next);
                return;
            }

            this.quickQuestionCreator.correct_options = [numericIndex];
        },

        resetQuickQuestionCreatorForm() {
            this.quickQuestionCreator = {
                subject_id: this.quickSubjectId ? String(this.quickSubjectId) : '',
                chapter_id: '',
                difficulty: this.quickDifficultyOptions[0]?.code || 'remember',
                question_type_id: '',
                content: '',
                options: ['', '', '', ''],
                correct_options: [0],
            };
            this.quickQuestionCreatorError = '';
            this.syncQuickQuestionCreatorChapterOptions();
        },

        validateQuickQuestionCreator() {
            if (!this.quickQuestionCreator.subject_id) {
                return 'Môn học là bắt buộc.';
            }

            if (!this.quickQuestionCreator.question_type_id) {
                return 'Loại câu hỏi là bắt buộc.';
            }

            if (!String(this.quickQuestionCreator.content || '').trim()) {
                return 'Nội dung câu hỏi là bắt buộc.';
            }

            const normalizedOptions = this.quickQuestionCreator.options
                .map((value) => String(value || '').trim())
                .filter((value) => value.length > 0);

            if (normalizedOptions.length < 2) {
                return 'Cần ít nhất 2 phương án trả lời có nội dung.';
            }

            if (!Array.isArray(this.quickQuestionCreator.correct_options) || this.quickQuestionCreator.correct_options.length === 0) {
                return 'Vui lòng chọn ít nhất một đáp án đúng.';
            }

            const hasValidCorrectOption = this.quickQuestionCreator.correct_options.some((optionIndex) => {
                const optionValue = this.quickQuestionCreator.options[Number(optionIndex)];
                return String(optionValue || '').trim().length > 0;
            });

            if (!hasValidCorrectOption) {
                return 'Vui lòng chọn đáp án đúng hợp lệ (không để trống).';
            }

            return '';
        },

        async submitQuickQuestionCreator() {
            this.quickQuestionCreatorError = '';
            const validationMessage = this.validateQuickQuestionCreator();
            if (validationMessage) {
                this.quickQuestionCreatorError = validationMessage;
                return;
            }

            const payload = new FormData();
            payload.append('subject_id', String(this.quickQuestionCreator.subject_id));
            payload.append('chapter_id', this.quickQuestionCreator.chapter_id ? String(this.quickQuestionCreator.chapter_id) : '');
            payload.append('question_type_id', String(this.quickQuestionCreator.question_type_id));
            payload.append('difficulty', String(this.quickQuestionCreator.difficulty || 'remember'));
            payload.append('content', String(this.quickQuestionCreator.content || '').trim());

            const normalizedOptions = this.quickQuestionCreator.options
                .map((value, index) => ({
                    originalIndex: index,
                    content: String(value || '').trim(),
                }))
                .filter((option) => option.content.length > 0);

            normalizedOptions.forEach((option, index) => {
                payload.append(`options[${index}][content]`, option.content);
            });

            const normalizedIndexMap = new Map(
                normalizedOptions.map((option, normalizedIndex) => [option.originalIndex, normalizedIndex])
            );
            const normalizedCorrectOptions = Array.from(new Set(
                this.quickQuestionCreator.correct_options
                    .map((optionIndex) => normalizedIndexMap.get(Number(optionIndex)))
                    .filter((optionIndex) => Number.isInteger(optionIndex))
            ));

            if (normalizedCorrectOptions.length === 0) {
                this.quickQuestionCreatorError = 'Vui lòng chọn đáp án đúng hợp lệ (không để trống).';
                return;
            }

            normalizedCorrectOptions.forEach((optionIndex) => {
                payload.append('correct_options[]', String(optionIndex));
            });

            this.isSubmittingQuickQuestion = true;
            try {
                const response = await fetch(this.quickQuestionCreateUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: payload,
                });

                const responseData = await response.json();
                if (!response.ok) {
                    const firstError = Object.values(responseData?.errors || {})[0];
                    this.quickQuestionCreatorError = Array.isArray(firstError) ?
                        firstError[0] :
                        (responseData?.error || responseData?.message || 'Không thể tạo câu hỏi nhanh.');
                    return;
                }

                const newQuestionId = Number(responseData?.id || 0);
                if (newQuestionId > 0 && !this.quickQuestionSelectedIds.includes(newQuestionId)) {
                    this.quickQuestionSelectedIds = [...this.quickQuestionSelectedIds, newQuestionId];
                    this.syncQuickQuestionHiddenInputs();
                }

                this.$dispatch('close-modal', 'quick-question-modal');
                this.resetQuickQuestionCreatorForm();
                await this.loadQuickQuestions({
                    page: 1
                });

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Đã tạo câu hỏi mới và thêm vào đề nhanh.',
                        type: 'success'
                    }
                }));
            } catch (error) {
                this.quickQuestionCreatorError = error?.message || 'Không thể tạo câu hỏi nhanh.';
            } finally {
                this.isSubmittingQuickQuestion = false;
            }
        },

        async loadExamPreview() {
            const {
                previewUrl
            } = this.selectedExamRoutes();
            if (!previewUrl) {
                this.examPreviewError = 'Vui lòng chọn đề thi trước khi xem chi tiết.';
                this.examPreviewData = null;
                return;
            }

            this.isLoadingExamPreview = true;
            this.examPreviewError = '';
            this.quickExamEditError = '';

            try {
                const response = await fetch(previewUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                let responseData = null;
                try {
                    responseData = await response.json();
                } catch (_) {
                    responseData = null;
                }

                if (!response.ok) {
                    throw new Error(responseData?.message || 'Không thể tải dữ liệu đề thi.');
                }

                this.examPreviewData = responseData;
                this.quickExamEditForm = {
                    title: responseData?.title || '',
                    description: responseData?.description || '',
                    duration_minutes: responseData?.duration_minutes || 45,
                };

                this.quickExamEditWarning = responseData?.can_edit_structure ?
                    '' :
                    'Đề thi đã có sinh viên làm bài, chỉ chỉnh sửa được tên và mô tả.';
            } catch (error) {
                this.examPreviewData = null;
                this.examPreviewError = error?.message || 'Không thể tải dữ liệu đề thi.';
            } finally {
                this.isLoadingExamPreview = false;
            }
        },

        async openExamPreviewModal() {
            if (!this.selectedExamId) {
                return;
            }

            this.$dispatch('open-modal', 'exam-preview-modal');
            await this.loadExamPreview();
        },

        async openExamEditModal() {
            if (!this.selectedExamId) {
                return;
            }

            this.$dispatch('open-modal', 'quick-edit-exam-modal');
            await this.loadExamPreview();
        },

        async submitQuickExamEdit() {
            if (!this.selectedExamId) {
                this.quickExamEditError = 'Vui lòng chọn đề thi cần chỉnh sửa.';
                return;
            }

            const {
                updateUrl
            } = this.selectedExamRoutes();
            if (!updateUrl) {
                this.quickExamEditError = 'Không xác định được đường dẫn cập nhật đề thi.';
                return;
            }

            this.isSavingQuickExamEdit = true;
            this.quickExamEditError = '';

            const payload = {
                title: this.quickExamEditForm.title,
                description: this.quickExamEditForm.description,
            };

            if (this.examPreviewData?.can_edit_structure !== false) {
                payload.duration_minutes = this.quickExamEditForm.duration_minutes;
            }

            try {
                const response = await fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                let responseData = null;
                try {
                    responseData = await response.json();
                } catch (_) {
                    responseData = null;
                }

                if (response.status === 422) {
                    const firstError = Object.values(responseData?.errors || {})[0];
                    this.quickExamEditError = Array.isArray(firstError) ?
                        firstError[0] :
                        'Dữ liệu cập nhật chưa hợp lệ.';
                    return;
                }

                if (!response.ok) {
                    throw new Error(responseData?.message || 'Không thể cập nhật đề thi.');
                }

                const updatedExam = responseData?.exam || null;
                const selectedOption = this.selectedExamOption();

                if (selectedOption && updatedExam) {
                    const subjectCode = updatedExam.subject_code || 'SUB';
                    selectedOption.textContent = `[${subjectCode}] ${updatedExam.title}`;
                    selectedOption.setAttribute('data-preview-url', this.buildExamUrl(this.quickPreviewUrlTemplate, updatedExam.id));
                    selectedOption.setAttribute('data-quick-update-url', this.buildExamUrl(this.quickUpdateUrlTemplate, updatedExam.id));
                    selectedOption.setAttribute('data-edit-url', this.buildExamUrl(this.examEditUrlTemplate, updatedExam.id));
                }

                if (this.examPreviewData && updatedExam) {
                    this.examPreviewData.title = updatedExam.title;
                    this.examPreviewData.description = updatedExam.description;
                    this.examPreviewData.duration_minutes = updatedExam.duration_minutes;
                    this.examPreviewData.can_edit_structure = updatedExam.can_edit_structure;
                    this.examPreviewData.subject = this.examPreviewData.subject || {};
                    this.examPreviewData.subject.code = this.examPreviewData.subject.code || updatedExam.subject_code;
                }

                this.quickExamEditWarning = responseData?.warning || '';
                this.$dispatch('close-modal', 'quick-edit-exam-modal');

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: responseData?.message || 'Đã cập nhật đề thi thành công.',
                        type: 'success'
                    }
                }));
            } catch (error) {
                this.quickExamEditError = error?.message || 'Không thể cập nhật đề thi.';
            } finally {
                this.isSavingQuickExamEdit = false;
            }
        },

        async submitQuickExamForm(formElement) {
            this.isSubmittingQuickExam = true;
            this.quickQuestionFormError = '';
            this.syncQuickQuestionHiddenInputs();

            if (this.quickQuestionSelectedIds.length === 0) {
                this.isSubmittingQuickExam = false;
                this.quickQuestionFormError = 'Vui lòng chọn ít nhất một câu hỏi để tạo đề nhanh.';
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: this.quickQuestionFormError,
                        type: 'error'
                    }
                }));
                return;
            }

            const formData = new FormData(formElement);

            try {
                const response = await fetch(scheduleCreateConfig.examStoreUrl || '', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                let responseData = null;
                try {
                    responseData = await response.json();
                } catch (_) {
                    responseData = null;
                }

                if (response.status === 422) {
                    const firstError = Object.values(responseData?.errors || {})[0];
                    const message = Array.isArray(firstError) ?
                        firstError[0] :
                        (responseData?.message || 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.');
                    throw new Error(message);
                }

                if (!response.ok || !responseData?.success || !responseData?.exam?.id) {
                    throw new Error(responseData?.message || 'Không thể tạo đề thi nhanh. Vui lòng dùng trình tạo đầy đủ.');
                }

                const examData = responseData.exam;
                const examId = examData.id;

                const selectedSubject = formElement.querySelector('select[name="subject_id"] option:checked');
                const subjectId = examData.subject_id || (selectedSubject ? selectedSubject.value : '');
                const subjectCode = examData.subject_code || (selectedSubject ? selectedSubject.textContent.split(' - ')[0] : 'SUB');
                const title = examData.title || String(formData.get('title') || 'Đề thi mới');

                const examSelect = document.getElementById('exam_id');
                if (examSelect) {
                    const existing = Array.from(examSelect.options).some(opt => String(opt.value) === String(examId));
                    if (!existing) {
                        const option = document.createElement('option');
                        option.value = examId;
                        option.setAttribute('data-subject-id', subjectId);
                        option.setAttribute('data-preview-url', examData.preview_url || this.buildExamUrl(this.quickPreviewUrlTemplate, examId));
                        option.setAttribute('data-quick-update-url', examData.quick_update_url || this.buildExamUrl(this.quickUpdateUrlTemplate, examId));
                        option.setAttribute('data-edit-url', examData.edit_url || this.buildExamUrl(this.examEditUrlTemplate, examId));
                        option.textContent = `[${subjectCode}] ${title}`;
                        examSelect.appendChild(option);
                    }
                    examSelect.value = examId;
                    examSelect.dispatchEvent(new Event('change'));
                }

                this.$dispatch('close-modal', 'quick-create-exam-modal');
                formElement.reset();
                this.quickSubjectId = '';
                this.onQuickSubjectChange();
                this.resetQuickQuestionCreatorForm();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Đã tạo đề thi và tự động chọn vào lịch thi.',
                        type: 'success'
                    }
                }));
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: error?.message || 'Không thể tạo đề thi nhanh. Hãy kiểm tra dữ liệu đầu vào.',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingQuickExam = false;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const examSelect = document.getElementById('exam_id');
    const examOptions = Array.from(examSelect.options);
    const sectionContainer = document.getElementById('section-selection-container');
    const mainFields = document.getElementById('main-form-fields');
    const sectionItems = document.querySelectorAll('.section-item');
    const noSectionsMsg = document.getElementById('no-sections-msg');
    const sectionsList = document.getElementById('sections-list');

    const rootEl = document.querySelector('[data-pre-selected-subject-id]');
    const preSelectedSubjectId = rootEl ? rootEl.dataset.preSelectedSubjectId : '';

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

    const alpineRoot = document.querySelector('[x-data^="scheduleCreateManager"]');
    if (alpineRoot && alpineRoot.__x && typeof alpineRoot.__x.$data.onQuickSubjectChange === 'function') {
        alpineRoot.__x.$data.onQuickSubjectChange();
    }
});
