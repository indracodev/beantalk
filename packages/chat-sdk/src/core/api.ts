import { ApiResponse, SessionInitData, Message } from '../types';

export class ApiClient {
  private baseUrl: string;
  private projectKey: string;

  constructor(projectKey: string, baseUrl: string = '') {
    this.projectKey = projectKey;
    // Normalize baseUrl: remove trailing slash
    this.baseUrl = baseUrl.replace(/\/+$/, '');
  }

  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${this.baseUrl}${endpoint}`;
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Project-Key': this.projectKey,
      ...((options.headers as Record<string, string>) || {}),
    };

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 12000);

    try {
      const response = await fetch(url, {
        ...options,
        headers,
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      const json = await response.json();
      return json as ApiResponse<T>;
    } catch (err: any) {
      clearTimeout(timeoutId);
      return {
        success: false,
        error: {
          code: err.name === 'AbortError' ? 'TIMEOUT' : 'NETWORK_ERROR',
          message: err.message || 'Gagal terhubung ke server chat.',
        },
      };
    }
  }

  async initSession(visitorUuid: string, metadata?: Record<string, any>, name?: string): Promise<ApiResponse<SessionInitData>> {
    return this.request<SessionInitData>('/api/v1/client/session/init', {
      method: 'POST',
      body: JSON.stringify({
        visitor_uuid: visitorUuid,
        name: name,
        project_key: this.projectKey,
        page_url: window.location.href,
        page_title: document.title,
        client_url: window.location.href,
        metadata: {
          referrer: document.referrer,
          userAgent: navigator.userAgent,
          title: document.title,
          ...metadata,
        },
      }),
    });
  }

  async updateProfile(visitorUuid: string, name: string): Promise<ApiResponse<{ visitor: any }>> {
    return this.request<{ visitor: any }>('/api/v1/client/session/profile', {
      method: 'POST',
      body: JSON.stringify({
        visitor_uuid: visitorUuid,
        name: name,
      }),
    });
  }

  async pollMessages(conversationId: number, afterId: number = 0): Promise<ApiResponse<{ messages: Message[]; last_id: number; has_more: boolean }>> {
    return this.request<{ messages: Message[]; last_id: number; has_more: boolean }>(
      `/api/v1/client/conversations/${conversationId}/messages?after_id=${afterId}`,
      { method: 'GET' }
    );
  }

  async sendMessage(
    conversationId: number,
    payload: {
      client_message_id: string;
      message: string;
      sender_name?: string;
      visitor_uuid?: string;
      page_url?: string;
      page_title?: string;
    }
  ): Promise<ApiResponse<Message>> {
    const targetId = conversationId && conversationId > 0 ? conversationId : 0;
    return this.request<Message>(`/api/v1/client/conversations/${targetId}/messages`, {
      method: 'POST',
      body: JSON.stringify({
        visitor_uuid: payload.visitor_uuid,
        client_message_id: payload.client_message_id,
        content: payload.message,
        message: payload.message,
        sender_name: payload.sender_name,
        page_url: payload.page_url,
        page_title: payload.page_title,
      }),
    });
  }

  async resolveConversation(conversationId: number, visitorUuid: string): Promise<ApiResponse<{ id: number; status: string }>> {
    return this.request<{ id: number; status: string }>(`/api/v1/client/conversations/${conversationId}/resolve`, {
      method: 'POST',
      body: JSON.stringify({
        visitor_uuid: visitorUuid,
      }),
    });
  }

  async getConversations(visitorUuid: string): Promise<ApiResponse<{ conversations: any[] }>> {
    return this.request<{ conversations: any[] }>(`/api/v1/client/conversations?visitor_uuid=${encodeURIComponent(visitorUuid)}`, {
      method: 'GET',
    });
  }
}
