/**
 * BEANTALK LIVE INBOX — JAVASCRIPT
 * Real-time adaptive polling, instant optimistic replies, ticket assignment, and lifecycle management.
 * Pure Vanilla JavaScript with Zero Dependencies.
 */

let lastMessageId = typeof initialLastMessageId !== 'undefined' ? initialLastMessageId : 0;
let isPolling = false;
let pollTimer = null;
let pollInterval = 3000; // 3 detik saat aktif

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

// ======================================================================
// 1. ADAPTIVE POLLING (AFTER_ID RANGE SCAN)
// ======================================================================
async function pollNewMessages() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId || isPolling) {
        return;
    }

    isPolling = true;

    try {
        const url = `/admin/inbox/${activeConversationId}/messages?after_id=${lastMessageId}`;
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (res.ok) {
            const json = await res.json();
            if (json.success && json.data && json.data.messages && json.data.messages.length > 0) {
                const thread = document.getElementById('chatThreadBody');
                let hasNewRendered = false;

                json.data.messages.forEach(msg => {
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                    }

                    // Cek jika elemen dengan data-id ini sudah ada di DOM (menghindari duplikasi dengan optimistik)
                    const existing = document.querySelector(`[data-id="${msg.id}"]`);
                    if (!existing && thread) {
                        const row = document.createElement('div');
                        row.className = `msg-row ${msg.sender_type === 'visitor' ? 'msg-visitor' : 'msg-agent'}`;
                        row.setAttribute('data-id', msg.id);
                        row.innerHTML = `
                            <span class="msg-sender">${escapeHtml(msg.sender_name)}</span>
                            <div class="msg-bubble">${escapeHtml(msg.content)}</div>
                            <span class="msg-time">${escapeHtml(msg.created_at)}</span>
                        `;
                        thread.appendChild(row);
                        hasNewRendered = true;
                    }
                });

                if (hasNewRendered) {
                    scrollToBottom();
                }
            }
        }
    } catch (err) {
        console.warn('[Inbox Poll] Warning:', err);
    } finally {
        isPolling = false;
    }
}

function startPollingSchedule() {
    if (pollTimer) clearInterval(pollTimer);
    if (typeof activeConversationId !== 'undefined' && activeConversationId) {
        pollTimer = setInterval(pollNewMessages, pollInterval);
    }
}

// Adaptive Interval: Turunkan frekuensi saat tab diminimalkan / background
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pollInterval = 15000; // 15 detik saat tab idle
    } else {
        pollInterval = 3000;  // 3 detik saat tab aktif
        pollNewMessages();    // Segera refresh saat kembali fokus
    }
    startPollingSchedule();
});

// ======================================================================
// 2. SEND CS REPLY (OPTIMISTIC UI + ASYNC PERSISTENCE)
// ======================================================================
async function handleSendReply(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('replyInput');
    if (!input) return;

    const text = input.value.trim();
    if (!text || typeof activeConversationId === 'undefined' || !activeConversationId) return;

    input.value = '';

    // Render pesan optimistik sementara di chat thread
    const thread = document.getElementById('chatThreadBody');
    let tempDiv = null;
    if (thread) {
        tempDiv = document.createElement('div');
        tempDiv.className = 'msg-row msg-agent';
        tempDiv.style.opacity = '0.7';
        const senderName = typeof currentUserName !== 'undefined' ? currentUserName : 'CS Agent';
        tempDiv.innerHTML = `
            <span class="msg-sender">${escapeHtml(senderName)}</span>
            <div class="msg-bubble">${escapeHtml(text)}</div>
            <span class="msg-time">Mengirim...</span>
        `;
        thread.appendChild(tempDiv);
        scrollToBottom();
    }

    try {
        const res = await fetch(`/admin/inbox/${activeConversationId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ content: text })
        });

        const json = await res.json();
        if (json.success && json.data) {
            if (tempDiv) {
                tempDiv.style.opacity = '1';
                tempDiv.setAttribute('data-id', json.data.id);
                const timeSpan = tempDiv.querySelector('.msg-time');
                if (timeSpan) timeSpan.textContent = json.data.created_at;
            }
            if (json.data.id > lastMessageId) {
                lastMessageId = json.data.id;
            }
        } else {
            if (tempDiv) tempDiv.remove();
            alert('Gagal mengirim balasan: ' + (json.error?.message || 'Terjadi kesalahan server.'));
        }
    } catch (err) {
        if (tempDiv) tempDiv.remove();
        console.error('Error reply:', err);
    }
}

// ======================================================================
// 3. TICKET CONTROLS: ASSIGN CS AGENT
// ======================================================================
async function handleAssign(userId) {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    try {
        const res = await fetch(`/admin/inbox/${activeConversationId}/assign`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ assigned_user_id: userId || null })
        });

        const json = await res.json();
        if (json.success) {
            console.log('[Assign CS] Berhasil:', json.data.assigned_user_name);
        } else {
            alert('Gagal menugaskan CS: ' + (json.error?.message || 'Error'));
        }
    } catch (err) {
        console.error('Error assign CS:', err);
    }
}

// ======================================================================
// 4. TICKET CONTROLS: TOGGLE OPEN / CLOSED
// ======================================================================
async function handleToggleStatus() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    try {
        const res = await fetch(`/admin/inbox/${activeConversationId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const json = await res.json();
        if (json.success && json.data) {
            const newStatus = json.data.status;
            const badge = document.getElementById('threadStatusBadge');
            const btn = document.getElementById('btnToggleStatus');

            if (badge) {
                badge.textContent = '● ' + newStatus;
                badge.className = `thread-status-badge ${newStatus === 'open' ? 'status-open' : 'status-closed'}`;
            }

            if (btn) {
                btn.textContent = newStatus === 'open' ? 'Tutup Tiket' : 'Buka Kembali';
            }
        }
    } catch (err) {
        console.error('Error toggle status:', err);
    }
}

// Inisialisasi thread saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    startPollingSchedule();
});
