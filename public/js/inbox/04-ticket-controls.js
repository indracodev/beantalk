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
// 6. TICKET CONTROLS: RESOLVE WITH CLOSING GREETING TEMPLATE / REOPEN
// ======================================================================
const CLOSING_TEMPLATES = {
    ramah: 'Terima kasih telah menghubungi kami. Semoga harimu menyenangkan! Jika ada pertanyaan lain, jangan ragu untuk chat kami kembali.',
    solutif: 'Senang dapat membantu Anda hari ini. Tiket percakapan ini kami tandai telah selesai. Semoga solusi yang kami berikan bermanfaat!',
    singkat: 'Terima kasih telah menghubungi Customer Support. Selamat beraktivitas kembali!'
};

function applyClosingTemplate(key) {
    const textarea = document.getElementById('resolveClosingMessage');
    if (textarea && CLOSING_TEMPLATES[key]) {
        textarea.value = CLOSING_TEMPLATES[key];
        textarea.focus();
    }
}

function openResolveModal() {
    const modal = document.getElementById('resolveTicketModal');
    if (!modal) return;
    const textarea = document.getElementById('resolveClosingMessage');
    if (textarea && !textarea.value.trim()) {
        textarea.value = CLOSING_TEMPLATES.ramah;
    }
    modal.classList.remove('hidden');
}

function closeResolveModal() {
    const modal = document.getElementById('resolveTicketModal');
    if (modal) modal.classList.add('hidden');
}

async function handleToggleStatus() {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    if (conversationStatus === 'open') {
        openResolveModal();
    } else {
        // Reopen ticket directly
        await executeStatusUpdate('open', null);
    }
}

async function confirmResolveTicket() {
    const btn = document.getElementById('btnConfirmResolve');
    const textarea = document.getElementById('resolveClosingMessage');
    const check = document.getElementById('resolveSendClosingCheck');
    
    let closingMessage = null;
    if (check && check.checked && textarea) {
        closingMessage = textarea.value.trim();
    }

    if (btn) btn.disabled = true;
    try {
        await executeStatusUpdate('closed', closingMessage);
        closeResolveModal();
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function executeStatusUpdate(targetStatus, closingMsg) {
    if (typeof activeConversationId === 'undefined' || !activeConversationId) return;

    try {
        const payload = { status: targetStatus };
        if (closingMsg) {
            payload.closing_message = closingMsg;
        }

        const res = await fetch(`/admin/inbox/${activeConversationId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (json.success && json.data) {
            const newStatus = json.data.status;
            conversationStatus = newStatus;

            const btn = document.getElementById('btnToggleStatus');
            if (btn) {
                if (newStatus === 'open') {
                    btn.className = 'px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white text-apple-red hover:bg-red-50 active:scale-95 transition font-medium cursor-pointer';
                    btn.innerHTML = '<span>Resolve</span>';
                } else {
                    btn.className = 'px-2.5 py-1 text-[10.5px] rounded-lg border border-apple-border bg-white text-apple-green hover:bg-emerald-50 active:scale-95 transition font-medium cursor-pointer';
                    btn.innerHTML = '<span>Reopen</span>';
                }
            }

            // Update status pill di toolbar aktif
            const statusPillToolbar = document.getElementById('threadStatusPill');
            if (statusPillToolbar) {
                if (newStatus === 'open') {
                    statusPillToolbar.className = 'text-[10px] font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
                    statusPillToolbar.textContent = 'Open';
                } else {
                    statusPillToolbar.className = 'text-[10px] font-medium px-2 py-0.5 rounded-full bg-apple-canvas text-apple-textTertiary border border-apple-border';
                    statusPillToolbar.textContent = 'Selesai';
                }
            }

            // Optimistic update pada kartu obrolan di panel kiri
            const activeFilterBadge = document.getElementById('inboxActiveFilterCountBadge');
            const currentFilter = activeFilterBadge ? activeFilterBadge.getAttribute('data-active-status') : 'open';
            const activeCard = document.querySelector(`[data-conv-id="${activeConversationId}"], #card-conv-${activeConversationId}`);
            if (activeCard) {
                if ((newStatus === 'closed' && (currentFilter === 'open' || currentFilter === 'mine')) ||
                    (newStatus === 'open' && currentFilter === 'closed')) {
                    activeCard.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    activeCard.style.opacity = '0';
                    activeCard.style.transform = 'translateX(-10px)';
                    setTimeout(() => activeCard.remove(), 250);
                } else {
                    const pill = activeCard.querySelector('.conv-status-pill');
                    if (pill) {
                        pill.className = `conv-status-pill text-[9px] font-semibold px-1.5 py-0.2 rounded border shrink-0 ${newStatus === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200'}`;
                        pill.textContent = newStatus === 'open' ? 'Open' : 'Selesai';
                    }
                }
            }

            // Immediately trigger poll to fetch newly appended closing message & refresh feed
            if (typeof window.pollGlobalFeedUpdates === 'function') {
                window.pollGlobalFeedUpdates();
            }
            setTimeout(runScheduledPoll, 300);
        }
    } catch (err) {
        console.error('Error updating status:', err);
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
    if (typeof activeConversationId !== 'undefined' && activeConversationId) {
        if (window.BeanTalkAudio) {
            window.BeanTalkAudio.stop();
        }
    }
});
