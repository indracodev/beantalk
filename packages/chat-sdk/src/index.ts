import { WidgetInitOptions, SessionInitData, Message } from './types';
import { EventEmitter } from './core/emitter';
import { ApiClient } from './core/api';
import { getOrCreateVisitorUuid, setLastConversationId, generateClientMessageId, getStoredCustomerName } from './core/storage';
import { PollingTransport } from './transport/polling-transport';
import { ChatWidgetUi } from './ui/widget';

function resolveScriptOrigin(): string {
  if (typeof document === 'undefined') return '';
  const current = document.currentScript as HTMLScriptElement;
  if (current && current.src) {
    try { return new URL(current.src, window.location.href).origin; } catch (e) {}
  }
  const scriptWithKey = document.querySelector('script[data-project-key]') as HTMLScriptElement;
  if (scriptWithKey && scriptWithKey.src) {
    try { return new URL(scriptWithKey.src, window.location.href).origin; } catch (e) {}
  }
  const scriptWithWidget = document.querySelector('script[src*="chat-widget.js"], script[src*="widget.js"]') as HTMLScriptElement;
  if (scriptWithWidget && scriptWithWidget.src) {
    try { return new URL(scriptWithWidget.src, window.location.href).origin; } catch (e) {}
  }
  return '';
}

export class BeanTalk {
  private options: WidgetInitOptions;
  private emitter: EventEmitter;
  private api: ApiClient;
  private transport: PollingTransport;
  private ui: ChatWidgetUi;
  private sessionData: SessionInitData | null = null;
  private initialized: boolean = false;

  isReady(): boolean {
    return this.initialized;
  }

  constructor(options: WidgetInitOptions) {
    this.options = options;
    this.emitter = new EventEmitter();

    // 1. Initialize API Client with auto-detected server origin
    const detectedOrigin = resolveScriptOrigin();
    const apiUrl = options.apiUrl || detectedOrigin || (typeof window !== 'undefined' ? window.location.origin : '');
    this.api = new ApiClient(options.projectKey, apiUrl);

    // 2. Initialize UI (Shadow DOM)
    this.ui = new ChatWidgetUi(options, this.emitter);

    // 3. Initialize Transport
    this.transport = new PollingTransport(this.api, this.emitter);

    // 4. Bind Internal Events
    this.bindEvents();

    // 5. Perform Handshake
    this.bootstrap();
  }

  // Serial message queue to prevent race conditions during rapid typing
  private sendQueue: Message[] = [];
  private isSending: boolean = false;

  private async processSendQueue(): Promise<void> {
    if (this.isSending || this.sendQueue.length === 0) return;
    this.isSending = true;

    while (this.sendQueue.length > 0) {
      const msg = this.sendQueue.shift()!;
      const convId = this.sessionData?.conversation?.id || 0;
      const visitorUuid = this.options.visitorUuid || getOrCreateVisitorUuid();
      const storedName = getStoredCustomerName();

      try {
        const res = await this.api.sendMessage(convId, {
          visitor_uuid: visitorUuid,
          client_message_id: msg.client_message_id || generateClientMessageId(),
          message: msg.content || msg.message || '',
          sender_name: msg.sender_name || storedName || 'Tamu',
          page_url: window.location.href,
          page_title: document.title,
        });

        if (res.success && res.data) {
          const resConvId = (res.data as any).conversation_id;
          if (resConvId && (!this.sessionData?.conversation?.id || this.sessionData.conversation.id !== resConvId)) {
            if (!this.sessionData) {
              this.sessionData = {} as any;
            }
            if (!this.sessionData.conversation) {
              this.sessionData.conversation = { id: resConvId, status: 'open' } as any;
            } else {
              this.sessionData.conversation.id = resConvId;
            }
            setLastConversationId(resConvId);
            this.transport.start(resConvId, res.data.id || 0);
          }
          this.emitter.emit('message:sent', res.data);
        }
      } catch (err) {
        console.error('[BeanTalk] Gagal mengirim pesan:', err);
      }
    }

    this.isSending = false;
    // Poll once after entire queue is drained
    this.transport.pollNow();
  }

