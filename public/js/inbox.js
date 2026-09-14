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
 * Plays a pleasant, crisp harmonic dual-tone chime (E5 -> A5 glide + C#6 harmonic)
 * 100% native synthesized Web Audio API (Zero audio files, Zero 404 risk)
 */
function playNotificationSound() {
    try {
        unlockAudio();
        if (!audioCtx) return;

        const now = audioCtx.currentTime;

        // Tone 1: High crisp ping
        const osc1 = audioCtx.createOscillator();
        const gain1 = audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(659.25, now); // E5
        osc1.frequency.exponentialRampToValueAtTime(880, now + 0.08); // Glide to A5
        gain1.gain.setValueAtTime(0, now);
        gain1.gain.linearRampToValueAtTime(0.3, now + 0.02);
        gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.start(now);
        osc1.stop(now + 0.35);

        // Tone 2: Harmonic pleasant chime
        const osc2 = audioCtx.createOscillator();
        const gain2 = audioCtx.createGain();
        osc2.type = 'triangle';
        osc2.frequency.setValueAtTime(1108.73, now + 0.09); // C#6
        gain2.gain.setValueAtTime(0, now + 0.09);
        gain2.gain.linearRampToValueAtTime(0.25, now + 0.12);
        gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
        osc2.connect(gain2);
        gain2.connect(audioCtx.destination);
        osc2.start(now + 0.09);
        osc2.stop(now + 0.55);
    } catch (err) {
        console.warn('[BeanTalk Sound] Audio playback warning:', err);
    }
}



// ======================================================================
// 1. HELPERS & CSRF
// ======================================================================
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

// ======================================================================
// 2. REAL-TIME CONVERSATION FEED HANDLER (DRIVEN BY GLOBAL ENGINE)
// ======================================================================
window.onGlobalFeedUpdate = function(data) {
    if (!data) return;

    // 1. Update unread counter badges
    if (typeof data.unread_total === 'number') {
        updateUnreadBadges(data.unread_total);
    }

    if (data.max_message_id > maxTenantMessageId) {
        maxTenantMessageId = data.max_message_id;
    }

    // 2. Update Conversation List in DOM
    const container = document.getElementById('convListContainer');
    if (container && Array.isArray(data.conversations)) {
        // Hapus empty state jika ada
        const emptyState = container.querySelector('.empty-state');
        if (emptyState && data.conversations.length > 0) {
            emptyState.remove();
        }

        data.conversations.forEach(conv => {
            // Temukan semua DOM item dengan ID / Visitor ID yang sama dan bersihkan duplikasi jika ada
            const selector = `[data-conv-id="${conv.id}"], #card-conv-${conv.id}` + (conv.visitor_id ? `, [data-visitor-id="${conv.visitor_id}"]` : '');
            const existingItems = container.querySelectorAll(selector);
            if (existingItems.length > 1) {
                for (let i = 1; i < existingItems.length; i++) {
                    existingItems[i].remove();
                }
            }
            let item = existingItems[0] || null;
            const isCurrentActive = typeof activeConversationId !== 'undefined' && (activeConversationId === conv.id || (item && item.getAttribute('data-conv-id') === String(activeConversationId)));

            if (!item) {
                // Percakapan BARU Masuk!
                item = document.createElement('a');
                item.href = `/admin/inbox/${conv.id}`;
                item.id = `card-conv-${conv.id}`;
                item.setAttribute('data-conv-id', conv.id);
                if (conv.visitor_id) {
                    item.setAttribute('data-visitor-id', conv.visitor_id);
                }
                if (conv.project_id) {
                    item.setAttribute('data-site', conv.project_id);
                }
                item.className = `conv-row conv-item no-loader w-full block px-2.5 py-2 rounded-lg transition ${isCurrentActive ? 'text-apple-textPrimary bg-white border border-apple-border/60 shadow-apple-sm font-medium' : 'text-apple-textSecondary hover:text-apple-textPrimary hover:bg-black/5 font-normal border border-transparent'}`;
                
                const initials = escapeHtml(conv.initials || (conv.customer_name ? conv.customer_name.substring(0, 2).toUpperCase() : 'TM'));
                const custName = escapeHtml(conv.customer_name || 'Tamu');
                const siteName = escapeHtml(conv.project_name || 'Website');
                const custCode = escapeHtml(conv.customer_code || 'CUS-0000');
                const timeText = escapeHtml(conv.last_message_time || '-');
                const lastPreview = escapeHtml(conv.last_message_preview || 'Percakapan baru...');
                const unreadCount = conv.unread_agent_count || 0;
                const badgeText = unreadCount > 9 ? '9+' : String(unreadCount);

                item.innerHTML = `
                    <div class="flex gap-2.5 items-start">
                        <div class="relative shrink-0">
                            <div class="conv-avatar w-8 h-8 rounded-full bg-[#E5E5EA] text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border border-black/5">
                                ${initials}
                            </div>
                            <span class="w-2 h-2 rounded-full bg-apple-green absolute bottom-0 right-0 ring-1 ring-white"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="conv-name font-semibold text-apple-textPrimary truncate text-[12.5px]" title="${custName}">
                                    ${custName}
                                </span>
                                <span class="conv-time text-[10.5px] text-apple-textTertiary font-mono">${timeText}</span>
                            </div>
                            <div class="conv-meta-row flex items-center gap-1.5 mb-1" data-conv-meta>
                                <span class="conv-site text-[9px] font-medium tracking-tight uppercase px-1.5 py-0.2 rounded bg-neutral-200/70 text-neutral-800 truncate max-w-[110px]">
                                    ${siteName}
                                </span>
                                <span class="conv-cust-code text-[10px] text-apple-textTertiary font-mono">${custCode}</span>
                                ${conv.is_unread ? `<span class="unread-badge ml-auto text-[9.5px] font-bold px-1.5 py-0.2 rounded-full bg-apple-blue text-white">${badgeText}</span>` : ''}
                            </div>
                            <p class="conv-snippet text-[11.5px] text-apple-textSecondary truncate">
                                ${lastPreview}
                            </p>
                        </div>
                    </div>
                `;
                container.prepend(item);
            } else {
                // Update item yang sudah ada
                item.setAttribute('data-conv-id', conv.id);
                if (conv.visitor_id) {
                    item.setAttribute('data-visitor-id', conv.visitor_id);
                }
                const snippet = item.querySelector('.conv-snippet');
                if (snippet) snippet.textContent = conv.last_message_preview;

                const time = item.querySelector('.conv-time');
                if (time) time.textContent = conv.last_message_time;

                const name = item.querySelector('.conv-name');
                if (name) {
                    name.textContent = conv.customer_name;
                    name.setAttribute('title', conv.customer_name);
                }

                const avatar = item.querySelector('.conv-avatar');
                if (avatar && conv.initials) {
                    avatar.textContent = conv.initials;
                }

                if (conv.is_unread && !isCurrentActive) {
                    item.classList.add('conv-unread');
                    let badge = item.querySelector('.unread-badge');
                    const badgeText = conv.unread_agent_count > 9 ? '9+' : String(conv.unread_agent_count);
                    if (!badge) {
                        const metaRow = item.querySelector('.conv-meta-row') || item.querySelector('[data-conv-meta]');
                        if (metaRow) {
                            badge = document.createElement('span');
                            badge.className = 'unread-badge ml-auto text-[9.5px] font-bold px-1.5 py-0.2 rounded-full bg-apple-blue text-white';
                            metaRow.appendChild(badge);
                        }
                    }
                    if (badge) badge.textContent = badgeText;
                } else {
                    item.classList.remove('conv-unread');
                    const badge = item.querySelector('.unread-badge');
                    if (badge) badge.remove();
                }

                // Jika percakapan mendapatkan pesan baru dan bukan di urutan pertama, pindahkan ke paling atas
                if (container.firstElementChild !== item && conv.is_unread) {
                    container.prepend(item);
                }
            }
        });
    }
};

function pollConversationFeed() {
    // Driven seamlessly by admin.js initGlobalNotificationEngine
}


// ======================================================================
// 3. ACTIVE CONVERSATION THREAD POLLING
// ======================================================================
async function pollNewMessages() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId || isPollingMessages) {
        return;
    }

    isPollingMessages = true;

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
            if (json.success && json.data) {
                if (typeof json.data.unread_total === 'number') {
                    updateUnreadBadges(json.data.unread_total);
                }

                // Tandai item percakapan aktif sebagai terbaca di UI
                const activeItem = document.querySelector(`[data-conv-id="${activeConversationId}"]`);
                if (activeItem) {
                    activeItem.classList.remove('conv-unread');
                    const unreadBadge = activeItem.querySelector('.unread-badge');
                    if (unreadBadge) unreadBadge.remove();
                }

                if (json.data.messages && json.data.messages.length > 0) {
                    const thread = document.getElementById('chatThreadBody');
                    let hasNewRendered = false;
                    let hasNewVisitor = false;

                    json.data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {
                            lastMessageId = msg.id;
                        }

                        if (msg.sender_type === 'visitor') {
                            hasNewVisitor = true;
                        }

                        // Cek jika elemen dengan data-id ini sudah ada di DOM
                        const existing = document.querySelector(`[data-id="${msg.id}"]`);
                        if (!existing && thread) {
                            const isVisitor = msg.sender_type === 'visitor';
                            const row = document.createElement('div');
                            row.className = isVisitor
                                ? 'flex flex-col items-start max-w-[85%] sm:max-w-[70%]'
                                : 'flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]';
                            row.setAttribute('data-id', msg.id);

                            const senderName = escapeHtml(msg.sender_name || (isVisitor ? 'Pengunjung' : 'Staff CS'));
                            const senderLabel = isVisitor ? `${senderName} (Visitor)` : senderName;
                            const alignPad = isVisitor ? 'pl-1' : 'pr-1';
                            const bubbleClass = isVisitor
                                ? 'bubble-visitor bg-white border border-apple-border/80 text-apple-textPrimary px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed'
                                : 'bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed';
                            const timeText = escapeHtml(msg.created_at || '-') + (isVisitor ? '' : ' • Sent');

                            row.innerHTML = `
                                <span class="text-[10.5px] text-apple-textTertiary mb-0.5 ${alignPad}">${senderLabel}</span>
                                <div class="${bubbleClass}">${escapeHtml(msg.content)}</div>
                                <span class="text-[9.5px] text-apple-textTertiary mt-0.5 ${alignPad} font-mono">${timeText}</span>
                            `;
                            thread.appendChild(row);
                            hasNewRendered = true;
                        }
                    });

                    if (hasNewVisitor) {
                        playNotificationSound();
                    }

                    if (activeItem) {
                        const lastMsg = json.data.messages[json.data.messages.length - 1];
                        const snippet = activeItem.querySelector('.conv-snippet');
                        if (snippet) snippet.textContent = lastMsg.content;
                        const time = activeItem.querySelector('.conv-time');
                        if (time) time.textContent = lastMsg.created_at;
                    }

                    if (hasNewRendered) {
                        scrollToBottom();
                    }
                }
            }
        }
    } catch (err) {
        console.warn('[Inbox Thread Poll] Warning:', err);
    } finally {
        isPollingMessages = false;
    }
}

