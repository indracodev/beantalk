/* visitors — dipindah dari view Blade agar bisa di-cache. */
function toggleTrafficHub() {
    var hub = document.getElementById('trafficHubSection');
    var btnTxt = document.getElementById('txtToggleTrafficHub');
    if (!hub) return;
    var isHidden = hub.classList.toggle('hidden');
    if (btnTxt) {
        btnTxt.innerText = isHidden ? 'Tampilkan Grafik' : 'Sembunyikan Grafik';
    }
    try {
        localStorage.setItem('beantalk_traffic_hub_hidden', isHidden ? 'true' : 'false');
    } catch(e) {}
}

// Restore traffic hub visibility preference
(function() {
    try {
        var isHubHidden = localStorage.getItem('beantalk_traffic_hub_hidden') === 'true';
        var hub = document.getElementById('trafficHubSection');
        var btnTxt = document.getElementById('txtToggleTrafficHub');
        if (hub && isHubHidden) {
            hub.classList.add('hidden');
            if (btnTxt) btnTxt.innerText = 'Tampilkan Grafik';
        }
    } catch(e) {}
})();

function toggleSortDirection() {
    var dirInput = document.getElementById('filterDirInput');
    if (dirInput) {
        dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
        document.getElementById('visitorsFilterForm').submit();
    }
}

// Responsive View Switcher (Table vs Card Feed)
function setVisitorViewMode(mode) {
    var tableView = document.getElementById('visitorsTableView');
    var cardsView = document.getElementById('visitorsCardsView');
    var btnTable = document.getElementById('btnViewTable');
    var btnCards = document.getElementById('btnViewCards');
    var scrollHint = document.getElementById('tableScrollHint');

    if (!tableView || !cardsView) return;

    if (mode === 'cards') {
        tableView.classList.add('hidden');
        cardsView.classList.remove('hidden');
        if (scrollHint) scrollHint.classList.add('hidden');

        if (btnCards) {
            btnCards.className = 'px-2.5 py-1 rounded-md bg-white text-apple-textPrimary shadow-2xs font-semibold inline-flex items-center gap-1.5';
        }
        if (btnTable) {
            btnTable.className = 'px-2.5 py-1 rounded-md text-apple-textSecondary hover:text-apple-textPrimary inline-flex items-center gap-1.5';
        }
        try { localStorage.setItem('beantalk_visitors_view_mode', 'cards'); } catch(e) {}
    } else {
        cardsView.classList.add('hidden');
        tableView.classList.remove('hidden');
        if (scrollHint) scrollHint.classList.remove('hidden');

        if (btnTable) {
            btnTable.className = 'px-2.5 py-1 rounded-md bg-white text-apple-textPrimary shadow-2xs font-semibold inline-flex items-center gap-1.5';
        }
        if (btnCards) {
            btnCards.className = 'px-2.5 py-1 rounded-md text-apple-textSecondary hover:text-apple-textPrimary inline-flex items-center gap-1.5';
        }
        try { localStorage.setItem('beantalk_visitors_view_mode', 'table'); } catch(e) {}
    }
}

// Auto-detect view on load
(function() {
    try {
        var savedMode = localStorage.getItem('beantalk_visitors_view_mode');
        if (savedMode) {
            setVisitorViewMode(savedMode);
        } else if (window.innerWidth < 768) {
            setVisitorViewMode('cards');
        } else {
            setVisitorViewMode('table');
        }
    } catch(e) {
        setVisitorViewMode('table');
    }
})();

// AJAX Detail Fetcher for Visitor Footprint Modal
function showVisitorDetail(visitorId) {
    openModal('modalVisitorDetail');
    var loading = document.getElementById('modalVisitorLoading');
    var content = document.getElementById('modalVisitorContent');
    if (loading) loading.classList.remove('hidden');
    if (content) content.classList.add('hidden');

    fetch('/admin/visitors/' + visitorId, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (!res.success || !res.data) throw new Error('Data tidak ditemukan');
        var d = res.data;

        document.getElementById('modalVisitorTitle').innerText = d.display_name;
        document.getElementById('modalVisitorCode').innerText = d.customer_code + ' • UUID: ' + d.visitor_uuid;

        document.getElementById('detVisitorName').innerText = d.name;
        document.getElementById('detVisitorEmail').innerText = d.email || '-';
        document.getElementById('detVisitorProject').innerText = d.project.name || '-';
        document.getElementById('detVisitorDevice').innerText = (d.device_type === 'mobile' ? '📱 Mobile' : (d.device_type === 'tablet' ? '📟 Tablet' : '💻 Desktop')) + ' (' + d.os_name + ')';
        document.getElementById('detVisitorBrowser').innerText = d.browser_name;
        document.getElementById('detVisitorIp').innerText = d.ip_address || '-';
        document.getElementById('detVisitorLastSeen').innerText = d.last_seen + ' (' + d.last_seen_human + ')';
        document.getElementById('detVisitorUserAgent').innerText = d.user_agent || '-';

        // Presence pill
        var presenceEl = document.getElementById('detVisitorPresence');
        if (d.is_online) {
            presenceEl.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded-full bg-apple-green/10 text-apple-green border border-apple-green/30 font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span> Online Sekarang</span>';
        } else {
            presenceEl.innerHTML = '<span class="text-[11px] text-apple-textTertiary font-mono">Offline</span>';
        }

        // Conversations List
        var convContainer = document.getElementById('detConversationsList');
        var convBadge = document.getElementById('detConvCountBadge');
        convContainer.innerHTML = '';

        if (d.conversations && d.conversations.length > 0) {
            convBadge.innerText = d.conversations.length + ' tiket';
            d.conversations.forEach(function(c) {
                var statusColor = c.status === 'open' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-black/5 text-apple-textSecondary border-black/10';
                var item = document.createElement('div');
                item.className = 'p-2.5 rounded-xl border border-apple-border/70 bg-white flex items-start justify-between gap-2 text-[11px]';
                item.innerHTML = `
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="font-mono text-[9px] px-1.5 py-0.2 rounded border font-semibold ${statusColor}">#${c.id} • ${c.status.toUpperCase()}</span>
                            <span class="text-[10px] text-apple-textTertiary font-mono">${c.last_message_at || '-'}</span>
                        </div>
                        <div class="text-apple-textPrimary font-medium truncate">${c.page_title || c.page_url || 'Chat Session'}</div>
                        <div class="text-[10.5px] text-apple-textTertiary truncate mt-0.5">${c.last_message_preview || '-'}</div>
                    </div>
                    <a href="${c.inbox_url}" class="px-2.5 py-1 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[10.5px] font-medium transition shadow-2xs shrink-0 self-center">
                        Buka di Inbox
                    </a>
                `;
                convContainer.appendChild(item);
            });
        } else {
            convBadge.innerText = '0 tiket';
            convContainer.innerHTML = '<div class="p-3 text-center text-apple-textTertiary bg-white rounded-lg border border-apple-border text-[11px]">Pengunjung ini belum memulai percakapan chat.</div>';
        }

        if (loading) loading.classList.add('hidden');
        if (content) content.classList.remove('hidden');
    })
    .catch(function(err) {
        if (loading) {
            loading.innerHTML = '<span class="text-apple-red">Gagal memuat data jejak pengunjung.</span>';
        }
    });
}
