const studentClassShowConfigEl = document.getElementById('student-class-show-config');
const studentClassShowConfig = studentClassShowConfigEl ? JSON.parse(studentClassShowConfigEl.textContent || '{}') : {};

window.studentAttendanceTab = function studentAttendanceTab(sectionId) {
    return {
        secretCode: '',
        isSubmittingCode: false,

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('qr_code')) {
                return;
            }

            this.secretCode = urlParams.get('qr_code') || '';
            window.history.replaceState({}, document.title, `${window.location.pathname}?tab=attendance`);
            this.submitQRCheckIn();
        },

        async submitQRCheckIn() {
            this.isSubmittingCode = true;

            try {
                const response = await fetch(`/student/classes/${sectionId}/attendance`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        secret_code: this.secretCode,
                    }),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success',
                        },
                    }));
                    setTimeout(() => window.location.reload(), 1000);
                    return;
                }

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: result.message || 'Mã không hợp lệ',
                        type: 'error',
                    },
                }));
            } catch (_) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mạng',
                        type: 'error',
                    },
                }));
            } finally {
                this.isSubmittingCode = false;
            }
        },
    };
};

window.studentLeaveRequestForm = function studentLeaveRequestForm(sectionId, defaultDate) {
    return {
        formDate: defaultDate,
        formReason: '',
        formProofImage: null,
        isSubmittingLeave: false,

        async submitLeaveRequest() {
            this.isSubmittingLeave = true;

            try {
                const formData = new FormData();
                formData.append('date', this.formDate);
                formData.append('reason', this.formReason);
                if (this.formProofImage) {
                    formData.append('proof_image', this.formProofImage);
                }

                const response = await fetch(`/student/classes/${sectionId}/leave-requests`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success',
                        },
                    }));
                    setTimeout(() => window.location.reload(), 1000);
                    return;
                }

                const validationMessage = result.errors ? Object.values(result.errors)?.[0]?.[0] : null;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: validationMessage || result.message || 'Lỗi gửi đơn',
                        type: 'error',
                    },
                }));
            } catch (_) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Lỗi mạng',
                        type: 'error',
                    },
                }));
            } finally {
                this.isSubmittingLeave = false;
            }
        },
    };
};

window.complaintModalState = function complaintModalState() {
    return {
        complaintExamTitle: '',
        complaintCurrentScore: '',
        complaintAttemptId: '',
        complaintCorrectCount: '',
        complaintTotalQuestions: '',
        complaintReason: '',

        applyPrefill(payload = {}) {
            this.complaintExamTitle = payload.examTitle ?? '';
            this.complaintCurrentScore = payload.score ?? '';
            this.complaintAttemptId = payload.attemptId ?? '';
            this.complaintCorrectCount = payload.correctCount ?? '';
            this.complaintTotalQuestions = payload.totalQuestions ?? '';
            this.complaintReason = '';
        },

        displayComplaintScore() {
            if (this.complaintCurrentScore === null || this.complaintCurrentScore === undefined || this.complaintCurrentScore === '') {
                return '—';
            }

            return `${this.complaintCurrentScore}/10`;
        },

        displayComplaintCorrectRatio() {
            if (this.complaintCorrectCount === null || this.complaintCorrectCount === undefined || this.complaintCorrectCount === '') {
                return '—';
            }

            if (this.complaintTotalQuestions === null || this.complaintTotalQuestions === undefined || this.complaintTotalQuestions === '') {
                return `${this.complaintCorrectCount}/—`;
            }

            return `${this.complaintCorrectCount}/${this.complaintTotalQuestions}`;
        },

        async submitComplaint() {
            if (!this.complaintAttemptId) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Không xác định được bài làm để khiếu nại. Vui lòng đóng hộp thoại và thử lại.', type: 'error' }
                }));
                return;
            }

            if (!this.complaintReason || this.complaintReason.trim().length < 10) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Vui lòng nhập lý do khiếu nại (ít nhất 10 ký tự).', type: 'error' }
                }));
                return;
            }

            try {
                const response = await fetch(studentClassShowConfig.complaintStoreUrl || '', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                    },
                    body: JSON.stringify({
                        attempt_id: this.complaintAttemptId,
                        reason: this.complaintReason
                    })
                });

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                const body = isJson ? await response.json() : null;

                if (response.ok) {
                    this.$dispatch('close-modal', 'complaint-modal');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: body?.message || 'Đã gửi khiếu nại thành công.', type: 'success' }
                    }));
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }

                const validationMessage = body?.errors ? Object.values(body.errors)?.[0]?.[0] : null;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: validationMessage || body?.message || `Gửi khiếu nại thất bại (HTTP ${response.status}).`,
                        type: 'error'
                    }
                }));
            } catch (_) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Lỗi kết nối máy chủ', type: 'error' }
                }));
            }
        },
    };
};

window.studentClassWorkspace = function studentClassWorkspace(initialTab) {
    return {
        activeTab: initialTab || 'feed',

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },
    }
}
