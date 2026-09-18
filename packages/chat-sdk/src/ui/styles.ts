export function generateWidgetCss(primaryColor: string = '#1E1E1E'): string {
  return `
    :host {
      all: initial;
      display: block !important;
      position: relative;
      z-index: 2147483647;
      pointer-events: none;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 14px;
      line-height: 1.5;
      color: #0F172A;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
      --chat-primary: ${primaryColor};
      --chat-primary-hover: ${adjustColor(primaryColor, -15)};
      --chat-primary-text: #FFFFFF;
      --chat-bg: #FFFFFF;
      --chat-surface: #F8FAFC;
      --chat-border: #E2E8F0;
      --chat-text-main: #0F172A;
      --chat-text-muted: #64748B;
      --chat-shadow: 0 12px 36px -4px rgba(15, 23, 42, 0.16), 0 4px 12px -2px rgba(15, 23, 42, 0.08);
      --chat-radius: 18px;
    }

    *, *::before, *::after {
      box-sizing: inherit;
      margin: 0;
      padding: 0;
      outline: none;
      -webkit-tap-highlight-color: transparent;
    }

    .chat-wrapper {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 2147483647;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 14px;
      pointer-events: none;
    }

    .chat-wrapper.pos-bottom-left {
      right: auto;
      left: 24px;
      align-items: flex-start;
    }

    .chat-wrapper.pos-bottom-left .chat-window {
      right: auto;
      left: 0;
    }

    /* ==========================================================================
       1. FLOATING LAUNCHER TRIGGER
       ========================================================================== */
    .chat-launcher-btn {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.22);
      transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.15s ease;
      position: relative;
      pointer-events: auto;
    }

    .chat-launcher-btn:hover {
      transform: scale(1.06);
      background: var(--chat-primary-hover);
    }

    .chat-launcher-btn:active {
      transform: scale(0.96);
    }

    .launcher-icon-chat, .launcher-icon-close {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .launcher-icon-close {
      opacity: 0;
      transform: rotate(-90deg) scale(0.7);
    }

    .chat-wrapper.is-open .launcher-icon-chat {
      opacity: 0;
      transform: rotate(90deg) scale(0.7);
    }

    .chat-wrapper.is-open .launcher-icon-close {
      opacity: 1;
      transform: rotate(0deg) scale(1);
    }

    /* Mascot Launcher Styling */
    .chat-launcher-btn.is-mascot-launcher {
      background: transparent !important;
      box-shadow: none !important;
      border: none !important;
      width: auto !important;
      height: auto !important;
      min-width: 56px;
      min-height: 56px;
      overflow: visible;
      padding: 0;
    }

    .chat-launcher-btn.is-mascot-launcher:hover {
      background: transparent !important;
      transform: none !important;
    }

    .launcher-mascot-container {
      display: flex;
      align-items: center;
      justify-content: center;
      filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.25));
      transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
      position: relative;
    }

    .chat-launcher-btn.is-mascot-launcher:hover .launcher-mascot-container {
      transform: scale(1.08) translateY(-3px);
    }

    .chat-launcher-btn.is-mascot-launcher:active .launcher-mascot-container {
      transform: scale(0.96);
    }

    .chat-launcher-btn.is-mascot-launcher .launcher-icon-close {
      background: var(--chat-primary);
      color: #FFFFFF;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.28);
    }

    .chat-wrapper.is-open .chat-launcher-btn.is-mascot-launcher .launcher-mascot-container {
      opacity: 0;
      pointer-events: none;
      transform: scale(0.5);
    }

    .launcher-unread-badge {
      position: absolute;
      top: -3px;
      right: -3px;
      min-width: 20px;
      height: 20px;
      padding: 0 6px;
      border-radius: 10px;
      background: #EF4444;
      color: #FFFFFF;
      font-size: 11px;
      font-weight: 700;
      display: none;
      align-items: center;
      justify-content: center;
      border: 2px solid #FFFFFF;
      box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
      animation: pulseBadge 2s infinite;
    }

    .launcher-unread-badge.has-unread {
      display: flex;
    }

    @keyframes pulseBadge {
      0% { transform: scale(1); }
      50% { transform: scale(1.12); }
      100% { transform: scale(1); }
    }

    /* ==========================================================================
       2. WIDGET WINDOW CONTAINER
       ========================================================================== */
    .chat-window {
      position: absolute;
      bottom: 72px;
      right: 0;
      width: 380px;
      height: 590px;
      max-height: calc(100vh - 110px);
      background: var(--chat-bg);
      border-radius: var(--chat-radius);
      box-shadow: var(--chat-shadow);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid var(--chat-border);
      opacity: 0;
      transform: translateY(20px) scale(0.96);
      pointer-events: none;
      visibility: hidden;
      transition: opacity 0.24s ease, transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.24s;
    }

    .chat-wrapper.is-open .chat-window {
      opacity: 1;
      transform: translateY(0) scale(1);
      pointer-events: auto;
      visibility: visible;
    }

    /* ==========================================================================
       3. STAGE 1: WELCOME HUB
       ========================================================================== */
    .stage-welcome {
      display: flex;
      flex-direction: column;
      height: 100%;
      background: #F8FAFC;
    }

    .welcome-header {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      padding: 24px 20px 36px 20px;
      /* position: relative; */
    }

    .welcome-header-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .welcome-brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.16);
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.02em;
    }

    .welcome-close-btn {
      background: none;
      border: none;
      color: var(--chat-primary-text);
      opacity: 0.8;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4px;
      border-radius: 6px;
      transition: opacity 0.15s, background 0.15s;
    }

    .welcome-close-btn:hover {
      opacity: 1;
      background: rgba(255, 255, 255, 0.15);
    }

    .welcome-title {
      font-size: 20px;
      font-weight: 700;
      line-height: 1.25;
      margin-bottom: 6px;
    }

    .welcome-subtitle {
      font-size: 13px;
      opacity: 0.9;
      line-height: 1.4;
    }

    .welcome-body {
      flex: 1;
      padding: 0 16px 16px 16px;
      margin-top: -30px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      overflow-y: auto;
    }

    /* CARD 1: ACTIVE CONVERSATION */
    .card-active-chat {
      background: #FFFFFF;
      border-radius: 14px;
      padding: 16px;
      margin-top: 10px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s;
    }

    .card-active-chat:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      border-color: #CBD5E1;
    }

    .card-live-indicator {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 600;
      color: #10B981;
      margin-bottom: 10px;
    }

    .live-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
      animation: livePulse 2s infinite;
    }

    @keyframes livePulse {
      0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
      70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .card-chat-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .card-chat-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .card-chat-info {
      flex: 1;
      min-width: 0;
    }

    .card-chat-name {
      font-size: 13px;
      font-weight: 600;
      color: #0F172A;
      margin-bottom: 2px;
    }

    .card-chat-snippet {
      font-size: 12px;
      color: #64748B;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .card-chat-chevron {
      color: #94A3B8;
      display: flex;
      align-items: center;
    }

    /* ==========================================================================
       STAGE 1.5: FORM PEMANGGILAN NAMA (KENALAN DULU)
       ========================================================================== */
    .stage-identity {
      display: flex;
      flex-direction: column;
      height: 100%;
      background: #F8FAFC;
    }

    .identity-stage-header {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      padding: 16px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .identity-back-btn {
      background: none;
      border: none;
      color: var(--chat-primary-text);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 6px;
      border-radius: 8px;
      transition: background 0.15s ease;
    }

    .identity-back-btn:hover {
      background: rgba(255, 255, 255, 0.18);
    }

    .identity-stage-header-title {
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .identity-stage-body {
      flex: 1;
      padding: 24px 20px 20px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      overflow-y: auto;
    }

    .identity-hero-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .identity-hero-avatar svg {
      width: 30px;
      height: 30px;
    }

    .identity-code-pill {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      background: #E2E8F0;
      color: #334155;
      margin-bottom: 10px;
      border: 1px solid #CBD5E1;
    }

    .identity-stage-title {
      font-size: 18px;
      font-weight: 700;
      color: #0F172A;
      margin: 0 0 6px 0;
    }

    .identity-stage-subtitle {
      font-size: 13px;
      color: #64748B;
      line-height: 1.45;
      margin: 0 0 18px 0;
      max-width: 290px;
    }

    .identity-form-box {
      width: 100%;
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-radius: 14px;
      padding: 18px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
      display: flex;
      flex-direction: column;
      gap: 12px;
      text-align: left;
      box-sizing: border-box;
    }

    .identity-form-label {
      font-size: 11.5px;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .identity-email-label {
      margin-top: 4px;
    }

    .identity-name-input,
    .identity-email-input {
      width: 100%;
      padding: 11px 14px;
      font-size: 13px;
      font-family: inherit;
      border: 1.5px solid #CBD5E1;
      border-radius: 9px;
      color: #0F172A;
      background: #FFFFFF;
      outline: none;
      box-sizing: border-box;
      transition: border-color 0.15s, box-shadow 0.15s;
    }

    .identity-name-input::placeholder,
    .identity-email-input::placeholder {
      color: #94A3B8;
      font-size: 13px;
    }

    .identity-name-input:focus,
    .identity-email-input:focus {
      border-color: var(--chat-primary);
      box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12);
    }

    .identity-email-error {
      font-size: 11.5px;
      color: #EF4444;
      margin-top: -4px;
      font-weight: 500;
    }

    .identity-continue-btn {
      width: 100%;
      padding: 11px 18px;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: opacity 0.15s, transform 0.1s, box-shadow 0.15s;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .identity-continue-btn:hover {
      opacity: 0.94;
      transform: translateY(-1px);
      box-shadow: 0 5px 14px rgba(0, 0, 0, 0.14);
    }

    .identity-continue-btn:active {
      transform: translateY(1px);
    }

    .identity-continue-btn svg {
      width: 16px;
      height: 16px;
    }

    /* CARD 3: REACH US ANYWHERE ELSE (SOCIAL CHANNELS) */
    .card-social-channels {
      background: #FFFFFF;
      border-radius: 14px;
      padding: 14px 16px;
      border: 1px solid #E2E8F0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }

    .social-channels-header {
      margin-bottom: 10px;
    }

    .social-channels-title {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #94A3B8;
    }

    .social-channels-row {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .social-channel-btn {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
      cursor: pointer;
      border: none;
      color: #FFFFFF;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
      flex-shrink: 0;
    }

    .social-channel-btn svg {
      width: 20px;
      height: 20px;
    }

    .social-channel-btn:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
      filter: brightness(1.05);
    }

    .social-btn-whatsapp {
      background: linear-gradient(135deg, #25D366, #128C7E);
      color: #FFFFFF;
    }

    .social-btn-instagram {
      background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
      color: #FFFFFF;
    }

    .social-btn-facebook, .social-btn-messenger {
      background: linear-gradient(135deg, #1877F2, #0D65D9);
      color: #FFFFFF;
    }

    .social-btn-tiktok {
      background: linear-gradient(135deg, #010101, #1e1e1e);
      color: #FFFFFF;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }

    .social-btn-threads {
      background: #000000;
      color: #FFFFFF;
    }

    .social-btn-x, .social-btn-twitter {
      background: #000000;
      color: #FFFFFF;
    }

    .social-btn-youtube {
      background: linear-gradient(135deg, #FF0000, #CC0000);
      color: #FFFFFF;
    }

    .social-btn-telegram {
      background: linear-gradient(135deg, #2AABEE, #229ED9);
      color: #FFFFFF;
    }

    .social-btn-shopee {
      background: linear-gradient(135deg, #EE4D2D, #FF5722);
      color: #FFFFFF;
    }

    .social-btn-tokopedia {
      background: linear-gradient(135deg, #03AC0E, #00880B);
      color: #FFFFFF;
    }

    .social-btn-custom, .social-btn-link {
      background: linear-gradient(135deg, #475569, #1E293B);
      color: #FFFFFF;
    }

    .social-channel-btn img,
    .social-picker-avatar img,
    .social-picker-item-avatar img {
      width: 20px;
      height: 20px;
      object-fit: contain;
      border-radius: 4px;
      display: block;
    }

    .social-channel-btn.has-multi-badge {
      position: relative;
    }

    .social-channel-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: #0071E3;
      color: #FFFFFF;
      font-size: 9.5px;
      font-weight: 800;
      min-width: 16px;
      height: 16px;
      padding: 0 3px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #FFFFFF;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
    }

    /* ==========================================================================
       STAGE 1.8: MULTI-CONTACT CHANNEL SELECTOR (PILIH KONTAK)
       ========================================================================== */
    .stage-social-picker {
      display: flex;
      flex-direction: column;
      height: 100%;
      background: #F8FAFC;
    }

    .social-picker-header {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      padding: 16px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .social-picker-back-btn {
      background: none;
      border: none;
      color: var(--chat-primary-text);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 6px;
      border-radius: 8px;
      transition: background 0.15s ease;
    }

    .social-picker-back-btn:hover {
      background: rgba(255, 255, 255, 0.18);
    }

    .social-picker-header-title {
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .social-picker-body {
      flex: 1;
      padding: 20px 16px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      overflow-y: auto;
    }

    .social-picker-hero {
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .social-picker-avatar {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
      color: #FFFFFF;
    }

    .social-picker-avatar svg {
      width: 28px;
      height: 28px;
    }

    .social-picker-title {
      font-size: 17px;
      font-weight: 700;
      color: #0F172A;
      margin: 0 0 4px 0;
    }

    .social-picker-subtitle {
      font-size: 12px;
      color: #64748B;
      line-height: 1.45;
      margin: 0;
      max-width: 290px;
    }

    .social-picker-list {
      display: flex;
      flex-direction: column;
      gap: 9px;
    }

    .social-picker-item {
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: inherit;
      transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      cursor: pointer;
    }

    .social-picker-item:hover {
      transform: translateY(-2px);
      border-color: #CBD5E1;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
    }

    .social-picker-item-avatar {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      color: #FFFFFF;
    }

    .social-picker-item-avatar svg {
      width: 18px;
      height: 18px;
    }

    .social-picker-item-info {
      flex: 1;
      min-width: 0;
    }

    .social-picker-item-name {
      font-size: 13px;
      font-weight: 600;
      color: #0F172A;
      margin-bottom: 2px;
    }

    .social-picker-item-sub {
      font-size: 11px;
      color: #64748B;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .social-picker-item-arrow {
      color: #94A3B8;
      display: flex;
      align-items: center;
    }

    .chat-identity-banner {
      padding: 8px 12px;
      background: #F8FAFC;
      border-bottom: 1px solid #E2E8F0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-size: 11px;
      color: #475569;
      flex-shrink: 0;
    }

    .chat-identity-banner input {
      flex: 1;
      padding: 4px 8px;
      font-size: 11px;
      border: 1px solid #CBD5E1;
      border-radius: 6px;
      outline: none;
    }

    .chat-identity-banner button {
      padding: 4px 8px;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      border-radius: 6px;
      font-size: 10px;
      font-weight: 600;
      cursor: pointer;
    }

    .welcome-footer {
      text-align: center;
      font-size: 11px;
      color: #94A3B8;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    /* ==========================================================================
       4. STAGE 2: CHAT UTAMA (ACTIVE THREAD)
       ========================================================================== */
    .stage-chat {
      display: none;
      flex-direction: column;
      height: 100%;
      background: #FFFFFF;
    }

    .chat-header {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-shrink: 0;
    }

    .chat-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chat-back-btn, .chat-close-btn {
      background: none;
      border: none;
      color: var(--chat-primary-text);
      opacity: 0.85;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4px;
      border-radius: 6px;
      transition: opacity 0.15s, background 0.15s;
    }

    .chat-back-btn:hover, .chat-close-btn:hover {
      opacity: 1;
      background: rgba(255, 255, 255, 0.15);
    }

    .chat-header-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .header-online-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: #10B981;
      position: absolute;
      bottom: 0;
      right: 0;
      border: 2px solid var(--chat-primary);
    }

    .chat-header-info {
      display: flex;
      flex-direction: column;
    }

    .chat-header-title {
      font-size: 13px;
      font-weight: 700;
      line-height: 1.2;
    }

    .chat-header-status {
      font-size: 11px;
      opacity: 0.85;
    }

    /* MESSAGES THREAD AREA */
    .chat-messages-area {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: #F8FAFC;
    }

    .msg-bubble-row {
      display: flex;
      flex-direction: column;
      max-width: 82%;
    }

    .msg-bubble-row.is-visitor {
      align-self: flex-end;
      align-items: flex-end;
    }

    .msg-bubble-row.is-agent {
      align-self: flex-start;
      align-items: flex-start;
    }

    .msg-sender-name {
      font-size: 10px;
      font-weight: 600;
      color: #94A3B8;
      margin-bottom: 2px;
      padding: 0 4px;
    }

    .msg-bubble {
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 13px;
      line-height: 1.5;
      word-break: break-word;
      position: relative;
      white-space: pre-line;
    }

    .msg-bubble a {
      color: #0071E3;
      text-decoration: underline;
      font-weight: 500;
    }

    .msg-bubble strong {
      font-weight: 700;
    }

    .msg-bubble-row.is-visitor .msg-bubble {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border-bottom-right-radius: 3px;
    }

    .msg-bubble-row.is-agent .msg-bubble {
      background: #FFFFFF;
      color: #0F172A;
      border: 1px solid #E2E8F0;
      border-bottom-left-radius: 3px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* INTERACTIVE QUICK-REPLY OPTION BUTTONS */
    .msg-options-container {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-top: 8px;
      width: 100%;
    }

    .msg-option-btn {
      background: #FFFFFF;
      border: 1.5px solid var(--chat-primary, #1A1A1A);
      color: var(--chat-primary, #1A1A1A);
      padding: 9px 13px;
      border-radius: 10px;
      font-size: 12.5px;
      font-weight: 600;
      text-align: left;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      transition: all 0.16s ease;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
      outline: none;
      user-select: none;
      line-height: 1.35;
    }

    .msg-option-btn:hover:not(:disabled) {
      background: var(--chat-primary, #1A1A1A);
      color: var(--chat-primary-text, #FFFFFF);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .msg-option-btn:active:not(:disabled) {
      transform: translateY(0);
    }

    .msg-option-btn.is-selected {
      background: var(--chat-primary, #1A1A1A);
      color: var(--chat-primary-text, #FFFFFF);
      border-color: var(--chat-primary, #1A1A1A);
      opacity: 0.95;
    }

    .msg-option-btn:disabled:not(.is-selected) {
      opacity: 0.55;
      cursor: default;
      border-color: #CBD5E1;
      color: #64748B;
      background: #F8FAFC;
    }

    .msg-option-arrow {
      font-size: 13px;
      font-weight: bold;
      opacity: 0.6;
      transition: transform 0.15s ease;
    }

    .msg-option-btn:hover .msg-option-arrow {
      opacity: 1;
      transform: translateX(2px);
    }

    .msg-time-status {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 10px;
      color: #94A3B8;
      margin-top: 3px;
      padding: 0 4px;
    }

    .typing-indicator {
      display: none;
      align-self: flex-start;
      padding: 8px 12px;
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      gap: 4px;
      align-items: center;
    }

    .typing-dot {
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: #94A3B8;
      animation: typingBounce 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBounce {
      0%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-5px); }
    }

    /* COMPOSER BAR */
    .chat-composer-box {
      padding: 10px 14px;
      padding-bottom: max(10px, env(safe-area-inset-bottom, 10px));
      background: #FFFFFF;
      border-top: 1px solid #E2E8F0;
      display: flex;
      align-items: flex-end;
      gap: 8px;
      flex-shrink: 0;
      box-sizing: border-box;
    }

    .composer-textarea {
      flex: 1;
      border: none;
      resize: none;
      max-height: 90px;
      height: 24px;
      font-family: inherit;
      font-size: 13px;
      line-height: 1.4;
      color: #0F172A;
      padding: 2px 0;
      box-sizing: border-box;
    }

    .composer-textarea::placeholder {
      color: #94A3B8;
    }

    .composer-send-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s, transform 0.15s;
      flex-shrink: 0;
    }

    .composer-send-btn:hover {
      background: var(--chat-primary-hover);
      transform: scale(1.05);
    }

    .composer-send-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      transform: none;
    }

    /* ==========================================================================
       TICKET HISTORY & RESOLVED CHAT CONTROLS
       ========================================================================== */
    .card-tickets-container {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .tickets-section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 4px;
      font-size: 11px;
      font-weight: 700;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .ticket-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 190px;
      overflow-y: auto;
    }

    .ticket-item {
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      padding: 10px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      cursor: pointer;
      transition: background 0.15s, border-color 0.15s, transform 0.12s;
    }

    .ticket-item:hover {
      background: #F8FAFC;
      border-color: #CBD5E1;
      transform: translateY(-1px);
    }

    .ticket-item-left {
      flex: 1;
      min-width: 0;
    }

    .ticket-item-title {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 700;
      color: #0F172A;
      margin-bottom: 2px;
    }

    .ticket-item-snippet {
      font-size: 11px;
      color: #64748B;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ticket-status-badge {
      font-size: 9.5px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .ticket-status-open {
      background: #ECFDF5;
      color: #059669;
      border: 1px solid #A7F3D0;
    }

    .ticket-status-closed {
      background: #F1F5F9;
      color: #64748B;
      border: 1px solid #E2E8F0;
    }

    .btn-new-ticket {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 12.5px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: opacity 0.15s, transform 0.12s;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      width: 100%;
    }

    .btn-new-ticket:hover {
      opacity: 0.92;
      transform: translateY(-1px);
    }

    .btn-new-ticket:active {
      transform: translateY(0);
    }

    .chat-end-btn {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.35);
      color: var(--chat-primary-text);
      font-size: 10.5px;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 7px;
      cursor: pointer;
      transition: background 0.15s, opacity 0.15s;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .chat-end-btn:hover {
      background: rgba(255, 255, 255, 0.35);
    }

    .chat-resolved-banner {
      background: #F8FAFC;
      border-top: 1px solid #E2E8F0;
      padding: 14px 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      text-align: center;
      flex-shrink: 0;
      box-sizing: border-box;
    }

    .chat-resolved-text {
      font-size: 12px;
      color: #64748B;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .chat-resolved-text svg {
      color: #10B981;
    }

    .chat-start-new-btn {
      background: var(--chat-primary);
      color: var(--chat-primary-text);
      border: none;
      border-radius: 10px;
      padding: 10px 18px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: opacity 0.15s, transform 0.12s;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .chat-start-new-btn:hover {
      opacity: 0.92;
      transform: translateY(-1px);
    }

    .chat-start-new-btn:active {
      transform: translateY(0);
    }

    /* ==========================================================================
       5. MOBILE RESPONSIVENESS (< 640px)
       ========================================================================== */
    @media (max-width: 640px) {
      .chat-wrapper {
        bottom: 16px;
        right: 16px;
      }

      .chat-wrapper.is-open {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        width: 100% !important;
        height: 100% !important;
        height: 100dvh !important;
        max-width: 100% !important;
        max-height: 100dvh !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 2147483647 !important;
        align-items: stretch !important;
        pointer-events: auto !important;
      }

      .chat-wrapper.is-open .chat-window {
        position: fixed !important;
        top: var(--bt-viewport-top, 0px) !important;
        left: 0 !important;
        right: 0 !important;
        bottom: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        height: var(--bt-viewport-height, 100dvh) !important;
        max-height: var(--bt-viewport-height, 100dvh) !important;
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        overscroll-behavior: contain;
      }

      .chat-wrapper.is-open .chat-launcher-btn {
        display: none !important;
      }

      /* iOS Auto-Zoom Prevention: min 16px on inputs */
      .composer-textarea, 
      .identity-name-input, 
      .identity-email-input,
      .chat-identity-banner input {
        font-size: 16px !important;
      }

      .chat-composer-box {
        padding: 8px 12px calc(8px + env(safe-area-inset-bottom, 0px)) 12px;
      }

      .welcome-header {
        padding-top: max(20px, calc(12px + env(safe-area-inset-top, 0px)));
      }

      .identity-stage-header {
        padding-top: max(14px, calc(10px + env(safe-area-inset-top, 0px)));
      }

      .chat-header {
        padding-top: max(12px, calc(8px + env(safe-area-inset-top, 0px)));
      }

      .chat-messages-area {
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
      }
    }
  `;
}

function adjustColor(hex: string, percent: number): string {
  let num = parseInt(hex.replace('#', ''), 16);
  let r = (num >> 16) + percent;
  let g = ((num >> 8) & 0x00ff) + percent;
  let b = (num & 0x0000ff) + percent;

  r = Math.min(255, Math.max(0, r));
  g = Math.min(255, Math.max(0, g));
  b = Math.min(255, Math.max(0, b));

  return '#' + (g | (b << 8) | (r << 16)).toString(16).padStart(6, '0');
}
