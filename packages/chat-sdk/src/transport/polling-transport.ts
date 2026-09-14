import { ApiClient } from '../core/api';
import { EventEmitter } from '../core/emitter';

export interface PollingOptions {
  activeIntervalMs?: number;
  idleIntervalMs?: number;
  immediateOnVisible?: boolean;
}

export class PollingTransport {
  private api: ApiClient;
  private emitter: EventEmitter;
  private conversationId: number | null = null;
  private lastMessageId: number = 0;
  private isRunning: boolean = false;
  private isPolling: boolean = false;
  private pollQueued: boolean = false;
  private timer: any = null;
  private activeIntervalMs: number;
  private idleIntervalMs: number;
  private isWindowVisible: boolean = true;
  private isOnline: boolean = true;
  private isWidgetOpen: boolean = false;

  // Burst mode: rapid polls right after sending messages
  private burstRemaining: number = 0;
  private burstIntervalMs: number = 1200;

  constructor(api: ApiClient, emitter: EventEmitter, options: PollingOptions = {}) {
    this.api = api;
    this.emitter = emitter;
    this.activeIntervalMs = options.activeIntervalMs || 2500;
    this.idleIntervalMs = options.idleIntervalMs || 15000;

    this.setupListeners();
  }

  private setupListeners(): void {
    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', () => {
        const visible = document.visibilityState === 'visible';
        this.isWindowVisible = visible;
        if (visible && this.isRunning) {
          // Immediately poll when user returns to tab
          this.pollNow();
        }
      });
    }

    if (typeof window !== 'undefined') {
      window.addEventListener('online', () => {
        this.isOnline = true;
        if (this.isRunning) this.pollNow();
      });
      window.addEventListener('offline', () => {
        this.isOnline = false;
        this.clearTimer();
      });
    }
  }

  setConversation(conversationId: number, lastMessageId: number = 0): void {
    this.conversationId = conversationId;
    if (lastMessageId > this.lastMessageId) {
      this.lastMessageId = lastMessageId;
    }
  }

  setWidgetOpen(isOpen: boolean): void {
    this.isWidgetOpen = isOpen;
    // Reschedule timer with new interval
    if (this.isRunning) {
      this.reschedule();
    }
  }

  start(conversationId: number, initialLastId: number = 0): void {
    this.conversationId = conversationId;
    this.lastMessageId = Math.max(this.lastMessageId, initialLastId);
    this.isRunning = true;
    this.reschedule();
  }

  stop(): void {
    this.isRunning = false;
    this.clearTimer();
  }

  private clearTimer(): void {
    if (this.timer) {
      clearTimeout(this.timer);
      this.timer = null;
    }
  }

  private getInterval(): number {
    // Burst mode: rapid follow-up polls after a send
    if (this.burstRemaining > 0) {
      return this.burstIntervalMs;
    }
    // Adaptive interval:
    // Window visible AND widget open -> fast (1.5s)
    // Otherwise -> idle (15s)
    return (this.isWindowVisible && this.isWidgetOpen)
      ? this.activeIntervalMs
      : this.idleIntervalMs;
  }

  private reschedule(): void {
    this.clearTimer();
    if (!this.isRunning || !this.isOnline) return;

    this.timer = setTimeout(() => {
      this.executePoll();
    }, this.getInterval());
  }

  async pollNow(): Promise<void> {
    // If already polling, queue a follow-up instead of dropping
    if (this.isPolling) {
      this.pollQueued = true;
      return;
    }
    this.clearTimer();
    // Activate burst mode: 3 rapid polls to catch server-side echo fast
    this.burstRemaining = 3;
    await this.executePoll();
  }

  private async executePoll(): Promise<void> {
    if (!this.isRunning || !this.conversationId || !this.isOnline || this.isPolling) {
      this.reschedule();
      return;
    }

    this.isPolling = true;

    try {
      const response = await this.api.pollMessages(this.conversationId, this.lastMessageId);

      if (response.success && response.data) {
        const { messages, last_id } = response.data;

        if (Array.isArray(messages) && messages.length > 0) {
          // Filter only truly new messages
          const newMessages = messages.filter((m) => m.id > this.lastMessageId);

          if (newMessages.length > 0) {
            newMessages.forEach((msg) => {
              this.emitter.emit('message:received', msg);
            });
          }

          if (last_id && last_id > this.lastMessageId) {
            this.lastMessageId = last_id;
          }
        }
      }
    } catch (err) {
      // Non-blocking: will retry next cycle
      this.emitter.emit('poll:error', err);
    } finally {
      this.isPolling = false;

      // Decrement burst counter
      if (this.burstRemaining > 0) {
        this.burstRemaining--;
      }

      // Process queued poll if one was requested during this cycle
      if (this.pollQueued) {
        this.pollQueued = false;
        this.burstRemaining = 2;
        // Immediate re-poll with tiny delay to let event loop breathe
        this.clearTimer();
        this.timer = setTimeout(() => this.executePoll(), 100);
      } else {
        this.reschedule();
      }
    }
  }
}
