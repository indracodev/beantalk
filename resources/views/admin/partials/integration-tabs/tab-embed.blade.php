        <!-- ============================================================ -->
        <!-- TAB 4: DOMAIN, AGENT & EMBED CODE                            -->
        <!-- ============================================================ -->
        <div id="tab-pane-embed" class="tab-pane flex flex-col gap-4" style="display: none;">
            
            <!-- 1. PENGATURAN WEBSITE & DOMAIN WHITELIST -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex items-center justify-between">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary">Pengaturan Website &amp; Domain Whitelist</h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Ubah nama identitas website serta kelola daftar domain yang diizinkan memuat widget chat ini.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold bg-blue-50 text-apple-blue border border-blue-200">Domain Whitelist</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[12px]">
                    <div>
                        <label class="block font-medium text-apple-textPrimary mb-1" for="inputProjectName">Nama Website / Brand</label>
                        <input type="text" id="inputProjectName" name="name" value="{{ $project->name }}" class="w-full px-3 py-2 border border-apple-border rounded-lg text-[12px] font-semibold text-apple-textPrimary focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Contoh: Supresso Coffee Store" required>
                        <span class="text-[10.5px] text-apple-textTertiary mt-1 block">Nama website untuk pengenal tiket dan filter percakapan di inbox.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-apple-textPrimary mb-1" for="inputProjectDomains">Domain Whitelist (Pisah koma jika lebih dari satu)</label>
                        <textarea id="inputProjectDomains" name="domains" rows="2" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] text-apple-textPrimary focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="contoh.com, www.contoh.com, dev.contoh.com" required>{{ $project->domains->pluck('domain')->implode(', ') }}</textarea>
                        <span class="text-[10.5px] text-apple-textTertiary mt-1 block">Hanya domain yang tertera di sini yang diizinkan berinteraksi dengan API chat.</span>
                    </div>
                </div>
            </div>

            <!-- 2. PENUGASAN AGENT CS (RESPONSIBLE AGENTS) -->
            @php
                $assignedIds = $project->assignedUsers->pluck('id')->toArray();
                $isAllAgents = empty($assignedIds);
            @endphp
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary">Staf Agent CS Penanggung Jawab</h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tentukan apakah semua staf CS menangani tiket website ini, atau hanya staf tertentu.</p>
                    </div>
                    <span id="badgeAgentScope" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold {{ $isAllAgents ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }} self-start sm:self-auto">
                        {{ $isAllAgents ? '👥 Semua Agent (All CS)' : '👤 ' . count($assignedIds) . ' Agent Terpilih' }}
                    </span>
                </div>

                <div class="flex flex-col gap-3 text-[12px]">
                    <!-- Radio Pilihan: Semua atau Tertentu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3 rounded-xl border {{ $isAllAgents ? 'border-apple-blue bg-blue-50/30' : 'border-apple-border bg-white' }} flex items-start gap-3 cursor-pointer hover:border-apple-blue/60 transition" id="labelAgentAll">
                            <input type="radio" name="agent_scope" value="all" {{ $isAllAgents ? 'checked' : '' }} onchange="toggleAgentScope(this.value)" class="mt-0.5 text-apple-blue focus:ring-apple-blue/20">
                            <div>
                                <span class="font-bold text-apple-textPrimary block">Semua Agent CS (Default &mdash; All)</span>
                                <span class="text-[11px] text-apple-textSecondary mt-0.5 block leading-relaxed">Seluruh tim CS operasional memiliki akses dan dapat merespons tiket yang masuk dari website ini.</span>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border {{ !$isAllAgents ? 'border-apple-blue bg-blue-50/30' : 'border-apple-border bg-white' }} flex items-start gap-3 cursor-pointer hover:border-apple-blue/60 transition" id="labelAgentSelected">
                            <input type="radio" name="agent_scope" value="selected" {{ !$isAllAgents ? 'checked' : '' }} onchange="toggleAgentScope(this.value)" class="mt-0.5 text-apple-blue focus:ring-apple-blue/20">
                            <div>
                                <span class="font-bold text-apple-textPrimary block">Pilih Agent Tertentu (Multi-Select)</span>
                                <span class="text-[11px] text-apple-textSecondary mt-0.5 block leading-relaxed">Hanya staf yang dicentang di bawah yang secara spesifik ditugaskan menangani tiket website ini.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Multi-select Checklist Container -->
                    <div id="containerAgentChecklist" class="{{ $isAllAgents ? 'hidden' : 'grid' }} grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-2 border-t border-apple-subtleBorder">
                        @forelse($agents as $ag)
                            @php
                                $isAssigned = in_array($ag->id, $assignedIds);
                                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $ag->name ?: 'User'), 0, 2));
                            @endphp
                            <label class="flex items-center gap-2.5 p-2.5 rounded-lg border {{ $isAssigned ? 'border-apple-blue/60 bg-blue-50/20' : 'border-apple-border bg-white' }} hover:bg-apple-canvas/60 cursor-pointer transition">
                                <input type="checkbox" name="agent_ids[]" value="{{ $ag->id }}" {{ $isAssigned ? 'checked' : '' }} onchange="updateAgentScopeBadge()" class="rounded border-apple-border text-apple-blue focus:ring-apple-blue/20 agent-checkbox">
                                <div class="w-7 h-7 rounded-full bg-apple-canvas text-apple-textPrimary font-semibold text-[10px] flex items-center justify-center border border-black/5 shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-[11.5px] text-apple-textPrimary truncate">{{ $ag->name }}</div>
                                    <div class="text-[10px] text-apple-textTertiary truncate">{{ ucfirst($ag->role) }} &bull; {{ $ag->email }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full p-4 text-center text-apple-textTertiary text-[11.5px]">
                                Belum ada anggota staf CS yang terdaftar. Tambahkan staf di menu Team.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 3. UNIVERSAL EMBED SCRIPT & PUBLIC KEY -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border">
                    <h3 class="text-[14px] font-bold text-apple-textPrimary">Universal Embed Script (1-Baris Kode)</h3>
                    <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Tempelkan script tag ini sebelum penutup tag <code>&lt;/body&gt;</code> pada website Shopify, WordPress, WooCommerce, atau HTML kustom Anda.</p>
                </div>

                @php
                    $publicKey = $project->activeApiKey->public_key ?? 'pk_live_' . $project->slug;
                @endphp

                <!-- Snippet Box -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-[11.5px] font-semibold text-apple-textPrimary">
                        <span>Kode Script Embed</span>
                        <button type="button" onclick="copySnippetText('embedCodeDetail', this)" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-apple-border rounded-lg text-[11px] font-semibold hover:bg-apple-canvas transition shadow-2xs cursor-pointer">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>Salin Kode</span>
                        </button>
                    </div>

                    <div id="embedCodeDetail" class="bg-[#1C1C1E] text-neutral-200 font-mono text-[11px] p-3 rounded-xl select-all break-all leading-relaxed shadow-inner">&lt;script src="{{ url('/chat-widget.js') }}" data-project-key="{{ $publicKey }}" data-api-url="{{ url('/') }}" async&gt;&lt;/script&gt;</div>
                </div>

                <!-- API Keys & Domains Quick Glance -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-apple-border">
                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Public API Key</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border select-all">{{ $publicKey }}</div>
                    </div>

                    <div class="p-3 rounded-xl border border-apple-border bg-apple-canvas/30">
                        <span class="text-[11px] font-semibold text-apple-textPrimary block mb-1">Domain Terdaftar Saat Ini</span>
                        <div class="font-mono text-[11px] text-neutral-700 bg-white p-2 rounded border border-apple-border">{{ $project->domains->pluck('domain')->implode(', ') ?: 'Belum ada domain' }}</div>
                    </div>
                </div>
            </div>
        </div>
