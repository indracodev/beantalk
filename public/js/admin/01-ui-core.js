/**
 * BEANTALK ADMIN DASHBOARD — CORE JAVASCRIPT
 * Zero dependencies, pure native browser Web APIs.
 * Full panel management: Resizing, Collapsing, Expanding, and Mobile Responsiveness.
 */

// ======================================================================
// 1. SIDEBAR MANAGEMENT (DESKTOP TOGGLE + MOBILE DRAWER)
// ======================================================================
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('main-sidebar') || document.getElementById('mainSidebar');
    if (!sidebar) return;

    if (window.innerWidth <= 768) {
        toggleSidebarMobile();
        return;
    }

    sidebar.classList.toggle('sidebar-collapsed');
    const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
    document.documentElement.classList.toggle('sidebar-is-collapsed', isCollapsed);
    localStorage.setItem('beantalk_sidebar_collapsed', isCollapsed);
}

function toggleSidebarMobile() {
    const sidebar = document.getElementById('main-sidebar') || document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar) return;

    const isOpen = sidebar.classList.toggle('open-mobile');
    if (backdrop) {
        backdrop.classList.toggle('hidden', !isOpen);
        backdrop.classList.toggle('active', isOpen);
    }
}

function closeSidebarMobile() {
    const sidebar = document.getElementById('main-sidebar') || document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar) sidebar.classList.remove('open-mobile');
    if (backdrop) {
        backdrop.classList.add('hidden');
        backdrop.classList.remove('active');
    }
}

function initSidebar() {
    const sidebar = document.getElementById('main-sidebar') || document.getElementById('mainSidebar');
    if (!sidebar) return;

    if (window.innerWidth > 1024) {
        const isCollapsed = localStorage.getItem('beantalk_sidebar_collapsed') === 'true';
        sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
        document.documentElement.classList.toggle('sidebar-is-collapsed', isCollapsed);
    }
}

function toggleWorkspaceDropdown(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('workspace-dropdown-menu');
    if (!menu) return;
    const isHidden = menu.classList.toggle('hidden');
    const chevron = document.getElementById('workspace-chevron');
    if (chevron) {
        chevron.style.transform = isHidden ? '' : 'rotate(180deg)';
    }
}

document.addEventListener('click', (e) => {
    const menu = document.getElementById('workspace-dropdown-menu');
    if (menu && !menu.classList.contains('hidden') && !e.target.closest('#workspace-dropdown-menu') && !e.target.closest('.workspace-box')) {
        menu.classList.add('hidden');
        const chevron = document.getElementById('workspace-chevron');
        if (chevron) chevron.style.transform = '';
    }
});

// ======================================================================
// 2. THEME TOGGLE (LIGHT / DARK)
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
// 3. COPY UTILITY (FOR SCRIPT SNIPPETS & API KEYS)
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
        const origHtml = btn.innerHTML;
        btn.innerHTML = `
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Tersalin!</span>
        `;
        btn.style.color = '#10B981';
        btn.style.borderColor = '#10B981';
        setTimeout(() => {
            btn.innerHTML = origHtml;
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2200);
    }
}

// ======================================================================
// 4. MODAL DIALOG UTILITIES
// ======================================================================
function openModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.style.display = 'flex';
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.style.display = 'none';
}

// ======================================================================
// 4. TOAST & NOTIFICATION UTILITIES
// ======================================================================
let toastTimer = null;
function showToast(title, message) {
    const toast = document.getElementById('apple-toast');
    const toastTitle = document.getElementById('toast-title');
    const toastMsg = document.getElementById('toast-message');
    if (!toast) return;

    if (toastTitle) toastTitle.textContent = title;
    if (toastMsg) toastMsg.textContent = message;

    toast.classList.remove('translate-y-12', 'opacity-0', 'pointer-events-none');
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.add('translate-y-12', 'opacity-0', 'pointer-events-none');
    }, 3500);
}

// Salin snippet embed yang dirender server (URL & key benar) dari elemen #elementId.
function copySnippet(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    copyToClipboard(el.textContent.trim(), btn);
    showToast('Tersalin', 'Script embed widget telah disalin ke clipboard.');
}

// ======================================================================
// 5. CUSTOMER SESSION INSPECTOR TOGGLE (EXPAND / ICON RAIL / MOBILE DRAWER)
// ======================================================================
function toggleInspectorMode() {
    const inspector = document.getElementById('pane-context-inspector') || document.getElementById('colContext');
    const backdrop = document.getElementById('inspectorBackdrop');
    if (!inspector) return;
    
    if (window.innerWidth < 1024) {
        const isOpen = inspector.classList.toggle('open-mobile');
        if (isOpen) {
            inspector.classList.remove('hidden');
            inspector.classList.add('flex');
        } else {
            inspector.classList.remove('flex');
            inspector.classList.add('hidden');
        }
        if (backdrop) {
            backdrop.classList.toggle('hidden', !isOpen);
        }
        return;
    }

    inspector.classList.toggle('inspector-collapsed');
    const isCollapsed = inspector.classList.contains('inspector-collapsed');
    document.documentElement.classList.toggle('inspector-is-collapsed', isCollapsed);
    localStorage.setItem('beantalk_inspector_collapsed', isCollapsed);
}

function closeInspectorMobile() {
    const inspector = document.getElementById('pane-context-inspector') || document.getElementById('colContext');
    const backdrop = document.getElementById('inspectorBackdrop');
    if (inspector) {
        inspector.classList.remove('open-mobile');
        if (window.innerWidth < 1024) {
            inspector.classList.remove('flex');
            inspector.classList.add('hidden');
        }
    }
    if (backdrop) {
        backdrop.classList.add('hidden');
    }
}

function initInspectorPanel() {
    const inspector = document.getElementById('pane-context-inspector') || document.getElementById('colContext');
    if (!inspector) return;

    if (window.innerWidth >= 1024) {
        const savedState = localStorage.getItem('beantalk_inspector_collapsed');
        const isCollapsed = savedState === 'true' || (savedState === null && window.innerWidth <= 1150);
        inspector.classList.toggle('inspector-collapsed', isCollapsed);
        document.documentElement.classList.toggle('inspector-is-collapsed', isCollapsed);
    }
}
