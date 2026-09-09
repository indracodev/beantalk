/**
 * BEANTALK LOGIN PAGE JAVASCRIPT
 */

function fillCredentials(user, pass) {
    const loginInput = document.getElementById('login');
    const passInput = document.getElementById('password');
    if (loginInput) loginInput.value = user;
    if (passInput) passInput.value = pass;
}

function getInitialTheme() {
    return localStorage.getItem('beantalk_theme') === 'dark' ? 'dark' : 'light';
}

function applyTheme(theme) {
    const html = document.documentElement;
    const label = document.getElementById('themeLabel');
    const icon = document.getElementById('themeIcon');

    if (theme === 'dark') {
        html.classList.remove('theme-light');
        html.classList.add('theme-dark');
        if (label) label.textContent = 'Light Mode';
        if (icon) {
            icon.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
        }
    } else {
        html.classList.remove('theme-dark');
        html.classList.add('theme-light');
        if (label) label.textContent = 'Dark Mode';
        if (icon) {
            icon.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
        }
    }
}

function toggleTheme() {
    const current = document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('beantalk_theme', next);
    applyTheme(next);
}

// Inisialisasi awal tema light (bukan sistem)
applyTheme(getInitialTheme());
