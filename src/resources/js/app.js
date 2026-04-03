import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.dropdownState = function dropdownState(initialOpen = false) {
    return {
        open: Boolean(initialOpen),
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    };
};

window.mobileNavigationState = function mobileNavigationState() {
    return {
        open: false,
        toggle() {
            this.open = !this.open;
        },
    };
};

window.passwordInputState = function passwordInputState() {
    return {
        show: false,
        toggle() {
            this.show = !this.show;
        },
    };
};

window.timedFlashState = function timedFlashState(durationMs = 2000) {
    return {
        show: true,
        startTimer() {
            window.setTimeout(() => {
                this.show = false;
            }, Number(durationMs) || 2000);
        },
    };
};

window.searchFilterState = function searchFilterState(initialQuery = '') {
    return {
        searchQuery: String(initialQuery || ''),
    };
};

window.lecturerClassIndexFiltersState = function lecturerClassIndexFiltersState() {
    return {
        searchQuery: '',
        statusFilter: 'all',
        semesterFilter: localStorage.getItem('ems_main_filter_semester') || 'all',
        subjectFilter: localStorage.getItem('ems_main_filter_subject') || 'all',

        persistMainFilters() {
            localStorage.setItem('ems_main_filter_semester', this.semesterFilter || 'all');
            localStorage.setItem('ems_main_filter_subject', this.subjectFilter || 'all');
        },
    };
};

window.subjectFilterState = function subjectFilterState(initialValue = '') {
    return {
        subjectFilter: String(initialValue || ''),
    };
};

window.confirmActionState = function confirmActionState() {
    return {
        confirming: false,
    };
};

window.simpleLoadingState = function simpleLoadingState() {
    return {
        loading: false,
    };
};

window.resultsExpansionState = function resultsExpansionState() {
    return {
        expandedRows: [],
        toggleRow(index) {
            if (this.expandedRows.includes(index)) {
                this.expandedRows = this.expandedRows.filter((item) => item !== index);
                return;
            }

            this.expandedRows.push(index);
        },
    };
};

window.appLayoutSidebarState = function appLayoutSidebarState() {
    return {
        isSidebarPinned: false,
        isSidebarHovered: false,
        openClassMenu: true,
        openQuestionBank: true,
        openExamBank: false,

        persistMenuState() {
            localStorage.setItem('ems_sidebar_menu_state', JSON.stringify({
                openClassMenu: this.openClassMenu,
                openQuestionBank: this.openQuestionBank,
                openExamBank: this.openExamBank,
            }));
        },

        toggleClassMenu() {
            this.openClassMenu = !this.openClassMenu;
            this.persistMenuState();
        },

        toggleQuestionBank() {
            this.openQuestionBank = !this.openQuestionBank;
            this.persistMenuState();
        },

        toggleExamBank() {
            this.openExamBank = !this.openExamBank;
            this.persistMenuState();
        },

        get isExpanded() {
            return (this.isSidebarPinned || this.isSidebarHovered) || (window.innerWidth < 1024 && this.isSidebarPinned);
        },

        togglePin() {
            this.isSidebarPinned = !this.isSidebarPinned;
            localStorage.setItem('ems_sidebar_pinned', this.isSidebarPinned ? 'true' : 'false');

            if (!this.isSidebarPinned) {
                this.isSidebarHovered = false;
            }
        },

        onWindowResize() {
            if (window.innerWidth < 1024) {
                this.isSidebarPinned = false;
            }
        },

        init() {
            const storedMenuState = localStorage.getItem('ems_sidebar_menu_state');

            if (storedMenuState) {
                try {
                    const parsed = JSON.parse(storedMenuState);
                    this.openClassMenu = typeof parsed.openClassMenu === 'boolean' ? parsed.openClassMenu : this.openClassMenu;
                    this.openQuestionBank = typeof parsed.openQuestionBank === 'boolean' ? parsed.openQuestionBank : this.openQuestionBank;
                    this.openExamBank = typeof parsed.openExamBank === 'boolean' ? parsed.openExamBank : this.openExamBank;
                } catch (_) {
                    // Keep default values if local storage is malformed.
                }
            }

            const stored = localStorage.getItem('ems_sidebar_pinned');
            if (stored !== null) {
                this.isSidebarPinned = stored === 'true';
                return;
            }

            this.isSidebarPinned = window.innerWidth >= 1024;
        },
    };
};

