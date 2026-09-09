@extends('layouts.admin')

@section('title', 'Live Inbox')

@push('styles')
<style>
    .inbox-workspace {
        flex: 1;
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    /* ==========================================================================
       COLUMN 1: CONVERSATIONS LIST
       ========================================================================== */
    .col-conversations {
        width: 320px;
        background: var(--bg-surface);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .col-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-title {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: var(--text-main);
    }

    .filter-select {
        padding: 6px 10px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-main);
        font-size: 12px;
        font-weight: 600;
        outline: none;
        cursor: pointer;
    }

    .search-input {
        width: 100%;
        padding: 7px 10px 7px 30px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        color: var(--text-main);
        outline: none;
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 9px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .filter-tabs {
        display: flex;
        gap: 4px;
        padding: 8px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-surface);
        overflow-x: auto;
    }

    .tab-btn {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: var(--text-main);
        background: var(--bg-subtle);
    }

    .tab-btn.active {
        color: var(--accent);
        background: var(--bg-subtle);
    }

    .conv-list {
        flex: 1;
        overflow-y: auto;
    }

    .conv-item {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: background 0.12s ease;
        position: relative;
    }

    .conv-item:hover {
        background: var(--bg-subtle);
    }

    .conv-item.active {
        background: var(--bg-subtle);
        border-left: 3px solid var(--accent);
    }

    .conv-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--text-main);
        flex-shrink: 0;
    }

    .conv-content {
        flex: 1;
        min-width: 0;
    }

    .conv-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 3px;
    }

    .conv-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .conv-time {
        font-size: 11px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .conv-project-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        background: var(--bg-canvas);
        border: 1px solid var(--border);
        margin-bottom: 4px;
    }

    .project-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .conv-snippet {
        font-size: 12px;
        color: var(--text-sub);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.3;
    }

    /* ==========================================================================
       COLUMN 2: ACTIVE CHAT THREAD
       ========================================================================== */
    .col-thread {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--bg-canvas);
        border-right: 1px solid var(--border);
        min-width: 0;
    }

    .thread-header {
        height: 60px;
        padding: 0 20px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .thread-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .thread-customer-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
    }

    .thread-status-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .status-open {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    .status-closed {
        background: rgba(148, 163, 184, 0.15);
        color: #475569;
    }

    .thread-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .thread-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .msg-row {
        display: flex;
        flex-direction: column;
        max-width: 75%;
    }

    .msg-row.msg-visitor {
        align-self: flex-start;
    }

    .msg-row.msg-agent {
        align-self: flex-end;
        align-items: flex-end;
    }

    .msg-sender {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 4px;
        padding: 0 4px;
    }

    .msg-bubble {
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.45;
        word-break: break-word;
    }

    .msg-visitor .msg-bubble {
        background: var(--bg-surface);
        color: var(--text-main);
        border: 1px solid var(--border);
        border-bottom-left-radius: 3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .msg-agent .msg-bubble {
        background: var(--accent);
        color: #FFFFFF;
        border-bottom-right-radius: 3px;
    }

    .msg-time {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 4px;
        padding: 0 4px;
    }

    .thread-composer {
        padding: 14px 20px;
        background: var(--bg-surface);
        border-top: 1px solid var(--border);
    }

    .composer-box {
        background: var(--bg-canvas);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: border-color 0.15s ease;
    }

    .composer-box:focus-within {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 2px rgba(197, 155, 39, 0.12);
    }

    .composer-textarea {
        width: 100%;
        background: transparent;
        border: none;
        color: var(--text-main);
        font-size: 13px;
        resize: none;
        outline: none;
        min-height: 52px;
    }

    .composer-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .composer-hint {
        font-size: 11px;
        color: var(--text-muted);
    }

    .btn-send {
        background: var(--accent);
        color: #FFFFFF;
        border: none;
        border-radius: 6px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }

    .btn-send:hover {
        background: var(--accent-hover);
    }

    /* ==========================================================================
       COLUMN 3: CUSTOMER CONTEXT DRAWER
       ========================================================================== */
    .col-context {
        width: 280px;
        background: var(--bg-surface);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        overflow-y: auto;
        padding: 20px 18px;
        gap: 20px;
    }

    .context-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 10px;
    }

    .context-row {
        margin-bottom: 10px;
    }

    .context-label {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .context-value {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        word-break: break-all;
    }

    .context-link {
        color: var(--accent);
        text-decoration: none;
    }

    .context-link:hover {
        text-decoration: underline;
    }

    .empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 12px;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="inbox-workspace">

    <!-- KOLOM 1: DAFTAR PERCAKAPAN -->
    <aside class="col-conversations">
        <div class="col-header">
            <div class="header-top">
                <span class="header-title">Inbox Percakapan</span>
                <select class="filter-select" onchange="location.href='?project_id='+this.value">
                    <option value="">Semua Website Toko</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <form action="{{ route('admin.inbox') }}" method="GET" class="search-wrapper">
                <span class="search-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Cari nama atau email..." 
                    value="{{ request('search') }}"
                >
            </form>
        </div>

        <div class="filter-tabs">
            <a href="?status=all" class="tab-btn {{ !request('status') || request('status') === 'all' ? 'active' : '' }}">
                <span>Semua</span>
                <span class="nav-badge">{{ $counts['all'] }}</span>
            </a>
            <a href="?status=open" class="tab-btn {{ request('status') === 'open' ? 'active' : '' }}">
                <span>Open</span>
                <span class="nav-badge">{{ $counts['open'] }}</span>
            </a>
            <a href="?status=mine" class="tab-btn {{ request('status') === 'mine' ? 'active' : '' }}">
                <span>Tugas Saya</span>
                <span class="nav-badge">{{ $counts['mine'] }}</span>
            </a>
            <a href="?status=closed" class="tab-btn {{ request('status') === 'closed' ? 'active' : '' }}">
                <span>Selesai</span>
            </a>
        </div>

        <div class="conv-list">
            @forelse($conversations as $conv)
                <a href="{{ route('admin.inbox.show', $conv->id) }}" class="conv-item {{ $activeConversation && $activeConversation->id === $conv->id ? 'active' : '' }}">
                    <div class="conv-avatar">
                        {{ strtoupper(substr($conv->visitor->name ?? 'Pengunjung', 0, 2)) }}
                    </div>
                    <div class="conv-content">
                        <div class="conv-top">
                            <span class="conv-name">{{ $conv->visitor->name ?? 'Pengunjung Web' }}</span>
                            <span class="conv-time">{{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(null, true) : '-' }}</span>
                        </div>
                        <div class="conv-project-pill">
                            <span class="project-dot" style="background-color: {{ $conv->project->widgetSetting->primary_color ?? '#C59B27' }}"></span>
                            <span>{{ $conv->project->name ?? 'Website' }}</span>
                        </div>
                        <div class="conv-snippet">
                            {{ $conv->latestMessage->content ?? 'Percakapan baru diinisialisasi...' }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p style="font-size: 13px; font-weight: 600;">Belum ada percakapan</p>
                    <p style="font-size: 11px; margin-top: 4px;">Pesan dari widget pengunjung akan tampil di sini secara realtime.</p>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- KOLOM 2: ACTIVE CHAT THREAD -->
    <section class="col-thread">
        @if($activeConversation)
            <div class="thread-header">
                <div class="thread-header-info">
                    <div class="thread-customer-name">{{ $activeConversation->visitor->name ?? 'Pengunjung Web' }}</div>
                    <span class="thread-status-badge {{ $activeConversation->status === 'open' ? 'status-open' : 'status-closed' }}">
                        ● {{ $activeConversation->status }}
                    </span>
                    <div class="conv-project-pill">
                        <span class="project-dot" style="background-color: {{ $activeConversation->project->widgetSetting->primary_color ?? '#C59B27' }}"></span>
                        <span>{{ $activeConversation->project->name }}</span>
                    </div>
                </div>

                <div class="thread-actions">
                    <span style="font-size: 12px; color: var(--text-muted);">Ditugaskan:</span>
                    <strong style="font-size: 12px;">{{ $activeConversation->assignedUser->name ?? 'Belum Ditugaskan' }}</strong>
                </div>
            </div>

            <!-- Pesan Percakapan -->
            <div class="thread-body" id="chatThreadBody">
                @forelse($activeConversation->messages as $msg)
                    <div class="msg-row {{ $msg->sender_type === 'visitor' ? 'msg-visitor' : 'msg-agent' }}">
                        <span class="msg-sender">
                            {{ $msg->sender_type === 'visitor' ? ($activeConversation->visitor->name ?? 'Pengunjung') : ($msg->user->name ?? 'Staff CS') }}
                        </span>
                        <div class="msg-bubble">
                            {{ $msg->content }}
                        </div>
                        <span class="msg-time">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <p style="font-size: 12px;">Belum ada riwayat pesan dalam tiket ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Composer Balasan CS -->
            <div class="thread-composer">
                <form id="replyForm" onsubmit="handleSendReply(event)">
                    <div class="composer-box">
                        <textarea 
                            id="replyInput" 
                            class="composer-textarea" 
                            placeholder="Ketik balasan untuk {{ $activeConversation->visitor->name ?? 'pelanggan' }}... (Tekan Enter untuk mengirim)"
                            onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); handleSendReply(event); }"
                            required
                        ></textarea>
                        <div class="composer-toolbar">
                            <span class="composer-hint">Enter untuk kirim • Shift+Enter untuk baris baru</span>
                            <button type="submit" class="btn-send" id="btnSubmitReply">
                                <span>Kirim Balasan</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="empty-state">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p style="font-size: 14px; font-weight: 700;">Pilih Percakapan</p>
                <p style="font-size: 12px; margin-top: 4px;">Pilih salah satu pesan dari panel kiri untuk membuka obrolan.</p>
            </div>
        @endif
    </section>

    <!-- KOLOM 3: DETAIL KONTEKS PELANGGAN -->
    @if($activeConversation)
    <aside class="col-context">
        <div>
            <div class="context-section-title">Informasi Pengunjung</div>
            <div class="context-row">
                <div class="context-label">Nama</div>
                <div class="context-value">{{ $activeConversation->visitor->name ?? 'Anonim' }}</div>
            </div>
            <div class="context-row">
                <div class="context-label">Email</div>
                <div class="context-value">{{ $activeConversation->visitor->email ?? 'Belum terdaftar' }}</div>
            </div>
            <div class="context-row">
                <div class="context-label">Visitor ID</div>
                <div class="context-value" style="font-family: monospace; font-size: 11px;">
                    #{{ substr($activeConversation->visitor->visitor_uuid ?? 'anon', 0, 16) }}
                </div>
            </div>
        </div>

        <div>
            <div class="context-section-title">Konteks Halaman Web</div>
            <div class="context-row">
                <div class="context-label">Channel Website</div>
                <div class="context-value">{{ $activeConversation->project->name }}</div>
            </div>
            <div class="context-row">
                <div class="context-label">Judul Halaman</div>
                <div class="context-value">{{ $activeConversation->page_title ?? 'Beranda Toko' }}</div>
            </div>
            <div class="context-row">
                <div class="context-label">URL Terakhir</div>
                <div class="context-value">
                    <a href="{{ $activeConversation->page_url ?? '#' }}" target="_blank" class="context-link">
                        {{ Str::limit($activeConversation->page_url ?? 'Tidak ada URL', 36) }}
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div class="context-section-title">Perangkat & Teknis</div>
            <div class="context-row">
                <div class="context-label">IP Address</div>
                <div class="context-value">{{ $activeConversation->visitor->ip_address ?? '127.0.0.1' }}</div>
            </div>
            <div class="context-row">
                <div class="context-label">Browser / OS</div>
                <div class="context-value" style="font-size: 11px;">
                    {{ Str::limit($activeConversation->visitor->user_agent ?? 'Mozilla/5.0 Web Browser', 50) }}
                </div>
            </div>
        </div>
    </aside>
    @endif

</div>
@endsection

@push('scripts')
<script>
    const activeConversationId = {{ $activeConversation ? $activeConversation->id : 'null' }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function scrollToBottom() {
        const body = document.getElementById('chatThreadBody');
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    }

    scrollToBottom();

    // ==========================================================================
    // KIRIM BALASAN CS REALTIME VIA REST API
    // ==========================================================================
    async function handleSendReply(e) {
        if (e) e.preventDefault();
        const input = document.getElementById('replyInput');
        const text = input.value.trim();
        if (!text || !activeConversationId) return;

        input.value = '';

        // Render optimistik sementara
        const thread = document.getElementById('chatThreadBody');
        const tempDiv = document.createElement('div');
        tempDiv.className = 'msg-row msg-agent';
        tempDiv.innerHTML = `
            <span class="msg-sender">{{ Auth::user()->name }}</span>
            <div class="msg-bubble">${escapeHtml(text)}</div>
            <span class="msg-time">Baru saja</span>
        `;
        thread.appendChild(tempDiv);
        scrollToBottom();

        try {
            const res = await fetch(`/api/v1/admin/conversations/${activeConversationId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
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

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>
@endpush
