// ======================================================================
window.BeanTalkAudio = (function() {
    let audioCtx = null;
    let alarmTimer = null;
    let alarmCountdownTimer = null;
    let currentOscillators = [];
    let currentHtmlAudio = null;
    let isPlaying = false;
    let activeFloatingBanner = null;

    function getContext() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;
            if (!audioCtx) {
                audioCtx = new AudioContextClass();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        } catch (e) {
            return null;
        }
    }

    function removeFloatingBanner() {
        if (activeFloatingBanner) {
            try { activeFloatingBanner.remove(); } catch (e) {}
            activeFloatingBanner = null;
        }
        const existing = document.getElementById('beantalkAlarmActiveBanner');
        if (existing) existing.remove();
    }

    function stop() {
        isPlaying = false;
        if (alarmTimer) {
            clearInterval(alarmTimer);
            clearTimeout(alarmTimer);
            alarmTimer = null;
        }
        if (alarmCountdownTimer) {
            clearInterval(alarmCountdownTimer);
            alarmCountdownTimer = null;
        }
        if (currentHtmlAudio) {
            try {
                currentHtmlAudio.pause();
                currentHtmlAudio.currentTime = 0;
            } catch (e) {}
            currentHtmlAudio = null;
        }
        currentOscillators.forEach(osc => {
            try { osc.stop(); osc.disconnect(); } catch (e) {}
        });
        currentOscillators = [];
        removeFloatingBanner();
    }

    const Tones = window.BeanTalkTones;
    const playPedestrianBeep = Tones.pedestrian;
    const playPoliceWut = Tones.police;
    const playChime = Tones.chime;

    // Sirene ambulans dilacak agar stop() bisa memutusnya di tengah nada.
    function playAmbulanceTone(ctx, isHigh) {
        const osc = Tones.ambulance(ctx, isHigh);
        if (!osc) return;
        currentOscillators.push(osc);
        setTimeout(() => {
            const idx = currentOscillators.indexOf(osc);
            if (idx !== -1) currentOscillators.splice(idx, 1);
        }, 460);
    }


    function showAlarmBanner(durationSec, soundLabel) {
        removeFloatingBanner();
        const banner = document.createElement('div');
        banner.id = 'beantalkAlarmActiveBanner';
        banner.className = 'fixed top-4 right-4 z-[9999] bg-rose-600 text-white px-4 py-2.5 rounded-xl shadow-apple-popover flex items-center gap-3 border border-rose-400 animate-bounce cursor-pointer';
        banner.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
            <div class="text-[12px] font-medium leading-tight">
                <div class="font-bold flex items-center gap-1.5">
                    <span>🔔 Pesan Baru Masuk!</span>
                    <span class="text-[10px] bg-white/20 px-1.5 py-0.2 rounded">${escapeHtml(soundLabel || 'Alarm')}</span>
                </div>
                <div class="text-[11px] text-white/90">Berdering: <strong id="alarmCountdownText">${durationSec}s</strong> &bull; Klik untuk mematikan</div>
            </div>
            <button type="button" class="ml-2 bg-white/20 hover:bg-white/30 text-white rounded-lg px-2 py-1 text-[11px] font-bold transition">
                Stop ✕
            </button>
        `;
        banner.onclick = () => stop();
        document.body.appendChild(banner);
        activeFloatingBanner = banner;

        let remaining = durationSec;
        alarmCountdownTimer = setInterval(() => {
            remaining--;
            const countEl = document.getElementById('alarmCountdownText');
            if (countEl) countEl.innerText = `${remaining}s`;
            if (remaining <= 0) {
                clearInterval(alarmCountdownTimer);
            }
        }, 1000);
    }

    function playAlarm(soundType, durationSec, customUrl, showBanner = true, onComplete = null) {
        stop();
        isPlaying = true;
        const dur = Math.max(2, Math.min(180, parseInt(durationSec, 10) || 15));
        const endTime = Date.now() + (dur * 1000);

        let soundLabel = 'Tot-Tot';
        if (soundType === 'ambulance') soundLabel = 'Ninu-Ninu';
        else if (soundType === 'police') soundLabel = 'Wut-Wut';
        else if (soundType === 'chime') soundLabel = 'Chime';
        else if (soundType === 'custom') soundLabel = 'Custom';

        if (showBanner) {
            showAlarmBanner(dur, soundLabel);
        }

        // 1. Custom Audio File playback
        if (soundType === 'custom' && customUrl) {
            try {
                currentHtmlAudio = new Audio(customUrl);
                currentHtmlAudio.loop = true;
                currentHtmlAudio.play().catch(e => console.warn('[Custom Audio Play Warning]', e));
                alarmTimer = setTimeout(() => {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                }, dur * 1000);
                return;
            } catch (err) {
                console.warn('[Custom Sound Error, falling back to synthesizer]', err);
            }
        }

        // 2. Web Audio synthesizers
        const ctx = getContext();
        if (!ctx) return;

        // Ulangi nada tiap `ms` sampai durasi habis / dihentikan.
        const repeat = (play, ms) => {
            play();
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                play();
            }, ms);
        };

        if (soundType === 'pedestrian') {
            // Rapid acoustic pedestrian crossing beep (every 135ms: tot... tot... tot...)
            repeat(() => playPedestrianBeep(ctx), 135);
        } else if (soundType === 'ambulance') {
            // Two-tone European / Indonesian siren (460ms cycle: nii... nuu... nii... nuu...)
            let isHigh = false;
            repeat(() => playAmbulanceTone(ctx, (isHigh = !isHigh)), 460);
        } else if (soundType === 'police') {
            // VIP police patrol yelp chirp (rapid wut-wut bursts every 650ms)
            const burst = () => {
                playPoliceWut(ctx);
                setTimeout(() => { if (isPlaying) playPoliceWut(ctx); }, 200);
            };
            repeat(burst, 680);
        } else {
            // Harmonic chime repeats every 2.2s
            repeat(() => playChime(ctx), 2200);
        }
    }

    function playVisitorSound(soundType, customUrl) {
        if (soundType === 'custom' && customUrl) {
            try {
                const a = new Audio(customUrl);
                a.volume = 0.7;
                a.play().catch(e => console.warn(e));
                return;
            } catch (e) {}
        }

        const ctx = getContext();
        if (!ctx) return;
        const now = ctx.currentTime;

        if (soundType === 'pop' || soundType === 'ding' || soundType === 'marimba') {
            Tones[soundType](ctx);
        } else {
            // Default: Apple harmonic chime glide
            playChime(ctx);
        }
    }


    function preview(soundType, customUrl) {
        stop();
        if (soundType === 'pop' || soundType === 'ding' || soundType === 'marimba') {
            playVisitorSound(soundType, customUrl);
            return;
        }
        if (soundType === 'chime') {
            const ctx = getContext();
            if (ctx) playChime(ctx);
            return;
        }
        if (soundType === 'custom' && customUrl) {
            try {
                const a = new Audio(customUrl);
                a.volume = 0.8;
                a.play().catch(() => {});
                setTimeout(() => {
                    try { a.pause(); a.currentTime = 0; } catch (e) {}
                }, 3000);
                return;
            } catch (e) {}
        }
        // Play 2-second preview of pedestrian, ambulance, or police without floating banner
        playAlarm(soundType, 2, customUrl, false);
    }

    return {
        play: playAlarm,
        playSingleChime: function() {
            const ctx = getContext();
            if (ctx) playChime(ctx);
        },
        playVisitorSound: playVisitorSound,
        preview: preview,
        stop: stop,
        isPlaying: () => isPlaying,
        unlock: getContext
    };
})();

document.addEventListener('click', () => { window.BeanTalkAudio.unlock(); }, { passive: true });
document.addEventListener('keydown', () => { window.BeanTalkAudio.unlock(); }, { passive: true });
document.addEventListener('touchstart', () => { window.BeanTalkAudio.unlock(); }, { passive: true });
