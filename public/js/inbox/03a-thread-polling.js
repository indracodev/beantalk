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

                if (typeof json.data.is_bot_active !== 'undefined') {
                    updateBotToggleUI(json.data.is_bot_active);
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
                            const dateKey = getLocalDateKey(msg.created_at);
                            const dateLabel = formatLocalDateLabel(msg.created_at);
                            ensureDateDivider(thread, dateKey, dateLabel);

                            const isVisitor = msg.sender_type === 'visitor';
                            const isBot = msg.sender_type === 'bot';
                            const row = document.createElement('div');
                            row.className = isVisitor
                                ? 'flex flex-col items-start max-w-[85%] sm:max-w-[70%]'
                                : 'flex flex-col items-end self-end max-w-[85%] sm:max-w-[70%]';
                            row.setAttribute('data-id', msg.id);
                            row.setAttribute('data-date-key', dateKey);
                            if (msg.created_at) {
                                row.setAttribute('data-created-at', msg.created_at);
                            }

                            const alignPad = isVisitor ? 'pl-1' : 'pr-1';
                            let bubbleClass = '';
                            let senderLabelHtml = '';
                            let suffix = ' • Sent';

                            if (isVisitor) {
                                bubbleClass = 'bubble-visitor bg-white border border-apple-border/80 text-apple-textPrimary px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed';
                                senderLabelHtml = `<span class="text-[10.5px] text-apple-textTertiary mb-0.5 ${alignPad}">${escapeHtml(msg.sender_name || 'Pengunjung')} (Visitor)</span>`;
                                suffix = '';
                            } else if (isBot) {
                                bubbleClass = 'bubble-bot bg-indigo-600 text-white px-3 py-2 text-[12.5px] rounded-2xl shadow-apple-sm leading-relaxed';
                                senderLabelHtml = `<span class="text-[10.5px] text-indigo-600 font-medium mb-0.5 ${alignPad} flex items-center gap-1"><span>🤖 ${escapeHtml(msg.sender_name || 'BeanBot')}</span><span class="text-[9px] px-1 py-0.2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded font-semibold">BOT AUTO</span></span>`;
                                suffix = ' • Bot Replied';
                            } else {
                                bubbleClass = 'bubble-agent bg-apple-blue text-white px-3 py-2 text-[12.5px] shadow-apple-sm leading-relaxed';
                                senderLabelHtml = `<span class="text-[10.5px] text-apple-textTertiary mb-0.5 ${alignPad}">${escapeHtml(msg.sender_name || 'Staff CS')}</span>`;
                            }

                            const localTime = formatLocalTime(msg.created_at);
                            const timeText = escapeHtml(localTime) + suffix;

                            row.innerHTML = `
                                ${senderLabelHtml}
                                <div class="${bubbleClass}">${escapeHtml(msg.content)}</div>
                                <span class="msg-time-display text-[9.5px] text-apple-textTertiary mt-0.5 ${alignPad} font-mono" data-created-at="${msg.created_at || ''}">${timeText}</span>
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
                        if (time) {
                            time.textContent = formatConvListTime(lastMsg.created_at);
                            if (lastMsg.created_at) {
                                time.setAttribute('data-timestamp', lastMsg.created_at);
                            }
                        }
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
