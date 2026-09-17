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
  language?: 'id' | 'en';
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
  language?: 'id' | 'en';
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
  bot_enabled?: boolean;
  bot_name?: string;
  bot_welcome_message?: string;
}

export interface BusinessHoursData {
  enabled: boolean;
  schedule: Record<string, { enabled: boolean; start: string | null; end: string | null }>;
  timezone: string;
  holidays: Array<{ date: string; label?: string }>;
  off_message: string;
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
  email?: string;
  customer_code?: string;
  display_name?: string;
}

export interface Message {
  id: number;
  conversation_id: number;
  client_message_id?: string;
  sender_type: 'visitor' | 'agent' | 'bot' | 'system';
  sender_name: string;
  content?: string;
  message?: string;
  metadata?: any;
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

export interface ConversationTicketItem {
  id: number;
  status: 'open' | 'closed' | 'assigned' | 'pending';
  channel?: string;
  channel_label?: string;
  last_message_preview?: string;
  last_message_at?: string;
  unread_visitor_count?: number;
  created_at?: string;
}

export interface SessionInitData {
  visitor: VisitorInfo;
  project: ProjectInfo;
  widget?: WidgetSettings;
  widget_settings?: WidgetSettings;
  conversation?: Conversation | null;
  conversations?: ConversationTicketItem[];
  business_hours?: BusinessHoursData;
  is_within_business_hours?: boolean;
}
