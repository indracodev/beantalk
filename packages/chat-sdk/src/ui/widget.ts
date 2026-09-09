import { ICONS } from './icons';
import { generateWidgetCss } from './styles';
import { Message, SessionInitData, WidgetInitOptions } from '../types';
import { EventEmitter } from '../core/emitter';
import { generateClientMessageId } from '../core/storage';

export class ChatWidgetUi {
  private shadowRoot: ShadowRoot;
  private emitter: EventEmitter;
  private options: WidgetInitOptions;
  private isOpen: boolean = false;
  private currentStage: 'welcome' | 'chat' = 'welcome';
  private unreadCount: number = 0;
  private messages: Message[] = [];
  private sessionData: SessionInitData | null = null;

  // DOM Elements inside Shadow DOM
  private wrapperEl!: HTMLElement;
  private launcherBtn!: HTMLButtonElement;
  private unreadBadge!: HTMLElement;
  private stageWelcome!: HTMLElement;
  private stageChat!: HTMLElement;
  private messagesArea!: HTMLElement;
  private composerInput!: HTMLTextAreaElement;
  private composerSendBtn!: HTMLButtonElement;
  private cardSnippetText!: HTMLElement;
  private styleEl!: HTMLStyleElement;

  constructor(options: WidgetInitOptions, emitter: EventEmitter) {
    this.options = options;
    this.emitter = emitter;

    // 1. Mount Host Container to DOM
    let hostEl = document.getElementById('beantalk-chat-root') || document.getElementById('universal-chat-root');
    if (!hostEl) {
      hostEl = document.createElement('div');
      hostEl.id = 'beantalk-chat-root';
      document.body.appendChild(hostEl);
    }

    // 2. Attach Open Shadow Root
    this.shadowRoot = hostEl.attachShadow({ mode: 'open' });

    // 3. Inject CSS
    this.styleEl = document.createElement('style');
    this.styleEl.textContent = generateWidgetCss(options.accentColor || '#1E1E1E');
    this.shadowRoot.appendChild(this.styleEl);

    // 4. Render UI Skeleton
    this.renderSkeleton();
    this.bindEvents();
  }

  updateTheming(primaryColor: string): void {
    if (this.styleEl) {
      this.styleEl.textContent = generateWidgetCss(primaryColor);
    }
  }

  setSessionData(data: SessionInitData): void {
    this.sessionData = data;
    const settings = (data as any).widget || data.widget_settings || {};

    // Update colors if configured on server
    if (settings.primary_color) {
      this.updateTheming(settings.primary_color);
    }

    // Update titles and greetings
    const title = settings.greeting_title || settings.header_title || data.project?.name || 'Chat Support';
    const greeting = settings.greeting_subtitle || settings.greeting_text || 'Hallo! Ada yang bisa kami bantu? Tanyakan apapun di sini.';

    const headerTitleEl = this.shadowRoot.querySelector('.welcome-title');
    if (headerTitleEl) headerTitleEl.textContent = title;

    const headerSubEl = this.shadowRoot.querySelector('.welcome-subtitle');
    if (headerSubEl) headerSubEl.textContent = greeting;

    const chatTitleEl = this.shadowRoot.querySelector('.chat-header-title');
    if (chatTitleEl) chatTitleEl.textContent = data.project?.name || 'Store Support';

    const brandBadge = this.shadowRoot.querySelector('.welcome-brand-badge');
    if (brandBadge) brandBadge.textContent = data.project?.name || 'Live Support';

    // Populate initial messages if present
    if (data.conversation?.messages && data.conversation.messages.length > 0) {
      this.setMessages(data.conversation.messages);
    }
  }

