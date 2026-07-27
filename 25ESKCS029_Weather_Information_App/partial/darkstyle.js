document.addEventListener('DOMContentLoaded', () => {
    // dark mode switch logic
    const darkModeBtn = document.getElementById('darkModeSwitch');
    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            
            // button ka text change kar diya
            if (document.body.classList.contains('dark-mode')) {
                darkModeBtn.textContent = 'Light Mode';
            } else {
                darkModeBtn.textContent = 'Dark Mode';
            }
        });
    }
});