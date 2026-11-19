/* Integrasi detail — sound. Data server: window.BT (lihat integration-detail.blade.php). */
// 1.05 Sound Alert Configuration Helpers
let testSoundTimer = null;
let testSoundCountdownInterval = null;

function toggleSoundSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('soundToggleLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const duration = document.getElementById('inputSoundDuration')?.value || 15;

    if (label) {
        label.innerText = isChecked ? 'Suara Aktif (ON)' : 'Suara Hening (MUTE)';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-rose-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        badgeTab.innerText = isChecked ? (duration + 's') : 'Mute';
        badgeTab.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold ' + (isChecked ? 'bg-rose-100 text-rose-800' : 'bg-neutral-100 text-neutral-600');
    }
}

function setSoundDuration(sec) {
    const input = document.getElementById('inputSoundDuration');
    const preview = document.getElementById('previewCurrentDurationLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const isMasterOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (input) input.value = sec;
    if (preview) preview.innerText = sec + ' Detik';
    if (badgeTab && isMasterOn) badgeTab.innerText = sec + 's';
}

document.getElementById('inputSoundDuration')?.addEventListener('input', function() {
    let val = parseInt(this.value, 10);
    if (isNaN(val) || val < 1) val = 1;
    const preview = document.getElementById('previewCurrentDurationLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const isMasterOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (preview) preview.innerText = val + ' Detik';
    if (badgeTab && isMasterOn) badgeTab.innerText = val + 's';
});

function previewAgentSound(type, e, isButton = false) {
    if (e && isButton) {
        e.stopPropagation();
        e.preventDefault();
    }
    const radio = document.querySelector(`input[name="sound_type"][value="${type}"]`);
    if (radio) {
        radio.checked = true;
        updateSoundOptionCardsHighlight(type);
    }
    const containerUpload = document.getElementById('containerCustomSoundUpload');
    if (containerUpload) {
        if (type === 'custom') {
            containerUpload.classList.remove('hidden');
        } else {
            containerUpload.classList.add('hidden');
        }
    }
    let customUrl = null;
    if (type === 'custom') {
        customUrl = window._pendingCustomSoundBlobUrl || BT.soundCustomUrl;
    }
    if (window.BeanTalkAudio && typeof window.BeanTalkAudio.preview === 'function') {
        window.BeanTalkAudio.preview(type, customUrl);
    }
}

function onSoundTypeChanged(selectedType) {
    previewAgentSound(selectedType, null, false);
}

function updateSoundOptionCardsHighlight(activeType) {
    document.querySelectorAll('.sound-option-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.value === activeType) {
            card.className = 'sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-rose-500 bg-rose-50/30 ring-2 ring-rose-500/20' + (activeType === 'custom' ? ' md:col-span-2' : '');
        } else {
            card.className = 'sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-apple-border hover:border-rose-300 bg-white' + (card.querySelector('input[value="custom"]') ? ' md:col-span-2' : '');
        }
    });
}

function onCustomFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const blobUrl = URL.createObjectURL(file);
        window._pendingCustomSoundBlobUrl = blobUrl;
    }
}

function testPlayCurrentSound() {
    testStopCurrentSound();

    const selectedRadio = document.querySelector('input[name="sound_type"]:checked');
    const soundType = selectedRadio ? selectedRadio.value : 'pedestrian';
    let duration = parseInt(document.getElementById('inputSoundDuration')?.value, 10);
    if (isNaN(duration) || duration < 1) duration = 15;

    let customUrl = null;
    if (soundType === 'custom') {
        customUrl = window._pendingCustomSoundBlobUrl || BT.soundCustomUrl;
    }

    if (!window.BeanTalkAudio) {
        alert('Modul audio BeanTalk belum dimuat. Silakan refresh halaman.');
        return;
    }

    const btnPlay = document.getElementById('btnTestPlaySound');
    const btnStop = document.getElementById('btnTestStopSound');
    const banner = document.getElementById('bannerSoundTestingStatus');
    const labelCountdown = document.getElementById('labelTestingCountdown');

    if (btnPlay) {
        btnPlay.disabled = true;
        btnPlay.classList.add('opacity-50', 'pointer-events-none');
    }
    if (btnStop) {
        btnStop.disabled = false;
        btnStop.className = 'inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-[12px] font-semibold transition cursor-pointer shadow-apple-sm';
    }
    if (banner) {
        banner.classList.remove('hidden');
    }

    let remainingSeconds = duration;
    if (labelCountdown) labelCountdown.innerText = remainingSeconds;

    testSoundCountdownInterval = setInterval(() => {
        remainingSeconds -= 1;
        if (labelCountdown) labelCountdown.innerText = remainingSeconds;
        if (remainingSeconds <= 0) {
            testStopCurrentSound();
        }
    }, 1000);

    window.BeanTalkAudio.play(soundType, duration, customUrl);

    testSoundTimer = setTimeout(() => {
        testStopCurrentSound();
    }, duration * 1000);
}

