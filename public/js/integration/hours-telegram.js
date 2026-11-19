/* Integrasi detail — hours-telegram. Data server: window.BT (lihat integration-detail.blade.php). */
// 1.1 Business Hours Toggle & Day Row Controls
function toggleHoursSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('hoursToggleLabel');
    const badgeTab = document.getElementById('badgeTabHoursStatus');
    const badgeHeader = document.getElementById('badgeHeaderHoursStatus');

    if (label) {
        label.innerText = isChecked ? 'Jam Kerja Aktif (ON)' : 'Jam Kerja Nonaktif (24/7 Selalu Buka)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-amber-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        badgeTab.innerText = isChecked ? 'Aktif' : '24/7';
        badgeTab.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-600');
    }
    if (badgeHeader) {
        badgeHeader.innerText = isChecked ? '⏰ Jam Kerja: Aktif' : '⏰ Jam Kerja: 24/7';
        badgeHeader.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold ' + (isChecked ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-neutral-100 text-neutral-600 border border-neutral-200');
    }
}

function toggleDayRow(dayKey, isChecked) {
    const badge = document.getElementById('badge-day-' + dayKey);
    const times = document.getElementById('times-day-' + dayKey);
    const row = document.getElementById('row-day-' + dayKey);

    if (badge) {
        badge.innerText = isChecked ? 'Buka Operasional' : 'Libur / Tutup';
        badge.className = 'block text-[10.5px] font-semibold ' + (isChecked ? 'text-emerald-700' : 'text-neutral-400');
    }
    if (times) {
        if (isChecked) {
            times.classList.remove('opacity-40', 'pointer-events-none');
        } else {
            times.classList.add('opacity-40', 'pointer-events-none');
        }
    }
    if (row) {
        if (isChecked) {
            row.className = 'p-3 rounded-xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border-apple-border';
        } else {
            row.className = 'p-3 rounded-xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-neutral-50/70 border-neutral-200';
        }
    }
}

function applyHoursPreset(preset) {
    const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    days.forEach(d => {
        const checkbox = document.querySelector(`.day-checkbox[data-day="${d}"]`);
        const startInput = document.querySelector(`.day-start-input[data-day="${d}"]`);
        const endInput = document.querySelector(`.day-end-input[data-day="${d}"]`);

        if (!checkbox || !startInput || !endInput) return;

        if (preset === 'office') {
            const isWorkday = (d !== 'sat' && d !== 'sun');
            checkbox.checked = isWorkday;
            startInput.value = '08:00';
            endInput.value = '17:00';
            toggleDayRow(d, isWorkday);
        } else if (preset === 'retail') {
            const isWorkday = (d !== 'sun');
            checkbox.checked = isWorkday;
            startInput.value = '09:00';
            endInput.value = '21:00';
            toggleDayRow(d, isWorkday);
        } else if (preset === 'always') {
            checkbox.checked = true;
            startInput.value = '00:00';
            endInput.value = '23:59';
            toggleDayRow(d, true);
        }
    });

    const masterCheckbox = document.getElementById('checkboxHoursEnabled');
    if (masterCheckbox && !masterCheckbox.checked) {
        masterCheckbox.checked = true;
        toggleHoursSwitch(masterCheckbox);
    }
}

