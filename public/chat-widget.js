"use strict";var BeanTalk=(()=>{var b=Object.defineProperty;var M=Object.getOwnPropertyDescriptor;var T=Object.getOwnPropertyNames;var C=Object.prototype.hasOwnProperty;var F=(s,e)=>{for(var t in e)b(s,t,{get:e[t],enumerable:!0})},A=(s,e,t,i)=>{if(e&&typeof e=="object"||typeof e=="function")for(let a of T(e))!C.call(s,a)&&a!==t&&b(s,a,{get:()=>e[a],enumerable:!(i=M(e,a))||i.enumerable});return s};var I=s=>A(b({},"__esModule",{value:!0}),s);var j={};F(j,{BeanTalk:()=>d,ChatWidget:()=>h,UniversalChatMe:()=>H,default:()=>D});var g=class{constructor(){this.events={}}on(e,t){return this.events[e]||(this.events[e]=[]),this.events[e].push(t),this}off(e,t){return this.events[e]?(this.events[e]=this.events[e].filter(i=>i!==t),this):this}emit(e,t){this.events[e]&&this.events[e].forEach(i=>{try{i(t)}catch(a){console.error(`[BeanTalk] Error in event handler for "${e}":`,a)}})}};var m=class{constructor(e,t=""){this.projectKey=e,this.baseUrl=t.replace(/\/+$/,"")}async request(e,t={}){let i=`${this.baseUrl}${e}`,a={Accept:"application/json","Content-Type":"application/json","X-Project-Key":this.projectKey,...t.headers||{}},n=new AbortController,l=setTimeout(()=>n.abort(),12e3);try{let c=await fetch(i,{...t,headers:a,signal:n.signal});return clearTimeout(l),await c.json()}catch(c){return clearTimeout(l),{success:!1,error:{code:c.name==="AbortError"?"TIMEOUT":"NETWORK_ERROR",message:c.message||"Gagal terhubung ke server chat."}}}}async initSession(e,t){return this.request("/api/v1/client/session/init",{method:"POST",body:JSON.stringify({visitor_uuid:e,project_key:this.projectKey,client_url:window.location.href,metadata:{referrer:document.referrer,userAgent:navigator.userAgent,title:document.title,...t}})})}async pollMessages(e,t=0){return this.request(`/api/v1/client/conversations/${e}/messages?after_id=${t}`,{method:"GET"})}async sendMessage(e,t){return this.request(`/api/v1/client/conversations/${e}/messages`,{method:"POST",body:JSON.stringify({client_message_id:t.client_message_id,content:t.message,message:t.message,sender_name:t.sender_name})})}};var y="beantalk_visitor_uuid",B="beantalk_last_conv_id",w={};function R(s){try{return window.localStorage.getItem(s)}catch{return w[s]||null}}function E(s,e){try{window.localStorage.setItem(s,e)}catch{w[s]=e}}function _(){return typeof crypto<"u"&&typeof crypto.randomUUID=="function"?crypto.randomUUID():"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,s=>{let e=Math.random()*16|0;return(s==="x"?e:e&3|8).toString(16)})}function p(){return"msg_"+Date.now().toString(36)+"_"+Math.random().toString(36).substring(2,9)}function k(){let s=R(y);return s||(s=_(),E(y,s)),s}function S(s){E(B,s.toString())}var u=class{constructor(e,t,i={}){this.conversationId=null;this.lastMessageId=0;this.isRunning=!1;this.isPolling=!1;this.timer=null;this.isWindowVisible=!0;this.isOnline=!0;this.isWidgetOpen=!1;this.api=e,this.emitter=t,this.activeIntervalMs=i.activeIntervalMs||2500,this.idleIntervalMs=i.idleIntervalMs||15e3,this.setupListeners()}setupListeners(){typeof document<"u"&&document.addEventListener("visibilitychange",()=>{let e=document.visibilityState==="visible";this.isWindowVisible=e,e&&this.isRunning&&this.pollNow()}),typeof window<"u"&&(window.addEventListener("online",()=>{this.isOnline=!0,this.isRunning&&this.pollNow()}),window.addEventListener("offline",()=>{this.isOnline=!1,this.clearTimer()}))}setConversation(e,t=0){this.conversationId=e,t>this.lastMessageId&&(this.lastMessageId=t)}setWidgetOpen(e){this.isWidgetOpen=e,this.isRunning&&this.reschedule()}start(e,t=0){this.conversationId=e,this.lastMessageId=Math.max(this.lastMessageId,t),this.isRunning=!0,this.reschedule()}stop(){this.isRunning=!1,this.clearTimer()}clearTimer(){this.timer&&(clearTimeout(this.timer),this.timer=null)}reschedule(){if(this.clearTimer(),!this.isRunning||!this.isOnline)return;let e=this.isWindowVisible&&this.isWidgetOpen?this.activeIntervalMs:this.idleIntervalMs;this.timer=setTimeout(()=>{this.executePoll()},e)}async pollNow(){this.clearTimer(),await this.executePoll()}async executePoll(){if(!this.isRunning||!this.conversationId||!this.isOnline||this.isPolling){this.reschedule();return}this.isPolling=!0;try{let e=await this.api.pollMessages(this.conversationId,this.lastMessageId);if(e.success&&e.data){let{messages:t,last_id:i}=e.data;if(Array.isArray(t)&&t.length>0){let a=t.filter(n=>n.id>this.lastMessageId);a.length>0&&a.forEach(n=>{this.emitter.emit("message:received",n)}),i&&i>this.lastMessageId&&(this.lastMessageId=i)}}}catch(e){this.emitter.emit("poll:error",e)}finally{this.isPolling=!1,this.reschedule()}}};var o={chat:`<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
  </svg>`,close:`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="18" y1="6" x2="6" y2="18"></line>
    <line x1="6" y1="6" x2="18" y2="18"></line>
  </svg>`,back:`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="19" y1="12" x2="5" y2="12"></line>
    <polyline points="12 19 5 12 12 5"></polyline>
  </svg>`,send:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="22" y1="2" x2="11" y2="13"></line>
    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
  </svg>`,chevronRight:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="9 18 15 12 9 6"></polyline>
  </svg>`,paperclip:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
  </svg>`,whatsapp:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
  </svg>`,messenger:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
    <polygon points="12 8 8 16 12 13 16 16 12 8" fill="currentColor"/>
  </svg>`,instagram:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
  </svg>`,sparkles:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
  </svg>`,check:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="20 6 9 17 4 12"></polyline>
  </svg>`,agentAvatar:`<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
    <circle cx="12" cy="7" r="4"></circle>
  </svg>`};function f(s="#1E1E1E"){return`
    :host {
      all: initial;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 14px;
      line-height: 1.5;
      color: #0F172A;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
      --chat-primary: ${s};
      --chat-primary-hover: ${L(s,-15)};
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
    }

    .chat-wrapper.pos-bottom-left {
      right: auto;
      left: 24px;
      align-items: flex-start;
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
      position: relative;
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
      margin-top: -18px;
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

    /* CARD 2: SOCIAL REACH US */
    .card-social-reach {
      background: #FFFFFF;
      border-radius: 14px;
      padding: 14px 16px;
      border: 1px solid #E2E8F0;
    }

    .social-reach-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #94A3B8;
      margin-bottom: 10px;
    }

    .social-channel-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .social-channel-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 10px;
      border-radius: 8px;
      text-decoration: none;
      background: #F8FAFC;
      border: 1px solid #EDF2F7;
      color: #1E293B;
      font-size: 12px;
      font-weight: 500;
      transition: background 0.15s, border-color 0.15s;
    }

    .social-channel-item:hover {
      background: #F1F5F9;
      border-color: #CBD5E1;
    }

    .social-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .social-icon-box {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #FFFFFF;
    }

    .social-icon-box.whatsapp { background: #25D366; }
    .social-icon-box.messenger { background: #0084FF; }
    .social-icon-box.instagram { background: linear-gradient(45deg, #F09433 0%, #E6683C 25%, #DC2743 50%, #CC2366 75%, #BC1888 100%); }

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
      line-height: 1.45;
      word-break: break-word;
      position: relative;
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
      background: #FFFFFF;
      border-top: 1px solid #E2E8F0;
      display: flex;
      align-items: flex-end;
      gap: 8px;
      flex-shrink: 0;
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
       5. MOBILE RESPONSIVENESS (< 640px)
       ========================================================================== */
    @media (max-width: 640px) {
      .chat-wrapper {
        bottom: 16px;
        right: 16px;
      }
      .chat-wrapper.is-open .chat-window {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
        border: none;
      }
      .chat-wrapper.is-open .chat-launcher-btn {
        display: none;
      }
    }
  `}function L(s,e){let t=parseInt(s.replace("#",""),16),i=(t>>16)+e,a=(t>>8&255)+e,n=(t&255)+e;return i=Math.min(255,Math.max(0,i)),a=Math.min(255,Math.max(0,a)),n=Math.min(255,Math.max(0,n)),"#"+(a|n<<8|i<<16).toString(16).padStart(6,"0")}var v=class{constructor(e,t){this.isOpen=!1;this.currentStage="welcome";this.unreadCount=0;this.messages=[];this.sessionData=null;this.options=e,this.emitter=t;let i=document.getElementById("beantalk-chat-root")||document.getElementById("universal-chat-root");i||(i=document.createElement("div"),i.id="beantalk-chat-root",document.body.appendChild(i)),this.shadowRoot=i.attachShadow({mode:"open"}),this.styleEl=document.createElement("style"),this.styleEl.textContent=f(e.accentColor||"#1E1E1E"),this.shadowRoot.appendChild(this.styleEl),this.renderSkeleton(),this.bindEvents()}updateTheming(e){this.styleEl&&(this.styleEl.textContent=f(e))}setSessionData(e){this.sessionData=e;let t=e.widget||e.widget_settings||{};t.primary_color&&this.updateTheming(t.primary_color);let i=t.greeting_title||t.header_title||e.project?.name||"Chat Support",a=t.greeting_subtitle||t.greeting_text||"Hallo! Ada yang bisa kami bantu? Tanyakan apapun di sini.",n=this.shadowRoot.querySelector(".welcome-title");n&&(n.textContent=i);let l=this.shadowRoot.querySelector(".welcome-subtitle");l&&(l.textContent=a);let c=this.shadowRoot.querySelector(".chat-header-title");c&&(c.textContent=e.project?.name||"Store Support");let x=this.shadowRoot.querySelector(".welcome-brand-badge");x&&(x.textContent=e.project?.name||"Live Support"),e.conversation?.messages&&e.conversation.messages.length>0&&this.setMessages(e.conversation.messages)}renderSkeleton(){let e=this.options.storeName||"Store Support",t=this.options.greetingTitle||"Hallo!",i=this.options.greetingSubtitle||"Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini.",n=`
      <div class="chat-wrapper ${this.options.position==="bottom-left"?"pos-bottom-left":""}">
        <!-- WIDGET WINDOW -->
        <div class="chat-window">
          
          <!-- ================= STAGE 1: WELCOME HUB ================= -->
          <div class="stage-welcome">
            <div class="welcome-header">
              <div class="welcome-header-top">
                <span class="welcome-brand-badge">${e}</span>
                <button type="button" class="welcome-close-btn" aria-label="Tutup">${o.close}</button>
              </div>
              <h2 class="welcome-title">${t}</h2>
              <p class="welcome-subtitle">${i}</p>
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
                    ${o.agentAvatar}
                  </div>
                  <div class="card-chat-info">
                    <div class="card-chat-name">${e}</div>
                    <div class="card-chat-snippet" id="card-snippet">Mulai obrolan baru dengan agen kami...</div>
                  </div>
                  <div class="card-chat-chevron">
                    ${o.chevronRight}
                  </div>
                </div>
              </div>

              <!-- CARD 2: REACH US ELSEWHERE -->
              <div class="card-social-reach">
                <div class="social-reach-title">Atau Hubungi Kami Lewat</div>
                <div class="social-channel-list">
                  <a href="${this.options.whatsappNumber?"https://wa.me/"+this.options.whatsappNumber:"#"}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box whatsapp">${o.whatsapp}</div>
                      <span>WhatsApp CS Resmi</span>
                    </div>
                    <span style="color:#94A3B8;">${o.chevronRight}</span>
                  </a>
                  <a href="${this.options.instagramHandle?"https://instagram.com/"+this.options.instagramHandle:"#"}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box instagram">${o.instagram}</div>
                      <span>Instagram Direct</span>
                    </div>
                    <span style="color:#94A3B8;">${o.chevronRight}</span>
                  </a>
                  <a href="${this.options.messengerUrl||"#"}" target="_blank" class="social-channel-item">
                    <div class="social-left">
                      <div class="social-icon-box messenger">${o.messenger}</div>
                      <span>Facebook Messenger</span>
                    </div>
                    <span style="color:#94A3B8;">${o.chevronRight}</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="welcome-footer">
              ${o.sparkles} <span>Powered by BeanTalk</span>
            </div>
          </div>

          <!-- ================= STAGE 2: CHAT UTAMA ================= -->
          <div class="stage-chat">
            <div class="chat-header">
              <div class="chat-header-left">
                <button type="button" class="chat-back-btn" aria-label="Kembali">${o.back}</button>
                <div class="chat-header-avatar">
                  ${o.agentAvatar}
                  <span class="header-online-dot"></span>
                </div>
                <div class="chat-header-info">
                  <div class="chat-header-title">${e}</div>
                  <div class="chat-header-status">Online \u2022 Membalas dalam hitungan menit</div>
                </div>
              </div>
              <button type="button" class="chat-close-btn" aria-label="Tutup">${o.close}</button>
            </div>

            <!-- MESSAGES THREAD -->
            <div class="chat-messages-area">
              <!-- Dynamically populated -->
            </div>

            <!-- COMPOSER BAR -->
            <div class="chat-composer-box">
              <textarea class="composer-textarea" placeholder="Tulis pesan ke CS..." rows="1"></textarea>
              <button type="button" class="composer-send-btn" aria-label="Kirim" disabled>${o.send}</button>
            </div>
          </div>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" class="chat-launcher-btn" aria-label="Buka Chat">
          <div class="launcher-icon-chat">${o.chat}</div>
          <div class="launcher-icon-close">${o.close}</div>
          <div class="launcher-unread-badge">0</div>
        </button>
      </div>
    `,l=document.createElement("div");l.innerHTML=n,this.shadowRoot.appendChild(l.firstElementChild),this.wrapperEl=this.shadowRoot.querySelector(".chat-wrapper"),this.launcherBtn=this.shadowRoot.querySelector(".chat-launcher-btn"),this.unreadBadge=this.shadowRoot.querySelector(".launcher-unread-badge"),this.stageWelcome=this.shadowRoot.querySelector(".stage-welcome"),this.stageChat=this.shadowRoot.querySelector(".stage-chat"),this.messagesArea=this.shadowRoot.querySelector(".chat-messages-area"),this.composerInput=this.shadowRoot.querySelector(".composer-textarea"),this.composerSendBtn=this.shadowRoot.querySelector(".composer-send-btn"),this.cardSnippetText=this.shadowRoot.querySelector("#card-snippet")}bindEvents(){this.launcherBtn.addEventListener("click",()=>{this.toggle()}),this.shadowRoot.querySelectorAll(".welcome-close-btn, .chat-close-btn").forEach(i=>{i.addEventListener("click",()=>this.close())});let e=this.shadowRoot.querySelector(".card-active-chat");e&&e.addEventListener("click",()=>{this.goToStage("chat")});let t=this.shadowRoot.querySelector(".chat-back-btn");t&&t.addEventListener("click",()=>{this.goToStage("welcome")}),this.composerInput.addEventListener("input",()=>{this.composerInput.style.height="auto",this.composerInput.style.height=Math.min(this.composerInput.scrollHeight,90)+"px";let i=this.composerInput.value.trim().length>0;this.composerSendBtn.disabled=!i}),this.composerInput.addEventListener("keydown",i=>{i.key==="Enter"&&!i.shiftKey&&(i.preventDefault(),this.handleSend())}),this.composerSendBtn.addEventListener("click",()=>{this.handleSend()})}handleSend(){let e=this.composerInput.value.trim();if(!e)return;this.composerInput.value="",this.composerInput.style.height="24px",this.composerSendBtn.disabled=!0;let t={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:p(),sender_type:"visitor",sender_name:"Anda",message:e,created_at:new Date().toISOString()};this.appendMessage(t),this.emitter.emit("ui:send",t)}goToStage(e){this.currentStage=e,e==="welcome"?(this.stageWelcome.style.display="flex",this.stageChat.style.display="none"):(this.stageWelcome.style.display="none",this.stageChat.style.display="flex",this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150))}open(){this.isOpen=!0,this.wrapperEl.classList.add("is-open"),this.unreadCount=0,this.updateUnreadBadge(),this.emitter.emit("widget:opened"),this.currentStage==="chat"&&(this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150))}close(){this.isOpen=!1,this.wrapperEl.classList.remove("is-open"),this.emitter.emit("widget:closed")}toggle(){this.isOpen?this.close():this.open()}setMessages(e){this.messages=[...e],this.messagesArea.innerHTML="",e.forEach(t=>this.renderMessageBubble(t)),this.updateSnippet(),this.scrollToBottom()}appendMessage(e){this.messages.some(i=>i.id===e.id||e.client_message_id&&i.client_message_id===e.client_message_id)||(this.messages.push(e),this.renderMessageBubble(e),this.updateSnippet(),this.scrollToBottom(),!this.isOpen&&e.sender_type!=="visitor"&&(this.unreadCount++,this.updateUnreadBadge()))}renderMessageBubble(e){let t=e.sender_type==="visitor",i=document.createElement("div");i.className=`msg-bubble-row ${t?"is-visitor":"is-agent"}`;let a=this.formatTime(e.created_at),n=e.content||e.message||"";i.innerHTML=`
      <div class="msg-sender-name">${t?"Anda":e.sender_name||"Agent"}</div>
      <div class="msg-bubble">${O(n)}</div>
      <div class="msg-time-status">
        <span>${a}</span>
        ${t?`<span style="display:inline-flex;">${o.check}</span>`:""}
      </div>
    `,this.messagesArea.appendChild(i)}updateSnippet(){if(this.messages.length>0){let e=this.messages[this.messages.length-1],t=e.sender_type==="visitor"?"Anda: ":"",i=e.content||e.message||"";this.cardSnippetText.textContent=t+i}}updateUnreadBadge(){this.unreadCount>0?(this.unreadBadge.textContent=this.unreadCount>9?"9+":this.unreadCount.toString(),this.unreadBadge.classList.add("has-unread")):this.unreadBadge.classList.remove("has-unread")}scrollToBottom(){setTimeout(()=>{this.messagesArea.scrollTop=this.messagesArea.scrollHeight},40)}formatTime(e){try{return new Date(e).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}catch{return""}}};function O(s){return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}var d=class{constructor(e){this.sessionData=null;this.initialized=!1;this.options=e,this.emitter=new g;let t=e.apiUrl||window.location.origin;this.api=new m(e.projectKey,t),this.ui=new v(e,this.emitter),this.transport=new u(this.api,this.emitter),this.bindEvents(),this.bootstrap()}isReady(){return this.initialized}bindEvents(){this.emitter.on("ui:send",async e=>{if(!this.sessionData?.conversation?.id){console.error("[BeanTalk] Percakapan belum diinisialisasi.");return}try{let t=await this.api.sendMessage(this.sessionData.conversation.id,{client_message_id:e.client_message_id||p(),message:e.content||e.message||"",sender_name:"Visitor"});t.success&&t.data&&(this.emitter.emit("message:sent",t.data),this.transport.pollNow())}catch(t){console.error("[BeanTalk] Gagal mengirim pesan:",t)}}),this.emitter.on("message:received",e=>{this.ui.appendMessage(e),this.emitter.emit("message",e)}),this.emitter.on("widget:opened",()=>{this.transport.setWidgetOpen(!0),this.transport.pollNow()}),this.emitter.on("widget:closed",()=>{this.transport.setWidgetOpen(!1)})}async bootstrap(){let e=this.options.visitorUuid||k();try{let t=await this.api.initSession(e);if(t.success&&t.data){this.sessionData=t.data;let i=t.data.conversation;if(i){S(i.id),this.ui.setSessionData(t.data);let a=0;i.messages&&i.messages.length>0&&(a=Math.max(...i.messages.map(n=>n.id))),this.transport.start(i.id,a)}this.initialized=!0,this.emitter.emit("ready",t.data)}else console.warn("[BeanTalk] Init session warning:",t.error?.message)}catch(t){console.error("[BeanTalk] Failed to initialize chat session:",t)}}open(){this.ui.open()}close(){this.ui.close()}toggle(){this.ui.toggle()}on(e,t){return this.emitter.on(e,t),this}sendMessage(e){if(!e.trim())return;let t={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:p(),sender_type:"visitor",sender_name:"Anda",content:e.trim(),message:e.trim(),created_at:new Date().toISOString()};this.ui.appendMessage(t),this.emitter.emit("ui:send",t)}},H=d,r=null,h={init(s){return r||(r=new d(s)),r},open(){r?.open()},close(){r?.close()},toggle(){r?.toggle()},on(s,e){r?.on(s,e)},sendMessage(s){r?.sendMessage(s)},getInstance(){return r}};typeof window<"u"&&(window.BeanTalk=h,window.ChatWidget=h,window.UniversalChatMe=d,document.addEventListener("DOMContentLoaded",()=>{let s=document.querySelectorAll("script[data-project-key]");if(s.length>0){let e=s[0],t=e.getAttribute("data-project-key"),i=e.getAttribute("data-api-url")||void 0,a=e.getAttribute("data-color")||void 0,n=e.getAttribute("data-store-name")||void 0;t&&!r&&h.init({projectKey:t,apiUrl:i,accentColor:a,storeName:n})}}));var D=h;return I(j);})();