  private renderSkeleton(): void {
    const storeName = this.options.storeName || 'Store Support';
    const greetingTitle = this.options.greetingTitle || 'Hallo!';
    const greetingSub = this.options.greetingSubtitle || 'Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini.';
    const posClass = this.options.position === 'bottom-left' ? 'pos-bottom-left' : '';

    const html = `
      <div class="chat-wrapper ${posClass}">
        <!-- WIDGET WINDOW -->
        <div class="chat-window">
          
          <!-- ================= STAGE 1: WELCOME HUB ================= -->
          <div class="stage-welcome">
            <div class="welcome-header">
              <div class="welcome-header-top">
                <span class="welcome-brand-badge">${storeName}</span>
                <button type="button" class="welcome-close-btn" aria-label="Tutup">${ICONS.close}</button>
              </div>
              <h2 class="welcome-title">${greetingTitle}</h2>
              <p class="welcome-subtitle">${greetingSub}</p>
            </div>

            <div class="welcome-body">
              <!-- CARD 1: ACTIVE CHAT -->
              <div class="card-active-chat">
                <div class="card-live-indicator">
                  <span class="live-dot"></span>
                  <span>Live Chat Available</span>
                </div>
                <div class="card-chat-row">
                  <div class="card-chat-avatar">
                    ${ICONS.agentAvatar}
                  </div>
                  <div class="card-chat-info">
                    <div class="card-chat-name">${storeName}</div>
                    <div class="card-chat-snippet" id="card-snippet">Mulai obrolan baru dengan agen kami...</div>
                  </div>
                  <div class="card-chat-chevron">
                    ${ICONS.chevronRight}
                  </div>
                </div>
              </div>

              <!-- CARD 2: REACH US ELSEWHERE -->
              <div class="card-social-reach">
                <div class="social-reach-title">Atau Hubungi Kami Lewat</div>
                <div class="social-channel-list">
                  <a href="${this.options.whatsappNumber ? 'https://wa.me/' + this.options.whatsappNumber : '#'}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box whatsapp">${ICONS.whatsapp}</div>
                      <span>WhatsApp CS Resmi</span>
                    </div>
                    <span style="color:#94A3B8;">${ICONS.chevronRight}</span>
                  </a>
                  <a href="${this.options.instagramHandle ? 'https://instagram.com/' + this.options.instagramHandle : '#'}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box instagram">${ICONS.instagram}</div>
                      <span>Instagram Direct</span>
                    </div>
                    <span style="color:#94A3B8;">${ICONS.chevronRight}</span>
                  </a>
                  <a href="${this.options.messengerUrl || '#'}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box messenger">${ICONS.messenger}</div>
                      <span>Facebook Messenger</span>
                    </div>
                    <span style="color:#94A3B8;">${ICONS.chevronRight}</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="welcome-footer">
              ${ICONS.sparkles} <span>Powered by BeanTalk</span>
            </div>
          </div>

          <!-- ================= STAGE 2: CHAT UTAMA ================= -->
          <div class="stage-chat">
            <div class="chat-header">
              <div class="chat-header-left">
                <button type="button" class="chat-back-btn" aria-label="Kembali">${ICONS.back}</button>
                <div class="chat-header-avatar">
                  ${ICONS.agentAvatar}
                  <span class="header-online-dot"></span>
                </div>
                <div class="chat-header-info">
                  <div class="chat-header-title">${storeName}</div>
                  <div class="chat-header-status">Online • Membalas dalam hitungan menit</div>
                </div>
              </div>
              <button type="button" class="chat-close-btn" aria-label="Tutup">${ICONS.close}</button>
            </div>

            <!-- MESSAGES THREAD -->
            <div class="chat-messages-area">
              <!-- Dynamically populated -->
            </div>

            <!-- COMPOSER BAR -->
            <div class="chat-composer-box">
              <textarea class="composer-textarea" placeholder="Tulis pesan ke CS..." rows="1"></textarea>
              <button type="button" class="composer-send-btn" aria-label="Kirim" disabled>${ICONS.send}</button>
            </div>
          </div>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" class="chat-launcher-btn" aria-label="Buka Chat">
          <div class="launcher-icon-chat">${ICONS.chat}</div>
          <div class="launcher-icon-close">${ICONS.close}</div>
          <div class="launcher-unread-badge">0</div>
        </button>
      </div>
    `;

    const container = document.createElement('div');
    container.innerHTML = html;
    this.shadowRoot.appendChild(container.firstElementChild!);

    // Query references
    this.wrapperEl = this.shadowRoot.querySelector('.chat-wrapper') as HTMLElement;
    this.launcherBtn = this.shadowRoot.querySelector('.chat-launcher-btn') as HTMLButtonElement;
    this.unreadBadge = this.shadowRoot.querySelector('.launcher-unread-badge') as HTMLElement;
    this.stageWelcome = this.shadowRoot.querySelector('.stage-welcome') as HTMLElement;
    this.stageChat = this.shadowRoot.querySelector('.stage-chat') as HTMLElement;
    this.messagesArea = this.shadowRoot.querySelector('.chat-messages-area') as HTMLElement;
    this.composerInput = this.shadowRoot.querySelector('.composer-textarea') as HTMLTextAreaElement;
    this.composerSendBtn = this.shadowRoot.querySelector('.composer-send-btn') as HTMLButtonElement;
    this.cardSnippetText = this.shadowRoot.querySelector('#card-snippet') as HTMLElement;
  }

