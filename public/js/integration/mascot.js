/* Integrasi detail — mascot. Data server: window.BT (lihat integration-detail.blade.php). */
// 8. Launcher & Mascot Controls
let currentAdminMascotId = BT.mascot.id;
let adminMascotSize = BT.mascot.size;
let adminMascotTrackingEnabled = BT.mascot.tracking;
let adminMascotBlinkTimer = null;
let adminMascotPokeTimeout = null;

function toggleLauncherType(type) {
    const section = document.getElementById('sectionMascotOptions');
    if (!section) return;
    if (type === 'mascot') {
        section.classList.remove('hidden');
        initAdminMascotSandbox();
    } else {
        section.classList.add('hidden');
    }
}

let currentMascotCategory = 'all';
let currentMascotSearch = '';

function filterMascotsCategory(cat, btn) {
    currentMascotCategory = cat;
    const buttons = document.querySelectorAll('.mascot-cat-btn');
    buttons.forEach(b => {
        b.className = 'mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0';
    });
    if (btn) {
        btn.className = 'mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-apple-blue text-white shadow-2xs transition shrink-0';
    }
    applyMascotFilters();
}

function searchMascots(query) {
    currentMascotSearch = query.trim().toLowerCase();
    applyMascotFilters();
}

function applyMascotFilters() {
    const cards = document.querySelectorAll('#mascotCardsContainer .mascot-card');
    cards.forEach(card => {
        const cat = card.getAttribute('data-cat');
        const name = card.getAttribute('data-name') || '';
        const matchesCat = (currentMascotCategory === 'all' || cat === currentMascotCategory);
        const matchesSearch = (!currentMascotSearch || name.includes(currentMascotSearch));

        if (matchesCat && matchesSearch) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

function selectMascot(mascotId) {
    currentAdminMascotId = mascotId;
    
    // 1. Sync hidden form input
    const hiddenInput = document.getElementById('inputSelectedMascotId');
    if (hiddenInput) {
        hiddenInput.value = mascotId;
    }

    // 2. Clear any active timers
    if (adminMascotBlinkTimer) {
        clearTimeout(adminMascotBlinkTimer);
        adminMascotBlinkTimer = null;
    }
    if (adminMascotPokeTimeout) {
        clearTimeout(adminMascotPokeTimeout);
        adminMascotPokeTimeout = null;
    }

    // 3. Highlight selected card & reset others
    const cards = document.querySelectorAll('#mascotCardsContainer .mascot-card');
    cards.forEach(c => {
        const isSelected = (c.getAttribute('data-mascot') === mascotId);
        if (isSelected) {
            c.classList.add('border-apple-blue', 'bg-blue-50/50', 'ring-1', 'ring-apple-blue');
            c.classList.remove('border-apple-border', 'bg-white');
        } else {
            c.classList.remove('border-apple-blue', 'bg-blue-50/50', 'ring-1', 'ring-apple-blue');
            c.classList.add('border-apple-border', 'bg-white');
        }
    });

    // 4. Update preview sprite smoothly without scroll jump
    const sprite = document.getElementById('adminMascotPreviewSprite');
    const statusText = document.getElementById('adminMascotStatusText');
    if (sprite) {
        sprite.style.transform = '';
        sprite.style.backgroundImage = `url('/mascots/${mascotId}-directions.webp')`;
        sprite.style.backgroundPosition = '50% 50%';
    }
    if (statusText) {
        statusText.innerText = 'Arahkan mouse di sekitar kotak ini';
    }

    scheduleAdminBlink();
}

function updateMascotSize(val) {
    adminMascotSize = parseInt(val, 10);
    const label = document.getElementById('labelMascotSize');
    if (label) label.innerText = `${adminMascotSize}px`;

    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (sprite) {
        sprite.style.width = `${adminMascotSize}px`;
        sprite.style.height = `${adminMascotSize}px`;
    }
}

function toggleMascotTracking(enabled) {
    adminMascotTrackingEnabled = enabled;
    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (!enabled && sprite) {
        sprite.style.backgroundPosition = '50% 50%';
    }
}

function pokeAdminMascot() {
    const sprite = document.getElementById('adminMascotPreviewSprite');
    const statusText = document.getElementById('adminMascotStatusText');
    if (!sprite) return;

    if (adminMascotPokeTimeout) clearTimeout(adminMascotPokeTimeout);

    // Switch to reactions sheet frame (col 2, row 2 = 100% 100% or 50% 50%)
    sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-reactions.webp')`;
    sprite.style.backgroundPosition = '50% 50%';
    sprite.style.transform = 'scale(1.15) rotate(5deg)';
    if (statusText) statusText.innerText = '✨ Yaaay! (Gembira)';

    adminMascotPokeTimeout = setTimeout(() => {
        sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-directions.webp')`;
        sprite.style.backgroundPosition = '50% 50%';
        sprite.style.transform = '';
        if (statusText) statusText.innerText = 'Arahkan mouse di sekitar kotak ini';
        adminMascotPokeTimeout = null;
    }, 1200);
}

function scheduleAdminBlink() {
    if (adminMascotBlinkTimer) clearTimeout(adminMascotBlinkTimer);
    const delay = Math.random() * 3000 + 2500;
    adminMascotBlinkTimer = setTimeout(() => {
        const sprite = document.getElementById('adminMascotPreviewSprite');
        if (!sprite || adminMascotPokeTimeout) {
            scheduleAdminBlink();
            return;
        }
        
        const directionsBg = `url('/mascots/${currentAdminMascotId}-directions.webp')`;
        const currentPos = sprite.style.backgroundPosition || '50% 50%';
        sprite.style.backgroundImage = `url('/mascots/${currentAdminMascotId}-reactions.webp')`;
        sprite.style.backgroundPosition = '0% 0%'; // Closed eyes
        
        setTimeout(() => {
            if (!adminMascotPokeTimeout && sprite) {
                sprite.style.backgroundImage = directionsBg;
                sprite.style.backgroundPosition = currentPos;
            }
        }, 140);
        scheduleAdminBlink();
    }, delay);
}

function initAdminMascotSandbox() {
    const sandbox = document.getElementById('adminMascotSandbox');
    const sprite = document.getElementById('adminMascotPreviewSprite');
    if (!sandbox || !sprite || sandbox.dataset.initialized) return;
    sandbox.dataset.initialized = 'true';

    const directionGrid = [
        [0, 0],   // 0: Top-Left
        [50, 0],  // 1: Top
        [100, 0], // 2: Top-Right
        [0, 50],  // 3: Center-Left
        [50, 50], // 4: Center
        [100, 50],// 5: Center-Right
        [0, 100], // 6: Bottom-Left
        [50, 100],// 7: Bottom
        [100, 100]// 8: Bottom-Right
    ];

    sandbox.addEventListener('mousemove', (e) => {
        if (!adminMascotTrackingEnabled || adminMascotPokeTimeout) return;
        const rect = sprite.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const dx = e.clientX - centerX;
        const dy = e.clientY - centerY;
        const distance = Math.hypot(dx, dy);

        // Center deadzone
        if (distance < 20) {
            sprite.style.backgroundPosition = '50% 50%';
            return;
        }

        const angle = Math.atan2(dy, dx) * (180 / Math.PI);
        let dirIndex = 4;

        if (angle >= -157.5 && angle < -112.5) dirIndex = 0;       // Up-Left
        else if (angle >= -112.5 && angle < -67.5) dirIndex = 1;   // Up
        else if (angle >= -67.5 && angle < -22.5) dirIndex = 2;    // Up-Right
        else if (angle >= -22.5 && angle < 22.5) dirIndex = 5;     // Right
        else if (angle >= 22.5 && angle < 67.5) dirIndex = 8;      // Down-Right
        else if (angle >= 67.5 && angle < 112.5) dirIndex = 7;     // Down
        else if (angle >= 112.5 && angle < 157.5) dirIndex = 6;    // Down-Left
        else dirIndex = 3;                                         // Left

        const [x, y] = directionGrid[dirIndex];
        sprite.style.backgroundPosition = `${x}% ${y}%`;
    });

    sandbox.addEventListener('mouseleave', () => {
        if (adminMascotPokeTimeout) return;
        sprite.style.backgroundPosition = '50% 50%';
    });

    scheduleAdminBlink();
}

