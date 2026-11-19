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
