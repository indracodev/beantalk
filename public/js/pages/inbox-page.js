/* Inbox — state awal dari window.BT_INBOX + helper UI. Dimuat sebelum inbox.js. */
const activeConversationId = BT_INBOX.activeConversationId;
const currentUserName = BT_INBOX.currentUserName;
let initialLastMessageId = BT_INBOX.initialLastMessageId;
let initialMaxTenantMessageId = BT_INBOX.initialMaxTenantMessageId;
let conversationStatus = BT_INBOX.conversationStatus;

// Canned Response Shortcut Helper
function insertCannedResponse(shortcut) {
    const textarea = document.getElementById('replyInput');
    if (!textarea) return;
    if (shortcut === '/shipping') {
        textarea.value = 'Pesanan sebelum jam 15:00 WIB dikirim pada hari yang sama!';
    } else if (shortcut === '/stock') {
        textarea.value = 'Stok produk ini siap kirim dari gudang pusat Surabaya!';
    } else if (shortcut === '/greeting') {
        textarea.value = 'Halo! Ada yang bisa kami bantu seputar produk dan pesanan Anda?';
    }
    textarea.focus();
}

// Instant filter search on client side
function handleSearchConv(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('#convListContainer .conv-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
}

// Mobile navigation switch
function mobileBackToList() {
    const convList = document.getElementById('pane-conv-list');
    const chatPane = document.getElementById('pane-chat-thread');
    const inspector = document.getElementById('pane-context-inspector');
    const inspectorBackdrop = document.getElementById('inspectorBackdrop');
    const bottomNav = document.getElementById('mobile-bottom-nav');

    if (convList) {
        convList.classList.remove('hidden');
        convList.classList.add('flex');
    }
    if (chatPane) {
        chatPane.classList.add('hidden');
        chatPane.classList.remove('flex');
    }
    if (inspector) {
        inspector.classList.remove('open-mobile', 'flex');
        inspector.classList.add('hidden');
    }
    if (inspectorBackdrop) {
        inspectorBackdrop.classList.add('hidden');
    }
    if (bottomNav) {
        bottomNav.classList.remove('hidden');
        bottomNav.classList.add('flex');
    }
    if (window.history && window.history.pushState) {
        window.history.pushState(null, '',
            BT_INBOX.listUrl
            );
    }
}