  private bindEvents(): void {
    // Toggle Launcher
    this.launcherBtn.addEventListener('click', () => {
      this.toggle();
    });

    // Close buttons
    this.shadowRoot.querySelectorAll('.welcome-close-btn, .chat-close-btn').forEach((btn) => {
      btn.addEventListener('click', () => this.close());
    });

    // Stage 1 Card Click -> Go to Stage 2
    const activeCard = this.shadowRoot.querySelector('.card-active-chat');
    if (activeCard) {
      activeCard.addEventListener('click', () => {
        this.goToStage('chat');
      });
    }

    // Stage 2 Back Button -> Go to Stage 1
    const backBtn = this.shadowRoot.querySelector('.chat-back-btn');
    if (backBtn) {
      backBtn.addEventListener('click', () => {
        this.goToStage('welcome');
      });
    }

    // Composer Input auto-expand & send enablement
    this.composerInput.addEventListener('input', () => {
      this.composerInput.style.height = 'auto';
      this.composerInput.style.height = Math.min(this.composerInput.scrollHeight, 90) + 'px';
      const hasText = this.composerInput.value.trim().length > 0;
      this.composerSendBtn.disabled = !hasText;
    });

    // Keyboard enter shortcut
    this.composerInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.handleSend();
      }
    });

    // Send button click
    this.composerSendBtn.addEventListener('click', () => {
      this.handleSend();
    });
  }

  private handleSend(): void {
    const text = this.composerInput.value.trim();
    if (!text) return;

    // Reset composer
    this.composerInput.value = '';
    this.composerInput.style.height = '24px';
    this.composerSendBtn.disabled = true;

    // Optimistic UI Append
    const tempMsg: Message = {
      id: Date.now(),
      conversation_id: this.sessionData?.conversation?.id || 0,
      client_message_id: generateClientMessageId(),
      sender_type: 'visitor',
      sender_name: 'Anda',
      message: text,
      created_at: new Date().toISOString(),
    };

    this.appendMessage(tempMsg);
    this.emitter.emit('ui:send', tempMsg);
  }

  goToStage(stage: 'welcome' | 'chat'): void {
    this.currentStage = stage;
    if (stage === 'welcome') {
      this.stageWelcome.style.display = 'flex';
      this.stageChat.style.display = 'none';
    } else {
      this.stageWelcome.style.display = 'none';
      this.stageChat.style.display = 'flex';
      this.scrollToBottom();
      setTimeout(() => this.composerInput.focus(), 150);
    }
  }

  open(): void {
    this.isOpen = true;
    this.wrapperEl.classList.add('is-open');
    this.unreadCount = 0;
    this.updateUnreadBadge();
    this.emitter.emit('widget:opened');

    if (this.currentStage === 'chat') {
      this.scrollToBottom();
      setTimeout(() => this.composerInput.focus(), 150);
    }
  }

  close(): void {
    this.isOpen = false;
    this.wrapperEl.classList.remove('is-open');
    this.emitter.emit('widget:closed');
  }

  toggle(): void {
    if (this.isOpen) {
      this.close();
    } else {
      this.open();
    }
  }

  setMessages(messages: Message[]): void {
    this.messages = [...messages];
    this.messagesArea.innerHTML = '';
    messages.forEach((msg) => this.renderMessageBubble(msg));
    this.updateSnippet();
    this.scrollToBottom();
  }

  appendMessage(msg: Message): void {
    // Avoid duplicates if client_message_id matches
    const exists = this.messages.some(
      (m) => m.id === msg.id || (msg.client_message_id && m.client_message_id === msg.client_message_id)
    );
    if (!exists) {
      this.messages.push(msg);
      this.renderMessageBubble(msg);
      this.updateSnippet();
      this.scrollToBottom();

      // If closed and message from agent, increment unread
      if (!this.isOpen && msg.sender_type !== 'visitor') {
        this.unreadCount++;
        this.updateUnreadBadge();
      }
    }
  }

  private renderMessageBubble(msg: Message): void {
    const isVisitor = msg.sender_type === 'visitor';
    const row = document.createElement('div');
    row.className = `msg-bubble-row ${isVisitor ? 'is-visitor' : 'is-agent'}`;

    const timeStr = this.formatTime(msg.created_at);
    const text = msg.content || msg.message || '';

    row.innerHTML = `
      <div class="msg-sender-name">${isVisitor ? 'Anda' : (msg.sender_name || 'Agent')}</div>
      <div class="msg-bubble">${escapeHtml(text)}</div>
      <div class="msg-time-status">
        <span>${timeStr}</span>
        ${isVisitor ? `<span style="display:inline-flex;">${ICONS.check}</span>` : ''}
      </div>
    `;

    this.messagesArea.appendChild(row);
  }

  private updateSnippet(): void {
    if (this.messages.length > 0) {
      const last = this.messages[this.messages.length - 1];
      const prefix = last.sender_type === 'visitor' ? 'Anda: ' : '';
      const text = last.content || last.message || '';
      this.cardSnippetText.textContent = prefix + text;
    }
  }

  private updateUnreadBadge(): void {
    if (this.unreadCount > 0) {
      this.unreadBadge.textContent = this.unreadCount > 9 ? '9+' : this.unreadCount.toString();
      this.unreadBadge.classList.add('has-unread');
    } else {
      this.unreadBadge.classList.remove('has-unread');
    }
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      this.messagesArea.scrollTop = this.messagesArea.scrollHeight;
    }, 40);
  }

  private formatTime(dateStr: string): string {
    try {
      const d = new Date(dateStr);
      return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch {
      return '';
    }
  }
}

function escapeHtml(str: string): string {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
