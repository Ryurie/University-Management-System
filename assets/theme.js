// theme.js
document.addEventListener("DOMContentLoaded", () => {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Kunin ang na-save na theme
    const currentTheme = localStorage.getItem('ums_theme');

    // Kung dark mode ang naka-save, i-apply agad
    if (currentTheme === 'dark') {
        body.classList.add('dark-mode');
    }

    // Kung sakaling walang toggle button sa page, wag mag-error
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('ums_theme', 'dark');
            } else {
                localStorage.setItem('ums_theme', 'light');
            }
        });
    }
});