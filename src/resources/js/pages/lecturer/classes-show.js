const classesShowConfigEl = document.getElementById('lecturer-class-show-config');
const classesShowConfig = classesShowConfigEl ? JSON.parse(classesShowConfigEl.textContent || '{}') : {};

function getClassesShowConfig() {
    return classesShowConfig;
}

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
        isSubmittingReview: false,
        complaintId: null,
        reviewStudentName: '',
        reviewReason: '',
        reviewCurrentScore: 0,
        resolutionStatus: 'resolved',
        updatedScore: '',
        reviewerNote: '',

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
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
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Có lỗi hệ thống xảy ra!',
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

        openReviewModal(id, studentName, reason, currentScore) {
            this.complaintId = id;
            this.reviewStudentName = studentName;
            this.reviewReason = reason;
            this.reviewCurrentScore = currentScore;
            this.resolutionStatus = 'resolved';
            this.updatedScore = currentScore;
            this.reviewerNote = '';
            this.$dispatch('open-modal', 'review-modal');
        },

        async submitReview() {
            if (!this.reviewerNote || this.reviewerNote.trim().length < 5) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Vui lòng nhập ghi chú phản hồi (ít nhất 5 ký tự).',
                        type: 'error'
                    }
                }));
                return;
            }

            if (this.resolutionStatus === 'resolved' && (this.updatedScore === '' || this.updatedScore < 0)) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Vui lòng nhập điểm mới hợp lệ.',
                        type: 'error'
                    }
                }));
                return;
            }

            this.isSubmittingReview = true;

            try {
                const response = await fetch(`/lecturer/complaints/${this.complaintId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: this.resolutionStatus,
                        reviewer_note: this.reviewerNote,
                        updated_score: this.resolutionStatus === 'resolved' ? parseFloat(this.updatedScore) : null
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: result.message,
                            type: 'success'
                        }
                    }));
                    this.$dispatch('close-modal', 'review-modal');
                    setTimeout(() => window.location.reload(), 1500);
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
                        message: 'Lỗi kết nối máy chủ',
                        type: 'error'
                    }
                }));
            } finally {
                this.isSubmittingReview = false;
            }
        }
    }
}

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
            // Quy đổi ra thang 10 so với totalWeight của Process Grade (Điểm quá trình do GV nắm)
            return ((total * 100) / this.totalWeight).toFixed(2);
        }
    }
}
