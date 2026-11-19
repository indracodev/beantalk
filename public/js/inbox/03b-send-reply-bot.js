// ======================================================================
// 4. SEND CS REPLY (OPTIMISTIC UI + ASYNC PERSISTENCE)
// ======================================================================
async function handleSendReply(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('replyInput');
    if (!input) return;

    const text = input.value.trim();
    if (!text || typeof activeConversationId === 'undefined' || !activeConversationId) return;

    if (window.BeanTalkAudio) {
        window.BeanTalkAudio.stop();
    }

    input.value = '';

    // Render pesan optimistik sementara di chat thread
    const thread = document.getElementById('chatThreadBody');
    let tempDiv = null;
    if (thread) {
        const todayKey = (new Date()).toISOString().split('T')[0];
        ensureDateDivider(thread, todayKey, 'Hari Ini');

        tempDiv = document.createElement('div');
        tempDiv.className = 'flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]';
        tempDiv.style.opacity = '0.7';
        tempDiv.setAttribute('data-date-key', todayKey);
        const senderName = typeof currentUserName !== 'undefined' ? currentUserName : 'Staff CS';
        tempDiv.innerHTML = `
            <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pr-1">${escapeHtml(senderName)}</span>
            <div class="bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">${escapeHtml(text)}</div>
            <span class="text-[9.5px] text-apple-textTertiary mt-0.5 pr-1 font-mono msg-time">Mengirim...</span>
        `;
        thread.appendChild(tempDiv);
        scrollToBottom();
    }

    // Optimistik: Pindahkan kartu percakapan aktif ke paling atas di daftar chat
    const activeCard = document.querySelector(`[data-conv-id="${activeConversationId}"]`);
    const convContainer = document.getElementById('convListContainer');
    if (activeCard && convContainer) {
        const snippet = activeCard.querySelector('.conv-snippet');
        if (snippet) snippet.textContent = text;
        const timeEl = activeCard.querySelector('.conv-time');
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        if (timeEl) timeEl.textContent = `${hours}:${mins}`;
        if (convContainer.firstElementChild !== activeCard) {
            convContainer.prepend(activeCard);
        }
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
                const replyTime = formatLocalTime(json.data.created_at);
                if (timeSpan) timeSpan.textContent = `${replyTime} • Sent`;
            }
            if (json.data.id > lastMessageId) {
                lastMessageId = json.data.id;
            }
            if (json.data.id > maxTenantMessageId) {
                maxTenantMessageId = json.data.id;
            }
            if (typeof json.data.is_bot_active !== 'undefined') {
                updateBotToggleUI(json.data.is_bot_active);
            } else {
                updateBotToggleUI(false);
            }
            // Immediate re-poll to catch echo for visitor widget
            setTimeout(runScheduledPoll, 300);
            // Sinkronkan dropdown penugasan jika tiket otomatis di-assign ke agen ini
            if (json.data.assigned_user_id) {
                const assignSelect = document.getElementById('assignCsSelect');
                if (assignSelect && !assignSelect.value) {
                    assignSelect.value = json.data.assigned_user_id;
                }
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
// 4B. BOT ACTIVE/INACTIVE TOGGLE (PER-CONVERSATION OVERRIDE)
// ======================================================================
function updateBotToggleUI(isActive) {
    const btn = document.getElementById('btnToggleBot');
    const dot = document.getElementById('botStatusDot');
    const text = document.getElementById('botToggleText');
    if (!btn || !text) return;

    if (isActive) {
        text.textContent = 'Bot: On';
        if (dot) {
            dot.className = 'w-2 h-2 rounded-full bg-apple-green animate-pulse';
        }
        btn.setAttribute('title', 'Bot aktif membalas otomatis. Klik untuk menjeda bot.');
    } else {
        text.textContent = 'Bot: Off';
        if (dot) {
            dot.className = 'w-2 h-2 rounded-full bg-apple-orange';
        }
        btn.setAttribute('title', 'Bot sedang dijeda. Staf CS menangani percakapan ini. Klik untuk mengaktifkan kembali bot.');
    }
}

async function handleToggleBot() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    const btn = document.getElementById('btnToggleBot');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch(`/admin/inbox/${activeConversationId}/toggle-bot`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const json = await res.json();
        if (json.success && json.data) {
            updateBotToggleUI(json.data.is_bot_active);
        }
    } catch (err) {
        console.error('[Bot Toggle] Error:', err);
    } finally {
        if (btn) btn.disabled = false;
    }
}