  private bindEvents(): void {
    // UI Outgoing Message -> Enqueue for serial processing
    this.emitter.on('ui:send', (msg: Message) => {
      this.sendQueue.push(msg);
      this.processSendQueue();
    });

    // Customer Name Update -> Sync with backend
    this.emitter.on('customer:rename', async (name: string) => {
      const visitorUuid = this.options.visitorUuid || getOrCreateVisitorUuid();
      try {
        await this.api.updateProfile(visitorUuid, name);
      } catch (e) {
        console.warn('[BeanTalk] Gagal update nama profil pengunjung:', e);
      }
    });

    // Transport Incoming Message -> Append to UI
    this.emitter.on('message:received', (msg: Message) => {
      this.ui.appendMessage(msg);
      this.emitter.emit('message', msg);
    });

    // Widget Open/Close state notification to polling
    this.emitter.on('widget:opened', () => {
      this.transport.setWidgetOpen(true);
      this.transport.pollNow();
    });

    this.emitter.on('widget:closed', () => {
      this.transport.setWidgetOpen(false);
    });
  }

  private async bootstrap(): Promise<void> {
    const visitorUuid = this.options.visitorUuid || getOrCreateVisitorUuid();
    const storedName = getStoredCustomerName();

    try {
      const response = await this.api.initSession(visitorUuid, undefined, storedName || undefined);

      if (response.success && response.data) {
        this.sessionData = response.data;
        this.ui.setSessionData(response.data);

        const conv = response.data.conversation;
        if (conv && conv.id) {
          setLastConversationId(conv.id);

          // Find last message ID
          let lastId = 0;
          if (conv.messages && conv.messages.length > 0) {
            lastId = Math.max(...conv.messages.map((m) => m.id));
          }

          // Start adaptive polling
          this.transport.start(conv.id, lastId);
        }

        this.initialized = true;
        this.emitter.emit('ready', response.data);
      } else {
        console.warn('[BeanTalk] Init session warning:', response.error?.message);
      }
    } catch (err) {
      console.error('[BeanTalk] Failed to initialize chat session:', err);
    }
  }

  // Public SDK Methods
  open(): void {
    this.ui.open();
  }

  close(): void {
    this.ui.close();
  }

  toggle(): void {
    this.ui.toggle();
  }

  on(event: string, handler: (data?: any) => void): this {
    this.emitter.on(event, handler);
    return this;
  }

  sendMessage(text: string): void {
    if (!text.trim()) return;
    const msg: Message = {
      id: Date.now(),
      conversation_id: this.sessionData?.conversation?.id || 0,
      client_message_id: generateClientMessageId(),
      sender_type: 'visitor',
      sender_name: 'Anda',
      content: text.trim(),
      message: text.trim(),
      created_at: new Date().toISOString(),
    };
    this.ui.appendMessage(msg);
    this.emitter.emit('ui:send', msg);
  }
}

// Backward Compatibility Alias
export const UniversalChatMe = BeanTalk;

// Global Export
let instance: BeanTalk | null = null;

export const ChatWidget = {
  init(options: WidgetInitOptions): BeanTalk {
    if (!instance) {
      instance = new BeanTalk(options);
    }
    return instance;
  },
  open(): void { instance?.open(); },
  close(): void { instance?.close(); },
  toggle(): void { instance?.toggle(); },
  on(event: string, handler: (data?: any) => void): void { instance?.on(event, handler); },
  sendMessage(text: string): void { instance?.sendMessage(text); },
  getInstance(): BeanTalk | null { return instance; },
};

// Expose to window
if (typeof window !== 'undefined') {
  (window as any).BeanTalk = ChatWidget;
  (window as any).ChatWidget = ChatWidget;
  (window as any).UniversalChatMe = BeanTalk;

  // Auto-boot if <script data-project-key="..."> is present
  function autoBootWidget() {
    const scripts = document.querySelectorAll('script[data-project-key]');
    if (scripts.length > 0) {
      const script = scripts[0] as HTMLScriptElement;
      const projectKey = script.getAttribute('data-project-key');
      let scriptOrigin = '';
      if (script.src) {
        try { scriptOrigin = new URL(script.src, window.location.href).origin; } catch (e) {}
      }
      const apiUrl = script.getAttribute('data-api-url') || scriptOrigin || undefined;
      const color = script.getAttribute('data-color') || undefined;
      const brandName = script.getAttribute('data-brand-name') || script.getAttribute('data-store-name') || undefined;
      const supportTitle = script.getAttribute('data-support-title') || undefined;

      if (projectKey && !instance) {
        ChatWidget.init({
          projectKey,
          apiUrl,
          accentColor: color,
          brandName,
          storeName: brandName,
          supportTitle,
        });
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoBootWidget);
  } else {
    autoBootWidget();
  }
}

export default ChatWidget;
