/* logs — dipindah dari view Blade agar bisa di-cache. */
function toggleSortDirection() {
    var dirInput = document.getElementById('filterDirInput');
    if (dirInput) {
        dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
        document.getElementById('logsFilterForm').submit();
    }
}

function toggleLogPayload(id) {
    var box = document.getElementById('payload-box-' + id);
    var arrow = document.getElementById('payload-arrow-' + id);
    if (box) {
        if (box.classList.contains('hidden')) {
            box.classList.remove('hidden');
            if (arrow) arrow.innerText = '▲';
        } else {
            box.classList.add('hidden');
            if (arrow) arrow.innerText = '▼';
        }
    }
}

// Responsive View Switcher (Table vs Card Feed)
function setLogViewMode(mode) {
    var tableView = document.getElementById('logsTableView');
    var cardsView = document.getElementById('logsCardsView');
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
        try { localStorage.setItem('beantalk_logs_view_mode', 'cards'); } catch(e) {}
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
        try { localStorage.setItem('beantalk_logs_view_mode', 'table'); } catch(e) {}
    }
}

// Auto-detect best view on load based on screen size or user preference
(function() {
    try {
        var savedMode = localStorage.getItem('beantalk_logs_view_mode');
        if (savedMode) {
            setLogViewMode(savedMode);
        } else if (window.innerWidth < 768) {
            // Default to comfortable card feed on mobile phones
            setLogViewMode('cards');
        } else {
            // Default to table on tablets and desktop
            setLogViewMode('table');
        }
    } catch(e) {
        setLogViewMode('table');
    }
})();
