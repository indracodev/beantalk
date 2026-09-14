const STORAGE_KEY_VISITOR = 'beantalk_visitor_uuid';
const STORAGE_KEY_CONV = 'beantalk_last_conv_id';

let memoryFallback: Record<string, string> = {};

function safeGet(key: string): string | null {
  try {
    return window.localStorage.getItem(key);
  } catch (e) {
    return memoryFallback[key] || null;
  }
}

function safeSet(key: string, value: string): void {
  try {
    window.localStorage.setItem(key, value);
  } catch (e) {
    memoryFallback[key] = value;
  }
}

export function generateUuid(): string {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

export function generateClientMessageId(): string {
  return 'msg_' + Date.now().toString(36) + '_' + Math.random().toString(36).substring(2, 9);
}

export function getOrCreateVisitorUuid(): string {
  let uuid = safeGet(STORAGE_KEY_VISITOR);
  if (!uuid) {
    uuid = generateUuid();
    safeSet(STORAGE_KEY_VISITOR, uuid);
  }
  return uuid;
}

export function setVisitorUuid(uuid: string): void {
  safeSet(STORAGE_KEY_VISITOR, uuid);
}

export function getLastConversationId(): number | null {
  const val = safeGet(STORAGE_KEY_CONV);
  return val ? parseInt(val, 10) : null;
}

export function setLastConversationId(id: number): void {
  safeSet(STORAGE_KEY_CONV, id.toString());
}

const STORAGE_KEY_NAME = 'beantalk_customer_name';

export function getStoredCustomerName(): string | null {
  return safeGet(STORAGE_KEY_NAME);
}

export function setStoredCustomerName(name: string): void {
  safeSet(STORAGE_KEY_NAME, name);
}
