window.toggleLateSettings = function toggleLateSettings() {
    const checkbox = document.getElementById('allow_late_entrance');
    const settings = document.getElementById('late_settings');
    if (checkbox && settings) {
        settings.style.display = checkbox.checked ? 'block' : 'none';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.toggleLateSettings();
    document.querySelector('[data-action="toggle-late-settings"]')?.addEventListener('change', () => {
        window.toggleLateSettings();
    });
});
