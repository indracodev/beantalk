import { ICONS } from './icons';
import { generateWidgetCss } from './styles';
import { LOCALES, Language, WidgetLocale } from './locale';
import { Message, SessionInitData, WidgetInitOptions } from '../types';
import { EventEmitter } from '../core/emitter';
import { generateClientMessageId, getStoredCustomerName, setStoredCustomerName } from '../core/storage';

export class ChatWidgetUi {
  private shadowRoot: ShadowRoot;
  private emitter: EventEmitter;
  private options: WidgetInitOptions;
  private lang: Language = 'id';
  private isOpen: boolean = false;
  private currentStage: 'welcome' | 'identity' | 'chat' | 'social-picker' = 'welcome';
  private unreadCount: number = 0;
  private messages: Message[] = [];
  private sessionData: SessionInitData | null = null;
  private customerName: string = '';
  private customerCode: string = '';

  private get t(): WidgetLocale {
    return LOCALES[this.lang] || LOCALES.id;
  }

  // DOM Elements inside Shadow DOM
  private wrapperEl!: HTMLElement;
  private launcherBtn!: HTMLButtonElement;
  private unreadBadge!: HTMLElement;
  private stageWelcome!: HTMLElement;
  private stageIdentity!: HTMLElement;
  private stageChat!: HTMLElement;
  private stageSocialPicker!: HTMLElement;
  private messagesArea!: HTMLElement;
  private composerInput!: HTMLTextAreaElement;
  private composerSendBtn!: HTMLButtonElement;
  private cardSnippetText!: HTMLElement;
  private styleEl!: HTMLStyleElement;

  // Ticket History & Resolved Controls
  private cardTicketsContainer!: HTMLElement;
  private ticketListContainer!: HTMLElement;
  private ticketCountBadge!: HTMLElement;
  private btnNewTicketWelcome!: HTMLButtonElement;
  private chatEndBtn!: HTMLButtonElement;
  private chatResolvedBanner!: HTMLElement;
  private chatStartNewBtn!: HTMLButtonElement;
  private chatComposerBox!: HTMLElement;

  // Customer Identity Elements
  private identityCustCode!: HTMLElement;
  private identityNameInput!: HTMLInputElement;
  private identityContinueBtn!: HTMLButtonElement;
  private identityBackBtn!: HTMLButtonElement;
  private identitySupportTag!: HTMLElement;
  private chatInlineIdentityBanner!: HTMLElement;
  private inlineIdentityInput!: HTMLInputElement;
  private inlineIdentityBtn!: HTMLButtonElement;
  private socialPickerBackBtn!: HTMLButtonElement;
  private audioCtx: AudioContext | null = null;

