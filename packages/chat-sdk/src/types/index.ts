export interface WidgetInitOptions {
  projectKey: string;
  apiUrl?: string;
  visitorUuid?: string;
  accentColor?: string;
  greetingTitle?: string;
  greetingSubtitle?: string;
  storeName?: string;
  position?: 'bottom-right' | 'bottom-left';
  whatsappNumber?: string;
  instagramHandle?: string;
  messengerUrl?: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: {
    code: string;
    message: string;
    details?: any;
  };
}

export interface WidgetSettings {
  primary_color: string;
  header_title: string;
  greeting_text: string;
  position: string;
  channel_whatsapp?: string;
  channel_messenger?: string;
  channel_instagram?: string;
}

export interface ProjectInfo {
  id: number;
  name: string;
  slug: string;
}

export interface VisitorInfo {
  id: number;
  uuid: string;
  name?: string;
}

export interface Message {
  id: number;
  conversation_id: number;
  client_message_id?: string;
  sender_type: 'visitor' | 'agent' | 'system';
  sender_name: string;
  content?: string;
  message?: string;
  attachment_url?: string;
  attachment_type?: string;
  created_at: string;
}

export interface Conversation {
  id: number;
  project_id: number;
  visitor_id: number;
  status: 'open' | 'closed' | 'assigned';
  last_message_snippet?: string;
  last_message_at?: string;
  unread_count?: number;
  messages?: Message[];
}

export interface SessionInitData {
  visitor: VisitorInfo;
  project: ProjectInfo;
  widget_settings: WidgetSettings;
  conversation: Conversation;
}
