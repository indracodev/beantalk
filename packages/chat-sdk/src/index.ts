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

// Initial startup banner
if (typeof window !== 'undefined') {
  try {
    console.log('%c[BeanTalk]%c Universal Chat Widget Started', 'background: #0071E3; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-weight: bold;', 'color: inherit; font-weight: 500;');
  } catch (e) {}
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

    console.log('[BeanTalk] Initializing widget instance for project:', options.projectKey);

    // 1. Initialize API Client with auto-detected server origin
    const detectedOrigin = resolveScriptOrigin();
    const apiUrl = options.apiUrl || detectedOrigin || (typeof window !== 'undefined' ? window.location.origin : '');
    options.apiUrl = apiUrl;
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
          sender_name: msg.sender_name || storedName || (this.options.language === 'en' ? 'Guest' : 'Tamu'),
          page_url: window.location.href,
          page_title: document.title,
        });

        if (res.success && res.data) {
          const resConvId = (res.data as any).conversation_id;
          if (resConvId && (!this.sessionData?.conversation?.id || this.sessionData.conversation.id !== resConvId)) {
            if (!this.sessionData) {
              this.sessionData = {} as any;
            }
            const sData = this.sessionData!;
            if (!sData.conversation) {
              sData.conversation = { id: resConvId, status: 'open' } as any;
            } else {
              sData.conversation.id = resConvId;
              sData.conversation.status = 'open';
            }
            this.ui.sessionData = sData;
            this.ui.updateResolvedUI(false);
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

    // Customer Resolves Conversation -> Call backend and update UI
    this.emitter.on('conversation:resolve', async (convId: number) => {
      const visitorUuid = this.options.visitorUuid || getOrCreateVisitorUuid();
      try {
        const res = await this.api.resolveConversation(convId, visitorUuid);
        if (res.success) {
          if (this.sessionData && this.sessionData.conversation) {
            this.sessionData.conversation.status = 'closed';
          }
          this.ui.updateResolvedUI(true);
          const isEn = this.options.language === 'en';
          this.ui.appendMessage({
            id: Date.now(),
            conversation_id: convId,
            sender_type: 'system',
            sender_name: 'System',
            content: isEn
              ? 'You have marked this conversation as resolved. Click "Start New Chat" to open a new ticket.'
              : 'Percakapan ini telah Anda tandai selesai. Klik "Mulai Chat Baru" untuk membuat tiket baru.',
            created_at: new Date().toISOString(),
          });
          // Refresh conversation list
          const listRes = await this.api.getConversations(visitorUuid);
          if (listRes.success && listRes.data?.conversations) {
            this.sessionData = {
              ...(this.sessionData as any),
              conversations: listRes.data.conversations,
            };
            this.ui.renderTicketsHistory(listRes.data.conversations);
          }
        }
      } catch (e) {
        console.error('[BeanTalk] Gagal menyelesaikan tiket:', e);
      }
    });

    // Start New Chat / Fresh Ticket Thread
    this.emitter.on('conversation:start-new', () => {
      if (this.sessionData) {
        this.sessionData.conversation = null;
      }
      setLastConversationId(0);
      this.transport.stop();
      this.ui.setMessages([]);
      this.ui.updateResolvedUI(false);

      const storedName = getStoredCustomerName();
      if (!storedName) {
        this.ui.goToStage('identity');
      } else {
        this.ui.goToStage('chat');
      }
    });

    // Switch to Past Conversation / Old Ticket
    this.emitter.on('conversation:switch', async (convId: number) => {
      try {
        const res = await this.api.pollMessages(convId, 0);
        if (res.success && res.data) {
          if (!this.sessionData) {
            this.sessionData = {} as any;
          }
          const sData = this.sessionData!;
          const ticket = (sData.conversations || []).find((c: any) => c.id === convId);
          const status = ticket?.status || 'open';
          sData.conversation = { id: convId, status } as any;
          setLastConversationId(convId);
          this.ui.setMessages(res.data.messages || []);
          this.ui.updateResolvedUI(status === 'closed');
          this.ui.goToStage('chat');

          if (status !== 'closed') {
            this.transport.start(convId, res.data.last_id || 0);
          } else {
            this.transport.stop();
          }
        }
      } catch (e) {
        console.error('[BeanTalk] Gagal memuat percakapan tiket:', e);
      }
    });
  }

  private async bootstrap(): Promise<void> {
    const visitorUuid = this.options.visitorUuid || getOrCreateVisitorUuid();
    const storedName = getStoredCustomerName();

    try {
      const response = await this.api.initSession(visitorUuid, undefined, storedName || undefined);

      if (response.success && response.data) {
        this.sessionData = response.data;
        const widgetSettings = (response.data as any).widget || response.data.widget_settings;
        if (widgetSettings && (widgetSettings.language === 'en' || widgetSettings.language === 'id')) {
          this.options.language = widgetSettings.language;
        }
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

          // Chat belum di-resolve -> langsung tampilkan layar chat
          if (conv.status !== 'closed') {
            this.ui.goToStage('chat');
            if (!conv.messages || conv.messages.length === 0) {
              this.transport.pollNow();
            }
          }
        }

        this.initialized = true;
        console.log('[BeanTalk] Session established successfully:', {
          brand: response.data.project?.name,
          customerCode: (response.data.visitor as any)?.customer_code,
          conversationId: response.data.conversation?.id || 'New Thread',
        });
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
    console.log('[BeanTalk] Opening chat widget window');
    this.ui.open();
  }

  close(): void {
    console.log('[BeanTalk] Closing chat widget window');
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

  setLanguage(lang: 'id' | 'en'): void {
    const validLang = lang === 'en' ? 'en' : 'id';
    this.options.language = validLang;
    this.ui.setLanguage(validLang);
  }
}

// Backward Compatibility Alias
export const UniversalChatMe = BeanTalk;

// Global Export
let instance: BeanTalk | null = null;

export const init = (options: WidgetInitOptions) => ChatWidget.init(options);
export const open = () => ChatWidget.open();
export const close = () => ChatWidget.close();
export const toggle = () => ChatWidget.toggle();
export const on = (event: string, handler: (data?: any) => void) => ChatWidget.on(event, handler);
export const sendMessage = (text: string) => ChatWidget.sendMessage(text);
export const setLanguage = (lang: 'id' | 'en') => ChatWidget.setLanguage(lang);
export const getInstance = () => ChatWidget.getInstance();

export const ChatWidget = {
  init(options: WidgetInitOptions): BeanTalk {
    if (!instance) {
      console.log('[BeanTalk] Creating singleton widget instance');
      instance = new BeanTalk(options);
    }
    return instance;
  },
  open(): void { 
    if (instance) {
      instance.open(); 
    } else {
      console.warn('[BeanTalk] Widget instance not yet initialized');
    }
  },
  close(): void { instance?.close(); },
  toggle(): void { instance?.toggle(); },
  on(event: string, handler: (data?: any) => void): void { instance?.on(event, handler); },
  sendMessage(text: string): void { instance?.sendMessage(text); },
  setLanguage(lang: 'id' | 'en'): void { instance?.setLanguage(lang); },
  getInstance(): BeanTalk | null { return instance; },
};

// Expose to window
if (typeof window !== 'undefined') {
  (window as any).ChatWidget = ChatWidget;
  (window as any).UniversalChatMe = BeanTalk;
  setTimeout(() => {
    try {
      if ((window as any).BeanTalk) {
        Object.assign((window as any).BeanTalk, ChatWidget);
      }
    } catch (e) {}
  }, 0);

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
      const langAttr = script.getAttribute('data-lang') || script.getAttribute('data-language');
      const language = (langAttr === 'en' || langAttr === 'id') ? (langAttr as 'id' | 'en') : undefined;

      console.log('[BeanTalk] Found embed tag on page:', { projectKey, apiUrl, brandName, language });

      if (projectKey && !instance) {
        ChatWidget.init({
          projectKey,
          apiUrl,
          accentColor: color,
          brandName,
          storeName: brandName,
          supportTitle,
          language,
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