// Telegram Test Connection Helper
function testTelegramConnection() {
    const btn = document.getElementById('btnTestTelegram');
    const resultBox = document.getElementById('telegramTestResult');
    const tokenInput = document.getElementById('inputTelegramBotToken');
    const chatInput = document.getElementById('inputTelegramChatId');

    const token = tokenInput ? tokenInput.value.trim() : '';
    const chatId = chatInput ? chatInput.value.trim() : '';

    if (!token || !chatId) {
        if (resultBox) {
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-amber-50 border border-amber-200 text-amber-800';
            resultBox.innerHTML = '⚠️ Mohon isi <strong>Telegram Bot Token</strong> dan <strong>Chat/Group ID</strong> terlebih dahulu.';
            resultBox.classList.remove('hidden');
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⏳</span> Menguji Koneksi...';
    }

    fetch(BT.routes.testTelegram, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': BT.csrf,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            telegram_bot_token: token,
            telegram_chat_id: chatId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.66-2.89 8.01-3.46 3.82-1.6 4.61-1.88 5.14-1.89.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.19-.04.35z"/></svg> <span>⚡ Test Kirim Pesan ke Telegram</span>';
        }

        if (resultBox) {
            resultBox.classList.remove('hidden');
            if (data.success) {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerHTML = '✅ <strong>' + data.message + '</strong>';
            } else {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
                resultBox.innerHTML = '❌ <strong>Gagal:</strong> ' + data.message;
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Test Kirim Pesan ke Telegram</span>';
        }
        if (resultBox) {
            resultBox.classList.remove('hidden');
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
            resultBox.innerHTML = '❌ Gagal mengirim request pengujian: ' + err.message;
        }
    });
}

// 1.2 Direct One-Click Telegram Webhook Registration
function setTelegramWebhookDirectly() {
    const token = document.getElementById('inputTelegramBotToken')?.value.trim();
    const btn = document.getElementById('btnSetWebhook');
    const resultBox = document.getElementById('telegramWebhookResult');

    if (!token) {
        if (resultBox) {
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-amber-50 border border-amber-200 text-amber-800';
            resultBox.innerHTML = '⚠️ Mohon isi <strong>Telegram Bot Token</strong> terlebih dahulu.';
            resultBox.classList.remove('hidden');
        }
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⏳</span> Mendaftarkan Webhook...';
    }

    fetch(BT.routes.setWebhook, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': BT.csrf,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            telegram_bot_token: token,
            webhook_url: BT.webhookUrl
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Daftarkan Webhook Otomatis</span>';
        }

        if (resultBox) {
            resultBox.classList.remove('hidden');
            if (data.success) {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-emerald-50 border border-emerald-200 text-emerald-800';
                resultBox.innerHTML = '✅ <strong>' + data.message + '</strong>';
            } else {
                resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
                resultBox.innerHTML = '❌ <strong>Gagal:</strong> ' + data.message;
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>⚡ Daftarkan Webhook Otomatis</span>';
        }
        if (resultBox) {
            resultBox.classList.remove('hidden');
            resultBox.className = 'p-3 rounded-xl text-[12px] bg-red-50 border border-red-200 text-red-800';
            resultBox.innerHTML = '❌ Gagal mendaftarkan webhook: ' + err.message;
        }
    });
}


// Real-time Telegram toggle badge updater
function updateTelegramToggleBadges() {
    const notifChecked = document.getElementById('toggleTelegramNotif')?.checked;
    const topicChecked = document.getElementById('toggleTelegramTopic')?.checked;

    const notifBadge = document.getElementById('badgeNotifToggle');
    const topicBadge = document.getElementById('badgeTopicToggle');
    const cardStatus = document.getElementById('cardTelegramStatus');

    if (notifBadge) {
        notifBadge.className = notifChecked 
            ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800'
            : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600';
        notifBadge.textContent = notifChecked ? 'ON (Aktif)' : 'OFF';
    }

    if (topicBadge) {
        topicBadge.className = topicChecked 
            ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800'
            : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600';
        topicBadge.textContent = topicChecked ? 'ON (Aktif)' : 'OFF';
    }

    if (cardStatus) {
        cardStatus.className = topicChecked 
            ? 'text-[13px] font-bold text-sky-600 mt-0.5 flex items-center gap-1.5'
            : 'text-[13px] font-bold text-neutral-500 mt-0.5 flex items-center gap-1.5';
        cardStatus.innerHTML = '<span class="w-2 h-2 rounded-full ' + (topicChecked ? 'bg-sky-500' : 'bg-neutral-400') + '"></span>' + (topicChecked ? 'Mode Topik ON' : 'Mode Topik OFF');
    }
}

