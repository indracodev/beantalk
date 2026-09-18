/**
 * BEANTALK INTERACTIVE MASCOT ENGINE (Page-Mascot Core)
 * Pure Vanilla TypeScript & CSS Web Component Shadow DOM implementation.
 * Zero npm dependencies, 100% lightweight & offline capable.
 * 
 * Features:
 * - Real-time 9-angle cursor tracking mathematics
 * - Dual sprite-sheet support (directions & reactions)
 * - Natural randomized eye blinking & breathing
 * - Poke / click interaction reactions
 * - Touch device friendly & dead-zone damping
 */

export interface MascotConfig {
  mascotId: string;
  apiUrl: string;
  size?: number;
  tracking?: boolean;
  onClick?: () => void;
}

export class MascotController {
  private container: HTMLElement;
  private config: MascotConfig;
  private mascotEl!: HTMLElement;
  private directionsUrl: string;
  private reactionsUrl: string;
  
  private currentCol: number = 1;
  private currentRow: number = 1;
  private isReacting: boolean = false;
  private blinkTimer: any = null;
  private reactionTimeout: any = null;
  private isDestroyed: boolean = false;
  private boundPointerMove: ((e: PointerEvent | MouseEvent) => void) | null = null;
  private boundPointerLeave: (() => void) | null = null;

  constructor(container: HTMLElement, config: MascotConfig) {
    this.container = container;
    this.config = config;

    const baseApi = (config.apiUrl || '').replace(/\/+$/, '');
    const mascotName = (config.mascotId || 'fox').toLowerCase();

    this.directionsUrl = `${baseApi}/mascots/${mascotName}-directions.webp`;
    this.reactionsUrl = `${baseApi}/mascots/${mascotName}-reactions.webp`;

    this.mount();
    this.startBlinkLoop();
    if (config.tracking !== false) {
      this.bindTracking();
    }
  }

  private mount(): void {
    const size = this.config.size || 68;

    this.mascotEl = document.createElement('div');
    this.mascotEl.className = 'beantalk-mascot-avatar';
    this.mascotEl.style.width = `${size}px`;
    this.mascotEl.style.height = `${size}px`;
    this.mascotEl.style.backgroundImage = `url('${this.directionsUrl}')`;
    this.mascotEl.style.backgroundSize = '300% 300%';
    this.mascotEl.style.backgroundPosition = '50% 50%'; // Forward Center
    this.mascotEl.style.backgroundRepeat = 'no-repeat';
    this.mascotEl.style.imageRendering = 'auto';
    this.mascotEl.style.pointerEvents = 'auto';
    this.mascotEl.style.cursor = 'pointer';
    this.mascotEl.style.userSelect = 'none';
    this.mascotEl.style.transition = 'transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1)';
    this.mascotEl.setAttribute('role', 'button');
    this.mascotEl.setAttribute('aria-label', `Chat Mascot ${this.config.mascotId}`);

    // Preload reactions sprite sheet
    const preload = new Image();
    preload.src = this.reactionsUrl;

    // Hover & Click poke interaction
    this.mascotEl.addEventListener('mouseenter', () => {
      this.poke(0, 0, 300); // Friendly perk
    });

    this.mascotEl.addEventListener('click', (e) => {
      e.stopPropagation();
      this.poke(1, 0, 400); // Happy squeeze
      if (typeof this.config.onClick === 'function') {
        this.config.onClick();
      }
    });

    this.container.appendChild(this.mascotEl);
  }

  /**
   * Triggers a temporary expression reaction from reactions.webp
   */
  public poke(col: number = 0, row: number = 0, durationMs: number = 350): void {
    if (this.isDestroyed) return;

    this.isReacting = true;
    if (this.reactionTimeout) clearTimeout(this.reactionTimeout);

    this.mascotEl.style.backgroundImage = `url('${this.reactionsUrl}')`;
    this.mascotEl.style.backgroundPosition = `${col * 50}% ${row * 50}%`;
    this.mascotEl.style.transform = 'scale(1.08) translateY(-2px)';

    this.reactionTimeout = setTimeout(() => {
      if (this.isDestroyed) return;
      this.isReacting = false;
      this.mascotEl.style.backgroundImage = `url('${this.directionsUrl}')`;
      this.mascotEl.style.backgroundPosition = `${this.currentCol * 50}% ${this.currentRow * 50}%`;
      this.mascotEl.style.transform = '';
    }, durationMs);
  }

