/* integrations — dipindah dari view Blade agar bisa di-cache. */
function copySnippet(elementId, btn) {
    const text = document.getElementById(elementId).innerText;
    copyToClipboard(text, btn);
    if (typeof showToast === 'function') {
        showToast('Copied!', 'Widget embed code copied to clipboard.');
    }
}

function toggleBotSection(projectId, isChecked) {
    const el = document.getElementById('botFields_' + projectId);
    if (el) {
        el.classList.toggle('hidden', !isChecked);
    }
}

function addBotRule(projectId) {
    const container = document.getElementById('rulesContainer_' + projectId);
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'p-2 bg-white rounded-lg border border-indigo-100 flex flex-col gap-1.5 rule-item shadow-2xs relative';
    div.innerHTML = `
        <button type="button" onclick="this.closest('.rule-item').remove(); syncBotRules('${projectId}');" class="absolute top-1.5 right-1.5 text-gray-400 hover:text-red-500 text-[11px] font-bold cursor-pointer">✕</button>
        <input type="text" placeholder="Kata kunci (pisah koma: ongkir, tarif, kirim)" class="w-[90%] px-2 py-1 text-[11px] border border-apple-border rounded font-mono rule-keywords" oninput="syncBotRules('${projectId}')">
        <textarea rows="1" placeholder="Jawaban otomatis bot..." class="w-full px-2 py-1 text-[11px] border border-apple-border rounded resize-none rule-response" oninput="syncBotRules('${projectId}')"></textarea>
    `;
    container.appendChild(div);
    div.querySelector('input').focus();
}

function syncBotRules(projectId) {
    const container = document.getElementById('rulesContainer_' + projectId);
    const hiddenInput = document.getElementById('botRulesJson_' + projectId);
    if (!container || !hiddenInput) return;

    const rules = [];
    container.querySelectorAll('.rule-item').forEach(item => {
        const kwInput = item.querySelector('.rule-keywords');
        const respInput = item.querySelector('.rule-response');
        const kwVal = kwInput ? kwInput.value.trim() : '';
        const respVal = respInput ? respInput.value.trim() : '';

        if (kwVal && respVal) {
            const kwArray = kwVal.split(',').map(s => s.trim()).filter(Boolean);
            rules.push({
                keywords: kwArray,
                response: respVal
            });
        }
    });

    hiddenInput.value = JSON.stringify(rules);
}
