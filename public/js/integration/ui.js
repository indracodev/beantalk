/* Integrasi detail — ui. Data server: window.BT (lihat integration-detail.blade.php). */
// 4. Bot Toggle Switch Visual Updates
function toggleBotSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('botToggleLabel');
    const badge = document.getElementById('badgeBotStatus');
    const cardText = document.getElementById('cardBotText');

    if (label) {
        label.innerText = isChecked ? 'Bot Aktif (ON)' : 'Bot Nonaktif (OFF)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-purple-700' : 'text-neutral-500');
    }

    if (badge) {
        badge.innerText = isChecked ? '🤖 Bot: Aktif' : '🤖 Bot: Nonaktif';
        badge.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold ' + (isChecked ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-neutral-100 text-neutral-600 border border-neutral-200');
    }

    if (cardText) {
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-purple-500' : 'bg-neutral-400'}"></span> ${isChecked ? 'Aktif' : 'Nonaktif'}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked ? 'text-purple-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 4.1 Telegram Master Toggle Switch Visual Updates
function toggleTelegramSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('telegramToggleLabel');
    const cardText = document.getElementById('cardTelegramStatus');

    if (label) {
        label.innerText = isChecked ? 'Telegram Aktif (ON)' : 'Telegram Nonaktif (OFF)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-sky-700' : 'text-neutral-500');
    }

    if (cardText) {
        const topicCheckbox = document.getElementById('toggleTelegramTopic');
        const isTopicOn = topicCheckbox ? topicCheckbox.checked : false;
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-sky-500' : 'bg-neutral-400'}"></span> ${isChecked ? (isTopicOn ? 'Mode Topik ON' : 'Notif ON') : 'Nonaktif'}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked ? 'text-sky-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 4.2 Telegram Topic Mode Toggle Visual Updates
function toggleTelegramTopicSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const badge = document.getElementById('badgeTopicToggle');
    const cardText = document.getElementById('cardTelegramStatus');

    if (badge) {
        badge.innerText = isChecked ? 'ON (Aktif)' : 'OFF';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-sky-100 text-sky-800' : 'bg-neutral-100 text-neutral-600');
    }

    if (cardText) {
        const masterCheckbox = document.getElementById('checkboxTelegramEnabled');
        const isMasterOn = masterCheckbox ? masterCheckbox.checked : false;
        cardText.innerHTML = `<span class="w-2 h-2 rounded-full ${isChecked ? 'bg-sky-500' : (isMasterOn ? 'bg-sky-500' : 'bg-neutral-400')}"></span> ${isChecked ? 'Mode Topik ON' : (isMasterOn ? 'Notif 1x ON' : 'Nonaktif')}`;
        cardText.className = 'text-[13px] font-bold ' + (isChecked || isMasterOn ? 'text-sky-600' : 'text-neutral-500') + ' mt-0.5 flex items-center gap-1.5';
    }
}

// 5. Brand Color Realtime Sync
function updateBrandColor(hex) {
    if (!hex) return;
    if (!hex.startsWith('#')) hex = '#' + hex;

    const inputColor = document.getElementById('inputColorPicker');
    const inputHex = document.getElementById('inputColorHex');
    const swatch = document.getElementById('previewHeaderSwatch');
    const hexText = document.getElementById('previewHeaderHex');

    if (inputColor) inputColor.value = hex;
    if (inputHex) inputHex.value = hex;
    if (swatch) swatch.style.backgroundColor = hex;
    if (hexText) hexText.innerText = hex;
}

// 6. Copy Code Snippet Helper
function copySnippetText(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text.trim()).then(() => {
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<span class="text-emerald-600 font-bold">✓ Tersalin!</span>';
        setTimeout(() => { btn.innerHTML = origHTML; }, 2000);
    });
}

// 7. Agent Scope & Multi-select Toggle
function toggleAgentScope(scope) {
    const container = document.getElementById('containerAgentChecklist');
    const labelAll = document.getElementById('labelAgentAll');
    const labelSelected = document.getElementById('labelAgentSelected');

    if (scope === 'all') {
        if (container) container.classList.add('hidden');
        if (labelAll) {
            labelAll.classList.add('border-apple-blue', 'bg-blue-50/30');
            labelAll.classList.remove('border-apple-border', 'bg-white');
        }
        if (labelSelected) {
            labelSelected.classList.remove('border-apple-blue', 'bg-blue-50/30');
            labelSelected.classList.add('border-apple-border', 'bg-white');
        }
    } else {
        if (container) container.classList.remove('hidden');
        if (labelSelected) {
            labelSelected.classList.add('border-apple-blue', 'bg-blue-50/30');
            labelSelected.classList.remove('border-apple-border', 'bg-white');
        }
        if (labelAll) {
            labelAll.classList.remove('border-apple-blue', 'bg-blue-50/30');
            labelAll.classList.add('border-apple-border', 'bg-white');
        }
    }
    updateAgentScopeBadge();
}

function updateAgentScopeBadge() {
    const scopeRadio = document.querySelector('input[name="agent_scope"]:checked');
    const badge = document.getElementById('badgeAgentScope');
    if (!badge || !scopeRadio) return;

    if (scopeRadio.value === 'all') {
        badge.innerText = '👥 Semua Agent (All CS)';
        badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto';
    } else {
        const checkedCount = document.querySelectorAll('.agent-checkbox:checked').length;
        badge.innerText = '👤 ' + checkedCount + ' Agent Terpilih';
        badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 self-start sm:self-auto';
    }
}

