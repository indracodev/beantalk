"use strict";var BeanTalk=(()=>{var $=Object.defineProperty;var tt=Object.getOwnPropertyDescriptor;var et=Object.getOwnPropertyNames;var it=Object.prototype.hasOwnProperty;var nt=(n,t)=>{for(var e in t)$(n,e,{get:t[e],enumerable:!0})},st=(n,t,e,i)=>{if(t&&typeof t=="object"||typeof t=="function")for(let s of et(t))!it.call(n,s)&&s!==e&&$(n,s,{get:()=>t[s],enumerable:!(i=tt(t,s))||i.enumerable});return n};var at=n=>st($({},"__esModule",{value:!0}),n);var kt={};nt(kt,{BeanTalk:()=>E,ChatWidget:()=>g,UniversalChatMe:()=>ht,close:()=>mt,default:()=>xt,getInstance:()=>yt,init:()=>ut,on:()=>bt,open:()=>gt,sendMessage:()=>ft,toggle:()=>vt});var L=class{constructor(){this.events={}}on(t,e){return this.events[t]||(this.events[t]=[]),this.events[t].push(e),this}off(t,e){return this.events[t]?(this.events[t]=this.events[t].filter(i=>i!==e),this):this}emit(t,e){this.events[t]&&this.events[t].forEach(i=>{try{i(e)}catch(s){console.error(`[BeanTalk] Error in event handler for "${t}":`,s)}})}};var R=class{constructor(t,e=""){this.projectKey=t,this.baseUrl=e.replace(/\/+$/,"")}async request(t,e={}){let i=`${this.baseUrl}${t}`,s={Accept:"application/json","Content-Type":"application/json","X-Project-Key":this.projectKey,...e.headers||{}},a=new AbortController,r=setTimeout(()=>a.abort(),12e3);try{let o=await fetch(i,{...e,headers:s,signal:a.signal});return clearTimeout(r),await o.json()}catch(o){return clearTimeout(r),{success:!1,error:{code:o.name==="AbortError"?"TIMEOUT":"NETWORK_ERROR",message:o.message||"Gagal terhubung ke server chat."}}}}async initSession(t,e,i){return this.request("/api/v1/client/session/init",{method:"POST",body:JSON.stringify({visitor_uuid:t,name:i,project_key:this.projectKey,page_url:window.location.href,page_title:document.title,client_url:window.location.href,metadata:{referrer:document.referrer,userAgent:navigator.userAgent,title:document.title,...e}})})}async updateProfile(t,e){return this.request("/api/v1/client/session/profile",{method:"POST",body:JSON.stringify({visitor_uuid:t,name:e})})}async pollMessages(t,e=0){return this.request(`/api/v1/client/conversations/${t}/messages?after_id=${e}`,{method:"GET"})}async sendMessage(t,e){let i=t&&t>0?t:0;return this.request(`/api/v1/client/conversations/${i}/messages`,{method:"POST",body:JSON.stringify({visitor_uuid:e.visitor_uuid,client_message_id:e.client_message_id,content:e.message,message:e.message,sender_name:e.sender_name,page_url:e.page_url,page_title:e.page_title})})}async resolveConversation(t,e){return this.request(`/api/v1/client/conversations/${t}/resolve`,{method:"POST",body:JSON.stringify({visitor_uuid:e})})}async getConversations(t){return this.request(`/api/v1/client/conversations?visitor_uuid=${encodeURIComponent(t)}`,{method:"GET"})}};var V="beantalk_visitor_uuid",ot="beantalk_last_conv_id",Y={};function K(n){try{return window.localStorage.getItem(n)}catch{return Y[n]||null}}function O(n,t){try{window.localStorage.setItem(n,t)}catch{Y[n]=t}}function rt(){return typeof crypto<"u"&&typeof crypto.randomUUID=="function"?crypto.randomUUID():"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,n=>{let t=Math.random()*16|0;return(n==="x"?t:t&3|8).toString(16)})}function S(){return"msg_"+Date.now().toString(36)+"_"+Math.random().toString(36).substring(2,9)}function B(){let n=K(V);return n||(n=rt(),O(V,n)),n}function F(n){O(ot,n.toString())}var Q="beantalk_customer_name";function C(){return K(Q)}function J(n){O(Q,n)}var N=class{constructor(t,e,i={}){this.conversationId=null;this.lastMessageId=0;this.isRunning=!1;this.isPolling=!1;this.pollQueued=!1;this.timer=null;this.isWindowVisible=!0;this.isOnline=!0;this.isWidgetOpen=!1;this.burstRemaining=0;this.burstIntervalMs=1200;this.api=t,this.emitter=e,this.activeIntervalMs=i.activeIntervalMs||2500,this.idleIntervalMs=i.idleIntervalMs||15e3,this.setupListeners()}setupListeners(){typeof document<"u"&&document.addEventListener("visibilitychange",()=>{let t=document.visibilityState==="visible";this.isWindowVisible=t,t&&this.isRunning&&this.pollNow()}),typeof window<"u"&&(window.addEventListener("online",()=>{this.isOnline=!0,this.isRunning&&this.pollNow()}),window.addEventListener("offline",()=>{this.isOnline=!1,this.clearTimer()}))}setConversation(t,e=0){this.conversationId=t,e>this.lastMessageId&&(this.lastMessageId=e)}setWidgetOpen(t){this.isWidgetOpen=t,this.isRunning&&this.reschedule()}start(t,e=0){this.conversationId=t,this.lastMessageId=Math.max(this.lastMessageId,e),this.isRunning=!0,this.reschedule()}stop(){this.isRunning=!1,this.clearTimer()}clearTimer(){this.timer&&(clearTimeout(this.timer),this.timer=null)}getInterval(){return this.burstRemaining>0?this.burstIntervalMs:this.isWindowVisible&&this.isWidgetOpen?this.activeIntervalMs:this.idleIntervalMs}reschedule(){this.clearTimer(),!(!this.isRunning||!this.isOnline)&&(this.timer=setTimeout(()=>{this.executePoll()},this.getInterval()))}async pollNow(){if(this.isPolling){this.pollQueued=!0;return}this.clearTimer(),this.burstRemaining=3,await this.executePoll()}async executePoll(){if(!this.isRunning||!this.conversationId||!this.isOnline||this.isPolling){this.reschedule();return}this.isPolling=!0;try{let t=await this.api.pollMessages(this.conversationId,this.lastMessageId);if(t.success&&t.data){let{messages:e,last_id:i}=t.data;if(Array.isArray(e)&&e.length>0){let s=e.filter(a=>a.id>this.lastMessageId);s.length>0&&s.forEach(a=>{this.emitter.emit("message:received",a)}),i&&i>this.lastMessageId&&(this.lastMessageId=i)}}}catch(t){this.emitter.emit("poll:error",t)}finally{this.isPolling=!1,this.burstRemaining>0&&this.burstRemaining--,this.pollQueued?(this.pollQueued=!1,this.burstRemaining=2,this.clearTimer(),this.timer=setTimeout(()=>this.executePoll(),100)):this.reschedule()}}};var c={chat:`<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
  </svg>`,whatsapp:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.53c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.03-1.25-.75-.67-1.26-1.5-1.41-1.75-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.32z"/>
  </svg>`,facebook:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
  </svg>`,messenger:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
  </svg>`,tiktok:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/>
  </svg>`,youtube:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
  </svg>`,instagram:`<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
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
  </svg>`,link:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
  </svg>`,custom:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
  </svg>`,ticket:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"></path>
    <path d="M13 5v2"></path>
    <path d="M13 17v2"></path>
    <path d="M13 11v2"></path>
  </svg>`,plus:`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="12" y1="5" x2="12" y2="19"></line>
    <line x1="5" y1="12" x2="19" y2="12"></line>
  </svg>`,checkCircle:`<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
    <polyline points="22 4 12 14.01 9 11.01"></polyline>
  </svg>`};function D(n="#1E1E1E"){return`
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
      --chat-primary-hover: ${lt(n,-15)};
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

    .social-btn-facebook, .social-btn-messenger {
      background: linear-gradient(135deg, #1877F2, #0D65D9);
      color: #FFFFFF;
    }

    .social-btn-tiktok {
      background: linear-gradient(135deg, #010101, #1e1e1e);
      color: #FFFFFF;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
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
  `}function lt(n,t){let e=parseInt(n.replace("#",""),16),i=(e>>16)+t,s=(e>>8&255)+t,a=(e&255)+t;return i=Math.min(255,Math.max(0,i)),s=Math.min(255,Math.max(0,s)),a=Math.min(255,Math.max(0,a)),"#"+(s|a<<8|i<<16).toString(16).padStart(6,"0")}var z={id:{liveChatAvailable:"Live Chat Aktif",newChatSnippet:"Mulai obrolan baru dengan tim kami...",ticketsTitle:"Tiket & Riwayat Chat",startNewChat:"Mulai Chat Baru",findUsTitleDefault:"Reach Us Anywhere Else",poweredBy:"Powered by BeanTalk \u2022 Web Chat",identityPillGuest:"Tamu",identityTitle:"Halo! Kenalan Dulu Yuk",identitySubtitle:"Boleh kami tahu nama panggilan Anda? Agar tim CS kami dapat menyapa Anda dengan ramah.",identityLabel:"Nama Panggilan Anda",identityPlaceholder:"Contoh: Budi, Sarah, Alex...",identityContinue:"Lanjut ke Obrolan",socialPickerTitle:n=>`Hubungi via ${n}`,socialPickerHeader:n=>`Pilih Kontak ${n}`,socialPickerSubtitle:"Pilih salah satu kontak layanan di bawah untuk terhubung langsung:",socialPickerContactChoice:n=>`(${n} pilihan kontak)`,contactVia:n=>`Hubungi via ${n}`,onlineStatus:"Online \u2022 Membalas dalam hitungan menit",resolveBtn:"Selesaikan",resolveConfirm:"Apakah Anda ingin menyelesaikan tiket percakapan ini?",resolvedBanner:"Tiket percakapan ini telah selesai.",resolvedSystemNotice:'Percakapan ini telah Anda tandai selesai. Klik "Mulai Chat Baru" untuk membuat tiket baru.',askNameInline:"Boleh tahu nama Anda?",namePlaceholder:"Nama Anda...",saveBtn:"Simpan",typeMessagePlaceholder:"Tulis pesan ke CS...",typeMessageAsPlaceholder:n=>`Tulis pesan sebagai ${n}...`,ticketsCount:n=>`${n} Tiket`,ticketPrefix:n=>`Tiket #${n}`,ticketStatusOpen:"Open",ticketStatusClosed:"Selesai",ticketDefaultSnippet:"Percakapan tiket...",senderYou:"Anda",closeAriaLabel:"Tutup",backAriaLabel:"Kembali",sendAriaLabel:"Kirim",openChatAriaLabel:"Buka Chat",defaultGreetingTitle:"Hallo!",defaultGreetingSubtitle:"Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!",defaultSupportTitle:"Customer Support"},en:{liveChatAvailable:"Live Chat Available",newChatSnippet:"Start a new conversation with our team...",ticketsTitle:"Tickets & Chat History",startNewChat:"Start New Chat",findUsTitleDefault:"Reach Us Anywhere Else",poweredBy:"Powered by BeanTalk \u2022 Web Chat",identityPillGuest:"Guest",identityTitle:"Hello! Let's get acquainted",identitySubtitle:"May we know your name so our support team can address you personally?",identityLabel:"Your Name / Nickname",identityPlaceholder:"E.g. Alex, Sarah, John...",identityContinue:"Continue to Chat",socialPickerTitle:n=>`Contact via ${n}`,socialPickerHeader:n=>`Choose ${n} Contact`,socialPickerSubtitle:"Choose one of our service contacts below to connect directly:",socialPickerContactChoice:n=>`(${n} contact options)`,contactVia:n=>`Contact via ${n}`,onlineStatus:"Online \u2022 Replies within minutes",resolveBtn:"Resolve",resolveConfirm:"Do you want to resolve this conversation ticket?",resolvedBanner:"This conversation ticket has ended.",resolvedSystemNotice:'You have marked this conversation as resolved. Click "Start New Chat" to open a new ticket.',askNameInline:"What is your name?",namePlaceholder:"Your name...",saveBtn:"Save",typeMessagePlaceholder:"Type a message to support...",typeMessageAsPlaceholder:n=>`Type a message as ${n}...`,ticketsCount:n=>`${n} Tickets`,ticketPrefix:n=>`Ticket #${n}`,ticketStatusOpen:"Open",ticketStatusClosed:"Closed",ticketDefaultSnippet:"Ticket conversation...",senderYou:"You",closeAriaLabel:"Close",backAriaLabel:"Back",sendAriaLabel:"Send",openChatAriaLabel:"Open Chat",defaultGreetingTitle:"Hello!",defaultGreetingSubtitle:"How can we help you today? Ask anything here!",defaultSupportTitle:"Customer Support"}};var H=class{constructor(t,e){this.lang="id";this.isOpen=!1;this.currentStage="welcome";this.unreadCount=0;this.messages=[];this.sessionData=null;this.customerName="";this.customerCode="";this.audioCtx=null;this.options=t,this.emitter=e,this.lang=t.language==="en"?"en":"id";let i=document.getElementById("beantalk-chat-root")||document.getElementById("universal-chat-root");i||(i=document.createElement("div"),i.id="beantalk-chat-root",i.style.position="relative",i.style.zIndex="2147483647",i.style.display="block",document.body.appendChild(i)),this.shadowRoot=i.attachShadow({mode:"open"}),this.styleEl=document.createElement("style"),this.styleEl.textContent=D(t.accentColor||"#1E1E1E"),this.shadowRoot.appendChild(this.styleEl),this.renderSkeleton(),this.bindEvents(),this.initViewportHandler();let s=C();s&&this.applyCustomerName(s,!1)}get t(){return z[this.lang]||z.id}updateTheming(t){this.styleEl&&(this.styleEl.textContent=D(t))}setLanguage(t){this.lang=t==="en"?"en":"id";let e=this.shadowRoot.querySelector(".card-live-indicator span:last-child");e&&(e.textContent=this.t.liveChatAvailable),this.messages.length===0&&this.cardSnippetText?this.cardSnippetText.textContent=this.t.newChatSnippet:this.updateSnippet();let i=this.shadowRoot.querySelector(".tickets-section-header span:first-child");if(i&&(i.textContent=this.t.ticketsTitle),this.btnNewTicketWelcome){let l=this.btnNewTicketWelcome.querySelector("span");l&&(l.textContent=this.t.startNewChat)}let s=this.shadowRoot.querySelector(".welcome-footer span");s&&(s.textContent=this.t.poweredBy),this.identityCustCode&&(!this.customerCode||this.identityCustCode.textContent==="Tamu"||this.identityCustCode.textContent==="Guest")&&(this.identityCustCode.textContent=this.customerCode||this.t.identityPillGuest);let a=this.shadowRoot.querySelector(".identity-stage-title");a&&(a.textContent=this.t.identityTitle);let r=this.shadowRoot.querySelector(".identity-stage-subtitle");r&&(r.textContent=this.t.identitySubtitle);let o=this.shadowRoot.querySelector(".identity-form-label");if(o&&(o.textContent=this.t.identityLabel),this.identityNameInput&&(this.identityNameInput.placeholder=this.t.identityPlaceholder),this.identityContinueBtn){let l=this.identityContinueBtn.querySelector("span");l&&(l.textContent=this.t.identityContinue)}let d=this.shadowRoot.querySelector(".chat-header-status");if(d&&(d.textContent=this.t.onlineStatus),this.chatEndBtn){this.chatEndBtn.title=this.t.resolveBtn;let l=this.chatEndBtn.querySelector("span");l&&(l.textContent=this.t.resolveBtn)}if(this.chatInlineIdentityBanner){let l=this.chatInlineIdentityBanner.querySelector("span");l&&(l.textContent=this.t.askNameInline)}if(this.inlineIdentityInput&&(this.inlineIdentityInput.placeholder=this.t.namePlaceholder),this.inlineIdentityBtn&&(this.inlineIdentityBtn.textContent=this.t.saveBtn),this.chatResolvedBanner){let l=this.chatResolvedBanner.querySelector(".chat-resolved-text span");l&&(l.textContent=this.t.resolvedBanner)}if(this.chatStartNewBtn){let l=this.chatStartNewBtn.querySelector("span");l&&(l.textContent=this.t.startNewChat)}this.composerInput&&(this.composerInput.placeholder=this.customerName?this.t.typeMessageAsPlaceholder(this.customerName):this.t.typeMessagePlaceholder),this.sessionData?.conversations&&this.renderTicketsHistory(this.sessionData.conversations),this.shadowRoot.querySelectorAll(".msg-bubble-row.is-visitor .msg-sender-name").forEach(l=>{l.textContent=this.customerName||this.t.senderYou})}setSessionData(t){this.sessionData=t;let e=t.widget||t.widget_settings||{};(e.language==="en"||e.language==="id")&&this.setLanguage(e.language),e.primary_color&&this.updateTheming(e.primary_color);let i=e.greeting_title||e.header_title;(!i||this.lang==="en"&&i==="Hallo!")&&(i=this.lang==="en"?"Hello!":t.project?.name||"Chat Support");let s=e.greeting_subtitle||e.greeting_text;(!s||this.lang==="en"&&(s==="Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!"||s==="Hallo! Ada yang bisa kami bantu? Tanyakan apapun di sini."))&&(s=this.t.defaultGreetingSubtitle);let a=this.shadowRoot.querySelector(".welcome-title");a&&(a.textContent=i);let r=this.shadowRoot.querySelector(".welcome-subtitle");r&&(r.textContent=s);let o=e.support_title||this.options.supportTitle||this.options.brandName||this.options.storeName||t.project?.name||this.t.defaultSupportTitle,d=this.shadowRoot.querySelector("#cardChatName");d&&(d.textContent=o),this.identitySupportTag&&(this.identitySupportTag.textContent=o);let l=this.shadowRoot.querySelector(".chat-header-title");l&&(l.textContent=o);let m=this.shadowRoot.querySelector(".welcome-brand-badge");m&&(m.textContent=t.project?.name||"Live Support");let h=t.visitor||{},b=h.customer_code||h.customer_code_formatted;b&&(this.customerCode=b,this.identityCustCode&&(this.identityCustCode.textContent=b));let k=h.name,T=C(),A=k||T;A?this.applyCustomerName(A,!1):this.chatInlineIdentityBanner&&(this.chatInlineIdentityBanner.style.display="flex"),t.conversation?.messages&&t.conversation.messages.length>0&&this.setMessages(t.conversation.messages),this.renderTicketsHistory(t.conversations||[]);let _=t.conversation?t.conversation.status==="closed":!1;this.updateResolvedUI(_);let f=this.shadowRoot.querySelector("#socialChannelsCard"),M=this.shadowRoot.querySelector("#socialChannelsTitle"),I=this.shadowRoot.querySelector("#socialChannelsRow"),U=e.social_channels||[],Z=e.find_us_title||"Reach Us Anywhere Else";if(M&&(M.textContent=Z),f&&I){let W=Array.isArray(U)?U.filter(x=>x.enabled&&x.url):[];if(W.length>0){I.innerHTML="";let x={};W.forEach(u=>{let y=(u.platform||u.icon||u.id||"whatsapp").toLowerCase();x[y]||(x[y]=[]),x[y].push(u)}),Object.keys(x).forEach(u=>{let y=x[u],P=y[0],G=j(P,u);if(y.length===1){let p=document.createElement("a");p.className=`social-channel-btn social-btn-${u}`,p.href=P.url,p.target="_blank",p.rel="noopener noreferrer",p.title=`Hubungi via ${P.name||q(u)}`,p.innerHTML=G,I.appendChild(p)}else{let p=document.createElement("button");p.type="button",p.className=`social-channel-btn social-btn-${u} has-multi-badge`,p.title=`${q(u)} (${y.length} pilihan kontak)`,p.innerHTML=`
              ${G}
              <span class="social-channel-badge">${y.length}</span>
            `,p.addEventListener("click",X=>{X.stopPropagation(),this.openSocialPicker(u,y)}),I.appendChild(p)}}),f.style.display="block"}else f.style.display="none"}}applyCustomerName(t,e=!0){let i=t.trim();i&&(this.customerName=i,e&&(J(i),this.emitter.emit("customer:rename",i)),this.identityNameInput&&(this.identityNameInput.value=i),this.chatInlineIdentityBanner&&(this.chatInlineIdentityBanner.style.display="none"),this.composerInput&&(this.composerInput.placeholder=this.t.typeMessageAsPlaceholder(i)))}renderSkeleton(){let t=this.options.brandName||this.options.storeName||this.t.defaultSupportTitle,e=this.options.supportTitle||t||this.t.defaultSupportTitle,i=this.options.greetingTitle||this.t.defaultGreetingTitle,s=this.options.greetingSubtitle||this.t.defaultGreetingSubtitle,r=`
      <div class="chat-wrapper ${this.options.position==="bottom-left"?"pos-bottom-left":""}">
        <!-- WIDGET WINDOW -->
        <div class="chat-window">
          
          <!-- ================= STAGE 1: WELCOME HUB ================= -->
          <div class="stage-welcome">
            <div class="welcome-header">
              <div class="welcome-header-top">
                <span class="welcome-brand-badge">${t}</span>
                <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${c.close}</button>
              </div>
              <h2 class="welcome-title">${i}</h2>
              <p class="welcome-subtitle">${s}</p>
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
                    ${c.agentAvatar}
                  </div>
                  <div class="card-chat-info">
                    <div class="card-chat-name" id="cardChatName">${e}</div>
                    <div class="card-chat-snippet" id="card-snippet">${this.t.newChatSnippet}</div>
                  </div>
                  <div class="card-chat-chevron">
                    ${c.chevronRight}
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
                  ${c.plus}
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
              ${c.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 1.5: FORM PEMANGGILAN NAMA ================= -->
          <div class="stage-identity" style="display: none;">
            <div class="identity-stage-header">
              <button type="button" class="identity-back-btn" id="identityBackBtn" aria-label="${this.t.backAriaLabel}">${c.back}</button>
              <div class="identity-stage-header-title" id="identitySupportTag">${e}</div>
              <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${c.close}</button>
            </div>

            <div class="identity-stage-body">
              <div class="identity-hero-avatar">
                ${c.agentAvatar}
              </div>
              <div class="identity-code-pill" id="identityCustCode">${this.customerCode||this.t.identityPillGuest}</div>
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
                  ${c.chevronRight}
                </button>
              </div>
            </div>

            <div class="welcome-footer">
              ${c.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 1.8: MULTI-CONTACT CHANNEL SELECTOR ================= -->
          <div class="stage-social-picker" id="stageSocialPicker" style="display: none;">
            <div class="social-picker-header">
              <button type="button" class="social-picker-back-btn" id="socialPickerBackBtn" aria-label="${this.t.backAriaLabel}">${c.back}</button>
              <div class="social-picker-header-title" id="socialPickerHeaderTitle">${this.t.socialPickerHeader("")}</div>
              <button type="button" class="welcome-close-btn" aria-label="${this.t.closeAriaLabel}">${c.close}</button>
            </div>

            <div class="social-picker-body">
              <div class="social-picker-hero">
                <div class="social-picker-avatar social-btn-whatsapp" id="socialPickerHeroAvatar">
                  ${c.whatsapp}
                </div>
                <h3 class="social-picker-title" id="socialPickerTitle">${this.t.socialPickerTitle("WhatsApp")}</h3>
                <p class="social-picker-subtitle" id="socialPickerSubtitle">
                  ${this.t.socialPickerSubtitle}
                </p>
              </div>

              <div class="social-picker-list" id="socialPickerList">
                <!-- Dynamically populated options -->
              </div>
            </div>

            <div class="welcome-footer">
              ${c.sparkles} <span>${this.t.poweredBy}</span>
            </div>
          </div>

          <!-- ================= STAGE 2: CHAT UTAMA ================= -->
          <div class="stage-chat">
            <div class="chat-header">
              <div class="chat-header-left">
                <button type="button" class="chat-back-btn" aria-label="${this.t.backAriaLabel}">${c.back}</button>
                <div class="chat-header-avatar">
                  ${c.agentAvatar}
                  <span class="header-online-dot"></span>
                </div>
                <div class="chat-header-info">
                  <div class="chat-header-title">${e}</div>
                  <div class="chat-header-status">${this.t.onlineStatus}</div>
                </div>
              </div>
              <div class="chat-header-actions" style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="chat-end-btn" id="chatEndBtn" title="${this.t.resolveBtn}">
                  ${c.checkCircle}
                  <span>${this.t.resolveBtn}</span>
                </button>
                <button type="button" class="chat-close-btn" aria-label="${this.t.closeAriaLabel}">${c.close}</button>
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
                ${c.checkCircle}
                <span>${this.t.resolvedBanner}</span>
              </div>
              <button type="button" class="chat-start-new-btn" id="chatStartNewBtn">
                ${c.plus}
                <span>${this.t.startNewChat}</span>
              </button>
            </div>

            <!-- COMPOSER BAR -->
            <div class="chat-composer-box" id="chatComposerBox">
              <textarea class="composer-textarea" placeholder="${this.t.typeMessagePlaceholder}" rows="1"></textarea>
              <button type="button" class="composer-send-btn" aria-label="${this.t.sendAriaLabel}" disabled>${c.send}</button>
            </div>
          </div>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" class="chat-launcher-btn" aria-label="${this.t.openChatAriaLabel}">
          <div class="launcher-icon-chat">${c.chat}</div>
          <div class="launcher-icon-close">${c.close}</div>
          <div class="launcher-unread-badge">0</div>
        </button>
      </div>
    `,o=document.createElement("div");o.innerHTML=r,this.shadowRoot.appendChild(o.firstElementChild),this.wrapperEl=this.shadowRoot.querySelector(".chat-wrapper"),this.launcherBtn=this.shadowRoot.querySelector(".chat-launcher-btn"),this.unreadBadge=this.shadowRoot.querySelector(".launcher-unread-badge"),this.stageWelcome=this.shadowRoot.querySelector(".stage-welcome"),this.stageIdentity=this.shadowRoot.querySelector(".stage-identity"),this.stageChat=this.shadowRoot.querySelector(".stage-chat"),this.stageSocialPicker=this.shadowRoot.querySelector("#stageSocialPicker"),this.messagesArea=this.shadowRoot.querySelector(".chat-messages-area"),this.composerInput=this.shadowRoot.querySelector(".composer-textarea"),this.composerSendBtn=this.shadowRoot.querySelector(".composer-send-btn"),this.cardSnippetText=this.shadowRoot.querySelector("#card-snippet"),this.cardTicketsContainer=this.shadowRoot.querySelector("#cardTicketsContainer"),this.ticketListContainer=this.shadowRoot.querySelector("#ticketListContainer"),this.ticketCountBadge=this.shadowRoot.querySelector("#ticketCountBadge"),this.btnNewTicketWelcome=this.shadowRoot.querySelector("#btnNewTicketWelcome"),this.chatEndBtn=this.shadowRoot.querySelector("#chatEndBtn"),this.chatResolvedBanner=this.shadowRoot.querySelector("#chatResolvedBanner"),this.chatStartNewBtn=this.shadowRoot.querySelector("#chatStartNewBtn"),this.chatComposerBox=this.shadowRoot.querySelector("#chatComposerBox"),this.identityCustCode=this.shadowRoot.querySelector("#identityCustCode"),this.identityNameInput=this.shadowRoot.querySelector("#identityNameInput"),this.identityContinueBtn=this.shadowRoot.querySelector("#identityContinueBtn"),this.identityBackBtn=this.shadowRoot.querySelector("#identityBackBtn"),this.identitySupportTag=this.shadowRoot.querySelector("#identitySupportTag"),this.chatInlineIdentityBanner=this.shadowRoot.querySelector("#chatInlineIdentityBanner"),this.inlineIdentityInput=this.shadowRoot.querySelector("#inlineIdentityInput"),this.inlineIdentityBtn=this.shadowRoot.querySelector("#inlineIdentityBtn"),this.socialPickerBackBtn=this.shadowRoot.querySelector("#socialPickerBackBtn")}bindEvents(){let t=()=>this.unlockAudio();this.launcherBtn.addEventListener("click",t),window.addEventListener("click",t,{passive:!0}),window.addEventListener("keydown",t,{passive:!0}),window.addEventListener("touchstart",t,{passive:!0}),this.launcherBtn.addEventListener("click",()=>{this.toggle()}),this.shadowRoot.querySelectorAll(".welcome-close-btn, .chat-close-btn").forEach(o=>{o.addEventListener("click",()=>this.close())});let e=this.shadowRoot.querySelector(".card-active-chat");e&&e.addEventListener("click",()=>{this.goToStage("identity")}),this.chatEndBtn&&this.chatEndBtn.addEventListener("click",o=>{o.stopPropagation();let d=this.sessionData?.conversation?.id;d&&confirm(this.t.resolveConfirm)&&this.emitter.emit("conversation:resolve",d)}),this.chatStartNewBtn&&this.chatStartNewBtn.addEventListener("click",o=>{o.stopPropagation(),this.emitter.emit("conversation:start-new")}),this.btnNewTicketWelcome&&this.btnNewTicketWelcome.addEventListener("click",o=>{o.stopPropagation(),this.emitter.emit("conversation:start-new")}),this.identityBackBtn&&this.identityBackBtn.addEventListener("click",()=>{this.goToStage("welcome")}),this.socialPickerBackBtn&&this.socialPickerBackBtn.addEventListener("click",()=>{this.goToStage("welcome")});let i=()=>{let o=this.identityNameInput?this.identityNameInput.value.trim():"";o&&this.applyCustomerName(o,!0),this.goToStage("chat")};this.identityContinueBtn&&this.identityContinueBtn.addEventListener("click",i),this.identityNameInput&&this.identityNameInput.addEventListener("keydown",o=>{o.key==="Enter"&&(o.preventDefault(),i())});let s=this.shadowRoot.querySelector(".chat-back-btn");s&&s.addEventListener("click",()=>{this.goToStage("welcome")});let a=()=>{let o=this.inlineIdentityInput.value.trim();o&&this.applyCustomerName(o,!0)};this.inlineIdentityBtn&&this.inlineIdentityBtn.addEventListener("click",a),this.inlineIdentityInput&&this.inlineIdentityInput.addEventListener("keydown",o=>{o.key==="Enter"&&(o.preventDefault(),a())}),this.composerInput.addEventListener("input",()=>{this.composerInput.style.height="auto",this.composerInput.style.height=Math.min(this.composerInput.scrollHeight,90)+"px";let o=this.composerInput.value.trim().length>0;this.composerSendBtn.disabled=!o}),this.composerInput.addEventListener("keydown",o=>{o.key==="Enter"&&!o.shiftKey&&(o.preventDefault(),this.handleSend())}),this.composerSendBtn.addEventListener("click",()=>{this.handleSend()});let r=()=>{typeof window<"u"&&window.innerWidth<=640&&(setTimeout(()=>{this.updateViewportDimensions(),this.scrollToBottom(),this.currentStage==="chat"&&this.composerInput.scrollIntoView({block:"nearest",behavior:"smooth"})},100),setTimeout(()=>{this.updateViewportDimensions(),this.scrollToBottom()},300))};this.composerInput.addEventListener("focus",r),this.identityNameInput&&this.identityNameInput.addEventListener("focus",r)}initViewportHandler(){if(typeof window>"u")return;let t=()=>{this.isOpen&&this.updateViewportDimensions()};window.visualViewport&&(window.visualViewport.addEventListener("resize",t),window.visualViewport.addEventListener("scroll",t)),window.addEventListener("resize",t),window.addEventListener("orientationchange",()=>{setTimeout(t,200)})}updateViewportDimensions(){if(typeof window>"u")return;if(!(window.innerWidth<=640)){this.wrapperEl.style.removeProperty("--bt-viewport-height"),this.wrapperEl.style.removeProperty("--bt-viewport-top");return}if(window.visualViewport){let e=Math.round(window.visualViewport.height),i=Math.round(window.visualViewport.offsetTop);this.wrapperEl.style.setProperty("--bt-viewport-height",`${e}px`),this.wrapperEl.style.setProperty("--bt-viewport-top",`${i}px`)}else this.wrapperEl.style.setProperty("--bt-viewport-height",`${window.innerHeight}px`),this.wrapperEl.style.setProperty("--bt-viewport-top","0px")}handleSendText(t,e){let i=(t||"").trim();if(!i)return;this.composerInput.value="",this.composerInput.style.height="24px",this.composerSendBtn.disabled=!0;let s=e||i,a={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:S(),sender_type:"visitor",sender_name:this.customerName||"Anda",content:s,message:i,created_at:new Date().toISOString()};this.appendMessage(a),this.emitter.emit("ui:send",a)}handleSend(){let t=this.composerInput.value.trim();t&&this.handleSendText(t)}goToStage(t){if(this.currentStage=t,t==="welcome")this.stageWelcome.style.display="flex",this.stageIdentity&&(this.stageIdentity.style.display="none"),this.stageSocialPicker&&(this.stageSocialPicker.style.display="none"),this.stageChat.style.display="none";else if(t==="identity")this.stageWelcome.style.display="none",this.stageIdentity&&(this.stageIdentity.style.display="flex"),this.stageSocialPicker&&(this.stageSocialPicker.style.display="none"),this.stageChat.style.display="none",this.identityNameInput&&(this.customerName&&(this.identityNameInput.value=this.customerName),setTimeout(()=>this.identityNameInput.focus(),150));else if(t==="social-picker")this.stageWelcome.style.display="none",this.stageIdentity&&(this.stageIdentity.style.display="none"),this.stageSocialPicker&&(this.stageSocialPicker.style.display="flex"),this.stageChat.style.display="none";else{if(this.stageWelcome.style.display="none",this.stageIdentity&&(this.stageIdentity.style.display="none"),this.stageSocialPicker&&(this.stageSocialPicker.style.display="none"),this.stageChat.style.display="flex",this.messages.length===0){let e=this.sessionData?.widget||this.sessionData?.widget_settings;if(e&&e.bot_enabled&&e.bot_welcome_message){let s=e.bot_mode_options!==!1&&Array.isArray(e.bot_welcome_options)&&e.bot_welcome_options.length>0?e.bot_welcome_options:null,a={id:0,conversation_id:this.sessionData?.conversation?.id||0,sender_type:"bot",sender_name:e.bot_name||"Assistant",content:e.bot_welcome_message,message:e.bot_welcome_message,metadata:{is_bot:!0,is_welcome:!0,options:s},created_at:new Date().toISOString()};this.appendMessage(a)}}this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150)}this.updateViewportDimensions()}openSocialPicker(t,e){let i=this.shadowRoot.querySelector("#socialPickerTitle"),s=this.shadowRoot.querySelector("#socialPickerHeaderTitle"),a=this.shadowRoot.querySelector("#socialPickerHeroAvatar"),r=this.shadowRoot.querySelector("#socialPickerList"),o=q(t);s&&(s.textContent=this.t.socialPickerHeader(o)),i&&(i.textContent=this.t.socialPickerTitle(o));let d=this.shadowRoot.querySelector("#socialPickerSubtitle");if(d&&(d.textContent=this.t.socialPickerSubtitle),a){let l=e[0]||{};a.innerHTML=j(l,t),a.className=`social-picker-avatar social-btn-${t}`}r&&(r.innerHTML="",e.forEach(l=>{let m=document.createElement("a");m.className="social-picker-item",m.href=l.url,m.target="_blank",m.rel="noopener noreferrer";let h=j(l,t),b=ct(l.url,t);m.innerHTML=`
          <div class="social-picker-item-avatar social-btn-${t}">
            ${h}
          </div>
          <div class="social-picker-item-info">
            <div class="social-picker-item-name">${w(l.name||o)}</div>
            ${b?`<div class="social-picker-item-sub">${w(b)}</div>`:""}
          </div>
          <div class="social-picker-item-arrow">
            ${c.chevronRight}
          </div>
        `,m.addEventListener("click",()=>{setTimeout(()=>{this.goToStage("welcome")},300)}),r.appendChild(m)})),this.goToStage("social-picker")}open(){this.isOpen=!0,this.wrapperEl.classList.add("is-open"),this.unreadCount=0,this.updateUnreadBadge(),this.updateViewportDimensions(),this.emitter.emit("widget:opened"),typeof document<"u"&&window.innerWidth<=640&&(document.documentElement.style.overflow="hidden",document.body.style.overflow="hidden"),this.currentStage==="chat"&&(this.scrollToBottom(),setTimeout(()=>this.composerInput.focus(),150))}close(){this.isOpen=!1,this.wrapperEl.classList.remove("is-open"),this.emitter.emit("widget:closed"),typeof document<"u"&&(document.documentElement.style.overflow="",document.body.style.overflow="")}toggle(){this.isOpen?this.close():this.open()}setMessages(t){this.messages=[...t],this.messagesArea.innerHTML="",t.forEach(e=>this.renderMessageBubble(e)),this.updateSnippet(),this.scrollToBottom()}unlockAudio(){try{let t=window.AudioContext||window.webkitAudioContext;if(!t)return;this.audioCtx||(this.audioCtx=new t),this.audioCtx.state==="suspended"&&this.audioCtx.resume()}catch{}}playNotificationSound(){try{if(this.unlockAudio(),!this.audioCtx)return;let t=this.audioCtx.currentTime,e=this.audioCtx.createOscillator(),i=this.audioCtx.createGain();e.type="sine",e.frequency.setValueAtTime(659.25,t),e.frequency.exponentialRampToValueAtTime(880,t+.08),i.gain.setValueAtTime(0,t),i.gain.linearRampToValueAtTime(.25,t+.02),i.gain.exponentialRampToValueAtTime(1e-4,t+.4),e.connect(i),i.connect(this.audioCtx.destination),e.start(t),e.stop(t+.4)}catch{}}appendMessage(t){this.messages.some(i=>i.id===t.id||t.client_message_id&&i.client_message_id===t.client_message_id)||(this.messages.push(t),this.renderMessageBubble(t),this.updateSnippet(),this.scrollToBottom(),t.sender_type!=="visitor"&&this.playNotificationSound(),!this.isOpen&&t.sender_type!=="visitor"&&(this.unreadCount++,this.updateUnreadBadge()))}renderMessageBubble(t){let e=t.sender_type==="visitor",i=t.sender_type==="bot",s=document.createElement("div");s.className=`msg-bubble-row ${e?"is-visitor":"is-agent"}`;let a=this.formatTime(t.created_at),r=t.content||t.message||"",o=this.formatMessageContent(r),d=e?this.customerName||this.t.senderYou:i?"\u{1F916} "+(t.sender_name||"BeanBot"):t.sender_name||"Agent",l=this.sessionData?.widget||this.sessionData?.widget_settings,h=(l?l.bot_mode_options!==!1:!0)&&t.metadata&&Array.isArray(t.metadata.options)?t.metadata.options:null,b="";if(h&&h.length>0&&!e&&(b=`
        <div class="msg-options-container">
          ${h.map((k,T)=>`
            <button type="button" class="msg-option-btn" data-idx="${T}">
              <span>${w(k.label||k.value||"")}</span>
              <span class="msg-option-arrow">\u2192</span>
            </button>
          `).join("")}
        </div>
      `),s.innerHTML=`
      <div class="msg-sender-name" style="${i?"color: #5856D6; font-weight: 600;":""}">${w(d)}</div>
      <div class="msg-bubble">${o}</div>
      ${b}
      <div class="msg-time-status">
        <span>${a}</span>
        ${e?`<span style="display:inline-flex;">${c.check}</span>`:""}
      </div>
    `,h&&h.length>0&&!e){let k=s.querySelectorAll(".msg-option-btn");k.forEach((T,A)=>{T.addEventListener("click",_=>{_.preventDefault();let f=h[A];f&&(k.forEach(M=>M.disabled=!0),T.classList.add("is-selected"),this.handleSendText(f.value||f.label,f.label||f.value))})})}this.messagesArea.appendChild(s)}formatMessageContent(t){if(!t)return"";let e=w(t);return e=e.replace(/\*(.*?)\*/g,"<strong>$1</strong>"),e=e.replace(/(https?:\/\/[^\s]+)/g,'<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'),e=e.replace(/(^|[\s])(www\.[^\s]+)/g,'$1<a href="https://$2" target="_blank" rel="noopener noreferrer">$2</a>'),e}updateSnippet(){if(this.messages.length>0){let t=this.messages[this.messages.length-1],e=t.sender_type==="visitor"?`${this.t.senderYou}: `:"",i=t.content||t.message||"";this.cardSnippetText.textContent=e+i}}updateUnreadBadge(){this.unreadCount>0?(this.unreadBadge.textContent=this.unreadCount>9?"9+":this.unreadCount.toString(),this.unreadBadge.classList.add("has-unread")):this.unreadBadge.classList.remove("has-unread")}scrollToBottom(){setTimeout(()=>{this.messagesArea.scrollTop=this.messagesArea.scrollHeight},40)}formatTime(t){try{if(!t)return"";let e=t;typeof e=="string"&&!e.includes("Z")&&!e.includes("+")&&!e.includes("T")&&(e=e.replace(" ","T")+"Z");let i=new Date(e);if(isNaN(i.getTime()))return"";let s=String(i.getHours()).padStart(2,"0"),a=String(i.getMinutes()).padStart(2,"0");return`${s}:${a}`}catch{return""}}updateResolvedUI(t){t?(this.chatEndBtn&&(this.chatEndBtn.style.display="none"),this.chatComposerBox&&(this.chatComposerBox.style.display="none"),this.chatResolvedBanner&&(this.chatResolvedBanner.style.display="flex")):(this.chatEndBtn&&(this.chatEndBtn.style.display="inline-flex"),this.chatComposerBox&&(this.chatComposerBox.style.display="flex"),this.chatResolvedBanner&&(this.chatResolvedBanner.style.display="none"))}renderTicketsHistory(t){if(!(!this.cardTicketsContainer||!this.ticketListContainer)){if(!t||t.length===0){this.cardTicketsContainer.style.display="none";return}this.ticketListContainer.innerHTML="",this.ticketCountBadge&&(this.ticketCountBadge.textContent=this.t.ticketsCount(t.length)),t.forEach(e=>{let i=document.createElement("div");i.className="ticket-item";let s=e.status==="open"||e.status==="pending",a=s?"ticket-status-open":"ticket-status-closed",r=s?this.t.ticketStatusOpen:this.t.ticketStatusClosed,o=e.last_message_preview||this.t.ticketDefaultSnippet,d=this.formatTime(e.last_message_at||e.created_at);i.innerHTML=`
        <div class="ticket-item-left">
          <div class="ticket-item-title">
            <span>${this.t.ticketPrefix(e.id)}</span>
            <span class="ticket-status-badge ${a}">${r}</span>
            <span style="font-size: 10px; color: #94A3B8; font-weight: normal; margin-left: auto;">${d}</span>
          </div>
          <div class="ticket-item-snippet">${w(o)}</div>
        </div>
        <div style="color: #94A3B8; display: flex; align-items: center;">
          ${c.chevronRight}
        </div>
      `,i.addEventListener("click",()=>{this.emitter.emit("conversation:switch",e.id)}),this.ticketListContainer.appendChild(i)}),this.cardTicketsContainer.style.display="flex"}}};function w(n){return n.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}function j(n,t){if(n){let i=(n.custom_icon||"").trim();if((n.icon_type==="custom"||i!==""&&!n.icon_type)&&i)return i.startsWith("<svg")&&i.endsWith("</svg>")?i:`<img src="${w(i)}" alt="${w(n.name||t)}" />`}let e=n?.icon||t;return c[e]||c[t]||c.chat}function q(n){return{whatsapp:"WhatsApp",instagram:"Instagram",facebook:"Facebook",messenger:"Facebook",tiktok:"TikTok",youtube:"YouTube",telegram:"Telegram",shopee:"Shopee Store",tokopedia:"Tokopedia Store",custom:"Link Kustom",link:"Tautan Kustom"}[n.toLowerCase()]||dt(n)}function ct(n,t){if(!n)return"";let e=t.toLowerCase();if(e==="whatsapp"){if(n.includes("wa.me/")){let i=n.split("wa.me/")[1]?.split("?")[0]||"";return i?`+${i}`:n}}else if(e==="instagram"){if(n.includes("instagram.com/")){let i=n.split("instagram.com/")[1]?.split("/")[0]?.split("?")[0]||"";return i?`@${i}`:n}}else if(e==="facebook"||e==="messenger"){if(n.includes("facebook.com/")){let i=n.split("facebook.com/")[1]?.split("/")[0]?.split("?")[0]||"";return i||n}}else if(e==="tiktok"){if(n.includes("tiktok.com/@")){let i=n.split("tiktok.com/@")[1]?.split("/")[0]?.split("?")[0]||"";return i?`@${i}`:n}}else if(e==="youtube"){if(n.includes("youtube.com/@")){let i=n.split("youtube.com/@")[1]?.split("/")[0]?.split("?")[0]||"";return i?`@${i}`:n}}else if(e==="telegram"&&n.includes("t.me/")){let i=n.split("t.me/")[1]?.split("/")[0]?.split("?")[0]||"";return i?`@${i}`:n}return n}function dt(n){return n?n.charAt(0).toUpperCase()+n.slice(1):""}function pt(){if(typeof document>"u")return"";let n=document.currentScript;if(n&&n.src)try{return new URL(n.src,window.location.href).origin}catch{}let t=document.querySelector("script[data-project-key]");if(t&&t.src)try{return new URL(t.src,window.location.href).origin}catch{}let e=document.querySelector('script[src*="chat-widget.js"], script[src*="widget.js"]');if(e&&e.src)try{return new URL(e.src,window.location.href).origin}catch{}return""}if(typeof window<"u")try{console.log("%c[BeanTalk]%c Universal Chat Widget Started","background: #0071E3; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-weight: bold;","color: inherit; font-weight: 500;")}catch{}var E=class{constructor(t){this.sessionData=null;this.initialized=!1;this.sendQueue=[];this.isSending=!1;this.options=t,this.emitter=new L,console.log("[BeanTalk] Initializing widget instance for project:",t.projectKey);let e=pt(),i=t.apiUrl||e||(typeof window<"u"?window.location.origin:"");this.api=new R(t.projectKey,i),this.ui=new H(t,this.emitter),this.transport=new N(this.api,this.emitter),this.bindEvents(),this.bootstrap()}isReady(){return this.initialized}async processSendQueue(){if(!(this.isSending||this.sendQueue.length===0)){for(this.isSending=!0;this.sendQueue.length>0;){let t=this.sendQueue.shift(),e=this.sessionData?.conversation?.id||0,i=this.options.visitorUuid||B(),s=C();try{let a=await this.api.sendMessage(e,{visitor_uuid:i,client_message_id:t.client_message_id||S(),message:t.content||t.message||"",sender_name:t.sender_name||s||(this.options.language==="en"?"Guest":"Tamu"),page_url:window.location.href,page_title:document.title});if(a.success&&a.data){let r=a.data.conversation_id;r&&(!this.sessionData?.conversation?.id||this.sessionData.conversation.id!==r)&&(this.sessionData||(this.sessionData={}),this.sessionData.conversation?this.sessionData.conversation.id=r:this.sessionData.conversation={id:r,status:"open"},F(r),this.transport.start(r,a.data.id||0)),this.emitter.emit("message:sent",a.data)}}catch(a){console.error("[BeanTalk] Gagal mengirim pesan:",a)}}this.isSending=!1,this.transport.pollNow()}}bindEvents(){this.emitter.on("ui:send",t=>{this.sendQueue.push(t),this.processSendQueue()}),this.emitter.on("customer:rename",async t=>{let e=this.options.visitorUuid||B();try{await this.api.updateProfile(e,t)}catch(i){console.warn("[BeanTalk] Gagal update nama profil pengunjung:",i)}}),this.emitter.on("message:received",t=>{this.ui.appendMessage(t),this.emitter.emit("message",t)}),this.emitter.on("widget:opened",()=>{this.transport.setWidgetOpen(!0),this.transport.pollNow()}),this.emitter.on("widget:closed",()=>{this.transport.setWidgetOpen(!1)}),this.emitter.on("conversation:resolve",async t=>{let e=this.options.visitorUuid||B();try{if((await this.api.resolveConversation(t,e)).success){this.sessionData&&this.sessionData.conversation&&(this.sessionData.conversation.status="closed"),this.ui.updateResolvedUI(!0);let s=this.options.language==="en";this.ui.appendMessage({id:Date.now(),conversation_id:t,sender_type:"system",sender_name:"System",content:s?'You have marked this conversation as resolved. Click "Start New Chat" to open a new ticket.':'Percakapan ini telah Anda tandai selesai. Klik "Mulai Chat Baru" untuk membuat tiket baru.',created_at:new Date().toISOString()});let a=await this.api.getConversations(e);a.success&&a.data?.conversations&&(this.sessionData={...this.sessionData,conversations:a.data.conversations},this.ui.renderTicketsHistory(a.data.conversations))}}catch(i){console.error("[BeanTalk] Gagal menyelesaikan tiket:",i)}}),this.emitter.on("conversation:start-new",()=>{this.sessionData&&(this.sessionData.conversation=null),F(0),this.transport.stop(),this.ui.setMessages([]),this.ui.updateResolvedUI(!1),C()?this.ui.goToStage("chat"):this.ui.goToStage("identity")}),this.emitter.on("conversation:switch",async t=>{try{let e=await this.api.pollMessages(t,0);if(e.success&&e.data){this.sessionData||(this.sessionData={});let s=(this.sessionData?.conversations||[]).find(a=>a.id===t)?.status||"open";this.sessionData.conversation={id:t,status:s},F(t),this.ui.setMessages(e.data.messages||[]),this.ui.updateResolvedUI(s==="closed"),this.ui.goToStage("chat"),s!=="closed"?this.transport.start(t,e.data.last_id||0):this.transport.stop()}}catch(e){console.error("[BeanTalk] Gagal memuat percakapan tiket:",e)}})}async bootstrap(){let t=this.options.visitorUuid||B(),e=C();try{let i=await this.api.initSession(t,void 0,e||void 0);if(i.success&&i.data){this.sessionData=i.data;let s=i.data.widget||i.data.widget_settings;s&&(s.language==="en"||s.language==="id")&&(this.options.language=s.language),this.ui.setSessionData(i.data);let a=i.data.conversation;if(a&&a.id){F(a.id);let r=0;a.messages&&a.messages.length>0&&(r=Math.max(...a.messages.map(o=>o.id))),this.transport.start(a.id,r)}this.initialized=!0,console.log("[BeanTalk] Session established successfully:",{brand:i.data.project?.name,customerCode:i.data.visitor?.customer_code,conversationId:i.data.conversation?.id||"New Thread"}),this.emitter.emit("ready",i.data)}else console.warn("[BeanTalk] Init session warning:",i.error?.message)}catch(i){console.error("[BeanTalk] Failed to initialize chat session:",i)}}open(){console.log("[BeanTalk] Opening chat widget window"),this.ui.open()}close(){console.log("[BeanTalk] Closing chat widget window"),this.ui.close()}toggle(){this.ui.toggle()}on(t,e){return this.emitter.on(t,e),this}sendMessage(t){if(!t.trim())return;let e={id:Date.now(),conversation_id:this.sessionData?.conversation?.id||0,client_message_id:S(),sender_type:"visitor",sender_name:"Anda",content:t.trim(),message:t.trim(),created_at:new Date().toISOString()};this.ui.appendMessage(e),this.emitter.emit("ui:send",e)}},ht=E,v=null,ut=n=>g.init(n),gt=()=>g.open(),mt=()=>g.close(),vt=()=>g.toggle(),bt=(n,t)=>g.on(n,t),ft=n=>g.sendMessage(n),yt=()=>g.getInstance(),g={init(n){return v||(console.log("[BeanTalk] Creating singleton widget instance"),v=new E(n)),v},open(){v?v.open():console.warn("[BeanTalk] Widget instance not yet initialized")},close(){v?.close()},toggle(){v?.toggle()},on(n,t){v?.on(n,t)},sendMessage(n){v?.sendMessage(n)},getInstance(){return v}};if(typeof window<"u"){let n=function(){let t=document.querySelectorAll("script[data-project-key]");if(t.length>0){let e=t[0],i=e.getAttribute("data-project-key"),s="";if(e.src)try{s=new URL(e.src,window.location.href).origin}catch{}let a=e.getAttribute("data-api-url")||s||void 0,r=e.getAttribute("data-color")||void 0,o=e.getAttribute("data-brand-name")||e.getAttribute("data-store-name")||void 0,d=e.getAttribute("data-support-title")||void 0;console.log("[BeanTalk] Found embed tag on page:",{projectKey:i,apiUrl:a,brandName:o}),i&&!v&&g.init({projectKey:i,apiUrl:a,accentColor:r,brandName:o,storeName:o,supportTitle:d})}};wt=n,window.ChatWidget=g,window.UniversalChatMe=E,setTimeout(()=>{try{window.BeanTalk&&Object.assign(window.BeanTalk,g)}catch{}},0),document.readyState==="loading"?document.addEventListener("DOMContentLoaded",n):n()}var wt,xt=g;return at(kt);})();
