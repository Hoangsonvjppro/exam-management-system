document.addEventListener('DOMContentLoaded', () => {
    if (document.body?.dataset?.openJoinClassModal === '1') {
        document.getElementById('join-class-modal')?.classList.remove('hidden');
    }
});
