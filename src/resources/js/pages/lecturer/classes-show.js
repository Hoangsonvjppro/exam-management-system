const classesShowConfigEl = document.getElementById('lecturer-class-show-config');
const classesShowConfig = classesShowConfigEl ? JSON.parse(classesShowConfigEl.textContent || '{}') : {};

function getClassesShowConfig() {
    return classesShowConfig;
}

window.inviteCodeCardState = function inviteCodeCardState() {
    return {
        copied: false,
        inviteCode: '',
        joinClassQrUrl: '',

        init() {
            this.inviteCode = String(this.$el?.dataset?.inviteCode || '').trim().toUpperCase();
            this.joinClassQrUrl = String(this.$el?.dataset?.joinQrUrl || '').trim();
        },

        copyInviteCode() {
            if (!this.inviteCode) {
                return;
            }

            if (!navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Trình duyệt không hỗ trợ sao chép tự động.',
                        type: 'error'
                    }
                }));
                return;
            }

            navigator.clipboard.writeText(this.inviteCode)
                .then(() => {
                    this.copied = true;
                    window.setTimeout(() => {
                        this.copied = false;
                    }, 1800);
                })
                .catch(() => {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: 'Không thể sao chép mã mời.',
                            type: 'error'
                        }
                    }));
                });
        },

        showInviteQr() {
            if (!this.inviteCode) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Không tìm thấy mã mời lớp học phần.',
                        type: 'error'
                    }
                }));
                return;
            }

            const joinClassUrl = new URL(this.joinClassQrUrl || `${window.location.origin}/join-class/qr`, window.location.origin);
            joinClassUrl.searchParams.set('invite_code', this.inviteCode);

            const inviteCodeEl = document.getElementById('display-invite-code');
            const inviteQrEl = document.getElementById('display-invite-qr-code');

            if (inviteCodeEl) {
                inviteCodeEl.textContent = this.inviteCode;
            }

            if (inviteQrEl) {
                const qrSize = window.innerWidth >= 1024 ? 420 : (window.innerWidth >= 640 ? 360 : 300);
                inviteQrEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=${qrSize}x${qrSize}&margin=2&format=png&data=${encodeURIComponent(joinClassUrl.toString())}`;
            }

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'show-invite-code-modal'
            }));
        }
    };
};

window.studentRowMenuState = function studentRowMenuState() {
    return {
        open: false,
        confirmingRemove: false,
        menuTop: 0,
        menuLeft: 0,
        menuWidth: 248,

        placeMenu() {
            const rect = this.$refs.menuBtn.getBoundingClientRect();
            this.menuTop = rect.bottom + 8;
            this.menuLeft = Math.max(12, rect.right - this.menuWidth);
        },

        toggleMenu() {
            this.open = !this.open;
            this.confirmingRemove = false;

            if (this.open) {
                this.$nextTick(() => this.placeMenu());
            }
        },

        closeMenu() {
            this.open = false;
            this.confirmingRemove = false;
        },
    };
};

window.attendanceManager = function attendanceManager(sectionId) {
    return {
        records: {},
        sessions: {},
        isSubmittingSession: false,
        isUpdatingRecord: false,
        isTogglingSession: false,

        showPinCode(code) {
            if (!code) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Không tìm thấy mã điểm danh',
                        type: 'error'
                    }
                }));
                return;
            }

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'show-pin-modal'
            }));
            document.getElementById('display-pin-code').textContent = code;
            const qrSize = window.innerWidth >= 1024 ? 420 : (window.innerWidth >= 640 ? 360 : 300);
            const checkinUrl = `${window.location.origin}/student/classes/${sectionId}?tab=attendance&qr_code=${code}`;
            document.getElementById('display-qr-code').src = `https://api.qrserver.com/v1/create-qr-code/?size=${qrSize}x${qrSize}&margin=2&format=png&data=${encodeURIComponent(checkinUrl)}`;
        },

        async toggleSessionOpen(sessionId) {
            if (this.isTogglingSession) return;
            this.isTogglingSession = true;

            try {
                const response = await fetch(`/lecturer/classes/${sectionId}/attendance-sessions/${sessionId}/toggle-open`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();
                if (response.ok && result.success) {
                    this.sessions[sessionId].is_open = result.is_open;
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success'
                        }
                    }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message || 'Lỗi trạng thái',
                            type: 'error'
                        }
                    }));
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mạng',
                        type: 'error'
                    }
                }));
            } finally {
                this.isTogglingSession = false;
            }
        },

        async submitAttendanceSessionForm(formElement) {
            this.isSubmittingSession = true;
            formElement.querySelectorAll('[data-error]').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });

            const formData = new FormData(formElement);

            try {
                const response = await fetch(`/lecturer/classes/${sectionId}/attendance-sessions`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success'
                        }
                    }));
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422 && result.errors) {
                    for (const [field, messages] of Object.entries(result.errors)) {
                        const errorEl = formElement.querySelector(`[data-error="${field}"]`);
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.remove('hidden');
                        }
                    }
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message || 'Có lỗi xảy ra',
                            type: 'error'
                        }
                    }));
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi hệ thống',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingSession = false;
            }
        },

        async toggleStatus(sessionId, studentId, recordId) {
            if (this.isUpdatingRecord || !recordId) return;

            const currentStatus = this.records[`${sessionId}_${studentId}`];
            let nextStatus = 'present';
            if (currentStatus === 'present') nextStatus = 'absent';
            else if (currentStatus === 'absent') nextStatus = 'excused';
            else if (currentStatus === 'excused') nextStatus = 'present';

            // Optimistic update
            this.records[`${sessionId}_${studentId}`] = nextStatus;
            this.isUpdatingRecord = true;

            try {
                const response = await fetch(`/lecturer/classes/${sectionId}/attendance-sessions/${sessionId}/records/${recordId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status: nextStatus
                    })
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    // Revert on fail
                    this.records[`${sessionId}_${studentId}`] = currentStatus;
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message || 'Không thể lưu trạng thái',
                            type: 'error'
                        }
                    }));
                }
            } catch (error) {
                this.records[`${sessionId}_${studentId}`] = currentStatus;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mất kết nối mạng',
                        type: 'error'
                    }
                }));
            } finally {
                this.isUpdatingRecord = false;
            }
        }
    }
}

window.leaveRequestManager = function leaveRequestManager(sectionId) {
    return {
        isUpdatingLeave: false,
        activeRequestId: null,

        async updateLeaveStatus(requestId, status) {
            if (this.isUpdatingLeave) {
                return;
            }

            this.isUpdatingLeave = true;
            this.activeRequestId = requestId;

            try {
                const response = await fetch(`/lecturer/classes/${sectionId}/leave-requests/${requestId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        status,
                    }),
                });

                if (response.ok) {
                    window.location.reload();
                    return;
                }

                const result = await response.json().catch(() => ({}));
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: result.message || 'Không thể cập nhật đơn xin nghỉ phép.',
                        type: 'error',
                    },
                }));
            } catch (_) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mạng, vui lòng thử lại.',
                        type: 'error',
                    },
                }));
            } finally {
                this.isUpdatingLeave = false;
                this.activeRequestId = null;
            }
        },
    };
}

