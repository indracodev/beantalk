export type Language = 'id' | 'en';

export interface WidgetLocale {
  liveChatAvailable: string;
  newChatSnippet: string;
  ticketsTitle: string;
  startNewChat: string;
  findUsTitleDefault: string;
  poweredBy: string;

  // Identity stage
  identityPillGuest: string;
  identityTitle: string;
  identitySubtitle: string;
  identityLabel: string;
  identityPlaceholder: string;
  identityEmailLabel: string;
  identityEmailPlaceholder: string;
  identityEmailRequired: string;
  identityContinue: string;

  // Social picker
  socialPickerTitle: (platform: string) => string;
  socialPickerHeader: (platform: string) => string;
  socialPickerSubtitle: string;
  socialPickerContactChoice: (count: number) => string;
  contactVia: (platform: string) => string;

  // Chat stage
  onlineStatus: string;
  resolveBtn: string;
  resolveConfirm: string;
  resolvedBanner: string;
  resolvedSystemNotice: string;
  askNameInline: string;
  namePlaceholder: string;
  saveBtn: string;
  typeMessagePlaceholder: string;
  typeMessageAsPlaceholder: (name: string) => string;

  // Ticket history & message bubbles
  ticketsCount: (count: number) => string;
  ticketPrefix: (id: number) => string;
  ticketStatusOpen: string;
  ticketStatusClosed: string;
  ticketDefaultSnippet: string;
  senderYou: string;
  closeAriaLabel: string;
  backAriaLabel: string;
  sendAriaLabel: string;
  openChatAriaLabel: string;
  defaultGreetingTitle: string;
  defaultGreetingSubtitle: string;
  defaultSupportTitle: string;
  offHoursBanner: string;
}

export const LOCALES: Record<Language, WidgetLocale> = {
  id: {
    liveChatAvailable: 'Live Chat Aktif',
    newChatSnippet: 'Mulai obrolan baru dengan tim kami...',
    ticketsTitle: 'Tiket & Riwayat Chat',
    startNewChat: 'Mulai Chat Baru',
    findUsTitleDefault: 'Reach Us Anywhere Else',
    poweredBy: 'Powered by BeanTalk • Web Chat',

    identityPillGuest: 'Tamu',
    identityTitle: 'Halo! Kenalan Dulu Yuk',
    identitySubtitle: 'Boleh kami tahu nama panggilan Anda? Agar tim CS kami dapat menyapa Anda dengan ramah.',
    identityLabel: 'Nama Panggilan Anda',
    identityPlaceholder: 'Contoh: Budi, Sarah, Alex...',
    identityEmailLabel: 'Alamat Email',
    identityEmailPlaceholder: 'Contoh: budi@gmail.com',
    identityEmailRequired: 'Email wajib diisi agar riwayat chat bisa dikirim ke Anda.',
    identityContinue: 'Lanjut ke Obrolan',

    socialPickerTitle: (plat) => `Hubungi via ${plat}`,
    socialPickerHeader: (plat) => `Pilih Kontak ${plat}`,
    socialPickerSubtitle: 'Pilih salah satu kontak layanan di bawah untuk terhubung langsung:',
    socialPickerContactChoice: (count) => `(${count} pilihan kontak)`,
    contactVia: (nameOrPlat) => `Hubungi via ${nameOrPlat}`,

    onlineStatus: 'Online • Membalas dalam hitungan menit',
    resolveBtn: 'Selesaikan',
    resolveConfirm: 'Apakah Anda ingin menyelesaikan tiket percakapan ini?',
    resolvedBanner: 'Tiket percakapan ini telah selesai.',
    resolvedSystemNotice: 'Percakapan ini telah Anda tandai selesai. Klik "Mulai Chat Baru" untuk membuat tiket baru.',
    askNameInline: 'Boleh tahu nama Anda?',
    namePlaceholder: 'Nama Anda...',
    saveBtn: 'Simpan',
    typeMessagePlaceholder: 'Tulis pesan ke CS...',
    typeMessageAsPlaceholder: (name) => `Tulis pesan sebagai ${name}...`,

    ticketsCount: (count) => `${count} Tiket`,
    ticketPrefix: (id) => `Tiket #${id}`,
    ticketStatusOpen: 'Open',
    ticketStatusClosed: 'Selesai',
    ticketDefaultSnippet: 'Percakapan tiket...',
    senderYou: 'Anda',
    closeAriaLabel: 'Tutup',
    backAriaLabel: 'Kembali',
    sendAriaLabel: 'Kirim',
    openChatAriaLabel: 'Buka Chat',
    defaultGreetingTitle: 'Hallo!',
    defaultGreetingSubtitle: 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!',
    defaultSupportTitle: 'Customer Support',
    offHoursBanner: 'Saat ini di luar jam kerja. Pesan Anda tetap kami terima dan akan dibalas via email.',
  },
  en: {
    liveChatAvailable: 'Live Chat Available',
    newChatSnippet: 'Start a new conversation with our team...',
    ticketsTitle: 'Tickets & Chat History',
    startNewChat: 'Start New Chat',
    findUsTitleDefault: 'Reach Us Anywhere Else',
    poweredBy: 'Powered by BeanTalk • Web Chat',

    identityPillGuest: 'Guest',
    identityTitle: "Hello! Let's get acquainted",
    identitySubtitle: 'May we know your name so our support team can address you personally?',
    identityLabel: 'Your Name / Nickname',
    identityPlaceholder: 'E.g. Alex, Sarah, John...',
    identityEmailLabel: 'Email Address',
    identityEmailPlaceholder: 'E.g. alex@gmail.com',
    identityEmailRequired: 'Email is required so we can send you the chat history.',
    identityContinue: 'Continue to Chat',

    socialPickerTitle: (plat) => `Contact via ${plat}`,
    socialPickerHeader: (plat) => `Choose ${plat} Contact`,
    socialPickerSubtitle: 'Choose one of our service contacts below to connect directly:',
    socialPickerContactChoice: (count) => `(${count} contact options)`,
    contactVia: (nameOrPlat) => `Contact via ${nameOrPlat}`,

    onlineStatus: 'Online • Replies within minutes',
    resolveBtn: 'Resolve',
    resolveConfirm: 'Do you want to resolve this conversation ticket?',
    resolvedBanner: 'This conversation ticket has ended.',
    resolvedSystemNotice: 'You have marked this conversation as resolved. Click "Start New Chat" to open a new ticket.',
    askNameInline: 'What is your name?',
    namePlaceholder: 'Your name...',
    saveBtn: 'Save',
    typeMessagePlaceholder: 'Type a message to support...',
    typeMessageAsPlaceholder: (name) => `Type a message as ${name}...`,

    ticketsCount: (count) => `${count} Tickets`,
    ticketPrefix: (id) => `Ticket #${id}`,
    ticketStatusOpen: 'Open',
    ticketStatusClosed: 'Closed',
    ticketDefaultSnippet: 'Ticket conversation...',
    senderYou: 'You',
    closeAriaLabel: 'Close',
    backAriaLabel: 'Back',
    sendAriaLabel: 'Send',
    openChatAriaLabel: 'Open Chat',
    defaultGreetingTitle: 'Hello!',
    defaultGreetingSubtitle: 'How can we help you today? Ask anything here!',
    defaultSupportTitle: 'Customer Support',
    offHoursBanner: 'Currently outside business hours. Your message will be replied to via email.',
  },
};
