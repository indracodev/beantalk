// ======================================================================
// 2. REAL-TIME CONVERSATION FEED HANDLER (DRIVEN BY GLOBAL ENGINE)
// ======================================================================
window.onGlobalFeedUpdate = function(data) {
    if (!data) return;

    // 1. Update unread counter badges
    if (typeof data.unread_total === 'number') {
        updateUnreadBadges(data.unread_total);
    }

    // 2. Update Scope Badges & Header Count jika tersedia
    if (data.counts) {
        updateScopeCounters(data.counts);
    }

    if (data.max_message_id > maxTenantMessageId) {
        maxTenantMessageId = data.max_message_id;
    }

    // 3. Update Conversation List in DOM
    const container = document.getElementById('convListContainer');
    if (container && Array.isArray(data.conversations)) {
        // Hapus empty state jika ada
        const emptyState = container.querySelector('.empty-state, .empty-conv-state');
        if (emptyState && data.conversations.length > 0) {
            emptyState.remove();
        }

        const returnedIds = new Set(data.conversations.map(c => String(c.id)));
        const searchInput = document.getElementById('search-conv-input');
        const isClientSearching = searchInput && searchInput.value.trim().length > 0;

        // Prune kartu yang sudah tidak masuk kriteria filter aktif (misal tiket di-resolve saat filter open/mine)
        if (!isClientSearching) {
            const allDomCards = container.querySelectorAll('.conv-row[data-conv-id]');
            allDomCards.forEach(card => {
                const cid = card.getAttribute('data-conv-id');
                if (cid && !returnedIds.has(cid)) {
                    card.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-10px)';
                    setTimeout(() => card.remove(), 200);
                }
            });

            if (data.conversations.length === 0 && !container.querySelector('.empty-conv-state')) {
                container.innerHTML = `
                    <div class="empty-conv-state p-8 text-center text-apple-textTertiary">
                        <svg class="w-8 h-8 mx-auto mb-2 text-apple-textTertiary/60" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p class="font-medium text-[12.5px] text-apple-textSecondary">Belum ada percakapan</p>
                        <p class="text-[11px] mt-0.5">Tidak ada pesan untuk filter ini saat ini.</p>
                    </div>
                `;
            }
        }

        data.conversations.forEach(conv => {
            // Temukan DOM item berdasarkan ID tiket percakapan unik
            const selector = `[data-conv-id="${conv.id}"], #card-conv-${conv.id}`;
            const existingItems = container.querySelectorAll(selector);
            if (existingItems.length > 1) {
                for (let i = 1; i < existingItems.length; i++) {
                    existingItems[i].remove();
                }
            }
            let item = existingItems[0] || null;
            const isCurrentActive = typeof activeConversationId !== 'undefined' && (activeConversationId === conv.id || (item && item.getAttribute('data-conv-id') === String(activeConversationId)));

            const timeText = conv.last_message_at ? formatConvListTime(conv.last_message_at) : (conv.last_message_time || '-');

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
                const lastPreview = escapeHtml(conv.last_message_preview || 'Percakapan baru...');
                const unreadCount = conv.unread_agent_count || 0;
                const badgeText = unreadCount > 9 ? '9+' : String(unreadCount);
                const projectColor = conv.project_color || '#0071E3';

                if (isCurrentActive) {
                    item.style.borderLeft = `3px solid ${projectColor}`;
                }

                item.innerHTML = `
                    <div class="flex gap-2.5 items-start">
                        <div class="relative shrink-0">
                            <div class="conv-avatar w-8 h-8 rounded-full bg-[#E5E5EA] text-apple-textPrimary font-semibold text-[11px] flex items-center justify-center border" style="border-color: ${projectColor}45;">
                                ${initials}
                            </div>
                            <span class="w-2 h-2 rounded-full bg-apple-green absolute bottom-0 right-0 ring-1 ring-white"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="conv-name font-semibold text-apple-textPrimary truncate text-[12.5px]" title="${custName}">
                                        ${custName}
                                    </span>
                                    <span class="conv-ticket-badge text-[9.5px] font-mono font-semibold px-1.5 py-0.2 rounded bg-black/5 text-apple-textSecondary border border-black/10 shrink-0">
                                        #${conv.id}
                                    </span>
                                </div>
                                <span class="conv-time text-[10.5px] text-apple-textTertiary font-mono" data-timestamp="${conv.last_message_at || ''}">${timeText}</span>
                            </div>
                            <div class="conv-meta-row flex items-center gap-1.5 mb-1 flex-wrap" data-conv-meta>
                                <span class="conv-site text-[9px] font-semibold tracking-tight uppercase px-1.5 py-0.5 rounded truncate max-w-[95px]" style="background-color: ${projectColor}14; color: ${projectColor}; border: 1px solid ${projectColor}30;">
                                    ${siteName}
                                </span>
                                <span class="conv-cust-code text-[10px] text-apple-textTertiary font-mono">${custCode}</span>
                                <span class="conv-status-pill text-[9px] font-semibold px-1.5 py-0.2 rounded border shrink-0 ${conv.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200'}">
                                    ${conv.status === 'open' ? 'Open' : 'Selesai'}
                                </span>
                                ${conv.visitor_tickets_count > 1 ? `<span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200 shrink-0" title="${conv.visitor_tickets_count} tiket total dari pelanggan ini">${conv.visitor_tickets_count} Tiket</span>` : ''}
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
                if (conv.project_color) {
                    const siteBadge = item.querySelector('.conv-site');
                    if (siteBadge) {
                        siteBadge.style.backgroundColor = `${conv.project_color}14`;
                        siteBadge.style.color = conv.project_color;
                        siteBadge.style.borderColor = `${conv.project_color}30`;
                    }
                    const avatar = item.querySelector('.conv-avatar');
                    if (avatar) {
                        avatar.style.borderColor = `${conv.project_color}45`;
                    }
                    if (isCurrentActive) {
                        item.style.borderLeft = `3px solid ${conv.project_color}`;
                    }
                }
                const snippet = item.querySelector('.conv-snippet');
                if (snippet) snippet.textContent = conv.last_message_preview;

                const time = item.querySelector('.conv-time');
                if (time) {
                    time.textContent = timeText;
                    if (conv.last_message_at) {
                        time.setAttribute('data-timestamp', conv.last_message_at);
                    }
                }

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

                let statusPill = item.querySelector('.conv-status-pill');
                if (statusPill && conv.status) {
                    statusPill.className = `conv-status-pill text-[9px] font-semibold px-1.5 py-0.2 rounded border shrink-0 ${conv.status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200'}`;
                    statusPill.textContent = conv.status === 'open' ? 'Open' : 'Selesai';
                }

                // Pertahankan urutan kartu percakapan sesuai array backend (aktivitas terbaru di paling atas)
                container.appendChild(item);
            }
        });
    }
};

