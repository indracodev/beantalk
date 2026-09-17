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

function copySnippet(key) {
    const text = `<script src="https://chat.indraco.com/widget.js" data-key="${key}" async><\/script>`;
    copyToClipboard(text);
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

// ======================================================================
// 6. DRAGGABLE HORIZONTAL RESIZERS (CONV LIST & INSPECTOR SPLITTERS)
// ======================================================================
function initPanelResizers() {
    // Resizer 1: Conv List <-> Chat Feed
    const resizerConvChat = document.getElementById('resizer-conv-chat');
    const paneConvList = document.getElementById('pane-conv-list');

    if (resizerConvChat && paneConvList) {
        let isDragging = false;
        const savedWidth = localStorage.getItem('beantalk_panel_conv_list');
        if (savedWidth && window.innerWidth > 1024) {
            const w = parseInt(savedWidth, 10);
            if (w >= 220 && w <= 520) {
                paneConvList.style.width = w + 'px';
            }
        }

        resizerConvChat.addEventListener('mousedown', (e) => {
            isDragging = true;
            resizerConvChat.classList.add('is-dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const sidebar = document.getElementById('main-sidebar') || document.getElementById('mainSidebar');
            const sidebarWidth = sidebar ? sidebar.offsetWidth : 0;
            const newWidth = e.clientX - sidebarWidth;
            if (newWidth >= 220 && newWidth <= 520) {
                paneConvList.style.width = `${newWidth}px`;
            }
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                resizerConvChat.classList.remove('is-dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                localStorage.setItem('beantalk_panel_conv_list', parseInt(paneConvList.style.width, 10));
            }
        });

        resizerConvChat.addEventListener('dblclick', () => {
            paneConvList.style.width = '320px';
            localStorage.setItem('beantalk_panel_conv_list', 320);
        });
    }

    // Resizer 2: Chat Feed <-> Customer Context
    const resizerChatContext = document.getElementById('resizer-chat-context');
    const paneContext = document.getElementById('pane-context-inspector');

    if (resizerChatContext && paneContext) {
        let isDraggingContext = false;
        const savedWidth = localStorage.getItem('beantalk_panel_context');
        if (savedWidth && window.innerWidth > 1024) {
            const w = parseInt(savedWidth, 10);
            if (w >= 220 && w <= 420) {
                paneContext.style.width = w + 'px';
            }
        }

        resizerChatContext.addEventListener('mousedown', (e) => {
            if (paneContext.classList.contains('inspector-collapsed')) return;
            isDraggingContext = true;
            resizerChatContext.classList.add('is-dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDraggingContext) return;
            const newWidth = window.innerWidth - e.clientX;
            if (newWidth >= 220 && newWidth <= 420) {
                paneContext.style.width = `${newWidth}px`;
            }
        });

        document.addEventListener('mouseup', () => {
            if (isDraggingContext) {
                isDraggingContext = false;
                resizerChatContext.classList.remove('is-dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                localStorage.setItem('beantalk_panel_context', parseInt(paneContext.style.width, 10));
            }
        });

        resizerChatContext.addEventListener('dblclick', () => {
            paneContext.style.width = '288px';
            localStorage.setItem('beantalk_panel_context', 288);
        });
    }
}

// ======================================================================
// 8. MOBILE RESPONSIVE: SHOW/HIDE CONVERSATION LIST VS THREAD
// ======================================================================
function showConvList() {
    const list = document.getElementById('colConversations');
    const thread = document.getElementById('colThread');
    if (list) list.classList.remove('hidden-mobile');
    if (thread) thread.classList.add('hidden-mobile');
}

function showThread() {
    const list = document.getElementById('colConversations');
    const thread = document.getElementById('colThread');
    if (list) list.classList.add('hidden-mobile');
    if (thread) thread.classList.remove('hidden-mobile');
}

function initMobileLayout() {
    if (window.innerWidth > 768) {
        const list = document.getElementById('colConversations');
        const thread = document.getElementById('colThread');
        if (list) list.classList.remove('hidden-mobile');
        if (thread) thread.classList.remove('hidden-mobile');
        closeSidebarMobile();
        return;
    }

    const hasActive = typeof activeConversationId !== 'undefined' && activeConversationId;
    if (hasActive) {
        showThread();
    } else {
        showConvList();
    }
}

// ======================================================================
// 9. WINDOW RESIZE EVENT LISTENER
// ======================================================================
let resizeTimeout = null;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        initMobileLayout();
        if (window.innerWidth > 768) {
            closeSidebarMobile();
        }
    }, 100);
});