window.classWorkspaceManager = function classWorkspaceManager(initialTab) {
    return {
        activeTab: initialTab || 'overview',
        isSubmittingSchedule: false,
        isSubmittingQuickExam: false,
        studentDetailLoading: false,
        studentDetailError: '',
        studentDetailStudentName: '',
        studentDetailEnrollmentLabel: '',
        studentDetailSummary: {
            attempt_count: 0,
            completed_count: 0,
            average_score: null,
            highest_score: null,
        },
        studentExamAttempts: [],
        removingStudentId: null,

        init() {
            window.addEventListener('lecturer-student-open-detail', (event) => {
                const detail = event?.detail || {};
                this.openStudentDetail(detail.id, detail.name || '');
            });

            window.addEventListener('lecturer-student-remove', (event) => {
                const detail = event?.detail || {};
                this.removeStudentFromSection(detail.id, detail.name || '');
            });
        },

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        buildStudentUrl(studentId, template) {
            if (!template || !studentId) {
                return '';
            }

            return template.replace('__STUDENT_ID__', String(studentId));
        },

        resetStudentDetailState() {
            this.studentDetailError = '';
            this.studentDetailEnrollmentLabel = '';
            this.studentDetailSummary = {
                attempt_count: 0,
                completed_count: 0,
                average_score: null,
                highest_score: null,
            };
            this.studentExamAttempts = [];
        },

        formatAttemptScore(score) {
            if (score === null || score === undefined || score === '') {
                return '—';
            }

            const numericScore = Number.parseFloat(score);
            if (!Number.isFinite(numericScore)) {
                return '—';
            }

            return `${numericScore.toFixed(1)}/10`;
        },

        formatCorrectCount(correctCount, questionCount) {
            if (correctCount === null || correctCount === undefined || correctCount === '') {
                return '—';
            }

            if (questionCount === null || questionCount === undefined || questionCount === '') {
                return `${correctCount}/—`;
            }

            return `${correctCount}/${questionCount}`;
        },

        async openStudentDetail(studentId, fallbackName = '') {
            this.studentDetailStudentName = fallbackName || '';
            this.resetStudentDetailState();
            this.studentDetailLoading = true;

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'student-detail-modal'
            }));

            try {
                const detailUrl = this.buildStudentUrl(
                    studentId,
                    getClassesShowConfig().studentDetailUrlTemplate || ''
                );

                if (!detailUrl) {
                    throw new Error('Không xác định được đường dẫn xem chi tiết sinh viên.');
                }

                const response = await fetch(detailUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok || !result?.success) {
                    throw new Error(result?.message || 'Không thể tải dữ liệu chi tiết sinh viên.');
                }

                const student = result.student || {};
                const summary = result.summary || {};

                this.studentDetailStudentName = student.name || fallbackName || '';
                this.studentDetailEnrollmentLabel = student.enrollment_status_label || '—';
                this.studentDetailSummary = {
                    attempt_count: Number(summary.attempt_count || 0),
                    completed_count: Number(summary.completed_count || 0),
                    average_score: summary.average_score ?? null,
                    highest_score: summary.highest_score ?? null,
                };
                this.studentExamAttempts = Array.isArray(result.attempts) ? result.attempts : [];
            } catch (error) {
                this.studentDetailError = error?.message || 'Không thể tải dữ liệu chi tiết sinh viên.';
            } finally {
                this.studentDetailLoading = false;
            }
        },

        async removeStudentFromSection(studentId, studentName = '') {
            if (this.removingStudentId) {
                return;
            }

            this.removingStudentId = studentId;

            try {
                const removeUrl = this.buildStudentUrl(
                    studentId,
                    getClassesShowConfig().studentRemoveUrlTemplate || ''
                );

                if (!removeUrl) {
                    throw new Error('Không xác định được đường dẫn xoá sinh viên.');
                }

                const response = await fetch(removeUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getClassesShowConfig().csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    }
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok || !result?.success) {
                    throw new Error(result?.message || 'Không thể xoá sinh viên khỏi lớp học phần.');
                }

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: result.message || `Đã xoá ${studentName || 'sinh viên'} khỏi lớp học phần.`,
                        type: 'success'
                    }
                }));

                window.setTimeout(() => {
                    window.location.reload();
                }, 600);
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: error?.message || 'Không thể xoá sinh viên khỏi lớp học phần.',
                        type: 'error'
                    }
                }));
            } finally {
                this.removingStudentId = null;
            }
        },

        clearErrors(formElement) {
            formElement.querySelectorAll('[data-error]').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
        },

        showErrors(formElement, errors) {
            for (const [field, messages] of Object.entries(errors)) {
                const errorEl = formElement.querySelector(`[data-error="${field}"]`);
                if (errorEl) {
                    errorEl.textContent = messages[0];
                    errorEl.classList.remove('hidden');
                }
            }
        },

        async submitScheduleForm(formElement) {
            if (this.isSubmittingSchedule) return;
            console.log('Manual submission triggered for form:', formElement);
            this.isSubmittingSchedule = true;
            this.clearErrors(formElement);

            const formData = new FormData(formElement);

            try {
                const response = await fetch(getClassesShowConfig().scheduleStoreUrl || '', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.$dispatch('close-slide-over', 'create-schedule-inline-slide');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success'
                        }
                    }));
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422 && result.errors) {
                    this.showErrors(formElement, result.errors);
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message || 'Có lỗi xảy ra.',
                            type: 'error'
                        }
                    }));
                }
            } catch (error) {
                console.error('Schedule submission error:', error);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Có lỗi hệ thống xảy ra: ' + error.message,
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingSchedule = false;
            }
        },

        async submitQuickExamForm(formElement) {
            this.isSubmittingQuickExam = true;

            const formData = new FormData(formElement);

            try {
                const response = await fetch(getClassesShowConfig().examStoreUrl || '', {
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
                        (responseData?.message || 'Không thể tạo đề thi nhanh. Vui lòng kiểm tra dữ liệu đầu vào.');
                    throw new Error(message);
                }

                if (!response.ok || !responseData?.success || !responseData?.exam?.id) {
                    throw new Error(responseData?.message || 'Không thể tạo đề thi nhanh. Vui lòng thử lại với trình tạo đầy đủ.');
                }

                const examData = responseData.exam;
                const examId = examData.id;

                const title = examData.title || String(formData.get('title') || 'Đề thi mới');
                const examSelect = document.getElementById('inline-exam-id');
                if (examSelect) {
                    const exists = Array.from(examSelect.options).some(opt => String(opt.value) === String(examId));
                    if (!exists) {
                        const option = document.createElement('option');
                        option.value = examId;
                        option.textContent = `[${getClassesShowConfig().subjectCode || 'SUB'}] ${title}`;
                        examSelect.appendChild(option);
                    }
                    examSelect.value = examId;
                }

                this.$dispatch('close-slide-over', 'quick-create-exam-slide');
                formElement.reset();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Đã tạo đề thi mới và tự động chọn vào lịch thi.',
                        type: 'success'
                    }
                }));
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: error?.message || 'Không thể tạo đề thi nhanh. Vui lòng kiểm tra dữ liệu đầu vào.',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingQuickExam = false;
            }
        },

    }
}

