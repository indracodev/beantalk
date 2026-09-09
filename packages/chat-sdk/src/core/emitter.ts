type Handler = (data?: any) => void;

export class EventEmitter {
  private events: Record<string, Handler[]> = {};

  on(event: string, handler: Handler): this {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(handler);
    return this;
  }

  off(event: string, handler: Handler): this {
    if (!this.events[event]) return this;
    this.events[event] = this.events[event].filter((h) => h !== handler);
    return this;
  }

  emit(event: string, data?: any): void {
    if (!this.events[event]) return;
    this.events[event].forEach((handler) => {
      try {
        handler(data);
      } catch (err) {
        console.error(`[BeanTalk] Error in event handler for "${event}":`, err);
      }
    });
  }
}