// ======================================================================
// 10. GLOBAL REAL-TIME CHAT NOTIFICATION & AUDIO ENGINE (ALL ADMIN PAGES)
// ======================================================================
let globalMaxMessageId = parseInt(sessionStorage.getItem('beantalk_max_message_id') || '0', 10);
let lastNotifiedMsgId = parseInt(sessionStorage.getItem('beantalk_last_notified_msg_id') || '0', 10);
let globalUnreadCount = 0;
let globalAudioCtx = null;
let globalPollTimer = null;
let globalTitleBlinkTimer = null;
const globalOriginalDocTitle = document.title;
let isGlobalPolling = false;


function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ======================================================================
// BEANTALK MULTI-SOUND SYNTHESIS & ALARM ENGINE
// Web Audio API Native Synthesizers:
// 1. Pedestrian Crossing (Lampu Merah Penyeberangan: Tot Tot Tot)
// 2. Ambulance Siren (Ambulans: Ninu Ninu)
// 3. Police / Patwal Horn Yelp (Mobil Dinas: Wut Wut)
// 4. Harmonic Apple Chime
// 5. Custom Audio File Upload
// ======================================================================
window.BeanTalkAudio = (function() {
    let audioCtx = null;
    let alarmTimer = null;
    let alarmCountdownTimer = null;
    let currentOscillators = [];
    let currentHtmlAudio = null;
    let isPlaying = false;
    let activeFloatingBanner = null;

    function getContext() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;
            if (!audioCtx) {
                audioCtx = new AudioContextClass();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        } catch (e) {
            return null;
        }
    }

    function removeFloatingBanner() {
        if (activeFloatingBanner) {
            try { activeFloatingBanner.remove(); } catch (e) {}
            activeFloatingBanner = null;
        }
        const existing = document.getElementById('beantalkAlarmActiveBanner');
        if (existing) existing.remove();
    }

    function stop() {
        isPlaying = false;
        if (alarmTimer) {
            clearInterval(alarmTimer);
            clearTimeout(alarmTimer);
            alarmTimer = null;
        }
        if (alarmCountdownTimer) {
            clearInterval(alarmCountdownTimer);
            alarmCountdownTimer = null;
        }
        if (currentHtmlAudio) {
            try {
                currentHtmlAudio.pause();
                currentHtmlAudio.currentTime = 0;
            } catch (e) {}
            currentHtmlAudio = null;
        }
        currentOscillators.forEach(osc => {
            try { osc.stop(); osc.disconnect(); } catch (e) {}
        });
        currentOscillators = [];
        removeFloatingBanner();
    }

    // 1. Lampu Merah Penyeberangan (Tot Tot Tot: Pelican Crossing Sound)
    function playPedestrianBeep(ctx) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1046.5, now); // C6 clear acoustic beep
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.35, now + 0.012);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.075);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.08);
        } catch (e) {}
    }

    // 2. Ambulans (Ninu Ninu: Two-Tone Siren)
    function playAmbulanceTone(ctx, isHigh) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle'; // Rich resonant tone
            const freq = isHigh ? 960 : 770;
            osc.frequency.setValueAtTime(freq, now);
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.28, now + 0.03);
            gain.gain.setValueAtTime(0.28, now + 0.38);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.44);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.45);
            currentOscillators.push(osc);
            setTimeout(() => {
                const idx = currentOscillators.indexOf(osc);
                if (idx !== -1) currentOscillators.splice(idx, 1);
            }, 460);
        } catch (e) {}
    }

    // 3. Mobil Dinas / Patwal (Wut Wut / Yelp Siren)
    function playPoliceWut(ctx) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';

            const filter = ctx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(2200, now);

            osc.frequency.setValueAtTime(620, now);
            osc.frequency.exponentialRampToValueAtTime(1480, now + 0.12);
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.38, now + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.16);
        } catch (e) {}
    }

    // 4. Apple Harmonic Chime (Single or Repeat)
    function playChime(ctx) {
        try {
            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            osc1.frequency.exponentialRampToValueAtTime(880, now + 0.08);
            gain1.gain.setValueAtTime(0, now);
            gain1.gain.linearRampToValueAtTime(0.32, now + 0.02);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1108.73, now + 0.09);
            gain2.gain.setValueAtTime(0, now + 0.09);
            gain2.gain.linearRampToValueAtTime(0.25, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.09);
            osc2.stop(now + 0.55);
        } catch (e) {}
    }

    function showAlarmBanner(durationSec, soundLabel) {
        removeFloatingBanner();
        const banner = document.createElement('div');
        banner.id = 'beantalkAlarmActiveBanner';
        banner.className = 'fixed top-4 right-4 z-[9999] bg-rose-600 text-white px-4 py-2.5 rounded-xl shadow-apple-popover flex items-center gap-3 border border-rose-400 animate-bounce cursor-pointer';
        banner.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
            <div class="text-[12px] font-medium leading-tight">
                <div class="font-bold flex items-center gap-1.5">
                    <span>🔔 Pesan Baru Masuk!</span>
                    <span class="text-[10px] bg-white/20 px-1.5 py-0.2 rounded">${escapeHtml(soundLabel || 'Alarm')}</span>
                </div>
                <div class="text-[11px] text-white/90">Berdering: <strong id="alarmCountdownText">${durationSec}s</strong> &bull; Klik untuk mematikan</div>
            </div>
            <button type="button" class="ml-2 bg-white/20 hover:bg-white/30 text-white rounded-lg px-2 py-1 text-[11px] font-bold transition">
                Stop ✕
            </button>
        `;
        banner.onclick = () => stop();
        document.body.appendChild(banner);
        activeFloatingBanner = banner;

        let remaining = durationSec;
        alarmCountdownTimer = setInterval(() => {
            remaining--;
            const countEl = document.getElementById('alarmCountdownText');
            if (countEl) countEl.innerText = `${remaining}s`;
            if (remaining <= 0) {
                clearInterval(alarmCountdownTimer);
            }
        }, 1000);
    }

    function playAlarm(soundType, durationSec, customUrl, showBanner = true, onComplete = null) {
        stop();
        isPlaying = true;
        const dur = Math.max(2, Math.min(180, parseInt(durationSec, 10) || 15));
        const endTime = Date.now() + (dur * 1000);

        let soundLabel = 'Tot-Tot';
        if (soundType === 'ambulance') soundLabel = 'Ninu-Ninu';
        else if (soundType === 'police') soundLabel = 'Wut-Wut';
        else if (soundType === 'chime') soundLabel = 'Chime';
        else if (soundType === 'custom') soundLabel = 'Custom';

        if (showBanner) {
            showAlarmBanner(dur, soundLabel);
        }

        // 1. Custom Audio File playback
        if (soundType === 'custom' && customUrl) {
            try {
                currentHtmlAudio = new Audio(customUrl);
                currentHtmlAudio.loop = true;
                currentHtmlAudio.play().catch(e => console.warn('[Custom Audio Play Warning]', e));
                alarmTimer = setTimeout(() => {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                }, dur * 1000);
                return;
            } catch (err) {
                console.warn('[Custom Sound Error, falling back to synthesizer]', err);
            }
        }

        // 2. Web Audio synthesizers
        const ctx = getContext();
        if (!ctx) return;

        if (soundType === 'pedestrian') {
            // Rapid acoustic pedestrian crossing beep (every 135ms: tot... tot... tot...)
            playPedestrianBeep(ctx);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                playPedestrianBeep(ctx);
            }, 135);
        } else if (soundType === 'ambulance') {
            // Two-tone European / Indonesian siren (460ms cycle: nii... nuu... nii... nuu...)
            let isHigh = true;
            playAmbulanceTone(ctx, isHigh);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                isHigh = !isHigh;
                playAmbulanceTone(ctx, isHigh);
            }, 460);
        } else if (soundType === 'police') {
            // VIP police patrol yelp chirp (rapid wut-wut bursts every 650ms)
            const burst = () => {
                playPoliceWut(ctx);
                setTimeout(() => { if (isPlaying) playPoliceWut(ctx); }, 200);
            };
            burst();
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                burst();
            }, 680);
        } else {
            // Harmonic chime repeats every 2.2s
            playChime(ctx);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                playChime(ctx);
            }, 2200);
        }
    }

    function playVisitorSound(soundType, customUrl) {
        if (soundType === 'custom' && customUrl) {
            try {
                const a = new Audio(customUrl);
                a.volume = 0.7;
                a.play().catch(e => console.warn(e));
                return;
            } catch (e) {}
        }

        const ctx = getContext();
        if (!ctx) return;
        const now = ctx.currentTime;

        if (soundType === 'pop') {
            // Crisp bubble burst (400Hz -> 880Hz sweep, 0.08s)
            try {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(420, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.06);
                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.35, now + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.08);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.085);
            } catch (e) {}
        } else if (soundType === 'ding') {
            // Crystal desk reception bell (1318Hz E6 + 2637Hz overtone)
            try {
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1318.5, now);
                gain1.gain.setValueAtTime(0.28, now);
                gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.55);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(2637, now);
                gain2.gain.setValueAtTime(0.12, now);
                gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now);
                osc2.stop(now + 0.35);
            } catch (e) {}
        } else if (soundType === 'marimba') {
            // Cheerful ascending 3-note marimba chord (C6, E6, G6)
            const notes = [1046.5, 1318.5, 1567.98];
            notes.forEach((freq, idx) => {
                try {
                    const noteTime = now + (idx * 0.08);
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, noteTime);
                    gain.gain.setValueAtTime(0.001, noteTime);
                    gain.gain.linearRampToValueAtTime(0.25, noteTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + 0.22);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(noteTime);
                    osc.stop(noteTime + 0.25);
                } catch (e) {}
            });
        } else {
            // Default: Apple harmonic chime glide
            playChime(ctx);
        }
    }

    return {
        play: playAlarm,
        playSingleChime: function() {
            const ctx = getContext();
            if (ctx) playChime(ctx);
        },
        playVisitorSound: playVisitorSound,
        stop: stop,
        isPlaying: () => isPlaying,
        unlock: getContext
    };
})();

document.addEventListener('click', () => { window.BeanTalkAudio.unlock(); }, { passive: true });
document.addEventListener('keydown', () => { window.BeanTalkAudio.unlock(); }, { passive: true });
document.addEventListener('touchstart', () => { window.BeanTalkAudio.unlock(); }, { passive: true });

function playGlobalChime() {
    window.BeanTalkAudio.playSingleChime();
}

function updateGlobalSidebarBadge(unreadCount) {
    globalUnreadCount = unreadCount;
    const badge = document.getElementById('sidebarUnreadBadge');
    const navItem = document.getElementById('navItemInbox');
    const mobilePill = document.querySelector('.mobile-unread-pill');

    const badgeText = unreadCount > 99 ? '99+' : String(unreadCount);

    if (badge) {
        badge.textContent = badgeText;
        if (unreadCount > 0) {
            badge.style.display = 'inline-flex';
            badge.classList.add('pulse');
        } else {
            badge.style.display = 'none';
            badge.classList.remove('pulse');
        }
    }

    if (navItem) {
        if (unreadCount > 0) {
            navItem.classList.add('has-unread');
        } else {
            navItem.classList.remove('has-unread');
        }
    }

    if (mobilePill) {
        mobilePill.textContent = unreadCount > 0 ? `${badgeText} Baru` : '';
        mobilePill.style.display = unreadCount > 0 ? '' : 'none';
    }

    const mobileBottomBadge = document.getElementById('mobileBottomUnreadBadge');
    if (mobileBottomBadge) {
        mobileBottomBadge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        if (unreadCount > 0) {
            mobileBottomBadge.classList.remove('hidden');
            mobileBottomBadge.classList.add('inline-block');
        } else {
            mobileBottomBadge.classList.add('hidden');
            mobileBottomBadge.classList.remove('inline-block');
        }
    }
}

function showGlobalToast(sender, message, convId) {
    let container = document.getElementById('beantalkToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'beantalkToastContainer';
        document.body.appendChild(container);
    }

    if (convId) {
        const existing = container.querySelector(`[data-toast-conv-id="${convId}"]`);
        if (existing) existing.remove();
    }

    const toast = document.createElement('div');
    toast.className = 'beantalk-toast';
    toast.setAttribute('role', 'alert');
    if (convId) toast.setAttribute('data-toast-conv-id', convId);
    toast.setAttribute('title', 'Klik untuk membuka chat');
    toast.innerHTML = `
        <div class="toast-avatar-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="toast-body">
            <div class="toast-header-row">
                <span class="toast-sender">${escapeHtml(sender || 'Pengunjung')}</span>
                <span class="toast-hint">Buka Chat &rarr;</span>
            </div>
            <div class="toast-msg">${escapeHtml(message || 'Mengirim pesan baru...')}</div>
        </div>
        <button type="button" class="toast-close-btn" aria-label="Tutup">&times;</button>
    `;

    toast.onclick = (e) => {
        if (e.target.classList.contains('toast-close-btn')) {
            toast.remove();
            return;
        }
        if (convId) {
            window.location.href = `/admin/inbox/${convId}`;
        }
    };

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(14px)';
        setTimeout(() => toast.remove(), 350);
    }, 7000);
}

function blinkGlobalTitle(senderName) {
    if (globalTitleBlinkTimer) clearInterval(globalTitleBlinkTimer);
    let state = false;
    let count = 0;
    globalTitleBlinkTimer = setInterval(() => {
        document.title = state ? `🔔 Pesan Baru: ${senderName}` : globalOriginalDocTitle;
        state = !state;
        count++;
        if (count > 16 || document.hasFocus()) {
            clearInterval(globalTitleBlinkTimer);
            document.title = globalOriginalDocTitle;
        }
    }, 900);
}

window.addEventListener('focus', () => {
    if (globalTitleBlinkTimer) {
        clearInterval(globalTitleBlinkTimer);
        document.title = globalOriginalDocTitle;
    }
});

async function pollGlobalFeedUpdates() {
    if (isGlobalPolling) return;
    isGlobalPolling = true;

    try {
        const params = new URLSearchParams();
        if (window.location.pathname.startsWith('/admin/inbox') && window.location.search) {
            const currentParams = new URLSearchParams(window.location.search);
            for (const [k, v] of currentParams.entries()) {
                params.set(k, v);
            }
        }
        if (globalMaxMessageId > 0) {
            params.set('since_message_id', globalMaxMessageId);
        }

        const res = await fetch(`/admin/inbox/feed/updates?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (res.ok) {
            const json = await res.json();
            if (json.success && json.data) {
                const data = json.data;

                // 1. Update sidebar unread badge and menu pulse
                if (typeof data.unread_total === 'number') {
                    updateGlobalSidebarBadge(data.unread_total);
                }

                // 2. Play chime & show toast ONLY for new unnotified incoming messages
                if (data.has_new_incoming && data.latest_incoming) {
                    const incomingMsgId = data.latest_incoming.message_id || data.max_message_id;
                    if (incomingMsgId && incomingMsgId > lastNotifiedMsgId) {
                        lastNotifiedMsgId = incomingMsgId;
                        sessionStorage.setItem('beantalk_last_notified_msg_id', String(lastNotifiedMsgId));

                        playGlobalChime();

                        const isCurrentActive = typeof activeConversationId !== 'undefined' && activeConversationId == data.latest_incoming.conversation_id;
                        if (!isCurrentActive) {
                            showGlobalToast(
                                data.latest_incoming.sender_name,
                                data.latest_incoming.content,
                                data.latest_incoming.conversation_id
                            );
                        }
                        blinkGlobalTitle(data.latest_incoming.sender_name);
                    }
                }

                if (data.max_message_id && data.max_message_id > globalMaxMessageId) {
                    globalMaxMessageId = data.max_message_id;
                    sessionStorage.setItem('beantalk_max_message_id', String(globalMaxMessageId));
                }

                // 3. Delegate to inbox.js if on inbox page
                if (typeof window.onGlobalFeedUpdate === 'function') {
                    window.onGlobalFeedUpdate(data);
                }
            }
        }
    } catch (err) {
        // Network drop ignored
    } finally {
        isGlobalPolling = false;
    }
}

window.updateGlobalSidebarBadge = updateGlobalSidebarBadge;
window.playGlobalChime = playGlobalChime;
window.showGlobalToast = showGlobalToast;


function initGlobalNotificationEngine() {
    // Initial fetch
    pollGlobalFeedUpdates();

    // Schedule periodic polling (3s)
    if (globalPollTimer) clearInterval(globalPollTimer);
    globalPollTimer = setInterval(pollGlobalFeedUpdates, 3000);

    // Adaptive polling: throttle when tab is hidden, resume when active
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(globalPollTimer);
            globalPollTimer = setInterval(pollGlobalFeedUpdates, 10000);
        } else {
            clearInterval(globalPollTimer);
            pollGlobalFeedUpdates();
            globalPollTimer = setInterval(pollGlobalFeedUpdates, 3000);
        }
    });
}

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
    initGlobalNotificationEngine();
    initGlobalLoaderEvents();
});
