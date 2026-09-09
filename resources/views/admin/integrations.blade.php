@extends('layouts.admin')

@section('title', 'Integrasi Multi-Website')

@push('styles')
<style>
    .page-container {
        flex: 1;
        overflow-y: auto;
        padding: 32px;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .page-header {
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .page-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .page-desc {
        font-size: 13px;
        color: var(--text-sub);
    }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 20px;
    }

    .project-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 22px;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        gap: 16px;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }

    .project-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-2px);
    }

    .card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .project-color-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-weight: 800;
        font-size: 14px;
        flex-shrink: 0;
    }

    .project-card-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
    }

    .project-domain {
        font-size: 12px;
        color: var(--text-muted);
    }

    .status-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10B981;
    }

    .stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 12px 14px;
        background: var(--bg-canvas);
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .stat-val {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
    }

    .code-box-wrapper {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .code-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
    }

    .code-box {
        background: var(--bg-canvas);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 12px;
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 11px;
        color: var(--text-main);
        word-break: break-all;
        position: relative;
    }

    .btn-copy {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        align-self: flex-start;
    }

    .btn-copy:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Integrasi Multi-Website</h1>
            <p class="page-desc">Kelola saluran toko online Anda, sesuaikan warna aksen widget, dan pasang kode sematan di website host.</p>
        </div>
        <div>
            <a href="/demo-store.html" target="_blank" class="btn-copy" style="background: var(--accent); color: #FFFFFF; border: none; padding: 10px 16px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Buka Demo Toko Realtime</span>
            </a>
        </div>
    </div>

    <div class="projects-grid">
        @foreach($projects as $p)
            <div class="project-card">
                <div class="card-top">
                    <div class="card-title-group">
                        <div class="project-color-badge" style="background-color: {{ $p->widgetSetting->primary_color ?? '#C59B27' }}">
                            {{ strtoupper(substr($p->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="project-card-name">{{ $p->name }}</div>
                            <div class="project-domain">{{ $p->domains->first()->domain ?? 'store.com' }}</div>
                        </div>
                    </div>
                    <span class="status-pill">
                        <span class="status-dot"></span>
                        Aktif
                    </span>
                </div>

                <div class="stats-row">
                    <div>
                        <div class="stat-label">Total Percakapan</div>
                        <div class="stat-val">{{ $p->conversations_count }}</div>
                    </div>
                    <div>
                        <div class="stat-label">Pengunjung Terdaftar</div>
                        <div class="stat-val">{{ $p->visitors_count }}</div>
                    </div>
                </div>

                <div class="code-box-wrapper">
                    <span class="code-label">Kunci Publik (Public API Key)</span>
                    <div class="code-box">
                        {{ $p->apiKeys->first()->public_key ?? 'pk_live_' . $p->slug }}
                    </div>
                </div>

                <div class="code-box-wrapper">
                    <span class="code-label">Script Tag Embed Widget (Salin ke Website)</span>
                    <div class="code-box" id="snippet_{{ $p->id }}">&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $p->apiKeys->first()->public_key ?? 'pk_live_' . $p->slug }}" data-color="{{ $p->widgetSetting->primary_color ?? '#C59B27' }}"&gt;&lt;/script&gt;</div>
                    <button type="button" class="btn-copy" onclick="copySnippet('snippet_{{ $p->id }}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Salin Script Tag</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copySnippet(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('Kode script embed berhasil disalin ke clipboard!');
        });
    }
</script>
@endpush
