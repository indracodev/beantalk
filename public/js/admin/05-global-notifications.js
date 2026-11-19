// Engine notifikasi global (polling feed, badge sidebar, toast, kedip judul) — semua halaman admin.
function playGlobalChime() {
    window.BeanTalkAudio.playSingleChime();
}

function updateGlobalSidebarBadge(unreadCount) {
    globalUnreadCount = unreadCount;
    if (window.BeanTalkTabBadge) {
        window.BeanTalkTabBadge.setCount(unreadCount);
    }
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
    const currentUnread = globalUnreadCount;
    const countPrefix = currentUnread > 0 ? `(${currentUnread > 99 ? '99+' : currentUnread}) ` : '';
    const cleanTitle = (typeof globalOriginalDocTitle !== 'undefined' ? globalOriginalDocTitle : document.title).replace(/^\(\d+\+?\)\s*/, '');

    globalTitleBlinkTimer = setInterval(() => {
        document.title = state ? `🔔 Pesan Baru: ${senderName}` : `${countPrefix}${cleanTitle}`;
        state = !state;
        count++;
        if (count > 16 || document.hasFocus()) {
            clearInterval(globalTitleBlinkTimer);
            globalTitleBlinkTimer = null;
            if (window.BeanTalkTabBadge) {
                window.BeanTalkTabBadge.refreshTitle();
            } else {
                document.title = globalOriginalDocTitle;
            }
        }
    }, 900);
}

window.addEventListener('focus', () => {
    if (globalTitleBlinkTimer) {
        clearInterval(globalTitleBlinkTimer);
        globalTitleBlinkTimer = null;
    }
    if (window.BeanTalkTabBadge) {
        window.BeanTalkTabBadge.refreshTitle();
    } else {
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

                        const isCurrentActive = typeof activeConversationId !== 'undefined' && activeConversationId == data.latest_incoming.conversation_id;

                        // Play sound alarm according to project widget settings
                        if (data.latest_incoming.sound_enabled !== false && window.BeanTalkAudio) {
                            if (!isCurrentActive) {
                                // Pesan belum dibuka / di percakapan lain: bunyikan alarm sesuai waktu yg ditentukan
                                window.BeanTalkAudio.play(
                                    data.latest_incoming.sound_type || 'pedestrian',
                                    data.latest_incoming.sound_duration || 15,
                                    data.latest_incoming.sound_custom_url || null
                                );
                            } else {
                                // Percakapan sedang aktif ditonton agen: bunyikan notifikasi halus 1x
                                window.BeanTalkAudio.playSingleChime();
                            }
                        }

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
window.pollGlobalFeedUpdates = pollGlobalFeedUpdates;


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
