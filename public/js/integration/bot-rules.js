/* Integrasi detail — bot-rules. Data server: window.BT (lihat integration-detail.blade.php). */
// 2. Render FAQ Rules List
function renderFaqRulesList() {
    const container = document.getElementById('faqRulesListContainer');
    const emptyState = document.getElementById('faqEmptyState');
    const labelCount = document.getElementById('labelRuleCount');
    const badgeTabCount = document.getElementById('badgeTabRuleCount');

    if (!container) return;

    container.innerHTML = '';

    if (!Array.isArray(currentFaqRules) || currentFaqRules.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
    } else {
        if (emptyState) emptyState.classList.add('hidden');

        currentFaqRules.forEach((rule, idx) => {
            const row = document.createElement('div');
            const ruleOptions = Array.isArray(rule.options) ? rule.options : [];
            row.className = 'p-3.5 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-purple-300 transition flex flex-col gap-3';
            
            let optionsSectionHtml = '';
            if (ruleOptions.length > 0) {
                optionsSectionHtml = `
                    <div class="mt-1 pt-2.5 border-t border-apple-border/70 flex flex-col gap-2 bg-apple-canvas/30 p-2.5 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-apple-textSecondary flex items-center gap-1.5">
                                🔘 Tombol Opsi Balasan (${ruleOptions.length})
                            </span>
                            <button type="button" onclick="addOptionToRule(${idx})" class="text-purple-600 hover:text-purple-700 font-semibold text-[10.5px] cursor-pointer">
                                + Tambah Tombol
                            </button>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            ${ruleOptions.map((opt, optIdx) => `
                                <div class="flex items-center gap-2">
                                    <input type="text" value="${escapeHtml(opt.label || '')}" oninput="updateRuleOption(${idx}, ${optIdx}, 'label', this.value)" placeholder="Label Tombol (cth: 📦 Pilihan A)" class="text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md flex-1">
                                    <input type="text" value="${escapeHtml(opt.value || '')}" oninput="updateRuleOption(${idx}, ${optIdx}, 'value', this.value)" placeholder="Nilai / Kata Kunci (cth: A)" class="text-[11px] px-2.5 py-1 bg-white border border-apple-border rounded-md w-36">
                                    <button type="button" onclick="removeOptionFromRule(${idx}, ${optIdx})" class="text-neutral-400 hover:text-red-600 p-1 text-[12px] cursor-pointer" title="Hapus Tombol">✕</button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                optionsSectionHtml = `
                    <div class="flex items-center justify-end">
                        <button type="button" onclick="addOptionToRule(${idx})" class="text-[10.5px] font-semibold text-purple-600 hover:text-purple-700 hover:underline flex items-center gap-1 cursor-pointer">
                            <span>+ Pasang Tombol Pilihan Interaktif untuk Jawaban Ini</span>
                        </button>
                    </div>
                `;
            }

            row.innerHTML = `
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-[11px] font-bold flex items-center justify-center shrink-0">
                        ${idx + 1}
                    </div>
                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-2.5">
                        <div class="md:col-span-5">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Kata Kunci Pemicu (Koma terpisah)</label>
                            <input type="text" value="${escapeHtml(rule.keywords || '')}" oninput="updateRuleField(${idx}, 'keywords', this.value)" placeholder="Contoh: ongkir, tarif, kurir, jne" class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-[10.5px] font-semibold text-apple-textSecondary mb-1">Template Jawaban Bot</label>
                            <textarea oninput="updateRuleField(${idx}, 'response', this.value)" rows="2" placeholder="Tulis jawaban otomatis lengkap untuk customer..." class="w-full text-[12px] px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 resize-none leading-relaxed">${escapeHtml(rule.response || '')}</textarea>
                        </div>
                    </div>
                    <button type="button" onclick="removeFaqRuleRow(${idx})" class="text-neutral-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0 cursor-pointer self-end md:self-center" title="Hapus Aturan FAQ Ini">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
                ${optionsSectionHtml}
            `;
            container.appendChild(row);
        });
    }

    const count = Array.isArray(currentFaqRules) ? currentFaqRules.length : 0;
    if (labelCount) labelCount.innerText = count + ' Aturan';
    if (badgeTabCount) badgeTabCount.innerText = count;
    serializeBotRules();
}

function updateRuleField(index, field, value) {
    if (currentFaqRules[index]) {
        currentFaqRules[index][field] = value;
        serializeBotRules();
    }
}

function addOptionToRule(ruleIdx) {
    if (!currentFaqRules[ruleIdx].options || !Array.isArray(currentFaqRules[ruleIdx].options)) {
        currentFaqRules[ruleIdx].options = [];
    }
    currentFaqRules[ruleIdx].options.push({ label: '', value: '' });
    renderFaqRulesList();
}

function updateRuleOption(ruleIdx, optIdx, field, value) {
    if (currentFaqRules[ruleIdx] && currentFaqRules[ruleIdx].options && currentFaqRules[ruleIdx].options[optIdx]) {
        currentFaqRules[ruleIdx].options[optIdx][field] = value;
        serializeBotRules();
    }
}

function removeOptionFromRule(ruleIdx, optIdx) {
    if (currentFaqRules[ruleIdx] && currentFaqRules[ruleIdx].options && currentFaqRules[ruleIdx].options[optIdx]) {
        currentFaqRules[ruleIdx].options.splice(optIdx, 1);
        if (currentFaqRules[ruleIdx].options.length === 0) {
            delete currentFaqRules[ruleIdx].options;
        }
        renderFaqRulesList();
    }
}

function addNewFaqRuleRow() {
    if (!Array.isArray(currentFaqRules)) {
        currentFaqRules = [];
    }
    currentFaqRules.push({
        keywords: '',
        response: ''
    });
    renderFaqRulesList();

    setTimeout(() => {
        const inputs = document.querySelectorAll('#faqRulesListContainer input');
        if (inputs.length > 0) {
            inputs[inputs.length - 1].focus();
        }
    }, 50);
}

function removeFaqRuleRow(index) {
    if (Array.isArray(currentFaqRules) && currentFaqRules[index]) {
        currentFaqRules.splice(index, 1);
        renderFaqRulesList();
    }
}

function serializeBotRules() {
    const hidden = document.getElementById('hiddenBotRules');
    if (hidden) {
        hidden.value = JSON.stringify(currentFaqRules);
    }
}