window.darkModeToggleState = function darkModeToggleState() {
    return {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('darkMode', this.dark ? 'true' : 'false');
        },
    };
};

window.slideOverState = function slideOverState(name) {
    return {
        show: false,
        name,
        open() {
            this.show = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.show = false;
            document.body.classList.remove('overflow-hidden');
        },
    };
};

window.modalState = function modalState(initialShow = false) {
    return {
        show: Boolean(initialShow),
        focusables() {
            const selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
            return [...this.$el.querySelectorAll(selector)]
                .filter((el) => !el.hasAttribute('disabled'));
        },
        firstFocusable() {
            return this.focusables()[0];
        },
        lastFocusable() {
            return this.focusables().slice(-1)[0];
        },
        nextFocusable() {
            return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
        },
        prevFocusable() {
            return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
        },
        nextFocusableIndex() {
            return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1);
        },
        prevFocusableIndex() {
            return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1;
        },
    };
};

window.toastState = function toastState() {
    return {
        show: false,
        message: '',
        type: 'success',
        hideTimer: null,

        init() {
            window.addEventListener('toast', (event) => {
                this.showToast(event.detail.message, event.detail.type || 'success');
            });
        },

        showToast(message, type) {
            if (this.hideTimer) {
                window.clearTimeout(this.hideTimer);
            }

            this.message = message;
            this.type = type === 'error' ? 'danger' : type;
            this.show = true;

            this.hideTimer = window.setTimeout(() => {
                this.show = false;
            }, 5000);
        },
    };
};

window.scheduleMonitorState = function scheduleMonitorState() {
    return {
        autoRefresh: true,
        secondsLeft: 20,
        timer: null,

        startTimer() {
            this.timer = window.setInterval(() => {
                if (!this.autoRefresh) {
                    return;
                }

                if (this.secondsLeft <= 1) {
                    window.location.reload();
                    return;
                }

                this.secondsLeft -= 1;
            }, 1000);
        },

        refreshNow() {
            window.location.reload();
        },
    };
};

window.studentNotificationsModalState = function studentNotificationsModalState() {
    return {
        modalOpen: false,
        currentTitle: '',
        currentMessage: '',
        currentDate: '',
        currentClass: '',

        openModal(title, message, date, className) {
            this.currentTitle = title;
            this.currentMessage = message;
            this.currentDate = date;
            this.currentClass = className;
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },
    };
};

function bindGlobalDomActions() {
    document.addEventListener('change', (event) => {
        const autoSubmitInput = event.target.closest('[data-auto-submit="form"]');
        if (!autoSubmitInput) {
            return;
        }

        autoSubmitInput.form?.submit();
    });

    document.addEventListener('click', (event) => {
        const submitTrigger = event.target.closest('[data-submit-closest-form="true"]');
        if (submitTrigger) {
            event.preventDefault();
            submitTrigger.closest('form')?.submit();
            return;
        }

        const openTrigger = event.target.closest('[data-open-target]');
        if (openTrigger) {
            const selector = openTrigger.getAttribute('data-open-target');
            if (selector) {
                document.querySelector(selector)?.classList.remove('hidden');
            }
            return;
        }

        const closeTrigger = event.target.closest('[data-close-target]');
        if (closeTrigger) {
            const selector = closeTrigger.getAttribute('data-close-target');
            if (selector) {
                document.querySelector(selector)?.classList.add('hidden');
            }
            return;
        }

        const confirmTrigger = event.target.closest('[data-confirm-message]');
        if (!confirmTrigger) {
            return;
        }

        const message = confirmTrigger.getAttribute('data-confirm-message') || 'Are you sure?';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindGlobalDomActions();

    if (window.__alpineStarted) {
        return;
    }

    Alpine.start();
    window.__alpineStarted = true;
});
