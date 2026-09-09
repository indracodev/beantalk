import { WidgetInitOptions, SessionInitData, Message } from './types';
import { EventEmitter } from './core/emitter';
import { ApiClient } from './core/api';
import { getOrCreateVisitorUuid, setLastConversationId, generateClientMessageId } from './core/storage';
import { PollingTransport } from './transport/polling-transport';
import { ChatWidgetUi } from './ui/widget';

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

    // 1. Initialize API Client
    const apiUrl = options.apiUrl || window.location.origin;
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

  private bindEvents(): void {
    // UI Outgoing Message -> Send via API
    this.emitter.on('ui:send', async (msg: Message) => {
      if (!this.sessionData?.conversation?.id) {
        console.error('[BeanTalk] Percakapan belum diinisialisasi.');
        return;
      }

      try {
        const res = await this.api.sendMessage(this.sessionData.conversation.id, {
          client_message_id: msg.client_message_id || generateClientMessageId(),
          message: msg.content || msg.message || '',
          sender_name: 'Visitor',
        });

        if (res.success && res.data) {
          this.emitter.emit('message:sent', res.data);
          // Poll immediately after sending message
          this.transport.pollNow();
        }
      } catch (err) {
        console.error('[BeanTalk] Gagal mengirim pesan:', err);
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

    try {
      const response = await this.api.initSession(visitorUuid);

      if (response.success && response.data) {
        this.sessionData = response.data;
        const conv = response.data.conversation;

        if (conv) {
          setLastConversationId(conv.id);
          this.ui.setSessionData(response.data);

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
  document.addEventListener('DOMContentLoaded', () => {
    const scripts = document.querySelectorAll('script[data-project-key]');
    if (scripts.length > 0) {
      const script = scripts[0] as HTMLScriptElement;
      const projectKey = script.getAttribute('data-project-key');
      const apiUrl = script.getAttribute('data-api-url') || undefined;
      const color = script.getAttribute('data-color') || undefined;
      const storeName = script.getAttribute('data-store-name') || undefined;

      if (projectKey && !instance) {
        ChatWidget.init({
          projectKey,
          apiUrl,
          accentColor: color,
          storeName,
        });
      }
    }
  });
}

export default ChatWidget;