window.lecturerComplaintReviewModalState = function lecturerComplaintReviewModalState() {
    return {
        complaintId: null,
        reviewStudentName: '',
        reviewReason: '',
        reviewCurrentScore: '',
        reviewCurrentCorrectCount: 0,
        reviewTotalQuestions: 0,
        resolutionStatus: 'resolved',
        updatedCorrectCount: '',
        reviewerNote: '',
        isSubmitting: false,

        applyPrefill(payload = {}) {
            this.complaintId = payload.complaintId ?? null;
            this.reviewStudentName = payload.studentName ?? '';
            this.reviewReason = payload.reason ?? '';
            this.reviewCurrentScore = payload.currentScore ?? '';
            this.reviewCurrentCorrectCount = Number.parseInt(payload.currentCorrectCount ?? 0, 10) || 0;
            this.reviewTotalQuestions = Number.parseInt(payload.totalQuestions ?? 0, 10) || 0;
            this.resolutionStatus = 'resolved';
            this.updatedCorrectCount = this.reviewCurrentCorrectCount;
            this.reviewerNote = '';
            this.isSubmitting = false;
        },

        displayCurrentScore() {
            if (this.reviewCurrentScore === null || this.reviewCurrentScore === undefined || this.reviewCurrentScore === '') {
                return '—';
            }

            return `${this.reviewCurrentScore}/10`;
        },

        displayCurrentCorrectRatio() {
            if (!Number.isFinite(this.reviewCurrentCorrectCount) || this.reviewCurrentCorrectCount < 0) {
                return '—';
            }

            if (!Number.isFinite(this.reviewTotalQuestions) || this.reviewTotalQuestions <= 0) {
                return `${this.reviewCurrentCorrectCount}/—`;
            }

            return `${this.reviewCurrentCorrectCount}/${this.reviewTotalQuestions}`;
        },

        get previewScore() {
            const count = Number.parseInt(this.updatedCorrectCount, 10);
            if (!Number.isInteger(count) || this.reviewTotalQuestions <= 0) {
                return '—';
            }

            return ((count / this.reviewTotalQuestions) * 10).toFixed(1);
        },

        async submitReview() {
            if (!this.complaintId) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Không xác định được khiếu nại cần xử lý.',
                        type: 'error'
                    }
                }));
                return;
            }

            if (!this.reviewerNote || this.reviewerNote.trim().length < 5) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Vui lòng nhập ghi chú phản hồi (ít nhất 5 ký tự).',
                        type: 'error'
                    }
                }));
                return;
            }

            let nextCorrectCount = null;
            if (this.resolutionStatus === 'resolved') {
                nextCorrectCount = Number.parseInt(this.updatedCorrectCount, 10);
                if (!Number.isInteger(nextCorrectCount) || nextCorrectCount < 0 || nextCorrectCount > this.reviewTotalQuestions) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: `Số câu đúng phải là số nguyên từ 0 đến ${this.reviewTotalQuestions}.`,
                            type: 'error'
                        }
                    }));
                    return;
                }
            }

            this.isSubmitting = true;

            try {
                const response = await fetch(`/lecturer/complaints/${this.complaintId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        status: this.resolutionStatus,
                        reviewer_note: this.reviewerNote,
                        updated_correct_count: this.resolutionStatus === 'resolved' ? nextCorrectCount : null
                    })
                });

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                const result = isJson ? await response.json() : null;

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result?.message || 'Đã lưu phản hồi trạng thái khiếu nại thành công.',
                            type: 'success'
                        }
                    }));
                    this.$dispatch('close-modal', 'review-modal');
                    setTimeout(() => window.location.reload(), 1200);
                    return;
                }

                const validationMessage = result?.errors ? Object.values(result.errors)?.[0]?.[0] : null;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: validationMessage || result?.message || `Có lỗi xảy ra (HTTP ${response.status}).`,
                        type: 'error'
                    }
                }));
            } catch (_) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi kết nối máy chủ',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmitting = false;
            }
        },
    };
};

window.gradeManager = function gradeManager(sectionId) {
    return {
        isSaving: false,
        isSubmittingColumn: false,
        isEditingColumn: false,
        editingColumnId: null,
        totalWeight: Number(getClassesShowConfig().gradeTotalWeight || 0),
        columnData: {
            name: '',
            weight: 10
        },
        scores: {},
        initialScores: {},
        saved: {},
        weights: getClassesShowConfig().gradeWeights || {},

        initData() { },

        editColumn(id, name, weight) {
            this.isEditingColumn = true;
            this.editingColumnId = id;
            this.columnData = {
                name: name,
                weight: weight
            };
            this.$dispatch('open-modal', 'column-modal');
        },

        async submitColumnForm() {
            const incomingWeight = Number.parseFloat(this.columnData.weight);
            const safeIncomingWeight = Number.isFinite(incomingWeight) ? incomingWeight : 0;
            const currentWeight = this.isEditingColumn
                ? (Number.parseFloat(this.weights[String(this.editingColumnId)]) || 0)
                : 0;
            const projectedTotalWeight = this.totalWeight - currentWeight + safeIncomingWeight;

            if (projectedTotalWeight > 100) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: `Tổng trọng số sau khi lưu sẽ là ${projectedTotalWeight.toFixed(2)}%. Vui lòng giảm xuống tối đa 100%.`,
                        type: 'error'
                    }
                }));
                return;
            }

            this.isSubmittingColumn = true;
            const url = this.isEditingColumn ?
                `/lecturer/classes/${sectionId}/grade-columns/${this.editingColumnId}` :
                `/lecturer/classes/${sectionId}/grade-columns`;
            const method = this.isEditingColumn ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.columnData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: data.message || 'Lỗi',
                            type: 'error'
                        }
                    }));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mạng',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingColumn = false;
            }
        },

        async deleteColumn(id) {
            if (!confirm('Xoá cột điểm này sẽ xoá toàn bộ điểm của sinh viên trong cột. Tiếp tục?')) return;
            try {
                const res = await fetch(`/lecturer/classes/${sectionId}/grade-columns/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) window.location.reload();
            } catch (e) { }
        },

        async saveScore(columnId, studentId, value) {
            const key = `${columnId}_${studentId}`;
            if (this.initialScores[key] === value) return; // Không thay đổi

            this.isSaving = true;
            try {
                const res = await fetch(`/lecturer/classes/${sectionId}/grade-columns/${columnId}/grades`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        score: value === '' ? null : parseFloat(value)
                    })
                });
                if (res.ok) {
                    this.initialScores[key] = value;
                    this.scores[key] = value;
                    this.saved[key] = true;
                    setTimeout(() => this.saved[key] = false, 2000);
                } else {
                    const err = await res.json();
                    this.scores[key] = this.initialScores[key]; // revert
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: err.message || 'Lỗi lưu điểm',
                            type: 'error'
                        }
                    }));
                }
            } catch (e) {
                this.scores[key] = this.initialScores[key]; // revert
            } finally {
                this.isSaving = false;
            }
        },

        calculateProcessGrade(studentId) {
            let total = 0;
            let hasAnyScore = false;
            for (const [colId, weight] of Object.entries(this.weights)) {
                const key = `${colId}_${studentId}`;
                const score = this.scores[key];
                if (score !== undefined && score !== '' && score !== null) {
                    total += (parseFloat(score) * (parseFloat(weight) / 100));
                    hasAnyScore = true;
                }
            }
            if (!this.totalWeight || this.totalWeight <= 0 || !hasAnyScore) return '-';

            // Guard against legacy invalid configurations where total weight exceeded 100.
            const normalized = (total * 100) / this.totalWeight;
            return Math.max(0, Math.min(10, normalized)).toFixed(2);
        }
    }
}
