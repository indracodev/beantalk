/**
 * BEANTALK ADMIN DASHBOARD — CORE JAVASCRIPT
 * Zero dependencies, pure native browser Web APIs.
 */

// ======================================================================
// SIDEBAR COLLAPSE TOGGLE
// ======================================================================
function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('beantalk_sidebar_collapsed', sidebar.classList.contains('collapsed'));
}

function initSidebar() {
    if (localStorage.getItem('beantalk_sidebar_collapsed') === 'true') {
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar) sidebar.classList.add('collapsed');
    }
}

// ======================================================================
// THEME TOGGLE — DEFAULT LIGHT (BUKAN SISTEM)
// ======================================================================
function applyTheme(theme) {
    const html = document.documentElement;
    const label = document.getElementById('themeLabelText');
    if (theme === 'dark') {
        html.classList.remove('theme-light');
        html.classList.add('theme-dark');
        if (label) label.textContent = 'Light';
    } else {
        html.classList.remove('theme-dark');
        html.classList.add('theme-light');
        if (label) label.textContent = 'Dark';
    }
}

function toggleTheme() {
    const current = document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('beantalk_theme', next);
    applyTheme(next);
}

// ======================================================================
// COPY UTILITY (FOR SCRIPT SNIPPETS & API KEYS)
// ======================================================================
function copyToClipboard(text, btn) {
    if (!navigator.clipboard) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    } else {
        navigator.clipboard.writeText(text);
    }

    if (btn) {
        const origText = btn.textContent;
        btn.textContent = 'Tersalin!';
        btn.style.color = 'var(--status-online)';
        btn.style.borderColor = 'var(--status-online)';
        setTimeout(() => {
            btn.textContent = origText;
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2000);
    }
}

// ======================================================================
// MODAL DIALOG UTILITIES
// ======================================================================
function openModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.style.display = 'flex';
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.style.display = 'none';
}

// Jalankan inisialisasi tema segera sebelum render selesai
applyTheme(localStorage.getItem('beantalk_theme') === 'dark' ? 'dark' : 'light');

// Inisialisasi sidebar saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
});

