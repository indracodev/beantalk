// ======================================================================
// 10. BEANTALK APPLE / macOS GLOBAL LOADER ENGINE (ZERO FLICKER)
// ======================================================================
let beantalkHideTimeout = null;

window.BeanTalkLoader = {
    minDuration: 400, // Minimal durasi tampil (ms) agar animasi halus & zero flash
    isActive: false,

    show: function(msg, customMinDuration) {
        this.isActive = true;
        if (beantalkHideTimeout) clearTimeout(beantalkHideTimeout);

        const startTime = Date.now();
        const minDur = customMinDuration !== undefined ? customMinDuration : this.minDuration;
        const message = msg || 'Memuat data...';

        try {
            sessionStorage.setItem('beantalk_loader_active', 'true');
            sessionStorage.setItem('beantalk_loader_start', startTime.toString());
            sessionStorage.setItem('beantalk_loader_dur', minDur.toString());
            sessionStorage.setItem('beantalk_loader_msg', message);
        } catch (e) {}

        const msgEl = document.getElementById('beantalk-loader-msg');
        if (msgEl) msgEl.textContent = message;

        const overlay = document.getElementById('beantalk-loading-overlay');
        if (overlay) overlay.classList.add('show');

        const bar = document.getElementById('beantalk-progress-bar');
        if (bar) {
            bar.style.width = '75%';
            bar.style.opacity = '1';
        }
    },

    hide: function() {
        this.isActive = false;
        let startTime = Date.now();
        let minDur = this.minDuration;

        try {
            const startTimeStr = sessionStorage.getItem('beantalk_loader_start');
            const minDurStr = sessionStorage.getItem('beantalk_loader_dur');
            if (startTimeStr) startTime = parseInt(startTimeStr, 10);
            if (minDurStr) minDur = parseInt(minDurStr, 10);
        } catch (e) {}

        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, minDur - elapsed);

        if (beantalkHideTimeout) clearTimeout(beantalkHideTimeout);

        beantalkHideTimeout = setTimeout(() => {
            const bar = document.getElementById('beantalk-progress-bar');
            if (bar) bar.style.width = '100%';

            setTimeout(() => {
                document.documentElement.classList.remove('beantalk-loader-active');
                const overlay = document.getElementById('beantalk-loading-overlay');
                if (overlay) overlay.classList.remove('show');
                if (bar) {
                    bar.style.opacity = '0';
                    bar.style.width = '0%';
                }
                try {
                    sessionStorage.removeItem('beantalk_loader_active');
                    sessionStorage.removeItem('beantalk_loader_start');
                    sessionStorage.removeItem('beantalk_loader_dur');
                    sessionStorage.removeItem('beantalk_loader_msg');
                } catch (e) {}
            }, 180);
        }, remaining);
    }
};