function testStopCurrentSound() {
    if (testSoundTimer) {
        clearTimeout(testSoundTimer);
        testSoundTimer = null;
    }
    if (testSoundCountdownInterval) {
        clearInterval(testSoundCountdownInterval);
        testSoundCountdownInterval = null;
    }

    if (window.BeanTalkAudio) {
        window.BeanTalkAudio.stop();
    }

    const btnPlay = document.getElementById('btnTestPlaySound');
    const btnStop = document.getElementById('btnTestStopSound');
    const banner = document.getElementById('bannerSoundTestingStatus');

    if (btnPlay) {
        btnPlay.disabled = false;
        btnPlay.classList.remove('opacity-50', 'pointer-events-none');
    }
    if (btnStop) {
        btnStop.disabled = true;
        btnStop.className = 'inline-flex items-center gap-2 bg-neutral-200 text-neutral-400 px-3.5 py-2 rounded-lg text-[12px] font-semibold transition cursor-not-allowed';
    }
    if (banner) {
        banner.classList.add('hidden');
    }
}

// 1.06 Customer Bubble Chat Sound Helpers
function toggleWidgetSoundSwitch(checkbox) {
    const isChecked = checkbox.checked;
    const label = document.getElementById('widgetSoundToggleLabel');
    const badgeTab = document.getElementById('badgeTabSoundStatus');
    const agentDuration = document.getElementById('inputSoundDuration')?.value || 15;
    const isAgentOn = document.getElementById('checkboxSoundEnabled')?.checked;

    if (label) {
        label.innerText = isChecked ? 'Suara Bubble ON' : 'Suara Bubble MUTE';
        label.className = 'text-[12px] font-bold ' + (isChecked ? 'text-purple-700' : 'text-neutral-500');
    }
    if (badgeTab) {
        const agentTxt = isAgentOn ? (agentDuration + 's') : 'Mute';
        const custTxt = isChecked ? 'ON' : 'OFF';
        badgeTab.innerText = `Agent: ${agentTxt} • Cust: ${custTxt}`;
    }
}

function previewWidgetSound(type, e, isButton = false) {
    if (e && isButton) {
        e.stopPropagation();
        e.preventDefault();
    }
    const radio = document.querySelector(`input[name="widget_sound_type"][value="${type}"]`);
    if (radio) {
        radio.checked = true;
        updateWidgetSoundOptionCardsHighlight(type);
    }
    const containerUpload = document.getElementById('containerWidgetCustomSoundUpload');
    if (containerUpload) {
        if (type === 'custom') {
            containerUpload.classList.remove('hidden');
        } else {
            containerUpload.classList.add('hidden');
        }
    }
    let customUrl = null;
    if (type === 'custom') {
        customUrl = window._pendingWidgetCustomSoundBlobUrl || BT.widgetSoundCustomUrl;
    }
    if (window.BeanTalkAudio && typeof window.BeanTalkAudio.preview === 'function') {
        window.BeanTalkAudio.preview(type, customUrl);
    }
}

function onWidgetSoundTypeChanged(selectedType) {
    previewWidgetSound(selectedType, null, false);
}

function updateWidgetSoundOptionCardsHighlight(activeType) {
    document.querySelectorAll('.widget-sound-option-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.value === activeType) {
            card.className = 'widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-purple-500 bg-purple-50/30 ring-2 ring-purple-500/20' + (activeType === 'custom' ? ' md:col-span-2' : '');
        } else {
            card.className = 'widget-sound-option-card relative p-3.5 rounded-xl border transition cursor-pointer flex items-start gap-3 border-apple-border hover:border-purple-300 bg-white' + (card.querySelector('input[value="custom"]') ? ' md:col-span-2' : '');
        }
    });
}

function onWidgetCustomFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const blobUrl = URL.createObjectURL(file);
        window._pendingWidgetCustomSoundBlobUrl = blobUrl;
    }
}

function testPlayCurrentWidgetSound() {
    const selectedRadio = document.querySelector('input[name="widget_sound_type"]:checked');
    const soundType = selectedRadio ? selectedRadio.value : 'chime';

    let customUrl = null;
    if (soundType === 'custom') {
        customUrl = window._pendingWidgetCustomSoundBlobUrl || BT.widgetSoundCustomUrl;
    }

    if (!window.BeanTalkAudio || typeof window.BeanTalkAudio.playVisitorSound !== 'function') {
        alert('Modul audio BeanTalk belum siap. Silakan refresh halaman.');
        return;
    }

    const btnPlay = document.getElementById('btnTestPlayWidgetSound');
    if (btnPlay) {
        const origHtml = btnPlay.innerHTML;
        btnPlay.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
            <span>Memutar Suara Customer...</span>
        `;
        btnPlay.classList.add('opacity-90');
        setTimeout(() => {
            btnPlay.innerHTML = origHtml;
            btnPlay.classList.remove('opacity-90');
        }, 800);
    }

    window.BeanTalkAudio.playVisitorSound(soundType, customUrl);
}

