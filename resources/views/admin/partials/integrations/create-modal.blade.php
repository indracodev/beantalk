<!-- MODAL TAMBAH INTEGRASI BARU -->
<div class="modal-backdrop fixed inset-0 bg-apple-textPrimary/25 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalNewIntegration" onclick="if(event.target===this) closeModal('modalNewIntegration')">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
        <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Connect Website Channel</h4>
            <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalNewIntegration')">✕</button>
        </div>
        <form action="{{ route('admin.integrations.store') }}" method="POST">
            @csrf
            <div class="p-4 flex flex-col gap-3.5 text-[12px]">
                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Site / Store Name</label>
                    <input type="text" id="integName" name="name" placeholder="e.g. Supresso Singapore" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Nama saluran untuk pengenal tiket inbox CS.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Domain Whitelist</label>
                    <input type="text" id="integDomain" name="domain" placeholder="e.g. supresso.sg atau tokoanda.com" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" required>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Domain tempat widget diinstal (tanpa http://).</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Widget Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="integColorPicker" value="#0071E3" class="w-9 h-8 border border-apple-border rounded-lg cursor-pointer bg-transparent" onchange="document.getElementById('integColorHex').value = this.value">
                        <input type="text" id="integColorHex" name="primary_color" value="#0071E3" class="w-28 px-2.5 py-1.5 border border-apple-border rounded-lg font-mono text-[11px]" oninput="document.getElementById('integColorPicker').value = this.value">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Greeting Title</label>
                    <input type="text" name="greeting_title" value="Hallo!" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Greeting Subtitle</label>
                    <input type="text" name="greeting_subtitle" value="Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1">Agent CS Penanggung Jawab</label>
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2 cursor-pointer text-[11.5px] text-apple-textPrimary">
                            <input type="radio" name="agent_scope" value="all" checked onchange="document.getElementById('newIntegAgentList').classList.add('hidden')" class="text-apple-blue focus:ring-apple-blue/20">
                            <span>Semua Agent (Default &mdash; All CS)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-[11.5px] text-apple-textPrimary">
                            <input type="radio" name="agent_scope" value="selected" onchange="document.getElementById('newIntegAgentList').classList.remove('hidden')" class="text-apple-blue focus:ring-apple-blue/20">
                            <span>Pilih Agent Tertentu (Multi-Select)</span>
                        </label>
                        <div id="newIntegAgentList" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-1.5 p-2 rounded-lg border border-apple-border bg-apple-canvas/40 max-h-36 overflow-y-auto">
                            @if(isset($agents))
                                @foreach($agents as $ag)
                                    <label class="flex items-center gap-2 p-1 text-[11px] text-apple-textPrimary hover:bg-white rounded cursor-pointer">
                                        <input type="checkbox" name="agent_ids[]" value="{{ $ag->id }}" class="rounded border-apple-border text-apple-blue focus:ring-apple-blue/20">
                                        <span class="truncate">{{ $ag->name }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium" onclick="closeModal('modalNewIntegration')">Batal</button>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm">Create Channel</button>
            </div>
        </form>
    </div>
</div>