function updateScopeCounters(counts) {
    if (!counts) return;

    const badgeOpen = document.getElementById('sidebarBadgeOpen') || document.querySelector('[data-scope="open"]');
    if (badgeOpen && typeof counts.open !== 'undefined') badgeOpen.textContent = counts.open;

    const badgeMine = document.getElementById('sidebarBadgeMine') || document.querySelector('[data-scope="mine"]');
    if (badgeMine && typeof counts.mine !== 'undefined') badgeMine.textContent = counts.mine;

    const badgeClosed = document.getElementById('sidebarBadgeClosed') || document.querySelector('[data-scope="closed"]');
    if (badgeClosed && typeof counts.closed !== 'undefined') badgeClosed.textContent = counts.closed;

    const badgeAll = document.getElementById('sidebarBadgeAll') || document.querySelector('[data-scope="all"]');
    if (badgeAll && typeof counts.all !== 'undefined') badgeAll.textContent = counts.all;

    const activeFilterBadge = document.getElementById('inboxActiveFilterCountBadge');
    if (activeFilterBadge) {
        const curStatus = activeFilterBadge.getAttribute('data-active-status') || 'open';
        let label = (counts.open || 0) + ' Aktif';
        if (curStatus === 'mine') label = (counts.mine || 0) + ' Mine';
        else if (curStatus === 'closed') label = (counts.closed || 0) + ' Selesai';
        else if (curStatus === 'all') label = (counts.all || 0) + ' Semua';
        activeFilterBadge.textContent = label;
    }
}

function pollConversationFeed() {
    if (typeof window.pollGlobalFeedUpdates === 'function') {
        window.pollGlobalFeedUpdates();
    }
}


function ensureDateDivider(thread, dateKey, dateLabel) {
    if (!thread || !dateKey) return;
    const existing = thread.querySelector(`[data-date-divider="${dateKey}"]`);
    if (!existing) {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-center my-1.5 select-none';
        div.setAttribute('data-date-divider', dateKey);
        div.innerHTML = `
            <span class="text-[10.5px] font-medium text-apple-textSecondary bg-white/95 border border-apple-border/80 px-3 py-0.5 rounded-full shadow-2xs">
                ${escapeHtml(dateLabel || 'Hari Ini')}
            </span>
        `;
        thread.appendChild(div);
    }
}
