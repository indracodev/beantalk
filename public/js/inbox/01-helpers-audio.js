/**
 * BEANTALK LIVE INBOX — JAVASCRIPT
 * Real-time adaptive polling, instant optimistic replies, live conversation feed,
 * Web Audio chime notification, ticket assignment, and lifecycle management.
 * Pure Vanilla JavaScript with Zero External Dependencies.
 */

let lastMessageId = typeof initialLastMessageId !== 'undefined' ? initialLastMessageId : 0;
let maxTenantMessageId = typeof initialMaxTenantMessageId !== 'undefined' ? initialMaxTenantMessageId : 0;
let isPollingMessages = false;
let isPollingFeed = false;
let pollTimer = null;
let pollInterval = 2000; // 2 detik saat aktif

// ======================================================================
// 0. AUDIO CHIME & DESKTOP / VISUAL NOTIFICATIONS
// ======================================================================
let audioCtx = null;
let originalDocumentTitle = document.title;
let titleBlinkTimer = null;

function unlockAudio() {
    try {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;
        if (!audioCtx) {
            audioCtx = new AudioContextClass();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    } catch (e) {
        console.warn('[AudioContext] Unlock warning:', e);
    }
}
document.addEventListener('click', unlockAudio, { once: false });
document.addEventListener('keydown', unlockAudio, { once: false });

/**
 * Plays a pleasant, crisp notification chime
 */
function playNotificationSound() {
    if (window.BeanTalkAudio) {
        window.BeanTalkAudio.playSingleChime();
    }
}

// Matikan alarm seketika saat agen CS mengklik percakapan apapun di inbox
document.addEventListener('click', (e) => {
    const card = e.target.closest('[data-conv-id]');
    if (card && window.BeanTalkAudio) {
        window.BeanTalkAudio.stop();
    }
}, { passive: true });



// ======================================================================
// 1. HELPERS, LOCAL TIME FORMATTERS & CSRF
// ======================================================================
function formatLocalTime(isoStr) {
    if (!isoStr) return '-';
    try {
        let parsed = isoStr;
        if (typeof parsed === 'string' && !parsed.includes('Z') && !parsed.includes('+') && !parsed.includes('T')) {
            parsed = parsed.replace(' ', 'T') + 'Z';
        }
        const d = new Date(parsed);
        if (isNaN(d.getTime())) return isoStr;
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    } catch (e) {
        return isoStr;
    }
}

function getLocalDateKey(isoStr) {
    if (!isoStr) return (new Date()).toISOString().split('T')[0];
    try {
        let parsed = isoStr;
        if (typeof parsed === 'string' && !parsed.includes('Z') && !parsed.includes('+') && !parsed.includes('T')) {
            parsed = parsed.replace(' ', 'T') + 'Z';
        }
        const d = new Date(parsed);
        if (isNaN(d.getTime())) return (new Date()).toISOString().split('T')[0];
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const date = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${date}`;
    } catch (e) {
        return (new Date()).toISOString().split('T')[0];
    }
}

function formatLocalDateLabel(isoStr) {
    if (!isoStr) return 'Hari Ini';
    try {
        let parsed = isoStr;
        if (typeof parsed === 'string' && !parsed.includes('Z') && !parsed.includes('+') && !parsed.includes('T')) {
            parsed = parsed.replace(' ', 'T') + 'Z';
        }
        const d = new Date(parsed);
        if (isNaN(d.getTime())) return 'Hari Ini';
        
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const targetDate = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        
        const diffDays = Math.round((today - targetDate) / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Hari Ini';
        if (diffDays === 1) return 'Kemarin';
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const day = d.getDate();
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        
        if (year === now.getFullYear()) {
            return `${day} ${month}`;
        }
        return `${day} ${month} ${year}`;
    } catch (e) {
        return 'Hari Ini';
    }
}

function formatConvListTime(isoStr) {
    if (!isoStr) return '-';
    try {
        let parsed = isoStr;
        if (typeof parsed === 'string' && !parsed.includes('Z') && !parsed.includes('+') && !parsed.includes('T')) {
            parsed = parsed.replace(' ', 'T') + 'Z';
        }
        const d = new Date(parsed);
        if (isNaN(d.getTime())) return '-';
        
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const targetDate = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        const diffDays = Math.round((today - targetDate) / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }
        if (diffDays === 1) return 'Kemarin';
        
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        if (year === now.getFullYear()) {
            return `${day}/${month}`;
        }
        return `${day}/${month}/${year}`;
    } catch (e) {
        return '-';
    }
}

function hydrateAllLocalTimestamps() {
    // 1. Hydrate message bubbles in chat thread
    document.querySelectorAll('.msg-time-display[data-created-at]').forEach(el => {
        const iso = el.getAttribute('data-created-at');
        if (iso) {
            const isSent = el.textContent.includes('Sent');
            el.textContent = formatLocalTime(iso) + (isSent ? ' • Sent' : '');
        }
    });

    // 2. Hydrate date dividers in chat thread
    document.querySelectorAll('[data-date-divider]').forEach(el => {
        const isoKey = el.getAttribute('data-date-divider');
        const badge = el.querySelector('span');
        if (badge && isoKey) {
            badge.textContent = formatLocalDateLabel(isoKey);
        }
    });

    // 3. Hydrate conversation list timestamps
    document.querySelectorAll('.conv-time[data-timestamp]').forEach(el => {
        const iso = el.getAttribute('data-timestamp');
        if (iso) {
            el.textContent = formatConvListTime(iso);
        }
    });
}

// Jalankan hydration saat script dimuat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hydrateAllLocalTimestamps);
} else {
    hydrateAllLocalTimestamps();
}

function scrollToBottom() {
    const body = document.getElementById('chatThreadBody');
    if (body) {
        body.scrollTop = body.scrollHeight;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function updateUnreadBadges(total) {
    if (typeof window.updateGlobalSidebarBadge === 'function') {
        window.updateGlobalSidebarBadge(total);
    }
    const sidebarBadge = document.getElementById('sidebarUnreadBadge');
    const listBadge = document.getElementById('inboxListUnreadBadge');
    const mobileBottomBadge = document.getElementById('mobileBottomUnreadBadge');
    const text = total > 99 ? '99+' : String(total);

    if (sidebarBadge) {
        sidebarBadge.textContent = text;
        sidebarBadge.style.display = total > 0 ? '' : 'none';
    }
    if (listBadge) {
        listBadge.textContent = text;
        listBadge.style.display = total > 0 ? 'inline-flex' : 'none';
    }
    if (mobileBottomBadge) {
        mobileBottomBadge.textContent = total > 9 ? '9+' : String(total);
        if (total > 0) {
            mobileBottomBadge.classList.remove('hidden');
            mobileBottomBadge.classList.add('inline-block');
        } else {
            mobileBottomBadge.classList.add('hidden');
            mobileBottomBadge.classList.remove('inline-block');
        }
    }
}
