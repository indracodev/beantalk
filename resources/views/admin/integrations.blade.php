@extends('layouts.admin')

@section('title', 'Integrasi Multi-Website')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Integrasi Multi-Website</h1>
            <p class="page-desc">Kelola saluran toko online Anda, sesuaikan warna aksen widget, dan pasang kode sematan di website host.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="btn-primary" onclick="openModal('modalNewIntegration')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Integrasi Baru</span>
            </button>
            <a href="/demo-store.html" target="_blank" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
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

<!-- MODAL TAMBAH INTEGRASI BARU -->
<div class="modal-backdrop" id="modalNewIntegration" onclick="if(event.target===this) closeModal('modalNewIntegration')">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Saluran / Website Baru</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalNewIntegration')">&times;</button>
        </div>
        <form action="{{ route('admin.integrations.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="integName">Nama Website / Toko</label>
                    <input type="text" id="integName" name="name" class="form-control" placeholder="Contoh: Kopi Kenangan Store" required>
                    <span class="form-hint">Nama pengenal saluran yang akan tampil di Inbox CS.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="integDomain">Domain Toko</label>
                    <input type="text" id="integDomain" name="domain" class="form-control" placeholder="kopikenangan.com atau staging.toko.id" required>
                    <span class="form-hint">Domain tempat widget akan dipasang (tanpa http://).</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna Aksen Chat Widget</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" id="integColorPicker" value="#C59B27" style="width: 44px; height: 38px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; background: transparent;" onchange="document.getElementById('integColorHex').value = this.value">
                        <input type="text" id="integColorHex" name="primary_color" class="form-control" value="#C59B27" style="width: 110px; font-family: monospace;" oninput="document.getElementById('integColorPicker').value = this.value">
                    </div>
                    <span class="form-hint">Warna dominan untuk tombol peluncur, bubble balasan, dan header widget.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="greetingTitle">Judul Sapaan (Greeting Title)</label>
                    <input type="text" id="greetingTitle" name="greeting_title" class="form-control" value="Hallo!">
                </div>

                <div class="form-group">
                    <label class="form-label" for="greetingSubtitle">Sub-judul Sapaan</label>
                    <input type="text" id="greetingSubtitle" name="greeting_subtitle" class="form-control" value="Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalNewIntegration')">Batal</button>
                <button type="submit" class="btn-primary">
                    <span>Simpan &amp; Generate Script</span>
                </button>
            </div>
        </form>
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
