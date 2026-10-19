(function () {
    const storageKey = 'weather-io-theme';
    const savedTheme = localStorage.getItem(storageKey);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.body.classList.add('dark-mode');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const darkModeBtn = document.getElementById('darkModeSwitch');
        if (!darkModeBtn) return;

        const updateButton = () => {
            const isDark = document.body.classList.contains('dark-mode');
            darkModeBtn.textContent = isDark ? '☀️ Light mode' : '🌙 Dark mode';
            darkModeBtn.setAttribute('aria-pressed', String(isDark));
        };

        updateButton();
        darkModeBtn.addEventListener('click', () => {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem(storageKey, isDark ? 'dark' : 'light');
            updateButton();
        });
    });
})();