  constructor(options: WidgetInitOptions, emitter: EventEmitter) {
    this.options = options;
    this.emitter = emitter;
    this.lang = options.language === 'en' ? 'en' : 'id';

    // 1. Mount Host Container to DOM
    let hostEl = document.getElementById('beantalk-chat-root') || document.getElementById('universal-chat-root');
    if (!hostEl) {
      hostEl = document.createElement('div');
      hostEl.id = 'beantalk-chat-root';
      hostEl.style.position = 'relative';
      hostEl.style.zIndex = '2147483647';
      hostEl.style.display = 'block';
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
    this.initViewportHandler();

    // Check stored customer name
    const stored = getStoredCustomerName();
    if (stored) {
      this.applyCustomerName(stored, false);
    }
  }

  updateTheming(primaryColor: string): void {
    if (this.styleEl) {
      this.styleEl.textContent = generateWidgetCss(primaryColor);
    }
  }

  setLanguage(lang: Language): void {
    this.lang = lang === 'en' ? 'en' : 'id';

    // 1. Live Chat Indicator
    const liveIndicator = this.shadowRoot.querySelector('.card-live-indicator span:last-child');
    if (liveIndicator) liveIndicator.textContent = this.t.liveChatAvailable;

    // 2. Welcome card default snippet if no messages yet
    if (this.messages.length === 0 && this.cardSnippetText) {
      this.cardSnippetText.textContent = this.t.newChatSnippet;
    } else {
      this.updateSnippet();
    }

    // 3. Tickets section header & start new button
    const ticketsHeader = this.shadowRoot.querySelector('.tickets-section-header span:first-child');
    if (ticketsHeader) ticketsHeader.textContent = this.t.ticketsTitle;

    if (this.btnNewTicketWelcome) {
      const label = this.btnNewTicketWelcome.querySelector('span');
      if (label) label.textContent = this.t.startNewChat;
    }

    // 4. Welcome footer
    const footerSpan = this.shadowRoot.querySelector('.welcome-footer span');
    if (footerSpan) footerSpan.textContent = this.t.poweredBy;

    // 5. Identity stage
    if (this.identityCustCode && (!this.customerCode || this.identityCustCode.textContent === 'Tamu' || this.identityCustCode.textContent === 'Guest')) {
      this.identityCustCode.textContent = this.customerCode || this.t.identityPillGuest;
    }
    const idTitle = this.shadowRoot.querySelector('.identity-stage-title');
    if (idTitle) idTitle.textContent = this.t.identityTitle;

    const idSub = this.shadowRoot.querySelector('.identity-stage-subtitle');
    if (idSub) idSub.textContent = this.t.identitySubtitle;

    const idLabel = this.shadowRoot.querySelector('.identity-form-label');
    if (idLabel) idLabel.textContent = this.t.identityLabel;

    if (this.identityNameInput) this.identityNameInput.placeholder = this.t.identityPlaceholder;

    if (this.identityContinueBtn) {
      const label = this.identityContinueBtn.querySelector('span');
      if (label) label.textContent = this.t.identityContinue;
    }

    // 6. Chat header status & resolve button
    const chatStatus = this.shadowRoot.querySelector('.chat-header-status');
    if (chatStatus) chatStatus.textContent = this.t.onlineStatus;

    if (this.chatEndBtn) {
      this.chatEndBtn.title = this.t.resolveBtn;
      const label = this.chatEndBtn.querySelector('span');
      if (label) label.textContent = this.t.resolveBtn;
    }

    // 7. Inline identity prompt banner in chat
    if (this.chatInlineIdentityBanner) {
      const bannerSpan = this.chatInlineIdentityBanner.querySelector('span');
      if (bannerSpan) bannerSpan.textContent = this.t.askNameInline;
    }
    if (this.inlineIdentityInput) this.inlineIdentityInput.placeholder = this.t.namePlaceholder;
    if (this.inlineIdentityBtn) this.inlineIdentityBtn.textContent = this.t.saveBtn;

    // 8. Resolved banner & start new button in chat
    if (this.chatResolvedBanner) {
      const bannerText = this.chatResolvedBanner.querySelector('.chat-resolved-text span');
      if (bannerText) bannerText.textContent = this.t.resolvedBanner;
    }
    if (this.chatStartNewBtn) {
      const label = this.chatStartNewBtn.querySelector('span');
      if (label) label.textContent = this.t.startNewChat;
    }

    // 9. Composer textarea placeholder
    if (this.composerInput) {
      this.composerInput.placeholder = this.customerName
        ? this.t.typeMessageAsPlaceholder(this.customerName)
        : this.t.typeMessagePlaceholder;
    }

    // 10. Re-render tickets history if populated
    if (this.sessionData?.conversations) {
      this.renderTicketsHistory(this.sessionData.conversations);
    }

    // 11. Update visitor message bubbles sender name
    this.shadowRoot.querySelectorAll('.msg-bubble-row.is-visitor .msg-sender-name').forEach((el) => {
      el.textContent = this.customerName || this.t.senderYou;
    });
  }

  setSessionData(data: SessionInitData): void {
    this.sessionData = data;
    const settings = (data as any).widget || data.widget_settings || {};

    // Update language if provided in widget settings
    if (settings.language === 'en' || settings.language === 'id') {
      this.setLanguage(settings.language);
    }

    // Update colors if configured on server
    if (settings.primary_color) {
      this.updateTheming(settings.primary_color);
    }

    // Update titles and greetings
    let title = settings.greeting_title || settings.header_title;
    if (!title || (this.lang === 'en' && title === 'Hallo!')) {
      title = this.lang === 'en' ? 'Hello!' : (data.project?.name || 'Chat Support');
    }

    let greeting = settings.greeting_subtitle || settings.greeting_text;
    if (!greeting || (this.lang === 'en' && (greeting === 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!' || greeting === 'Hallo! Ada yang bisa kami bantu? Tanyakan apapun di sini.'))) {
      greeting = this.t.defaultGreetingSubtitle;
    }

    const headerTitleEl = this.shadowRoot.querySelector('.welcome-title');
    if (headerTitleEl) headerTitleEl.textContent = title;

    const headerSubEl = this.shadowRoot.querySelector('.welcome-subtitle');
    if (headerSubEl) headerSubEl.textContent = greeting;

    const supportTitle = settings.support_title || this.options.supportTitle || this.options.brandName || this.options.storeName || data.project?.name || this.t.defaultSupportTitle;

    const cardChatName = this.shadowRoot.querySelector('#cardChatName');
    if (cardChatName) cardChatName.textContent = supportTitle;

    if (this.identitySupportTag) this.identitySupportTag.textContent = supportTitle;

    const chatTitleEl = this.shadowRoot.querySelector('.chat-header-title');
    if (chatTitleEl) chatTitleEl.textContent = supportTitle;

    const brandBadge = this.shadowRoot.querySelector('.welcome-brand-badge');
    if (brandBadge) brandBadge.textContent = data.project?.name || 'Live Support';

    // Populate customer code and name
    const visitorObj = (data.visitor as any) || {};
    const code = visitorObj.customer_code || visitorObj.customer_code_formatted;
    if (code) {
      this.customerCode = code;
      if (this.identityCustCode) {
        this.identityCustCode.textContent = code;
      }
    }

    const serverName = visitorObj.name;
    const storedName = getStoredCustomerName();
    const effectiveName = serverName || storedName;
    if (effectiveName) {
      this.applyCustomerName(effectiveName, false);
    } else {
      if (this.chatInlineIdentityBanner) {
        this.chatInlineIdentityBanner.style.display = 'flex';
      }
    }

    // Populate initial messages if present
    if (data.conversation?.messages && data.conversation.messages.length > 0) {
      this.setMessages(data.conversation.messages);
    }

    // Populate past tickets history if present
    this.renderTicketsHistory(data.conversations || []);

    // Check if active conversation is closed
    const isClosed = data.conversation ? data.conversation.status === 'closed' : false;
    this.updateResolvedUI(isClosed);

    // Populate social channels ("Find us somewhere else")
    const socialChannelsCard = this.shadowRoot.querySelector('#socialChannelsCard') as HTMLElement;
    const socialChannelsTitle = this.shadowRoot.querySelector('#socialChannelsTitle') as HTMLElement;
    const socialChannelsRow = this.shadowRoot.querySelector('#socialChannelsRow') as HTMLElement;

    const channels = settings.social_channels || [];
    const findUsTitle = settings.find_us_title || 'Reach Us Anywhere Else';

    if (socialChannelsTitle) {
      socialChannelsTitle.textContent = findUsTitle;
    }

    if (socialChannelsCard && socialChannelsRow) {
      const activeChannels = Array.isArray(channels)
        ? channels.filter((c: any) => c.enabled && c.url)
        : [];

      if (activeChannels.length > 0) {
        socialChannelsRow.innerHTML = '';

        // Group by platform to support multi-contact (e.g. multiple WhatsApp numbers)
        const grouped: { [key: string]: any[] } = {};
        activeChannels.forEach((chan: any) => {
          const plat = (chan.platform || chan.icon || chan.id || 'whatsapp').toLowerCase();
          if (!grouped[plat]) {
            grouped[plat] = [];
          }
          grouped[plat].push(chan);
        });

        Object.keys(grouped).forEach((plat) => {
          const list = grouped[plat];
          const first = list[0];
          const iconContent = renderChannelIconHtml(first, plat);

          if (list.length === 1) {
            // Single contact for this platform -> direct link
            const btn = document.createElement('a');
            btn.className = `social-channel-btn social-btn-${plat}`;
            btn.href = first.url;
            btn.target = '_blank';
            btn.rel = 'noopener noreferrer';
            btn.title = `Hubungi via ${first.name || getPlatformDisplayName(plat)}`;
            btn.innerHTML = iconContent;
            socialChannelsRow.appendChild(btn);
          } else {
            // Multiple contacts for this platform (e.g. 2+ WA numbers) -> opens multi-contact picker
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `social-channel-btn social-btn-${plat} has-multi-badge`;
            btn.title = `${getPlatformDisplayName(plat)} (${list.length} pilihan kontak)`;
            btn.innerHTML = `
              ${iconContent}
              <span class="social-channel-badge">${list.length}</span>
            `;
            btn.addEventListener('click', (e) => {
              e.stopPropagation();
              this.openSocialPicker(plat, list);
            });
            socialChannelsRow.appendChild(btn);
          }
        });

        socialChannelsCard.style.display = 'block';
      } else {
        socialChannelsCard.style.display = 'none';
      }
    }
  }

  applyCustomerName(name: string, persist: boolean = true): void {
    const clean = name.trim();
    if (!clean) return;
    this.customerName = clean;

    if (persist) {
      setStoredCustomerName(clean);
      this.emitter.emit('customer:rename', clean);
    }

    if (this.identityNameInput) this.identityNameInput.value = clean;
    if (this.chatInlineIdentityBanner) this.chatInlineIdentityBanner.style.display = 'none';
    if (this.composerInput) {
      this.composerInput.placeholder = this.t.typeMessageAsPlaceholder(clean);
    }
  }

  private renderSkeleton(): void {
    const brandName = this.options.brandName || this.options.storeName || this.t.defaultSupportTitle;
    const supportTitle = this.options.supportTitle || brandName || this.t.defaultSupportTitle;
    const greetingTitle = this.options.greetingTitle || this.t.defaultGreetingTitle;
    const greetingSub = this.options.greetingSubtitle || this.t.defaultGreetingSubtitle;
    const posClass = this.options.position === 'bottom-left' ? 'pos-bottom-left' : '';

    const html = `
      <div class="chat-wrapper ${posClass}">
        <!-- WIDGET WINDOW -->
        <div class="chat-window">
          
          <!-- ================= STAGE 1: WELCOME HUB ================= -->
          <div class="stage-welcome">
            <div class="welcome-header">
              <div class="welcome-header-top">
                <span class="welcome-brand-badge">${brandName}</span>
                <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${ICONS.close}</button>
              </div>
              <h2 class="welcome-title">${greetingTitle}</h2>
              <p class="welcome-subtitle">${greetingSub}</p>
            </div>

            <div class="welcome-body">
              <!-- CARD 1: STORE SUPPORT (ACTIVE CHAT TRIGGER) -->
              <div class="card-active-chat" id="cardActiveChat">
                <div class="card-live-indicator">
                  <span class="live-dot"></span>
                  <span>${this.t.liveChatAvailable}</span>
                </div>
                <div class="card-chat-row">
                  <div class="card-chat-avatar">
                    ${ICONS.agentAvatar}
                  </div>
                  <div class="card-chat-info">
                    <div class="card-chat-name" id="cardChatName">${supportTitle}</div>
                    <div class="card-chat-snippet" id="card-snippet">${this.t.newChatSnippet}</div>
                  </div>
                  <div class="card-chat-chevron">
                    ${ICONS.chevronRight}
                  </div>
                </div>
              </div>

              <!-- CARD 1.5: TICKET HISTORY / PAST CONVERSATIONS -->
              <div class="card-tickets-container" id="cardTicketsContainer" style="display: none;">
                <div class="tickets-section-header">
                  <span>${this.t.ticketsTitle}</span>
                  <span id="ticketCountBadge"></span>
                </div>
                <div class="ticket-list" id="ticketListContainer">
                  <!-- Populated dynamically -->
                </div>
                <button type="button" class="btn-new-ticket" id="btnNewTicketWelcome">
                  ${ICONS.plus}
                  <span>${this.t.startNewChat}</span>
                </button>
              </div>

              <!-- CARD 2: REACH US ANYWHERE ELSE / FIND US SOMEWHERE ELSE -->
              <div class="card-social-channels" id="socialChannelsCard" style="display: none;">
                <div class="social-channels-header">
                  <span class="social-channels-title" id="socialChannelsTitle">${this.t.findUsTitleDefault}</span>
                </div>
                <div class="social-channels-row" id="socialChannelsRow">
                  <!-- Populated dynamically from server settings -->
                </div>
              </div>
            </div>

            <div class="welcome-footer">
              ${ICONS.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 1.5: FORM PEMANGGILAN NAMA ================= -->
          <div class="stage-identity" style="display: none;">
            <div class="identity-stage-header">
              <button type="button" class="identity-back-btn" id="identityBackBtn" aria-label="${this.t.backAriaLabel}">${ICONS.back}</button>
              <div class="identity-stage-header-title" id="identitySupportTag">${supportTitle}</div>
              <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${ICONS.close}</button>
            </div>

            <div class="identity-stage-body">
              <div class="identity-hero-avatar">
                ${ICONS.agentAvatar}
              </div>
              <div class="identity-code-pill" id="identityCustCode">${this.customerCode || this.t.identityPillGuest}</div>
              <h3 class="identity-stage-title">${this.t.identityTitle}</h3>
              <p class="identity-stage-subtitle">
                ${this.t.identitySubtitle}
              </p>

              <div class="identity-form-box">
                <label class="identity-form-label" for="identityNameInput">${this.t.identityLabel}</label>
                <input 
                  type="text" 
                  class="identity-name-input" 
                  id="identityNameInput" 
                  placeholder="${this.t.identityPlaceholder}" 
                  maxlength="40" 
                  autocomplete="name"
                />
                <button type="button" class="identity-continue-btn" id="identityContinueBtn">
                  <span>${this.t.identityContinue}</span>
                  ${ICONS.chevronRight}
                </button>
              </div>
            </div>

            <div class="welcome-footer">
              ${ICONS.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 1.8: MULTI-CONTACT CHANNEL SELECTOR ================= -->
          <div class="stage-social-picker" id="stageSocialPicker" style="display: none;">
            <div class="social-picker-header">
              <button type="button" class="social-picker-back-btn" id="socialPickerBackBtn" aria-label="${this.t.backAriaLabel}">${ICONS.back}</button>
              <div class="social-picker-header-title" id="socialPickerHeaderTitle">${this.t.socialPickerHeader('')}</div>
              <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${ICONS.close}</button>
            </div>

            <div class="social-picker-body">
              <div class="social-picker-hero">
                <div class="social-picker-avatar social-btn-whatsapp" id="socialPickerHeroAvatar">
                  ${ICONS.whatsapp}
                </div>
                <h3 class="social-picker-title" id="socialPickerTitle">${this.t.socialPickerTitle('WhatsApp')}</h3>
                <p class="social-picker-subtitle" id="socialPickerSubtitle">
                  ${this.t.socialPickerSubtitle}
                </p>
              </div>

              <div class="social-picker-list" id="socialPickerList">
                <!-- Dynamically populated options -->
              </div>
            </div>

            <div class="welcome-footer">
              ${ICONS.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 2: CHAT UTAMA ================= -->
          <div class="stage-chat">
            <div class="chat-header">
              <div class="chat-header-left">
                <button type="button" class="chat-back-btn" aria-label="${this.t.backAriaLabel}">${ICONS.back}</button>
                <div class="chat-header-avatar">
                  ${ICONS.agentAvatar}
                  <span class="header-online-dot"></span>
                </div>
                <div class="chat-header-info">
                  <div class="chat-header-title">${supportTitle}</div>
                  <div class="chat-header-status">${this.t.onlineStatus}</div>
                </div>
              </div>
              <div class="chat-header-actions" style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="chat-end-btn" id="chatEndBtn" title="${this.t.resolveBtn}">
                  ${ICONS.checkCircle}
                  <span>${this.t.resolveBtn}</span>
                </button>
                <button type="button" class="chat-close-btn" aria-label="${this.t.closeAriaLabel}">${ICONS.close}</button>
              </div>
            </div>

            <!-- INLINE IDENTITY BANNER -->
            <div class="chat-identity-banner" id="chatInlineIdentityBanner" style="display: none;">
              <span>${this.t.askNameInline}</span>
              <input type="text" id="inlineIdentityInput" placeholder="${this.t.namePlaceholder}" maxlength="40" />
              <button type="button" id="inlineIdentityBtn">${this.t.saveBtn}</button>
            </div>

            <!-- MESSAGES THREAD -->
            <div class="chat-messages-area">
              <!-- Dynamically populated -->
            </div>

            <!-- RESOLVED TICKET BANNER & START NEW CHAT -->
            <div class="chat-resolved-banner" id="chatResolvedBanner" style="display: none;">
              <div class="chat-resolved-text">
                ${ICONS.checkCircle}
                <span>${this.t.resolvedBanner}</span>
              </div>
              <button type="button" class="chat-start-new-btn" id="chatStartNewBtn">
                ${ICONS.plus}
                <span>${this.t.startNewChat}</span>
              </button>
            </div>

            <!-- COMPOSER BAR -->
            <div class="chat-composer-box" id="chatComposerBox">
              <textarea class="composer-textarea" placeholder="${this.t.typeMessagePlaceholder}" rows="1"></textarea>
              <button type="button" class="composer-send-btn" aria-label="${this.t.sendAriaLabel}" disabled>${ICONS.send}</button>
            </div>
          </div>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" class="chat-launcher-btn" aria-label="${this.t.openChatAriaLabel}">
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
    this.stageIdentity = this.shadowRoot.querySelector('.stage-identity') as HTMLElement;
    this.stageChat = this.shadowRoot.querySelector('.stage-chat') as HTMLElement;
    this.stageSocialPicker = this.shadowRoot.querySelector('#stageSocialPicker') as HTMLElement;
    this.messagesArea = this.shadowRoot.querySelector('.chat-messages-area') as HTMLElement;
    this.composerInput = this.shadowRoot.querySelector('.composer-textarea') as HTMLTextAreaElement;
    this.composerSendBtn = this.shadowRoot.querySelector('.composer-send-btn') as HTMLButtonElement;
    this.cardSnippetText = this.shadowRoot.querySelector('#card-snippet') as HTMLElement;

    // Ticket History & Resolved Controls references
    this.cardTicketsContainer = this.shadowRoot.querySelector('#cardTicketsContainer') as HTMLElement;
    this.ticketListContainer = this.shadowRoot.querySelector('#ticketListContainer') as HTMLElement;
    this.ticketCountBadge = this.shadowRoot.querySelector('#ticketCountBadge') as HTMLElement;
    this.btnNewTicketWelcome = this.shadowRoot.querySelector('#btnNewTicketWelcome') as HTMLButtonElement;
    this.chatEndBtn = this.shadowRoot.querySelector('#chatEndBtn') as HTMLButtonElement;
    this.chatResolvedBanner = this.shadowRoot.querySelector('#chatResolvedBanner') as HTMLElement;
    this.chatStartNewBtn = this.shadowRoot.querySelector('#chatStartNewBtn') as HTMLButtonElement;
    this.chatComposerBox = this.shadowRoot.querySelector('#chatComposerBox') as HTMLElement;

    // Customer Identity references
    this.identityCustCode = this.shadowRoot.querySelector('#identityCustCode') as HTMLElement;
    this.identityNameInput = this.shadowRoot.querySelector('#identityNameInput') as HTMLInputElement;
    this.identityContinueBtn = this.shadowRoot.querySelector('#identityContinueBtn') as HTMLButtonElement;
    this.identityBackBtn = this.shadowRoot.querySelector('#identityBackBtn') as HTMLButtonElement;
    this.identitySupportTag = this.shadowRoot.querySelector('#identitySupportTag') as HTMLElement;
    this.chatInlineIdentityBanner = this.shadowRoot.querySelector('#chatInlineIdentityBanner') as HTMLElement;
    this.inlineIdentityInput = this.shadowRoot.querySelector('#inlineIdentityInput') as HTMLInputElement;
    this.inlineIdentityBtn = this.shadowRoot.querySelector('#inlineIdentityBtn') as HTMLButtonElement;
    this.socialPickerBackBtn = this.shadowRoot.querySelector('#socialPickerBackBtn') as HTMLButtonElement;
  }

  private bindEvents(): void {
    // Lazy audio unlock on interaction
    const unlockFn = () => this.unlockAudio();
    this.launcherBtn.addEventListener('click', unlockFn);
    window.addEventListener('click', unlockFn, { passive: true });
    window.addEventListener('keydown', unlockFn, { passive: true });
    window.addEventListener('touchstart', unlockFn, { passive: true });

    // Toggle Launcher
    this.launcherBtn.addEventListener('click', () => {
      this.toggle();
    });

    // Close buttons
    this.shadowRoot.querySelectorAll('.welcome-close-btn, .chat-close-btn').forEach((btn) => {
      btn.addEventListener('click', () => this.close());
    });

    // Stage 1 Card Click -> Go to Stage 1.5 Identity Form
    const activeCard = this.shadowRoot.querySelector('.card-active-chat');
    if (activeCard) {
      activeCard.addEventListener('click', () => {
        this.goToStage('identity');
      });
    }

    // End Chat / Selesaikan Percakapan button in header
    if (this.chatEndBtn) {
      this.chatEndBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const convId = this.sessionData?.conversation?.id;
        if (!convId) return;
        if (confirm(this.t.resolveConfirm)) {
          this.emitter.emit('conversation:resolve', convId);
        }
      });
    }

    // Start New Chat from Banner in chat stage
    if (this.chatStartNewBtn) {
      this.chatStartNewBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.emitter.emit('conversation:start-new');
      });
    }

    // Start New Chat from Welcome stage ticket list button
    if (this.btnNewTicketWelcome) {
      this.btnNewTicketWelcome.addEventListener('click', (e) => {
        e.stopPropagation();
        this.emitter.emit('conversation:start-new');
      });
    }

    // Stage 1.5 Back Button -> Return to Welcome
    if (this.identityBackBtn) {
      this.identityBackBtn.addEventListener('click', () => {
        this.goToStage('welcome');
      });
    }

    // Stage 1.8 Social Picker Back Button -> Return to Welcome
    if (this.socialPickerBackBtn) {
      this.socialPickerBackBtn.addEventListener('click', () => {
        this.goToStage('welcome');
      });
    }

    // Stage 1.5 Form Submit (Lanjut ke Chat)
    const handleIdentitySubmit = () => {
      const val = this.identityNameInput ? this.identityNameInput.value.trim() : '';
      if (val) {
        this.applyCustomerName(val, true);
      }
      this.goToStage('chat');
    };

    if (this.identityContinueBtn) {
      this.identityContinueBtn.addEventListener('click', handleIdentitySubmit);
    }
    if (this.identityNameInput) {
      this.identityNameInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          handleIdentitySubmit();
        }
      });
    }

    // Stage 2 Back Button -> Go to Stage 1 Welcome
    const backBtn = this.shadowRoot.querySelector('.chat-back-btn');
    if (backBtn) {
      backBtn.addEventListener('click', () => {
        this.goToStage('welcome');
      });
    }

    // Customer Identity Save in Inline Banner
    const saveInlineIdentity = () => {
      const val = this.inlineIdentityInput.value.trim();
      if (val) {
        this.applyCustomerName(val, true);
      }
    };
    if (this.inlineIdentityBtn) {
      this.inlineIdentityBtn.addEventListener('click', saveInlineIdentity);
    }
    if (this.inlineIdentityInput) {
      this.inlineIdentityInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          saveInlineIdentity();
        }
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

    // Mobile Virtual Keyboard auto-scroll on focus
    const handleInputFocus = () => {
      if (typeof window !== 'undefined' && window.innerWidth <= 640) {
        setTimeout(() => {
          this.updateViewportDimensions();
          this.scrollToBottom();
          if (this.currentStage === 'chat') {
            this.composerInput.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
          }
        }, 100);
        setTimeout(() => {
          this.updateViewportDimensions();
          this.scrollToBottom();
        }, 300);
      }
    };

    this.composerInput.addEventListener('focus', handleInputFocus);
    if (this.identityNameInput) {
      this.identityNameInput.addEventListener('focus', handleInputFocus);
    }
  }

  private initViewportHandler(): void {
    if (typeof window === 'undefined') return;

    const onResizeOrScroll = () => {
      if (!this.isOpen) return;
      this.updateViewportDimensions();
    };

    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', onResizeOrScroll);
      window.visualViewport.addEventListener('scroll', onResizeOrScroll);
    }
    window.addEventListener('resize', onResizeOrScroll);
    window.addEventListener('orientationchange', () => {
      setTimeout(onResizeOrScroll, 200);
    });
  }

  private updateViewportDimensions(): void {
    if (typeof window === 'undefined') return;
    const isMobile = window.innerWidth <= 640;
    if (!isMobile) {
      this.wrapperEl.style.removeProperty('--bt-viewport-height');
      this.wrapperEl.style.removeProperty('--bt-viewport-top');
      return;
    }

    if (window.visualViewport) {
      const vh = Math.round(window.visualViewport.height);
      const offsetTop = Math.round(window.visualViewport.offsetTop);
      this.wrapperEl.style.setProperty('--bt-viewport-height', `${vh}px`);
      this.wrapperEl.style.setProperty('--bt-viewport-top', `${offsetTop}px`);
    } else {
      this.wrapperEl.style.setProperty('--bt-viewport-height', `${window.innerHeight}px`);
      this.wrapperEl.style.setProperty('--bt-viewport-top', '0px');
    }
  }

  handleSendText(sendValue: string, displayLabel?: string): void {
    const textToSend = (sendValue || '').trim();
    if (!textToSend) return;

    // Reset composer
    this.composerInput.value = '';
    this.composerInput.style.height = '24px';
    this.composerSendBtn.disabled = true;

    // Optimistic UI Append with displayLabel or sendValue
    const displayText = displayLabel || textToSend;
    const tempMsg: Message = {
      id: Date.now(),
      conversation_id: this.sessionData?.conversation?.id || 0,
      client_message_id: generateClientMessageId(),
      sender_type: 'visitor',
      sender_name: this.customerName || 'Anda',
      content: displayText,
      message: textToSend,
      created_at: new Date().toISOString(),
    };

    this.appendMessage(tempMsg);
    this.emitter.emit('ui:send', tempMsg);
  }

  private handleSend(): void {
    const text = this.composerInput.value.trim();
    if (!text) return;
    this.handleSendText(text);
  }

  goToStage(stage: 'welcome' | 'identity' | 'chat' | 'social-picker'): void {
    this.currentStage = stage;
    if (stage === 'welcome') {
      this.stageWelcome.style.display = 'flex';
      if (this.stageIdentity) this.stageIdentity.style.display = 'none';
      if (this.stageSocialPicker) this.stageSocialPicker.style.display = 'none';
      this.stageChat.style.display = 'none';
    } else if (stage === 'identity') {
      this.stageWelcome.style.display = 'none';
      if (this.stageIdentity) this.stageIdentity.style.display = 'flex';
      if (this.stageSocialPicker) this.stageSocialPicker.style.display = 'none';
      this.stageChat.style.display = 'none';
      if (this.identityNameInput) {
        if (this.customerName) {
          this.identityNameInput.value = this.customerName;
        }
        setTimeout(() => this.identityNameInput.focus(), 150);
      }
    } else if (stage === 'social-picker') {
      this.stageWelcome.style.display = 'none';
      if (this.stageIdentity) this.stageIdentity.style.display = 'none';
      if (this.stageSocialPicker) this.stageSocialPicker.style.display = 'flex';
      this.stageChat.style.display = 'none';
    } else {
      this.stageWelcome.style.display = 'none';
      if (this.stageIdentity) this.stageIdentity.style.display = 'none';
      if (this.stageSocialPicker) this.stageSocialPicker.style.display = 'none';
      this.stageChat.style.display = 'flex';

      // Auto-render bot welcome message with interactive options if messages area is empty
      if (this.messages.length === 0) {
        const settings = (this.sessionData as any)?.widget || this.sessionData?.widget_settings;
        if (settings && settings.bot_enabled && settings.bot_welcome_message) {
          const isOptionsEnabled = settings.bot_mode_options !== false;
          const welcomeOptions = (isOptionsEnabled && Array.isArray(settings.bot_welcome_options) && settings.bot_welcome_options.length > 0)
            ? settings.bot_welcome_options
            : null;

          const initialBotMsg: Message = {
            id: 0,
            conversation_id: this.sessionData?.conversation?.id || 0,
            sender_type: 'bot' as any,
            sender_name: settings.bot_name || 'Assistant',
            content: settings.bot_welcome_message,
            message: settings.bot_welcome_message,
            metadata: {
              is_bot: true,
              is_welcome: true,
              options: welcomeOptions,
            },
            created_at: new Date().toISOString(),
          };
          this.appendMessage(initialBotMsg);
        }
      }

      this.scrollToBottom();
      setTimeout(() => this.composerInput.focus(), 150);
    }
    this.updateViewportDimensions();
  }

  openSocialPicker(platform: string, contacts: any[]): void {
    const titleEl = this.shadowRoot.querySelector('#socialPickerTitle') as HTMLElement;
    const headerTitleEl = this.shadowRoot.querySelector('#socialPickerHeaderTitle') as HTMLElement;
    const avatarEl = this.shadowRoot.querySelector('#socialPickerHeroAvatar') as HTMLElement;
    const listEl = this.shadowRoot.querySelector('#socialPickerList') as HTMLElement;

    const platName = getPlatformDisplayName(platform);
    if (headerTitleEl) headerTitleEl.textContent = this.t.socialPickerHeader(platName);
    if (titleEl) titleEl.textContent = this.t.socialPickerTitle(platName);
    const subtitleEl = this.shadowRoot.querySelector('#socialPickerSubtitle');
    if (subtitleEl) subtitleEl.textContent = this.t.socialPickerSubtitle;

    if (avatarEl) {
      const first = contacts[0] || {};
      avatarEl.innerHTML = renderChannelIconHtml(first, platform);
      avatarEl.className = `social-picker-avatar social-btn-${platform}`;
    }

    if (listEl) {
      listEl.innerHTML = '';
      contacts.forEach((c) => {
        const item = document.createElement('a');
        item.className = 'social-picker-item';
        item.href = c.url;
        item.target = '_blank';
        item.rel = 'noopener noreferrer';

        const iconContent = renderChannelIconHtml(c, platform);
        const cleanUrlSnippet = extractContactDisplay(c.url, platform);

        item.innerHTML = `
          <div class="social-picker-item-avatar social-btn-${platform}">
            ${iconContent}
          </div>
          <div class="social-picker-item-info">
            <div class="social-picker-item-name">${escapeHtml(c.name || platName)}</div>
            ${cleanUrlSnippet ? `<div class="social-picker-item-sub">${escapeHtml(cleanUrlSnippet)}</div>` : ''}
          </div>
          <div class="social-picker-item-arrow">
            ${ICONS.chevronRight}
          </div>
        `;

        item.addEventListener('click', () => {
          setTimeout(() => {
            this.goToStage('welcome');
          }, 300);
        });

        listEl.appendChild(item);
      });
    }

    this.goToStage('social-picker');
  }

  open(): void {
    this.isOpen = true;
    this.wrapperEl.classList.add('is-open');
    this.unreadCount = 0;
    this.updateUnreadBadge();
    this.updateViewportDimensions();
    this.emitter.emit('widget:opened');

    // Prevent body scroll bounce on mobile
    if (typeof document !== 'undefined' && window.innerWidth <= 640) {
      document.documentElement.style.overflow = 'hidden';
      document.body.style.overflow = 'hidden';
    }

    if (this.currentStage === 'chat') {
      this.scrollToBottom();
      setTimeout(() => this.composerInput.focus(), 150);
    }
  }

  close(): void {
    this.isOpen = false;
    this.wrapperEl.classList.remove('is-open');
    this.emitter.emit('widget:closed');

    // Restore body scroll
    if (typeof document !== 'undefined') {
      document.documentElement.style.overflow = '';
      document.body.style.overflow = '';
    }
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

  private unlockAudio(): void {
    try {
      const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
      if (!AudioContextClass) return;
      if (!this.audioCtx) {
        this.audioCtx = new AudioContextClass();
      }
      if (this.audioCtx.state === 'suspended') {
        this.audioCtx.resume();
      }
    } catch (e) {
      // Lazy unlock error ignored
    }
  }

  playNotificationSound(): void {
    try {
      this.unlockAudio();
      if (!this.audioCtx) return;

      const now = this.audioCtx.currentTime;
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(659.25, now); // E5
      osc.frequency.exponentialRampToValueAtTime(880, now + 0.08); // Glide to A5

      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(0.25, now + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

      osc.connect(gain);
      gain.connect(this.audioCtx.destination);

      osc.start(now);
      osc.stop(now + 0.4);
    } catch (e) {
      // Audio playback ignored
    }
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

      // If message from agent, play lazy audio chime
      if (msg.sender_type !== 'visitor') {
        this.playNotificationSound();
      }

      // If closed and message from agent, increment unread
      if (!this.isOpen && msg.sender_type !== 'visitor') {
        this.unreadCount++;
        this.updateUnreadBadge();
      }
    }
  }

  private renderMessageBubble(msg: Message): void {
    const isVisitor = msg.sender_type === 'visitor';
    const isBot = msg.sender_type === 'bot';
    const row = document.createElement('div');
    row.className = `msg-bubble-row ${isVisitor ? 'is-visitor' : 'is-agent'}`;

    const timeStr = this.formatTime(msg.created_at);
    const rawText = msg.content || msg.message || '';
    const formattedHtml = this.formatMessageContent(rawText);
    const senderTitle = isVisitor ? (this.customerName || this.t.senderYou) : (isBot ? ('🤖 ' + (msg.sender_name || 'BeanBot')) : (msg.sender_name || 'Agent'));

    const widgetSettings = (this.sessionData as any)?.widget || this.sessionData?.widget_settings;
    const isOptionsEnabled = widgetSettings ? widgetSettings.bot_mode_options !== false : true;
    const options = (isOptionsEnabled && msg.metadata && Array.isArray(msg.metadata.options)) ? msg.metadata.options : null;
    let optionsHtml = '';
    if (options && options.length > 0 && !isVisitor) {
      optionsHtml = `
        <div class="msg-options-container">
          ${options.map((opt: any, idx: number) => `
            <button type="button" class="msg-option-btn" data-idx="${idx}">
              <span>${escapeHtml(opt.label || opt.value || '')}</span>
              <span class="msg-option-arrow">→</span>
            </button>
          `).join('')}
        </div>
      `;
    }

    row.innerHTML = `
      <div class="msg-sender-name" style="${isBot ? 'color: #5856D6; font-weight: 600;' : ''}">${escapeHtml(senderTitle)}</div>
      <div class="msg-bubble">${formattedHtml}</div>
      ${optionsHtml}
      <div class="msg-time-status">
        <span>${timeStr}</span>
        ${isVisitor ? `<span style="display:inline-flex;">${ICONS.check}</span>` : ''}
      </div>
    `;

    // Bind click events on option buttons
    if (options && options.length > 0 && !isVisitor) {
      const btns = row.querySelectorAll('.msg-option-btn');
      btns.forEach((btn, idx) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const opt = options[idx];
          if (!opt) return;

          // Disable siblings in this container and mark selected
          btns.forEach((b) => (b as HTMLButtonElement).disabled = true);
          btn.classList.add('is-selected');

          // Send chosen option to conversation
          this.handleSendText(opt.value || opt.label, opt.label || opt.value);
        });
      });
    }

    this.messagesArea.appendChild(row);
  }

  private formatMessageContent(rawText: string): string {
    if (!rawText) return '';
    let escaped = escapeHtml(rawText);
    // Convert *bold* to <strong>bold</strong>
    escaped = escaped.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
    // Convert URLs to clickable links
    escaped = escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
    // Convert standalone www.xxx to links
    escaped = escaped.replace(/(^|[\s])(www\.[^\s]+)/g, '$1<a href="https://$2" target="_blank" rel="noopener noreferrer">$2</a>');
    return escaped;
  }

  private updateSnippet(): void {
    if (this.messages.length > 0) {
      const last = this.messages[this.messages.length - 1];
      const prefix = last.sender_type === 'visitor' ? `${this.t.senderYou}: ` : '';
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
      if (!dateStr) return '';
      let parsed = dateStr;
      if (typeof parsed === 'string' && !parsed.includes('Z') && !parsed.includes('+') && !parsed.includes('T')) {
        parsed = parsed.replace(' ', 'T') + 'Z';
      }
      const d = new Date(parsed);
      if (isNaN(d.getTime())) return '';
      const hours = String(d.getHours()).padStart(2, '0');
      const mins = String(d.getMinutes()).padStart(2, '0');
      return `${hours}:${mins}`;
    } catch {
      return '';
    }
  }

  updateResolvedUI(isResolved: boolean): void {
    if (isResolved) {
      if (this.chatEndBtn) this.chatEndBtn.style.display = 'none';
      if (this.chatComposerBox) this.chatComposerBox.style.display = 'none';
      if (this.chatResolvedBanner) this.chatResolvedBanner.style.display = 'flex';
    } else {
      if (this.chatEndBtn) this.chatEndBtn.style.display = 'inline-flex';
      if (this.chatComposerBox) this.chatComposerBox.style.display = 'flex';
      if (this.chatResolvedBanner) this.chatResolvedBanner.style.display = 'none';
    }
  }

  renderTicketsHistory(conversations: any[]): void {
    if (!this.cardTicketsContainer || !this.ticketListContainer) return;

    if (!conversations || conversations.length === 0) {
      this.cardTicketsContainer.style.display = 'none';
      return;
    }

    this.ticketListContainer.innerHTML = '';
    if (this.ticketCountBadge) {
      this.ticketCountBadge.textContent = this.t.ticketsCount(conversations.length);
    }

    conversations.forEach((conv) => {
      const item = document.createElement('div');
      item.className = 'ticket-item';
      const isOpen = conv.status === 'open' || conv.status === 'pending';
      const statusClass = isOpen ? 'ticket-status-open' : 'ticket-status-closed';
      const statusLabel = isOpen ? this.t.ticketStatusOpen : this.t.ticketStatusClosed;
      const preview = conv.last_message_preview || this.t.ticketDefaultSnippet;
      const timeStr = this.formatTime(conv.last_message_at || conv.created_at);

      item.innerHTML = `
        <div class="ticket-item-left">
          <div class="ticket-item-title">
            <span>${this.t.ticketPrefix(conv.id)}</span>
            <span class="ticket-status-badge ${statusClass}">${statusLabel}</span>
            <span style="font-size: 10px; color: #94A3B8; font-weight: normal; margin-left: auto;">${timeStr}</span>
          </div>
          <div class="ticket-item-snippet">${escapeHtml(preview)}</div>
        </div>
        <div style="color: #94A3B8; display: flex; align-items: center;">
          ${ICONS.chevronRight}
        </div>
      `;

      item.addEventListener('click', () => {
        this.emitter.emit('conversation:switch', conv.id);
      });

      this.ticketListContainer.appendChild(item);
    });

    this.cardTicketsContainer.style.display = 'flex';
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

function renderChannelIconHtml(chan: any, platform: string): string {
  if (chan) {
    const custom = (chan.custom_icon || '').trim();
    if (chan.icon_type === 'custom' || (custom !== '' && !chan.icon_type)) {
      if (custom) {
        if (custom.startsWith('<svg') && custom.endsWith('</svg>')) {
          return custom;
        }
        return `<img src="${escapeHtml(custom)}" alt="${escapeHtml(chan.name || platform)}" />`;
      }
    }
  }
  const iconKey = chan?.icon || platform;
  return (ICONS as any)[iconKey] || (ICONS as any)[platform] || ICONS.chat;
}

function getPlatformDisplayName(platform: string): string {
  const map: { [key: string]: string } = {
    whatsapp: 'WhatsApp',
    instagram: 'Instagram',
    facebook: 'Facebook',
    messenger: 'Facebook',
    tiktok: 'TikTok',
    youtube: 'YouTube',
    telegram: 'Telegram',
    shopee: 'Shopee Store',
    tokopedia: 'Tokopedia Store',
    custom: 'Link Kustom',
    link: 'Tautan Kustom',
  };
  return map[platform.toLowerCase()] || capitalize(platform);
}

function extractContactDisplay(url: string, platform: string): string {
  if (!url) return '';
  const plat = platform.toLowerCase();

  if (plat === 'whatsapp') {
    if (url.includes('wa.me/')) {
      const num = url.split('wa.me/')[1]?.split('?')[0] || '';
      return num ? `+${num}` : url;
    }
  } else if (plat === 'instagram') {
    if (url.includes('instagram.com/')) {
      const user = url.split('instagram.com/')[1]?.split('/')[0]?.split('?')[0] || '';
      return user ? `@${user}` : url;
    }
  } else if (plat === 'facebook' || plat === 'messenger') {
    if (url.includes('facebook.com/')) {
      const user = url.split('facebook.com/')[1]?.split('/')[0]?.split('?')[0] || '';
      return user ? user : url;
    }
  } else if (plat === 'tiktok') {
    if (url.includes('tiktok.com/@')) {
      const user = url.split('tiktok.com/@')[1]?.split('/')[0]?.split('?')[0] || '';
      return user ? `@${user}` : url;
    }
  } else if (plat === 'youtube') {
    if (url.includes('youtube.com/@')) {
      const user = url.split('youtube.com/@')[1]?.split('/')[0]?.split('?')[0] || '';
      return user ? `@${user}` : url;
    }
  } else if (plat === 'telegram') {
    if (url.includes('t.me/')) {
      const user = url.split('t.me/')[1]?.split('/')[0]?.split('?')[0] || '';
      return user ? `@${user}` : url;
    }
  }

  return url;
}

function capitalize(str: string): string {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}