// Master Polling Loop
function runScheduledPoll() {
    pollConversationFeed();
    if (typeof activeConversationId !== 'undefined' && activeConversationId) {
        pollNewMessages();
    }
}

function startPollingSchedule() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(runScheduledPoll, pollInterval);
}

// Adaptive Interval: Turunkan frekuensi saat tab diminimalkan / background
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pollInterval = 10000; // 10 detik saat tab idle
    } else {
        pollInterval = 2000;  // 2 detik saat tab aktif
        runScheduledPoll();   // Segera refresh saat kembali fokus
    }
    startPollingSchedule();
});

// ======================================================================
// 4. SEND CS REPLY (OPTIMISTIC UI + ASYNC PERSISTENCE)
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
        tempDiv.className = 'flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]';
        tempDiv.style.opacity = '0.7';
        const senderName = typeof currentUserName !== 'undefined' ? currentUserName : 'Staff CS';
        tempDiv.innerHTML = `
            <span class="text-[10.5px] text-apple-textTertiary mb-0.5 pr-1">${escapeHtml(senderName)}</span>
            <div class="bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed">${escapeHtml(text)}</div>
            <span class="text-[9.5px] text-apple-textTertiary mt-0.5 pr-1 font-mono msg-time">Mengirim...</span>
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
                if (timeSpan) timeSpan.textContent = `${json.data.created_at || 'Baru saja'} • Sent`;
            }
            if (json.data.id > lastMessageId) {
                lastMessageId = json.data.id;
            }
            if (json.data.id > maxTenantMessageId) {
                maxTenantMessageId = json.data.id;
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
// 5. TICKET CONTROLS: ASSIGN CS AGENT
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
// 6. TICKET CONTROLS: TOGGLE OPEN / CLOSED
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
                if (newStatus === 'open') {
                    btn.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Tutup Tiket</span>
                    `;
                } else {
                    btn.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                        <span>Buka Kembali</span>
                    `;
                }
            }
        }
    } catch (err) {
        console.error('Error toggle status:', err);
    }
}

// ======================================================================
// 7. TICKET CONTROLS: EDIT CUSTOMER DISPLAY NAME
// ======================================================================
async function promptEditCustomerName() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    const currentNameEl = document.getElementById('contextCustomerName');
    const currentName = currentNameEl ? currentNameEl.textContent.trim() : '';

    const newName = prompt('Ubah atau lengkapi nama customer untuk percakapan ini:', currentName);
    if (!newName || newName.trim() === '' || newName.trim() === currentName) {
        return;
    }

    try {
        const res = await fetch(`/admin/inbox/${activeConversationId}/customer`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: newName.trim() })
        });

        const json = await res.json();
        if (json.success && json.data) {
            const displayName = json.data.display_name || json.data.name;

            // Update thread header
            const threadName = document.getElementById('threadCustomerName');
            if (threadName) {
                threadName.textContent = displayName;
                threadName.setAttribute('title', displayName);
            }
            const threadAvatar = document.getElementById('threadCustomerAvatar');
            if (threadAvatar) {
                threadAvatar.textContent = (displayName || 'TA').substring(0, 2).toUpperCase();
            }

            // Update context drawer
            if (currentNameEl) currentNameEl.textContent = displayName;
            const detailName = document.getElementById('contextDetailName');
            if (detailName) detailName.textContent = displayName;
            const contextAvatarLg = document.getElementById('contextAvatarLg');
            if (contextAvatarLg) {
                contextAvatarLg.textContent = (displayName || 'TA').substring(0, 2).toUpperCase();
            }

            // Update active item in conversation list
            const activeItem = document.querySelector(`[data-conv-id="${activeConversationId}"]`);
            if (activeItem) {
                const convName = activeItem.querySelector('.conv-name');
                if (convName) {
                    convName.textContent = displayName;
                    convName.setAttribute('title', displayName);
                }
                const convAvatar = activeItem.querySelector('.conv-avatar');
                if (convAvatar) {
                    convAvatar.textContent = (displayName || 'TA').substring(0, 2).toUpperCase();
                }
            }

            const replyInput = document.getElementById('replyInput');
            if (replyInput) {
                replyInput.setAttribute('placeholder', `Ketik balasan untuk ${displayName}... (Tekan Enter untuk kirim)`);
            }
        } else {
            alert('Gagal mengubah nama customer: ' + (json.error?.message || 'Error'));
        }
    } catch (err) {
        console.error('Error rename customer:', err);
    }
}

// Inisialisasi thread saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    startPollingSchedule();
});
