/**
 * BEANTALK LIVE INBOX — JAVASCRIPT
 * Asynchronous messaging and thread interaction.
 */

function scrollToBottom() {
    const body = document.getElementById('chatThreadBody');
    if (body) {
        body.scrollTop = body.scrollHeight;
    }
}

function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

async function handleSendReply(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('replyInput');
    if (!input) return;

    const text = input.value.trim();
    if (!text || typeof activeConversationId === 'undefined' || !activeConversationId) return;

    input.value = '';

    // Render pesan optimistik sementara di chat thread
    const thread = document.getElementById('chatThreadBody');
    if (thread) {
        const tempDiv = document.createElement('div');
        tempDiv.className = 'msg-row msg-agent';
        const senderName = typeof currentUserName !== 'undefined' ? currentUserName : 'CS Agent';
        tempDiv.innerHTML = `
            <span class="msg-sender">${escapeHtml(senderName)}</span>
            <div class="msg-bubble">${escapeHtml(text)}</div>
            <span class="msg-time">Baru saja</span>
        `;
        thread.appendChild(tempDiv);
        scrollToBottom();
    }

    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const token = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const res = await fetch(`/api/v1/admin/conversations/${activeConversationId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: text })
        });

        const json = await res.json();
        if (!json.success) {
            alert('Gagal mengirim balasan: ' + (json.error?.message || 'Error'));
        }
    } catch (err) {
        console.error('Error reply:', err);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
});
