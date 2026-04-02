window.lecturerComplaints = function lecturerComplaints() {
    return {
        complaintId: null,
        reviewStudentName: '',
        reviewReason: '',
        reviewCurrentCorrectCount: 0,
        reviewTotalQuestions: 0,
        resolutionStatus: 'resolved',
        updatedCorrectCount: '',
        reviewerNote: '',
        isSubmitting: false,

        get currentScoreDisplay() {
            if (this.reviewTotalQuestions > 0) {
                return (this.reviewCurrentCorrectCount / this.reviewTotalQuestions * 10).toFixed(1) + '/10';
            }
            return '0/10';
        },

        get previewScore() {
            const count = parseInt(this.updatedCorrectCount);
            if (isNaN(count) || this.reviewTotalQuestions === 0) return '—';
            return (count / this.reviewTotalQuestions * 10).toFixed(1);
        },

        openReviewModal(id, studentName, reason, currentCorrectCount, totalQuestions) {
            this.complaintId = id;
            this.reviewStudentName = studentName;
            this.reviewReason = reason;
            this.reviewCurrentCorrectCount = currentCorrectCount;
            this.reviewTotalQuestions = totalQuestions;
            this.resolutionStatus = 'resolved';
            this.updatedCorrectCount = currentCorrectCount;
            this.reviewerNote = '';
            this.$dispatch('open-modal', 'review-modal');
        },

        submitReview() {
            if (!this.reviewerNote || this.reviewerNote.trim().length < 5) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Vui lòng nhập ghi chú phản hồi (ít nhất 5 ký tự).', type: 'error' }
                }));
                return;
            }

            if (this.resolutionStatus === 'resolved') {
                const count = parseInt(this.updatedCorrectCount);
                if (isNaN(count) || count < 0 || count > this.reviewTotalQuestions || this.updatedCorrectCount.toString().includes('.')) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: `Số câu đúng phải là số nguyên từ 0 đến ${this.reviewTotalQuestions}.`, type: 'error' }
                    }));
                    return;
                }
            }

            this.isSubmitting = true;

            fetch(`/lecturer/complaints/${this.complaintId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: this.resolutionStatus,
                    reviewer_note: this.reviewerNote,
                    updated_correct_count: this.resolutionStatus === 'resolved' ? parseInt(this.updatedCorrectCount) : null
                })
            })
                .then(r => r.json().then(data => ({ status: r.status, body: data })))
                .then(res => {
                    this.isSubmitting = false;
                    if (res.status === 200) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: res.body.message, type: 'success' }
                        }));
                        this.$dispatch('close-modal', 'review-modal');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        const errorMsg = res.body.errors
                            ? Object.values(res.body.errors).flat().join(', ')
                            : (res.body.message || 'Có lỗi xảy ra');
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { message: errorMsg, type: 'error' }
                        }));
                    }
                })
                .catch(() => {
                    this.isSubmitting = false;
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message: 'Lỗi kết nối máy chủ', type: 'error' }
                    }));
                });
        }
    }
}
