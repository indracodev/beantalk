// ======================================================================
// SYNTHESIZER NADA (Web Audio API) — fungsi murni, tanpa state.
// Dipakai engine alarm admin (03-audio-engine.js) & suara widget pengunjung.
// ======================================================================
window.BeanTalkTones = (function() {
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
            return osc; // engine melacaknya agar bisa dihentikan
        } catch (e) { return null; }
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

    // Pop: gelembung singkat (420Hz -> 880Hz)
    function playPop(ctx) {
        const now = ctx.currentTime;
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
    }

    // Ding: bel resepsionis (E6 + overtone)
    function playDing(ctx) {
        const now = ctx.currentTime;
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
    }

    // Marimba: akor naik C6-E6-G6
    function playMarimba(ctx) {
        const now = ctx.currentTime;
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
    }

    return {
        pedestrian: playPedestrianBeep,
        ambulance: playAmbulanceTone,
        police: playPoliceWut,
        chime: playChime,
        pop: playPop,
        ding: playDing,
        marimba: playMarimba,
    };
})();
