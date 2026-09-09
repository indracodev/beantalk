@extends('layouts.admin')

@section('title', 'Integrasi Multi-Website')

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
                    <button type="button" class="btn-copy" onclick="copySnippet('snippet_{{ $p->id }}', this)">
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
    function copySnippet(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        copyToClipboard(text, btn);
    }
</script>
@endpush
