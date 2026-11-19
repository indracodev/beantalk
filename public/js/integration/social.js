/* Integrasi detail — social. Data server: window.BT (lihat integration-detail.blade.php). */
// 3. Dynamic Multi-Contact Social Channels Builder
function renderSocialChannelsList() {
    const container = document.getElementById('socialChannelsListContainer');
    const emptyState = document.getElementById('socialChannelsEmptyState');
    const labelCount = document.getElementById('labelSocialCount');
    const badgeTabCount = document.getElementById('badgeTabSocialCount');

    if (!container) return;

    container.innerHTML = '';

    if (!Array.isArray(currentSocialChannels) || currentSocialChannels.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
    } else {
        if (emptyState) emptyState.classList.add('hidden');

        currentSocialChannels.forEach((chan, idx) => {
            const row = document.createElement('div');
            const isEnabled = Boolean(chan.enabled);
            const currentPlatform = (chan.platform || chan.icon || 'whatsapp').toLowerCase();
            const matchedOpt = PLATFORM_OPTIONS.find(o => o.value === currentPlatform) || PLATFORM_OPTIONS[0];
            const isCustomIcon = (chan.icon_type === 'custom') || (Boolean(chan.custom_icon) && chan.icon_type !== 'default');

            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-emerald-300 transition flex flex-col gap-3';
            
            let platformOptionsHtml = '';
            PLATFORM_OPTIONS.forEach(opt => {
                const selected = (opt.value === currentPlatform) ? 'selected' : '';
                platformOptionsHtml += `<option value="${opt.value}" ${selected}>${opt.name}</option>`;
            });

            row.innerHTML = `
                <!-- Baris Utama: Platform, Nama, URL, Status & Hapus -->
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <div class="flex items-center gap-2 shrink-0 self-start md:self-center">
                        <span class="w-6 h-6 rounded-full bg-apple-canvas border border-apple-border text-[11px] font-bold text-apple-textSecondary flex items-center justify-center">
                            ${idx + 1}
                        </span>
                        <div id="socialIconPreview_${idx}" class="w-7 h-7 rounded-lg border border-apple-border bg-apple-canvas/60 flex items-center justify-center shrink-0 shadow-2xs">
                            ${getSocialPreviewIconHtml(chan, currentPlatform)}
                        </div>
                        <select onchange="updateSocialPlatform(${idx}, this.value)" class="text-[11.5px] font-semibold py-1.5 px-2.5 bg-apple-canvas/60 border border-apple-border rounded-lg focus:bg-white focus:outline-none cursor-pointer">
                            ${platformOptionsHtml}
                        </select>
                    </div>

                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-2.5">
                        <div class="md:col-span-5">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Nama / Label Kontak</label>
                            <input type="text" value="${escapeHtml(chan.name || '')}" oninput="updateSocialChannelField(${idx}, 'name', this.value)" placeholder="Contoh: CS Sales, Akun Resmi, CS Retur..." class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Nomor HP / URL Tautan</label>
                            <input type="text" value="${escapeHtml(chan.url || '')}" oninput="updateSocialChannelField(${idx}, 'url', this.value)" placeholder="${matchedOpt.placeholder}" class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 self-end md:self-center">
                        <label class="inline-flex items-center gap-1.5 text-[11.5px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer ${isEnabled ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-neutral-100 border-neutral-200 text-neutral-500'}">
                            <input type="checkbox" ${isEnabled ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'enabled', this.checked)" class="rounded text-emerald-600 focus:ring-0 cursor-pointer">
                            <span>${isEnabled ? 'Aktif' : 'Nonaktif'}</span>
                        </label>

                        <button type="button" onclick="removeSocialChannel(${idx})" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition cursor-pointer" title="Hapus Kontak Ini">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Baris Opsi Icon: Default Asli vs Custom Icon -->
                <div class="pt-2 border-t border-apple-border/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-apple-canvas/20 -mx-3.5 -mb-3.5 p-2.5 px-3.5 rounded-b-xl text-[11px]">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-apple-textSecondary flex items-center gap-1">
                            🎨 Pilihan Icon:
                        </span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="icon_type_${idx}" value="default" ${!isCustomIcon ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'icon_type', 'default')" class="text-apple-blue focus:ring-0">
                            <span class="${!isCustomIcon ? 'font-bold text-apple-textPrimary' : 'text-apple-textSecondary'}">Default Icon Asli (${matchedOpt.name})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="icon_type_${idx}" value="custom" ${isCustomIcon ? 'checked' : ''} onchange="updateSocialChannelField(${idx}, 'icon_type', 'custom')" class="text-apple-blue focus:ring-0">
                            <span class="${isCustomIcon ? 'font-bold text-apple-textPrimary' : 'text-apple-textSecondary'}">Custom Icon</span>
                        </label>
                    </div>

                    ${isCustomIcon ? `
                        <div class="flex-1 sm:max-w-md flex items-center gap-2">
                            <input type="text" value="${escapeHtml(chan.custom_icon || '')}" oninput="updateSocialChannelField(${idx}, 'custom_icon', this.value)" placeholder="URL Gambar / SVG (cth: https://.../logo.png)" class="w-full text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue focus:outline-none">
                        </div>
                    ` : ''}
                </div>
            `;
            container.appendChild(row);
        });
    }

    const activeCount = Array.isArray(currentSocialChannels) ? currentSocialChannels.filter(c => c.enabled).length : 0;
    if (labelCount) labelCount.innerText = activeCount + ' Aktif';
    if (badgeTabCount) badgeTabCount.innerText = activeCount;
    serializeSocialChannels();
}

