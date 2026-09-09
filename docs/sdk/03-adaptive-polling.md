# SDK: Adaptive Polling & State Throttling

Salah satu fitur kunci yang membuat platform ini sangat ramah shared hosting adalah **Smart Adaptive Polling**. SDK tidak pernah membombardir server dengan request konstan ketika pengguna tidak aktif.

---

## 1. State Matrix & Dynamic Interval

Polling interval dihitung secara dinamis berdasarkan 3 faktor:
1. **Window / Tab Visibility** (`document.visibilityState`)
2. **Widget Window State** (Open vs Closed)
3. **Koneksi Jaringan** (`navigator.onLine`)

```text
┌──────────────────────────────┬──────────────────┬─────────────────────────────────┐
│ Kondisi Pengguna             │ Interval Polling │ Perilaku                        │
├──────────────────────────────┼──────────────────┼─────────────────────────────────┤
│ Tab Aktif + Widget Buka      │ 2.000 ms (2 dtk) │ Ambil pesan chat baru           │
│ Tab Aktif + Widget Tutup     │ 15.000 ms (15 dt)│ Cek unread message badge        │
│ Tab Hidden / Background Tab  │ 30.000 ms (30 dt)│ Throttled (hemat baterai & CPU) │
│ Tab Inactive > 10 menit      │ Pause            │ Resume saat tab kembali fokus   │
│ Jaringan Offline             │ Stop             │ Berhenti seketika               │
│ Kembali Online               │ Immediate Poll   │ Langsung query dan restart loop │
└──────────────────────────────┴──────────────────┴─────────────────────────────────┘
```

---

## 2. Core Polling Implementation

```typescript
export class AdaptivePoller {
  private timer: number | null = null;
  private activeIntervalMs = 2000;
  private idleIntervalMs = 15000;
  private backgroundIntervalMs = 30000;
  private isWidgetOpen = false;
  private lastMessageId = 0;
  private isPolling = false;

  constructor(private fetchCallback: (afterId: number) => Promise<number>) {
    this.bindEvents();
  }

  private bindEvents(): void {
    // 1. Deteksi saat tab diminimize atau ganti tab
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "visible") {
        this.resetTimer(this.getCurrentInterval());
        this.poll(); // Langsung poll saat kembali ke tab
      } else {
        this.resetTimer(this.backgroundIntervalMs);
      }
    });

    // 2. Deteksi status koneksi internet browser
    window.addEventListener("online", () => {
      this.poll();
      this.resetTimer(this.getCurrentInterval());
    });

    window.addEventListener("offline", () => {
      this.stop();
    });
  }

  public setWidgetState(isOpen: boolean): void {
    this.isWidgetOpen = isOpen;
    this.resetTimer(this.getCurrentInterval());
    if (isOpen) {
      this.poll();
    }
  }

  private getCurrentInterval(): number {
    if (document.visibilityState === "hidden") {
      return this.backgroundIntervalMs;
    }
    return this.isWidgetOpen ? this.activeIntervalMs : this.idleIntervalMs;
  }

  public async poll(): Promise<void> {
    if (this.isPolling || !navigator.onLine) return;
    this.isPolling = true;

    try {
      const newLatestId = await this.fetchCallback(this.lastMessageId);
      if (newLatestId > this.lastMessageId) {
        this.lastMessageId = newLatestId;
      }
    } catch (err) {
      console.warn("[Chat SDK] Polling error, backing off...", err);
    } finally {
      this.isPolling = false;
    }
  }

  public resetTimer(intervalMs: number): void {
    this.stop();
    this.timer = window.setInterval(() => this.poll(), intervalMs);
  }

  public stop(): void {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
    }
  }
}
```

---

## 3. Server-Side Polling Throttling Protection

Jika client browser mengalami error loop JavaScript dan mengirim request polling terlalu cepat:
1. Laravel Middleware `throttle:chat-poll` membatasi request maksimal 60 per menit per visitor.
2. Jika terlampaui, server mengembalikan status HTTP `429 Too Many Requests` dengan header `Retry-After: 30`.
3. SDK mendeteksi status 429 dan otomatis mem-pause interval selama nilai `Retry-After` tanpa menimbulkan error ke UI pengguna.
