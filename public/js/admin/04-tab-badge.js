// ======================================================================
// 7. BROWSER TAB NOTIFICATION & DYNAMIC FAVICON BADGE ENGINE
// Renders dynamic high-contrast badge (red dot/counter) directly onto
// the Chrome / browser tab favicon using HTML5 Canvas & updates document.title
// ======================================================================
window.BeanTalkTabBadge = (function() {
    // Warna aksen dari CSS (--apple-blue) agar favicon selalu sama dengan tema.
    const accentColor = () => getComputedStyle(document.documentElement).getPropertyValue('--apple-blue').trim() || '#7A5A44';

    let unreadCount = 0;
    let canvas = null;
    let ctx = null;
    let faviconEl = null;
    let animTimer = null;
    let animFrame = 0; // 0 = normal, 1 = expanded pulse

    function getFavicon() {
        if (!faviconEl) {
            faviconEl = document.getElementById('beantalkFavicon') ||
                        document.querySelector('link[rel="icon"]') ||
                        document.querySelector('link[rel="shortcut icon"]');
            if (!faviconEl) {
                faviconEl = document.createElement('link');
                faviconEl.rel = 'icon';
                faviconEl.type = 'image/png';
                document.head.appendChild(faviconEl);
            }
        }
        return faviconEl;
    }

    function init() {
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.width = 64;
            canvas.height = 64;
            ctx = canvas.getContext('2d');
        }
    }

    function drawBaseIcon() {
        init();
        ctx.clearRect(0, 0, 64, 64);

        // Apple Blue rounded squircle
        ctx.fillStyle = accentColor();
        if (typeof ctx.roundRect === 'function') {
            ctx.beginPath();
            ctx.roundRect(4, 4, 56, 56, 16);
            ctx.fill();
        } else {
            ctx.beginPath();
            ctx.arc(32, 32, 28, 0, Math.PI * 2);
            ctx.fill();
        }

        // White speech bubble
        ctx.fillStyle = '#FFFFFF';
        if (typeof ctx.roundRect === 'function') {
            ctx.beginPath();
            ctx.roundRect(14, 15, 36, 26, 7);
            ctx.fill();
        } else {
            ctx.fillRect(14, 15, 36, 26);
        }

        // Tail
        ctx.beginPath();
        ctx.moveTo(20, 41);
        ctx.lineTo(16, 49);
        ctx.lineTo(28, 41);
        ctx.closePath();
        ctx.fill();

        // 3 chat dots inside bubble
        ctx.fillStyle = accentColor();
        ctx.beginPath();
        ctx.arc(24, 28, 2.5, 0, Math.PI * 2);
        ctx.arc(32, 28, 2.5, 0, Math.PI * 2);
        ctx.arc(40, 28, 2.5, 0, Math.PI * 2);
        ctx.fill();
    }

    function renderFrame() {
        drawBaseIcon();

        if (unreadCount > 0) {
            const isPulse = animFrame === 1;
            const badgeText = unreadCount > 9 ? '9+' : String(unreadCount);
            const isPill = unreadCount > 9;

            if (!isPill) {
                // Large circular badge with breathing pulse (radius 20px -> 24px)
                const badgeRadius = isPulse ? 24 : 20;
                const badgeX = 43;
                const badgeY = 21;

                // High-contrast white halo
                ctx.fillStyle = '#FFFFFF';
                ctx.beginPath();
                ctx.arc(badgeX, badgeY, badgeRadius + 4, 0, Math.PI * 2);
                ctx.fill();

                // Glowing crimson / vibrant red badge
                ctx.fillStyle = isPulse ? '#FF1744' : '#FF3B30';
                ctx.beginPath();
                ctx.arc(badgeX, badgeY, badgeRadius, 0, Math.PI * 2);
                ctx.fill();

                // White bold counter text
                ctx.fillStyle = '#FFFFFF';
                ctx.font = `bold ${isPulse ? 24 : 21}px system-ui, -apple-system, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(badgeText, badgeX, badgeY + 1);
            } else {
                // Wide horizontal stadium pill for multi-digit (e.g. 9+)
                const pillX = isPulse ? 14 : 18;
                const pillY = isPulse ? 2 : 4;
                const pillW = isPulse ? 48 : 44;
                const pillH = isPulse ? 34 : 30;
                const pillR = isPulse ? 17 : 15;

                // White halo outline
                ctx.fillStyle = '#FFFFFF';
                if (typeof ctx.roundRect === 'function') {
                    ctx.beginPath();
                    ctx.roundRect(pillX - 3.5, pillY - 3.5, pillW + 7, pillH + 7, pillR + 3.5);
                    ctx.fill();
                }

                // Vibrant red pill
                ctx.fillStyle = isPulse ? '#FF1744' : '#FF3B30';
                if (typeof ctx.roundRect === 'function') {
                    ctx.beginPath();
                    ctx.roundRect(pillX, pillY, pillW, pillH, pillR);
                    ctx.fill();
                } else {
                    ctx.fillRect(pillX, pillY, pillW, pillH);
                }

                // Text
                ctx.fillStyle = '#FFFFFF';
                ctx.font = `bold ${isPulse ? 22 : 19}px system-ui, -apple-system, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(badgeText, pillX + (pillW / 2), pillY + (pillH / 2) + 1);
            }
        }

        try {
            const link = getFavicon();
            link.type = 'image/png';
            link.href = canvas.toDataURL('image/png');
        } catch (e) {}

        // Alternate animated title if title is not actively taken over by incoming message blinker
        if (!globalTitleBlinkTimer) {
            refreshTitle();
        }
    }

    function startAnimation() {
        if (animTimer) return;
        animFrame = 0;
        renderFrame();
        const interval = document.hidden ? 1200 : 650;
        animTimer = setInterval(() => {
            animFrame = animFrame === 0 ? 1 : 0;
            renderFrame();
        }, interval);
    }

    function stopAnimation() {
        if (animTimer) {
            clearInterval(animTimer);
            animTimer = null;
        }
        animFrame = 0;
        renderFrame();
    }

    function refreshTitle() {
        const cleanTitle = (typeof globalOriginalDocTitle !== 'undefined' ? globalOriginalDocTitle : document.title).replace(/^(\([^\)]+\)|[🔴💬🔔\s])+\s*/, '');
        if (unreadCount > 0) {
            const countStr = unreadCount > 99 ? '99+' : unreadCount;
            const prefix = animFrame === 1 ? `🔴 (${countStr})` : `💬 (${countStr})`;
            document.title = `${prefix} ${cleanTitle}`;
        } else {
            document.title = cleanTitle;
        }
    }

    function setCount(count) {
        unreadCount = Math.max(0, parseInt(count, 10) || 0);

        if (unreadCount > 0) {
            startAnimation();
        } else {
            stopAnimation();
        }

        // Native PWA / Desktop App Badging API support
        if ('setAppBadge' in navigator) {
            if (unreadCount > 0) {
                navigator.setAppBadge(unreadCount).catch(() => {});
            } else {
                navigator.clearAppBadge().catch(() => {});
            }
        }
    }

    // Adaptive animation speed when switching browser tabs
    document.addEventListener('visibilitychange', () => {
        if (unreadCount > 0 && animTimer) {
            clearInterval(animTimer);
            animTimer = null;
            const interval = document.hidden ? 1200 : 650;
            animTimer = setInterval(() => {
                animFrame = animFrame === 0 ? 1 : 0;
                renderFrame();
            }, interval);
        }
    });

    return {
        init: init,
        setCount: setCount,
        refreshTitle: refreshTitle
    };
})();

// Console helper for quick testing
window.testTabBadge = function(num) {
    if (window.BeanTalkTabBadge) {
        window.BeanTalkTabBadge.setCount(num !== undefined ? num : 3);
        console.log(`[BeanTalkTabBadge] Testing badge count: ${num !== undefined ? num : 3}`);
    }
};
