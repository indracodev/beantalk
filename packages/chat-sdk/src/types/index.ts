export interface WidgetInitOptions {
  projectKey: string;
  apiUrl?: string;
  visitorUuid?: string;
  accentColor?: string;
  greetingTitle?: string;
  greetingSubtitle?: string;
  storeName?: string;
  brandName?: string;
  supportTitle?: string;
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

export interface SocialChannel {
  id: string;
  name: string;
  enabled: boolean;
  url: string;
  icon?: string;
}

export interface WidgetSettings {
  primary_color: string;
  accent_color?: string;
  header_title?: string;
  greeting_text?: string;
  greeting_title?: string;
  greeting_subtitle?: string;
  support_title?: string;
  position?: string;
  is_online?: boolean;
  find_us_title?: string;
  social_channels?: SocialChannel[];
  channel_whatsapp?: string;
  channel_messenger?: string;
  channel_instagram?: string;
}

export interface ProjectInfo {
  id: number;
  name: string;
  slug?: string;
}

export interface VisitorInfo {
  id?: number;
  uuid: string;
  name?: string;
  customer_code?: string;
  display_name?: string;
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
  project_id?: number;
  visitor_id?: number;
  status: 'open' | 'closed' | 'assigned' | 'pending';
  last_message_snippet?: string;
  last_message_at?: string;
  unread_count?: number;
  messages?: Message[];
}

export interface SessionInitData {
  visitor: VisitorInfo;
  project: ProjectInfo;
  widget?: WidgetSettings;
  widget_settings?: WidgetSettings;
  conversation: Conversation;
}