function updateSocialPlatform(index, newPlatform) {
    if (currentSocialChannels[index]) {
        currentSocialChannels[index].platform = newPlatform;
        currentSocialChannels[index].icon = newPlatform;
        const opt = PLATFORM_OPTIONS.find(o => o.value === newPlatform);
        if (opt && (!currentSocialChannels[index].name || PLATFORM_OPTIONS.some(p => p.name === currentSocialChannels[index].name))) {
            currentSocialChannels[index].name = opt.name;
        }
        renderSocialChannelsList();
    }
}

function updateSocialChannelField(index, field, value) {
    if (currentSocialChannels[index]) {
        currentSocialChannels[index][field] = value;
        // Auto-enable if user entered URL
        if (field === 'url' && value && value.trim().length > 0 && !currentSocialChannels[index].enabled) {
            currentSocialChannels[index].enabled = true;
            renderSocialChannelsList();
            return;
        }
        if (field === 'enabled' || field === 'icon_type') {
            renderSocialChannelsList();
        } else if (field === 'custom_icon') {
            const previewEl = document.getElementById('socialIconPreview_' + index);
            if (previewEl) {
                previewEl.innerHTML = getSocialPreviewIconHtml(currentSocialChannels[index], currentSocialChannels[index].platform);
            }
            serializeSocialChannels();
        } else {
            serializeSocialChannels();
        }
    }
}

function addNewSocialChannel(platform = 'whatsapp', name = '', url = '') {
    if (!Array.isArray(currentSocialChannels)) {
        currentSocialChannels = [];
    }

    const opt = PLATFORM_OPTIONS.find(o => o.value === platform) || PLATFORM_OPTIONS[0];
    const defaultName = name || opt.name;

    currentSocialChannels.push({
        id: platform + '_' + Date.now(),
        platform: platform,
        name: defaultName,
        url: url,
        enabled: true,
        icon: platform,
        icon_type: 'default',
        custom_icon: ''
    });

    renderSocialChannelsList();

    setTimeout(() => {
        const inputs = document.querySelectorAll('#socialChannelsListContainer input[type="text"]');
        if (inputs.length > 0) {
            inputs[inputs.length - 2].focus();
        }
    }, 50);
}

function removeSocialChannel(index) {
    if (Array.isArray(currentSocialChannels) && currentSocialChannels[index]) {
        currentSocialChannels.splice(index, 1);
        renderSocialChannelsList();
    }
}

function serializeSocialChannels() {
    const hidden = document.getElementById('hiddenSocialChannels');
    if (hidden) {
        hidden.value = JSON.stringify(currentSocialChannels);
    }
}

function serializeAllSettings() {
    serializeBotTree();
    serializeBotRules();
    serializeSocialChannels();
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

