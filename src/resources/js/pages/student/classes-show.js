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

window.studentClassWorkspace = function studentClassWorkspace(initialTab) {
    return {
        activeTab: initialTab || 'feed',
        complaintExamTitle: '',
        complaintCurrentScore: '',
        complaintAttemptId: '',
        complaintCorrectCount: '',
        complaintTotalQuestions: '',
        complaintReason: '',

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        openComplaintModal(examTitle, score, attemptId, correctCount, totalQuestions) {
            this.complaintExamTitle = examTitle;
            this.complaintCurrentScore = score;
            this.complaintAttemptId = attemptId;
            this.complaintCorrectCount = correctCount;
            this.complaintTotalQuestions = totalQuestions;
            this.complaintReason = '';
            this.$dispatch('open-modal', 'complaint-modal');
        },

        submitComplaint() {
            if (!this.complaintReason || this.complaintReason.trim().length < 10) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Vui lòng nhập lý do khiếu nại (ít nhất 10 ký tự).', type: 'error' }
                }));
                return;
            }

            fetch(studentClassShowConfig.complaintStoreUrl || '', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                body: JSON.stringify({
                    attempt_id: this.complaintAttemptId,
                    reason: this.complaintReason
                })
            })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    this.$dispatch('close-modal', 'complaint-modal');
                    if (res.status === 201 || res.status === 200) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: res.body.message, type: 'success' }
                        }));
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: res.body.message || 'Có lỗi xảy ra', type: 'error' }
                        }));
                    }
                })
                .catch(() => {
                    this.$dispatch('close-modal', 'complaint-modal');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Lỗi kết nối máy chủ', type: 'error' }
                    }));
                });
        },
    }
}
