"use strict";var BeanTalk=(()=>{var S=Object.defineProperty;var q=Object.getOwnPropertyDescriptor;var V=Object.getOwnPropertyNames;var G=Object.prototype.hasOwnProperty;var K=(n,e)=>{for(var t in e)S(n,t,{get:e[t],enumerable:!0})},Y=(n,e,t,i)=>{if(e&&typeof e=="object"||typeof e=="function")for(let s of V(e))!G.call(n,s)&&s!==t&&S(n,s,{get:()=>e[s],enumerable:!(i=q(e,s))||i.enumerable});return n};var Q=n=>Y(S({},"__esModule",{value:!0}),n);var he={};K(he,{BeanTalk:()=>g,ChatWidget:()=>d,UniversalChatMe:()=>ie,close:()=>ae,default:()=>ce,getInstance:()=>de,init:()=>ne,on:()=>re,open:()=>se,sendMessage:()=>le,toggle:()=>oe});var x=class{constructor(){this.events={}}on(e,t){return this.events[e]||(this.events[e]=[]),this.events[e].push(t),this}off(e,t){return this.events[e]?(this.events[e]=this.events[e].filter(i=>i!==t),this):this}emit(e,t){this.events[e]&&this.events[e].forEach(i=>{try{i(t)}catch(s){console.error(`[BeanTalk] Error in event handler for "${e}":`,s)}})}};var b=class{constructor(e,t=""){this.projectKey=e,this.baseUrl=t.replace(/\/+$/,"")}async request(e,t={}){let i=`${this.baseUrl}${e}`,s={Accept:"application/json","Content-Type":"application/json","X-Project-Key":this.projectKey,...t.headers||{}},o=new AbortController,r=setTimeout(()=>o.abort(),12e3);try{let a=await fetch(i,{...t,headers:s,signal:o.signal});return clearTimeout(r),await a.json()}catch(a){return clearTimeout(r),{success:!1,error:{code:a.name==="AbortError"?"TIMEOUT":"NETWORK_ERROR",message:a.message||"Gagal terhubung ke server chat."}}}}async initSession(e,t,i){return this.request("/api/v1/client/session/init",{method:"POST",body:JSON.stringify({visitor_uuid:e,name:i,project_key:this.projectKey,page_url:window.location.href,page_title:document.title,client_url:window.location.href,metadata:{referrer:document.referrer,userAgent:navigator.userAgent,title:document.title,...t}})})}async updateProfile(e,t){return this.request("/api/v1/client/session/profile",{method:"POST",body:JSON.stringify({visitor_uuid:e,name:t})})}async pollMessages(e,t=0){return this.request(`/api/v1/client/conversations/${e}/messages?after_id=${t}`,{method:"GET"})}async sendMessage(e,t){let i=e&&e>0?e:0;return this.request(`/api/v1/client/conversations/${i}/messages`,{method:"POST",body:JSON.stringify({visitor_uuid:t.visitor_uuid,client_message_id:t.client_message_id,content:t.message,message:t.message,sender_name:t.sender_name,page_url:t.page_url,page_title:t.page_title})})}};var H="beantalk_visitor_uuid",J="beantalk_last_conv_id",O={};function D(n){try{return window.localStorage.getItem(n)}catch{return O[n]||null}}function M(n,e){try{window.localStorage.setItem(n,e)}catch{O[n]=e}}function X(){return typeof crypto<"u"&&typeof crypto.randomUUID=="function"?crypto.randomUUID():"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,n=>{let e=Math.random()*16|0;return(n==="x"?e:e&3|8).toString(16)})}function v(){return"msg_"+Date.now().toString(36)+"_"+Math.random().toString(36).substring(2,9)}function y(){let n=D(H);return n||(n=X(),M(H,n)),n}function F(n){M(J,n.toString())}var j="beantalk_customer_name";function u(){return D(j)}function z(n){M(j,n)}var f=class{constructor(e,t,i={}){this.conversationId=null;this.lastMessageId=0;this.isRunning=!1;this.isPolling=!1;this.pollQueued=!1;this.timer=null;this.isWindowVisible=!0;this.isOnline=!0;this.isWidgetOpen=!1;this.burstRemaining=0;this.burstIntervalMs=1200;this.api=e,this.emitter=t,this.activeIntervalMs=i.activeIntervalMs||2500,this.idleIntervalMs=i.idleIntervalMs||15e3,this.setupListeners()}setupListeners(){typeof document<"u"&&document.addEventListener("visibilitychange",()=>{let e=document.visibilityState==="visible";this.isWindowVisible=e,e&&this.isRunning&&this.pollNow()}),typeof window<"u"&&(window.addEventListener("online",()=>{this.isOnline=!0,this.isRunning&&this.pollNow()}),window.addEventListener("offline",()=>{this.isOnline=!1,this.clearTimer()}))}setConversation(e,t=0){this.conversationId=e,t>this.lastMessageId&&(this.lastMessageId=t)}setWidgetOpen(e){this.isWidgetOpen=e,this.isRunning&&this.reschedule()}start(e,t=0){this.conversationId=e,this.lastMessageId=Math.max(this.lastMessageId,t),this.isRunning=!0,this.reschedule()}stop(){this.isRunning=!1,this.clearTimer()}clearTimer(){this.timer&&(clearTimeout(this.timer),this.timer=null)}getInterval(){return this.burstRemaining>0?this.burstIntervalMs:this.isWindowVisible&&this.isWidgetOpen?this.activeIntervalMs:this.idleIntervalMs}reschedule(){this.clearTimer(),!(!this.isRunning||!this.isOnline)&&(this.timer=setTimeout(()=>{this.executePoll()},this.getInterval()))}async pollNow(){if(this.isPolling){this.pollQueued=!0;return}this.clearTimer(),this.burstRemaining=3,await this.executePoll()}async executePoll(){if(!this.isRunning||!this.conversationId||!this.isOnline||this.isPolling){this.reschedule();return}this.isPolling=!0;try{let e=await this.api.pollMessages(this.conversationId,this.lastMessageId);if(e.success&&e.data){let{messages:t,last_id:i}=e.data;if(Array.isArray(t)&&t.length>0){let s=t.filter(o=>o.id>this.lastMessageId);s.length>0&&s.forEach(o=>{this.emitter.emit("message:received",o)}),i&&i>this.lastMessageId&&(this.lastMessageId=i)}}}catch(e){this.emitter.emit("poll:error",e)}finally{this.isPolling=!1,this.burstRemaining>0&&this.burstRemaining--,this.pollQueued?(this.pollQueued=!1,this.burstRemaining=2,this.clearTimer(),this.timer=setTimeout(()=>this.executePoll(),100)):this.reschedule()}}};var l={chat:`<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
  </svg>`,telegram:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
  </svg>`,shopee:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="9" cy="21" r="1"></circle>
    <circle cx="20" cy="21" r="1"></circle>
    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
  </svg>`,tokopedia:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
    <line x1="3" y1="6" x2="21" y2="6"></line>
    <path d="M16 10a4 4 0 0 1-8 0"></path>
  </svg>`,agentAvatar:`<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
    <circle cx="12" cy="7" r="4"></circle>
  </svg>`};function A(n="#1E1E1E"){return`
    :host {
      all: initial;
      display: block !important;
      position: relative;
      z-index: 2147483647;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 14px;
      line-height: 1.5;
      color: #0F172A;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
      --chat-primary: ${n};
      --chat-primary-hover: ${Z(n,-15)};
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
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .identity-name-input {
      width: 100%;
      padding: 11px 14px;
      font-size: 13px;
      border: 1.5px solid #CBD5E1;
      border-radius: 9px;
      color: #0F172A;
      outline: none;
      box-sizing: border-box;
      transition: border-color 0.15s, box-shadow 0.15s;
    }

    .identity-name-input:focus {
      border-color: var(--chat-primary);
      box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.18);
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

    .social-btn-messenger {
      background: linear-gradient(135deg, #00B2FF, #006AFF);
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
  `}function Z(n,e){let t=parseInt(n.replace("#",""),16),i=(t>>16)+e,s=(t>>8&255)+e,o=(t&255)+e;return i=Math.min(255,Math.max(0,i)),s=Math.min(255,Math.max(0,s)),o=Math.min(255,Math.max(0,o)),"#"+(s|o<<8|i<<16).toString(16).padStart(6,"0")}var w=class{constructor(e,t){this.isOpen=!1;this.currentStage="welcome";this.unreadCount=0;this.messages=[];this.sessionData=null;this.customerName="";this.customerCode="";this.audioCtx=null;this.options=e,this.emitter=t;let i=document.getElementById("beantalk-chat-root")||document.getElementById("universal-chat-root");i||(i=document.createElement("div"),i.id="beantalk-chat-root",i.style.position="relative",i.style.zIndex="2147483647",i.style.display="block",document.body.appendChild(i)),this.shadowRoot=i.attachShadow({mode:"open"}),this.styleEl=document.createElement("style"),this.styleEl.textContent=A(e.accentColor||"#1E1E1E"),this.shadowRoot.appendChild(this.styleEl),this.renderSkeleton(),this.bindEvents(),this.initViewportHandler();let s=u();s&&this.applyCustomerName(s,!1)}updateTheming(e){this.styleEl&&(this.styleEl.textContent=A(e))}setSessionData(e){this.sessionData=e;let t=e.widget||e.widget_settings||{};t.primary_color&&this.updateTheming(t.primary_color);let i=t.greeting_title||t.header_title||e.project?.name||"Chat Support",s=t.greeting_subtitle||t.greeting_text||"Hallo! Ada yang bisa kami bantu? Tanyakan apapun di sini.",o=this.shadowRoot.querySelector(".welcome-title");o&&(o.textContent=i);let r=this.shadowRoot.querySelector(".welcome-subtitle");r&&(r.textContent=s);let a=t.support_title||this.options.supportTitle||this.options.brandName||this.options.storeName||e.project?.name||"Customer Support",m=this.shadowRoot.querySelector("#cardChatName");m&&(m.textContent=a),this.identitySupportTag&&(this.identitySupportTag.textContent=a);let E=this.shadowRoot.querySelector(".chat-header-title");E&&(E.textContent=a);let B=this.shadowRoot.querySelector(".welcome-brand-badge");B&&(B.textContent=e.project?.name||"Live Support");let k=e.visitor||{},T=k.customer_code||k.customer_code_formatted;T&&(this.customerCode=T,this.identityCustCode&&(this.identityCustCode.textContent=T));let U=k.name,W=u(),L=U||W;L?this.applyCustomerName(L,!1):this.chatInlineIdentityBanner&&(this.chatInlineIdentityBanner.style.display="flex"),e.conversation?.messages&&e.conversation.messages.length>0&&this.setMessages(e.conversation.messages);let C=this.shadowRoot.querySelector("#socialChannelsCard"),R=this.shadowRoot.querySelector("#socialChannelsTitle"),I=this.shadowRoot.querySelector("#socialChannelsRow"),N=t.social_channels||[],P=t.find_us_title||"Reach Us Anywhere Else";if(R&&(R.textContent=P),C&&I){let _=Array.isArray(N)?N.filter(p=>p.enabled&&p.url):[];_.length>0?(I.innerHTML="",_.forEach(p=>{let h=document.createElement("a");h.className=`social-channel-btn social-btn-${p.id}`,h.href=p.url,h.target="_blank",h.rel="noopener noreferrer",h.title=`Hubungi via ${p.name}`;let $=l[p.icon||p.id]||l.chat;h.innerHTML=$,I.appendChild(h)}),C.style.display="block"):C.style.display="none"}}applyCustomerName(e,t=!0){let i=e.trim();i&&(this.customerName=i,t&&(z(i),this.emitter.emit("customer:rename",i)),this.identityNameInput&&(this.identityNameInput.value=i),this.chatInlineIdentityBanner&&(this.chatInlineIdentityBanner.style.display="none"),this.composerInput&&(this.composerInput.placeholder=`Tulis pesan sebagai ${i}...`))}renderSkeleton(){let e=this.options.brandName||this.options.storeName||"Customer Support",t=this.options.supportTitle||e||"Customer Support",i=this.options.greetingTitle||"Hallo!",s=this.options.greetingSubtitle||"Apakah ada yang bisa kami bantu? Tanyakan informasi apapun di sini.",r=`
      <div class="chat-wrapper ${this.options.position==="bottom-left"?"pos-bottom-left":""}">
        <!-- WIDGET WINDOW -->
        <div class="chat-window">
          
          <!-- ================= STAGE 1: WELCOME HUB ================= -->
          <div class="stage-welcome">
            <div class="welcome-header">
              <div class="welcome-header-top">
                <span class="welcome-brand-badge">${e}</span>
                <button type="button" class="welcome-close-btn" aria-label="Tutup">${l.close}</button>
              </div>
              <h2 class="welcome-title">${i}</h2>
              <p class="welcome-subtitle">${s}</p>
            </div>

            <div class="welcome-body">
              <!-- CARD 1: STORE SUPPORT (ACTIVE CHAT TRIGGER) -->
              <div class="card-active-chat" id="cardActiveChat">
                <div class="card-live-indicator">
                  <span class="live-dot"></span>
                  <span>Live Chat Available</span>
                </div>
                <div class="card-chat-row">
                  <div class="card-chat-avatar">
                    ${l.agentAvatar}
                  </div>
                  <div class="card-chat-info">
                    <div class="card-chat-name" id="cardChatName">${t}</div>
                    <div class="card-chat-snippet" id="card-snippet">Mulai obrolan baru dengan tim kami...</div>
                  </div>
                  <div class="card-chat-chevron">
                    ${l.chevronRight}
                  </div>
                </div>
              </div>

              <!-- CARD 2: REACH US ANYWHERE ELSE / FIND US SOMEWHERE ELSE -->
              <div class="card-social-channels" id="socialChannelsCard" style="display: none;">
                <div class="social-channels-header">
                  <span class="social-channels-title" id="socialChannelsTitle">Reach Us Anywhere Else</span>
                </div>
                <div class="social-channels-row" id="socialChannelsRow">
                  <!-- Populated dynamically from server settings -->
                </div>
              </div>
            </div>

            <div class="welcome-footer">
              ${l.sparkles} <span>Powered by BeanTalk \u2022 Web Chat</span>
            </div>
          </div>

          <!-- ================= STAGE 1.5: FORM PEMANGGILAN NAMA ================= -->
          <div class="stage-identity" style="display: none;">
            <div class="identity-stage-header">
              <button type="button" class="identity-back-btn" id="identityBackBtn" aria-label="Kembali">${l.back}</button>
              <div class="identity-stage-header-title" id="identitySupportTag">${t}</div>
              <button type="button" class="welcome-close-btn" aria-label="Tutup">${l.close}</button>
            </div>

            <div class="identity-stage-body">
              <div class="identity-hero-avatar">
                ${l.agentAvatar}
              </div>
              <div class="identity-code-pill" id="identityCustCode">Tamu</div>
              <h3 class="identity-stage-title">Halo! Kenalan Dulu Yuk</h3>
              <p class="identity-stage-subtitle">
                Boleh kami tahu nama panggilan Anda? Agar tim CS kami dapat menyapa Anda dengan ramah.
              </p>

              <div class="identity-form-box">
                <label class="identity-form-label" for="identityNameInput">Nama Panggilan Anda</label>
                <input 
                  type="text" 
                  class="identity-name-input" 
                  id="identityNameInput" 
                  placeholder="Contoh: Budi, Sarah, Alex..." 
                  maxlength="40" 
                  autocomplete="name"
                />
                <button type="button" class="identity-continue-btn" id="identityContinueBtn">
                  <span>Lanjut ke Obrolan</span>
                  ${l.chevronRight}
                </button>
              </div>
            </div>

            <div class="welcome-footer">
              ${l.sparkles} <span>Powered by BeanTalk \u2022 Web Chat</span>
            </div>
          </div>

          <!-- ================= STAGE 2: CHAT UTAMA ================= -->
          <div class="stage-chat">
            <div class="chat-header">
              <div class="chat-header-left">
                <button type="button" class="chat-back-btn" aria-label="Kembali">${l.back}</button>
                <div class="chat-header-avatar">
                  ${l.agentAvatar}
                  <span class="header-online-dot"></span>
                </div>
                <div class="chat-header-info">
                  <div class="chat-header-title">${t}</div>
                  <div class="chat-header-status">Online \u2022 Membalas dalam hitungan menit</div>
                </div>
              </div>
              <button type="button" class="chat-close-btn" aria-label="Tutup">${l.close}</button>
            </div>

            <!-- INLINE IDENTITY BANNER -->
            <div class="chat-identity-banner" id="chatInlineIdentityBanner" style="display: none;">
              <span>Boleh tahu nama Anda?</span>
              <input type="text" id="inlineIdentityInput" placeholder="Nama Anda..." maxlength="40" />
              <button type="button" id="inlineIdentityBtn">Simpan</button>
            </div>

            <!-- MESSAGES THREAD -->
            <div class="chat-messages-area">
              <!-- Dynamically populated -->
            </div>

            <!-- COMPOSER BAR -->
            <div class="chat-composer-box">
              <textarea class="composer-textarea" placeholder="Tulis pesan ke CS..." rows="1"></textarea>
              <button type="button" class="composer-send-btn" aria-label="Kirim" disabled>${l.send}</button>
            </div>
          </div>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" class="chat-launcher-btn" aria-label="Buka Chat">
          <div class="launcher-icon-chat">${l.chat}</div>
          <div class="launcher-icon-close">${l.close}</div>
          <div class="launcher-unread-badge">0</div>
        </button>
      </div>
    `,a=document.createElement("div");a.innerHTML=r,this.shadowRoot.appendChild(a.firstElementChild),this.wrapperEl=this.shadowRoot.querySelector(".chat-wrapper"),this.launcherBtn=this.shadowRoot.querySelector(".chat-launcher-btn"),this.unreadBadge=this.shadowRoot.querySelector(".launcher-unread-badge"),this.stageWelcome=this.shadowRoot.querySelector(".stage-welcome"),this.stageIdentity=this.shadowRoot.querySelector(".stage-identity"),this.stageChat=this.shadowRoot.querySelector(".stage-chat"),this.messagesArea=this.shadowRoot.querySelector(".chat-messages-area"),this.composerInput=this.shadowRoot.querySelector(".composer-textarea"),this.composerSendBtn=this.shadowRoot.querySelector(".composer-send-btn"),this.cardSnippetText=this.shadowRoot.querySelector("#card-snippet"),this.identityCustCode=this.shadowRoot.querySelector("#identityCustCode"),this.identityNameInput=this.shadowRoot.querySelector("#identityNameInput"),this.identityContinueBtn=this.shadowRoot.querySelector("#identityContinueBtn"),this.identityBackBtn=this.shadowRoot.querySelector("#identityBackBtn"),this.identitySupportTag=this.shadowRoot.querySelector("#identitySupportTag"),this.chatInlineIdentityBanner=this.shadowRoot.querySelector("#chatInlineIdentityBanner"),this.inlineIdentityInput=this.shadowRoot.querySelector("#inlineIdentityInput"),this.inlineIdentityBtn=this.shadowRoot.querySelector("#inlineIdentityBtn")}bindEvents(){let e=()=>this.unlockAudio();this.launcherBtn.addEventListener("click",e),window.addEventListener("click",e,{passive:!0}),window.addEventListener("keydown",e,{passive:!0}),window.addEventListener("touchstart",e,{passive:!0}),this.launcherBtn.addEventListener("click",()=>{this.toggle()}),this.shadowRoot.querySelectorAll(".welcome-close-btn, .chat-close-btn").forEach(a=>{a.addEventListener("click",()=>this.close())});let t=this.shadowRoot.querySelector(".card-active-chat");t&&t.addEventListener("click",()=>{this.goToStage("identity")}),this.identityBackBtn&&this.identityBackBtn.addEventListener("click",()=>{this.goToStage("welcome")});let i=()=>{let a=this.identityNameInput?this.identityNameInput.value.trim():"";a&&this.applyCustomerName(a,!0),this.goToStage("chat")};this.identityContinueBtn&&this.identityContinueBtn.addEventListener("click",i),this.identityNameInput&&this.identityNameInput.addEventListener("keydown",a=>{a.key==="Enter"&&(a.preventDefault(),i())});let s=this.shadowRoot.querySelector(".chat-back-btn");s&&s.addEventListener("click",()=>{this.goToStage("welcome")});let o=()=>{let a=this.inlineIdentityInput.value.trim();a&&this.applyCustomerName(a,!0)};this.inlineIdentityBtn&&this.inlineIdentityBtn.addEventListener("click",o),this.inlineIdentityInput&&this.inlineIdentityInput.addEventListener("keydown",a=>{a.key==="Enter"&&(a.preventDefault(),o())}),this.composerInput.addEventListener("input",()=>{this.composerInput.style.height="auto",this.composerInput.style.height=Math.min(this.composerInput.scrollHeight,90)+"px";let a=this.composerInput.value.trim().length>0;this.composerSendBtn.disabled=!a}),this.composerInput.addEventListener("keydown",a=>{a.key==="Enter"&&!a.shiftKey&&(a.preventDefault(),this.handleSend())}),this.composerSendBtn.addEventListener("click",()=>{this.handleSend()});let r=()=>{typeof window<"u"&&window.innerWidth<=640&&(setTimeout(()=>{this.updateViewportDimensions(),this.scrollToBottom(),this.currentStage==="chat"&&this.composerInput.scrollIntoView({block:"nearest",behavior:"smooth"})},100),setTimeout(()=>{this.updateViewportDimensions(),this.scrollToBottom()},300))};this.composerInput.addEventListener("focus",r),this.identityNameInput&&this.identityNameInput.addEventListener("focus",r)}initViewportHandler(){if(typeof window>"u")return;let e=()=>{this.isOpen&&this.updateViewportDimensions()};window.visualViewport&&(window.visualViewport.addEventListener("resize",e),window.visualViewport.addEventListener("scroll",e)),window.addEventListener("resize",e),window.addEventListener("orientationchange",()=>{setTimeout(e,200)})}updateViewportDimensions(){if(typeof window>"u")return;if(!(window.innerWidth<=640)){this.wrapperEl.style.removeProperty("--bt-viewport-height"),this.wrapperEl.style.removeProperty("--bt-viewport-top");return}if(window.visualViewport){let t=Math.round(window.visualViewport.height),i=Math.round(window.visualViewport.offsetTop);this.wrapperEl.style.setProperty("--bt-viewport-height",`${t}px`),this.wrapperEl.style.setProperty("--bt-viewport-top",`${i}px`)}else this.wrapperEl.style.setProperty("--bt-viewport-height",`${window.innerHeight}px`),this.wrapperEl.style.setProperty("--bt-viewport-top","0px")}handleSend(){let e=this.composerInput.value.trim();if(!e)return;this.composerInput.value="",this.composerInput.style.height="24px",this.composerSendBtn.disabled=!0;let t={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:v(),sender_type:"visitor",sender_name:this.customerName||"Anda",message:e,created_at:new Date().toISOString()};this.appendMessage(t),this.emitter.emit("ui:send",t)}goToStage(e){this.currentStage=e,e==="welcome"?(this.stageWelcome.style.display="flex",this.stageIdentity&&(this.stageIdentity.style.display="none"),this.stageChat.style.display="none"):e==="identity"?(this.stageWelcome.style.display="none",this.stageIdentity&&(this.stageIdentity.style.display="flex"),this.stageChat.style.display="none",this.identityNameInput&&(this.customerName&&(this.identityNameInput.value=this.customerName),setTimeout(()=>this.identityNameInput.focus(),150))):(this.stageWelcome.style.display="none",this.stageIdentity&&(this.stageIdentity.style.display="none"),this.stageChat.style.display="flex",this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150)),this.updateViewportDimensions()}open(){this.isOpen=!0,this.wrapperEl.classList.add("is-open"),this.unreadCount=0,this.updateUnreadBadge(),this.updateViewportDimensions(),this.emitter.emit("widget:opened"),typeof document<"u"&&window.innerWidth<=640&&(document.documentElement.style.overflow="hidden",document.body.style.overflow="hidden"),this.currentStage==="chat"&&(this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150))}close(){this.isOpen=!1,this.wrapperEl.classList.remove("is-open"),this.emitter.emit("widget:closed"),typeof document<"u"&&(document.documentElement.style.overflow="",document.body.style.overflow="")}toggle(){this.isOpen?this.close():this.open()}setMessages(e){this.messages=[...e],this.messagesArea.innerHTML="",e.forEach(t=>this.renderMessageBubble(t)),this.updateSnippet(),this.scrollToBottom()}unlockAudio(){try{let e=window.AudioContext||window.webkitAudioContext;if(!e)return;this.audioCtx||(this.audioCtx=new e),this.audioCtx.state==="suspended"&&this.audioCtx.resume()}catch{}}playNotificationSound(){try{if(this.unlockAudio(),!this.audioCtx)return;let e=this.audioCtx.currentTime,t=this.audioCtx.createOscillator(),i=this.audioCtx.createGain();t.type="sine",t.frequency.setValueAtTime(659.25,e),t.frequency.exponentialRampToValueAtTime(880,e+.08),i.gain.setValueAtTime(0,e),i.gain.linearRampToValueAtTime(.25,e+.02),i.gain.exponentialRampToValueAtTime(1e-4,e+.4),t.connect(i),i.connect(this.audioCtx.destination),t.start(e),t.stop(e+.4)}catch{}}appendMessage(e){this.messages.some(i=>i.id===e.id||e.client_message_id&&i.client_message_id===e.client_message_id)||(this.messages.push(e),this.renderMessageBubble(e),this.updateSnippet(),this.scrollToBottom(),e.sender_type!=="visitor"&&this.playNotificationSound(),!this.isOpen&&e.sender_type!=="visitor"&&(this.unreadCount++,this.updateUnreadBadge()))}renderMessageBubble(e){let t=e.sender_type==="visitor",i=document.createElement("div");i.className=`msg-bubble-row ${t?"is-visitor":"is-agent"}`;let s=this.formatTime(e.created_at),o=e.content||e.message||"";i.innerHTML=`
      <div class="msg-sender-name">${t?"Anda":e.sender_name||"Agent"}</div>
      <div class="msg-bubble">${ee(o)}</div>
      <div class="msg-time-status">
        <span>${s}</span>
        ${t?`<span style="display:inline-flex;">${l.check}</span>`:""}
      </div>
    `,this.messagesArea.appendChild(i)}updateSnippet(){if(this.messages.length>0){let e=this.messages[this.messages.length-1],t=e.sender_type==="visitor"?"Anda: ":"",i=e.content||e.message||"";this.cardSnippetText.textContent=t+i}}updateUnreadBadge(){this.unreadCount>0?(this.unreadBadge.textContent=this.unreadCount>9?"9+":this.unreadCount.toString(),this.unreadBadge.classList.add("has-unread")):this.unreadBadge.classList.remove("has-unread")}scrollToBottom(){setTimeout(()=>{this.messagesArea.scrollTop=this.messagesArea.scrollHeight},40)}formatTime(e){try{return new Date(e).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}catch{return""}}};function ee(n){return n.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}function te(){if(typeof document>"u")return"";let n=document.currentScript;if(n&&n.src)try{return new URL(n.src,window.location.href).origin}catch{}let e=document.querySelector("script[data-project-key]");if(e&&e.src)try{return new URL(e.src,window.location.href).origin}catch{}let t=document.querySelector('script[src*="chat-widget.js"], script[src*="widget.js"]');if(t&&t.src)try{return new URL(t.src,window.location.href).origin}catch{}return""}if(typeof window<"u")try{console.log("%c[BeanTalk]%c Universal Chat Widget Started","background: #0071E3; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-weight: bold;","color: inherit; font-weight: 500;")}catch{}var g=class{constructor(e){this.sessionData=null;this.initialized=!1;this.sendQueue=[];this.isSending=!1;this.options=e,this.emitter=new x,console.log("[BeanTalk] Initializing widget instance for project:",e.projectKey);let t=te(),i=e.apiUrl||t||(typeof window<"u"?window.location.origin:"");this.api=new b(e.projectKey,i),this.ui=new w(e,this.emitter),this.transport=new f(this.api,this.emitter),this.bindEvents(),this.bootstrap()}isReady(){return this.initialized}async processSendQueue(){if(!(this.isSending||this.sendQueue.length===0)){for(this.isSending=!0;this.sendQueue.length>0;){let e=this.sendQueue.shift(),t=this.sessionData?.conversation?.id||0,i=this.options.visitorUuid||y(),s=u();try{let o=await this.api.sendMessage(t,{visitor_uuid:i,client_message_id:e.client_message_id||v(),message:e.content||e.message||"",sender_name:e.sender_name||s||"Tamu",page_url:window.location.href,page_title:document.title});if(o.success&&o.data){let r=o.data.conversation_id;r&&(!this.sessionData?.conversation?.id||this.sessionData.conversation.id!==r)&&(this.sessionData||(this.sessionData={}),this.sessionData.conversation?this.sessionData.conversation.id=r:this.sessionData.conversation={id:r,status:"open"},F(r),this.transport.start(r,o.data.id||0)),this.emitter.emit("message:sent",o.data)}}catch(o){console.error("[BeanTalk] Gagal mengirim pesan:",o)}}this.isSending=!1,this.transport.pollNow()}}bindEvents(){this.emitter.on("ui:send",e=>{this.sendQueue.push(e),this.processSendQueue()}),this.emitter.on("customer:rename",async e=>{let t=this.options.visitorUuid||y();try{await this.api.updateProfile(t,e)}catch(i){console.warn("[BeanTalk] Gagal update nama profil pengunjung:",i)}}),this.emitter.on("message:received",e=>{this.ui.appendMessage(e),this.emitter.emit("message",e)}),this.emitter.on("widget:opened",()=>{this.transport.setWidgetOpen(!0),this.transport.pollNow()}),this.emitter.on("widget:closed",()=>{this.transport.setWidgetOpen(!1)})}async bootstrap(){let e=this.options.visitorUuid||y(),t=u();try{let i=await this.api.initSession(e,void 0,t||void 0);if(i.success&&i.data){this.sessionData=i.data,this.ui.setSessionData(i.data);let s=i.data.conversation;if(s&&s.id){F(s.id);let o=0;s.messages&&s.messages.length>0&&(o=Math.max(...s.messages.map(r=>r.id))),this.transport.start(s.id,o)}this.initialized=!0,console.log("[BeanTalk] Session established successfully:",{brand:i.data.project?.name,customerCode:i.data.visitor?.customer_code,conversationId:i.data.conversation?.id||"New Thread"}),this.emitter.emit("ready",i.data)}else console.warn("[BeanTalk] Init session warning:",i.error?.message)}catch(i){console.error("[BeanTalk] Failed to initialize chat session:",i)}}open(){console.log("[BeanTalk] Opening chat widget window"),this.ui.open()}close(){console.log("[BeanTalk] Closing chat widget window"),this.ui.close()}toggle(){this.ui.toggle()}on(e,t){return this.emitter.on(e,t),this}sendMessage(e){if(!e.trim())return;let t={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:v(),sender_type:"visitor",sender_name:"Anda",content:e.trim(),message:e.trim(),created_at:new Date().toISOString()};this.ui.appendMessage(t),this.emitter.emit("ui:send",t)}},ie=g,c=null,ne=n=>d.init(n),se=()=>d.open(),ae=()=>d.close(),oe=()=>d.toggle(),re=(n,e)=>d.on(n,e),le=n=>d.sendMessage(n),de=()=>d.getInstance(),d={init(n){return c||(console.log("[BeanTalk] Creating singleton widget instance"),c=new g(n)),c},open(){c?c.open():console.warn("[BeanTalk] Widget instance not yet initialized")},close(){c?.close()},toggle(){c?.toggle()},on(n,e){c?.on(n,e)},sendMessage(n){c?.sendMessage(n)},getInstance(){return c}};if(typeof window<"u"){let n=function(){let e=document.querySelectorAll("script[data-project-key]");if(e.length>0){let t=e[0],i=t.getAttribute("data-project-key"),s="";if(t.src)try{s=new URL(t.src,window.location.href).origin}catch{}let o=t.getAttribute("data-api-url")||s||void 0,r=t.getAttribute("data-color")||void 0,a=t.getAttribute("data-brand-name")||t.getAttribute("data-store-name")||void 0,m=t.getAttribute("data-support-title")||void 0;console.log("[BeanTalk] Found embed tag on page:",{projectKey:i,apiUrl:o,brandName:a}),i&&!c&&d.init({projectKey:i,apiUrl:o,accentColor:r,brandName:a,storeName:a,supportTitle:m})}};pe=n,window.ChatWidget=d,window.UniversalChatMe=g,setTimeout(()=>{try{window.BeanTalk&&Object.assign(window.BeanTalk,d)}catch{}},0),document.readyState==="loading"?document.addEventListener("DOMContentLoaded",n):n()}var pe,ce=d;return Q(he);})();