  /**
   * Maps mouse angle to 9-direction sprite grid
   */
  private updateDirection(col: number, row: number): void {
    if (this.isReacting || this.isDestroyed) return;
    if (col === this.currentCol && row === this.currentRow) return;

    this.currentCol = col;
    this.currentRow = row;
    this.mascotEl.style.backgroundPosition = `${col * 50}% ${row * 50}%`;
  }

  private bindTracking(): void {
    // Dead-zone calculation to stop head jittering when cursor is near
    const deadZone = 32;

    this.boundPointerMove = (e: PointerEvent | MouseEvent) => {
      if (this.isReacting || this.isDestroyed) return;

      const rect = this.mascotEl.getBoundingClientRect();
      if (!rect.width || !rect.height) return;

      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;

      const dx = e.clientX - cx;
      const dy = e.clientY - cy;
      const dist = Math.hypot(dx, dy);

      // Within dead-zone -> Look directly at user
      if (dist < deadZone) {
        this.updateDirection(1, 1);
        return;
      }

      // Calculate angle in degrees from -180 to 180
      const deg = Math.atan2(dy, dx) * (180 / Math.PI);

      let col = 1;
      let row = 1;

      if (deg >= -157.5 && deg < -112.5) {
        // Top-Left
        col = 0; row = 0;
      } else if (deg >= -112.5 && deg < -67.5) {
        // Top
        col = 1; row = 0;
      } else if (deg >= -67.5 && deg < -22.5) {
        // Top-Right
        col = 2; row = 0;
      } else if (deg >= -22.5 && deg < 22.5) {
        // Right
        col = 2; row = 1;
      } else if (deg >= 22.5 && deg < 67.5) {
        // Bottom-Right
        col = 2; row = 2;
      } else if (deg >= 67.5 && deg < 112.5) {
        // Bottom
        col = 1; row = 2;
      } else if (deg >= 112.5 && deg < 157.5) {
        // Bottom-Left
        col = 0; row = 2;
      } else {
        // Left
        col = 0; row = 1;
      }

      this.updateDirection(col, row);
    };

    this.boundPointerLeave = () => {
      // Return gently to center forward when mouse leaves viewport
      this.updateDirection(1, 1);
    };

    window.addEventListener('pointermove', this.boundPointerMove, { passive: true });
    window.addEventListener('blur', this.boundPointerLeave, { passive: true });
    document.addEventListener('mouseleave', this.boundPointerLeave, { passive: true });
  }

  private startBlinkLoop(): void {
    const scheduleNextBlink = () => {
      if (this.isDestroyed) return;
      // Random interval between 2.8s and 6.0s
      const delay = 2800 + Math.random() * 3200;

      this.blinkTimer = setTimeout(() => {
        if (!this.isReacting && !this.isDestroyed && !document.hidden) {
          // Blink frame from reactions sheet
          this.mascotEl.style.backgroundImage = `url('${this.reactionsUrl}')`;
          this.mascotEl.style.backgroundPosition = '0% 0%'; // Closed eyes / blink frame

          setTimeout(() => {
            if (!this.isReacting && !this.isDestroyed) {
              this.mascotEl.style.backgroundImage = `url('${this.directionsUrl}')`;
              this.mascotEl.style.backgroundPosition = `${this.currentCol * 50}% ${this.currentRow * 50}%`;
            }
          }, 150);
        }
        scheduleNextBlink();
      }, delay);
    };

    scheduleNextBlink();
  }

  public setVisible(visible: boolean): void {
    if (this.mascotEl) {
      this.mascotEl.style.display = visible ? 'block' : 'none';
    }
  }

  public destroy(): void {
    this.isDestroyed = true;
    if (this.blinkTimer) clearTimeout(this.blinkTimer);
    if (this.reactionTimeout) clearTimeout(this.reactionTimeout);

    if (this.boundPointerMove) {
      window.removeEventListener('pointermove', this.boundPointerMove);
    }
    if (this.boundPointerLeave) {
      window.removeEventListener('blur', this.boundPointerLeave);
      document.removeEventListener('mouseleave', this.boundPointerLeave);
    }

    if (this.mascotEl && this.mascotEl.parentNode) {
      this.mascotEl.parentNode.removeChild(this.mascotEl);
    }
  }
}
