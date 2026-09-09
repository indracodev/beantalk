@extends('layouts.admin')

@section('title', 'Live Inbox')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inbox.css') }}">
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
                <a href="{{ route('admin.inbox', $conv->id) }}" class="conv-item {{ $activeConversation && $activeConversation->id === $conv->id ? 'active' : '' }}">
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
                            {{ $conv->last_message_preview ?? ($conv->latestMessage->content ?? 'Percakapan baru diinisialisasi...') }}
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
                    <span id="threadStatusBadge" class="thread-status-badge {{ $activeConversation->status === 'open' ? 'status-open' : 'status-closed' }}">
                        ● {{ $activeConversation->status }}
                    </span>
                    <div class="conv-project-pill">
                        <span class="project-dot" style="background-color: {{ $activeConversation->project->widgetSetting->primary_color ?? '#C59B27' }}"></span>
                        <span>{{ $activeConversation->project->name }}</span>
                    </div>
                </div>

                <div class="thread-actions">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Tugaskan:</span>
                        <select id="assignCsSelect" class="filter-select" onchange="handleAssign(this.value)">
                            <option value="">-- Belum Ditugaskan --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ $activeConversation->assigned_user_id == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn-action" id="btnToggleStatus" onclick="handleToggleStatus()">
                        {{ $activeConversation->status === 'open' ? 'Tutup Tiket' : 'Buka Kembali' }}
                    </button>
                </div>
            </div>

            <!-- Pesan Percakapan -->
            <div class="thread-body" id="chatThreadBody">
                @forelse($activeConversation->messages as $msg)
                    <div class="msg-row {{ $msg->sender_type === 'visitor' ? 'msg-visitor' : 'msg-agent' }}" data-id="{{ $msg->id }}">
                        <span class="msg-sender">
                            {{ $msg->sender_type === 'visitor' ? ($activeConversation->visitor->name ?? 'Pengunjung') : ($msg->sender_name ?? $msg->user->name ?? 'Staff CS') }}
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
    const currentUserName = "{{ Auth::user()->name }}";
    let initialLastMessageId = {{ ($activeConversation && $activeConversation->messages->isNotEmpty()) ? $activeConversation->messages->last()->id : 0 }};
    let conversationStatus = "{{ $activeConversation ? $activeConversation->status : 'open' }}";
</script>
<script src="{{ asset('js/inbox.js') }}"></script>
@endpush
