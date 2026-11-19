<!-- Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="integrations-card-container">
    @forelse($projects as $p)
        @php
            $primaryColor = $p->widgetSetting->primary_color ?? '#0071E3';
            $publicKey = $p->activeApiKey->public_key ?? 'pk_live_' . $p->slug;
            $domain = $p->domains->first()->domain ?? 'store.com';
            $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $p->name), 0, 2));
        @endphp
        <div class="bg-white border border-apple-border rounded-xl p-4 shadow-apple-sm flex flex-col justify-between gap-3">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg text-white flex items-center justify-center font-bold text-[13px] shadow-2xs" style="background-color: {{ $primaryColor }}">
                        {{ $initials }}
                    </div>
                    <div>
                        <h4 class="font-semibold text-[13px] text-apple-textPrimary">{{ $p->name }}</h4>
                        <div class="text-[11px] text-apple-textTertiary font-mono">{{ $domain }}</div>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 bg-apple-canvas/50 p-2 rounded-lg text-[11px]">
                <div>
                    <span class="text-apple-textTertiary text-[10px] block">Total Percakapan</span>
                    <span class="font-semibold text-apple-textPrimary font-mono">{{ $p->conversations_count }}</span>
                </div>
                <div>
                    <span class="text-apple-textTertiary text-[10px] block">Pengunjung Terdaftar</span>
                    <span class="font-semibold text-apple-textPrimary font-mono">{{ $p->visitors_count }}</span>
                </div>
            </div>

            <!-- Assigned CS Agent Info -->
            <div class="flex items-center justify-between text-[11px] px-2.5 py-1.5 rounded-lg bg-apple-canvas/40 border border-apple-subtleBorder">
                <span class="text-apple-textTertiary text-[10.5px]">Agent CS:</span>
                @if($p->assignedUsers->isEmpty())
                    <span class="inline-flex items-center gap-1 font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10.5px]">
                        <span>👥 Semua Agent (All)</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-medium text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 text-[10.5px] truncate max-w-[200px]" title="{{ $p->assignedUsers->pluck('name')->implode(', ') }}">
                        <span>👤 {{ $p->assignedUsers->pluck('name')->implode(', ') }}</span>
                    </span>
                @endif
            </div>

            <!-- Code Snippet Box -->
            <div>
                <div class="flex items-center justify-between text-[10px] text-apple-textTertiary mb-1">
                    <span>Universal Embed Script</span>
                    <span class="font-mono text-[9.5px]">Public Key: {{ $publicKey }}</span>
                </div>
                <div class="bg-apple-canvas text-apple-textPrimary border border-apple-border font-mono text-[10.5px] p-2.5 rounded-lg break-all select-all leading-relaxed" id="snippet_{{ $p->id }}">&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $publicKey }}" data-api-url="{{ url('/') }}" async&gt;&lt;/script&gt;</div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-apple-subtleBorder">
                <div class="flex gap-2">
                    <button type="button" onclick="copySnippet('snippet_{{ $p->id }}', this)" class="px-2.5 py-1 text-[11px] font-medium rounded-md border border-apple-border text-apple-textPrimary hover:bg-apple-canvas transition shadow-2xs">
                        Copy Code
                    </button>
                    <a href="{{ route('admin.integrations.detail', $p->id) }}" class="px-2.5 py-1 text-[11px] font-medium rounded-md bg-apple-blue/10 text-apple-blue hover:bg-apple-blue hover:text-white transition inline-flex items-center gap-1">
                        <span>Settings &amp; Bot</span>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>

                <form action="{{ route('admin.integrations.regenerate', $p->id) }}" method="POST" class="m-0" onsubmit="return confirm('Regenerate API Key untuk {{ $p->name }}?\n\nKey lama akan dinonaktifkan. Anda harus memperbarui kode embed di website klien.')">
                    @csrf
                    <button type="submit" class="px-2 py-1 text-[10.5px] text-apple-orange hover:text-red-600 transition">
                        Regenerate Key
                    </button>
                </form>
            </div>
        </div>

        @include('admin.partials.integrations.settings-modal')
    @empty
        <div class="col-span-2 p-12 text-center text-apple-textTertiary bg-white rounded-xl border border-apple-border">
            <p class="text-[13px] font-medium text-apple-textSecondary">Belum ada website atau saluran yang terhubung.</p>
            <button type="button" onclick="openModal('modalNewIntegration')" class="mt-3 px-3.5 py-1.5 rounded-lg bg-apple-blue text-white text-[12px] font-medium shadow-apple-sm">
                Hubungkan Website Pertama
            </button>
        </div>
    @endforelse
</div>
