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

    // 1. Lampu Merah Penyeberangan (Tot Tot Tot: Pelican Crossing Sound)
    function playPedestrianBeep(ctx) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1046.5, now); // C6 clear acoustic beep
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.35, now + 0.012);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.075);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.08);
        } catch (e) {}
    }

    // 2. Ambulans (Ninu Ninu: Two-Tone Siren)
    function playAmbulanceTone(ctx, isHigh) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle'; // Rich resonant tone
            const freq = isHigh ? 960 : 770;
            osc.frequency.setValueAtTime(freq, now);
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.28, now + 0.03);
            gain.gain.setValueAtTime(0.28, now + 0.38);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.44);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.45);
            currentOscillators.push(osc);
            setTimeout(() => {
                const idx = currentOscillators.indexOf(osc);
                if (idx !== -1) currentOscillators.splice(idx, 1);
            }, 460);
        } catch (e) {}
    }

    // 3. Mobil Dinas / Patwal (Wut Wut / Yelp Siren)
    function playPoliceWut(ctx) {
        try {
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';

            const filter = ctx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(2200, now);

            osc.frequency.setValueAtTime(620, now);
            osc.frequency.exponentialRampToValueAtTime(1480, now + 0.12);
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.38, now + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.16);
        } catch (e) {}
    }

    // 4. Apple Harmonic Chime (Single or Repeat)
    function playChime(ctx) {
        try {
            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            osc1.frequency.exponentialRampToValueAtTime(880, now + 0.08);
            gain1.gain.setValueAtTime(0, now);
            gain1.gain.linearRampToValueAtTime(0.32, now + 0.02);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1108.73, now + 0.09);
            gain2.gain.setValueAtTime(0, now + 0.09);
            gain2.gain.linearRampToValueAtTime(0.25, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.09);
            osc2.stop(now + 0.55);
        } catch (e) {}
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

        if (soundType === 'pedestrian') {
            // Rapid acoustic pedestrian crossing beep (every 135ms: tot... tot... tot...)
            playPedestrianBeep(ctx);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                playPedestrianBeep(ctx);
            }, 135);
        } else if (soundType === 'ambulance') {
            // Two-tone European / Indonesian siren (460ms cycle: nii... nuu... nii... nuu...)
            let isHigh = true;
            playAmbulanceTone(ctx, isHigh);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                isHigh = !isHigh;
                playAmbulanceTone(ctx, isHigh);
            }, 460);
        } else if (soundType === 'police') {
            // VIP police patrol yelp chirp (rapid wut-wut bursts every 650ms)
            const burst = () => {
                playPoliceWut(ctx);
                setTimeout(() => { if (isPlaying) playPoliceWut(ctx); }, 200);
            };
            burst();
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                burst();
            }, 680);
        } else {
            // Harmonic chime repeats every 2.2s
            playChime(ctx);
            alarmTimer = setInterval(() => {
                if (Date.now() >= endTime || !isPlaying) {
                    stop();
                    if (typeof onComplete === 'function') onComplete();
                    return;
                }
                playChime(ctx);
            }, 2200);
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

        if (soundType === 'pop') {
            // Crisp bubble burst (400Hz -> 880Hz sweep, 0.08s)
            try {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(420, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.06);
                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.35, now + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.08);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.085);
            } catch (e) {}
        } else if (soundType === 'ding') {
            // Crystal desk reception bell (1318Hz E6 + 2637Hz overtone)
            try {
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1318.5, now);
                gain1.gain.setValueAtTime(0.28, now);
                gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.55);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(2637, now);
                gain2.gain.setValueAtTime(0.12, now);
                gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now);
                osc2.stop(now + 0.35);
            } catch (e) {}
        } else if (soundType === 'marimba') {
            // Cheerful ascending 3-note marimba chord (C6, E6, G6)
            const notes = [1046.5, 1318.5, 1567.98];
            notes.forEach((freq, idx) => {
                try {
                    const noteTime = now + (idx * 0.08);
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, noteTime);
                    gain.gain.setValueAtTime(0.001, noteTime);
                    gain.gain.linearRampToValueAtTime(0.25, noteTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + 0.22);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(noteTime);
                    osc.stop(noteTime + 0.25);
                } catch (e) {}
            });
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