function initGlobalLoaderEvents() {
    // 1. Check if loader was active from previous navigation (e.g. clicking menu Inbox, Websites, Team, Logs)
    if (sessionStorage.getItem('beantalk_loader_active') === 'true') {
        const savedMsg = sessionStorage.getItem('beantalk_loader_msg') || 'Memuat halaman...';
        const msgEl = document.getElementById('beantalk-loader-msg');
        if (msgEl) msgEl.textContent = savedMsg;

        document.documentElement.classList.add('beantalk-loader-active');
        const overlay = document.getElementById('beantalk-loading-overlay');
        if (overlay) overlay.classList.add('show');

        const bar = document.getElementById('beantalk-progress-bar');
        if (bar) {
            bar.style.width = '75%';
            bar.style.opacity = '1';
        }

        // Trigger smooth graceful hide
        window.BeanTalkLoader.hide();
    } else {
        document.documentElement.classList.remove('beantalk-loader-active');
        const overlay = document.getElementById('beantalk-loading-overlay');
        if (overlay) overlay.classList.remove('show');
        const bar = document.getElementById('beantalk-progress-bar');
        if (bar) {
            bar.style.opacity = '0';
            bar.style.width = '0%';
        }
    }

    // 2. Auto-trigger on Form Submissions (exclude forms marked with no-loader or in inbox workspace)
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        if (!form || form.classList.contains('no-loader') || form.closest('#inboxWorkspace') || form.closest('#pane-conv-list')) return;

        const msg = form.getAttribute('data-loading-msg') || 'Memproses data...';
        const customDur = form.getAttribute('data-loading-duration');
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');

        if (submitBtn && !submitBtn.classList.contains('no-spinner')) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        window.BeanTalkLoader.show(msg, customDur ? parseInt(customDur, 10) : undefined);
    });

    // 3. Auto-trigger on Action Buttons & Navigation Links (Capture phase for instant execution)
    document.addEventListener('click', (e) => {
        // A. Check explicit button/element with loading message
        const btn = e.target.closest('button[data-loading-msg], a[data-loading-msg]');
        
        // Check if inside internal chat workspace
        const isInternalChatWorkspace = !!e.target.closest('#inboxWorkspace, #convListContainer, #pane-conv-list, .conv-item, .conv-row, .inbox-scope-btn, [data-conv-id]');

        if (btn && !btn.classList.contains('no-loader') && !isInternalChatWorkspace) {
            const msg = btn.getAttribute('data-loading-msg') || 'Memuat data...';
            const customDur = btn.getAttribute('data-loading-duration');
            window.BeanTalkLoader.show(msg, customDur ? parseInt(customDur, 10) : undefined);
            return;
        }

        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;

        // B. MAIN NAVIGATION MENU (Sidebar, Bottom Navigation, Top Brand, Channel Switcher):
        // Selalu tampilkan Global Swiss Loader saat klik menu navigasi utama
        const isMainNavigationMenu = link.closest('#main-sidebar') || 
                                     link.closest('#mobile-bottom-nav') || 
                                     link.id === 'navItemDashboard' ||
                                     link.id === 'bottomNavItemDashboard' ||
                                     link.id === 'navItemInbox' || 
                                     link.id === 'bottomNavItemInbox' ||
                                     link.id === 'navItemIntegrations' ||
                                     link.id === 'bottomNavItemWebsites' ||
                                     link.id === 'navItemTeam' ||
                                     link.id === 'bottomNavItemTeam' ||
                                     link.id === 'navItemLogs' ||
                                     link.id === 'bottomNavItemLogs' ||
                                     link.classList.contains('sidebar-item');

        if (isMainNavigationMenu) {
            const msg = link.getAttribute('data-loading-msg') || (href.includes('/admin/inbox') ? 'Memuat Inbox...' : 'Memuat halaman...');
            const customDur = link.getAttribute('data-loading-duration');
            window.BeanTalkLoader.show(msg, customDur ? parseInt(customDur, 10) : undefined);
            return;
        }

        // C. CRITICAL EXCLUSION: Internal inbox workspace content (switching tickets, chat cards, status tabs)
        const isInternalInboxContent = link.classList.contains('no-loader') ||
            link.classList.contains('conv-item') ||
            link.classList.contains('conv-row') ||
            link.classList.contains('inbox-scope-btn') ||
            link.hasAttribute('data-conv-id') ||
            isInternalChatWorkspace;

        if (isInternalInboxContent) {
            try {
                sessionStorage.removeItem('beantalk_loader_active');
                sessionStorage.removeItem('beantalk_loader_start');
                sessionStorage.removeItem('beantalk_loader_dur');
                sessionStorage.removeItem('beantalk_loader_msg');
            } catch (e) {}
            return;
        }

        const customDur = link.getAttribute('data-loading-duration');
        window.BeanTalkLoader.show('Memuat halaman...', customDur ? parseInt(customDur, 10) : undefined);
    }, true);

    // 4. Auto-trigger on Refresh (F5 / Ctrl+R / Browser Reload) ONLY if loader was explicitly triggered
    window.addEventListener('beforeunload', () => {
        try {
            if (!window.BeanTalkLoader.isActive) {
                sessionStorage.removeItem('beantalk_loader_active');
                sessionStorage.removeItem('beantalk_loader_start');
                sessionStorage.removeItem('beantalk_loader_dur');
                sessionStorage.removeItem('beantalk_loader_msg');
            }
        } catch (e) {}
    });

    document.addEventListener('keydown', (e) => {
        const isInternalInboxPage = window.location.pathname.indexOf('/admin/inbox') !== -1;
        if (isInternalInboxPage) return; // Skip full loader on internal chat reload
        const key = e.which || e.keyCode;
        if (key === 116 || (e.ctrlKey && key === 82)) { // F5 or Ctrl+R
            window.BeanTalkLoader.show('Memuat ulang halaman...');
        }
    });

    // 5. Handle Back/Forward Browser Cache (BFCache)
    window.addEventListener('pageshow', () => {
        window.BeanTalkLoader.hide();
    });
}

// ======================================================================
// 11. INIT ALL ON DOM READY
// ======================================================================
applyTheme(localStorage.getItem('beantalk_theme') === 'dark' ? 'dark' : 'light');

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initPanelResizers();
    initInspectorPanel();
    initMobileLayout();
    if (window.BeanTalkTabBadge) {
        window.BeanTalkTabBadge.init();
    }
    initGlobalNotificationEngine();
    initGlobalLoaderEvents();
});

// Toggle tampil/sembunyi password (form profil di layout admin)
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const eyeIcon = btn.querySelector('.eye-icon');
    const eyeOffIcon = btn.querySelector('.eye-off-icon');
    if (eyeIcon && eyeOffIcon) {
        eyeIcon.classList.toggle('hidden', isPassword);
        eyeOffIcon.classList.toggle('hidden', !isPassword);
    }
}
