<!-- BeanTalk iOS / macOS Inspired Mobile Bottom Navigation Bar -->
@php
    $isSuper = Auth::user()->isSuperAdmin();
    $dashboardActive = request()->routeIs('admin.dashboard*');
    $inboxActive = request()->routeIs('admin.inbox*');
    $integrationsActive = request()->routeIs('admin.integrations*');
    $teamActive = request()->routeIs('admin.team*');
    $logsActive = request()->routeIs('admin.logs*');
    
    // Check if on an active mobile chat thread
    $isMobileChatActive = $inboxActive && request()->route('id') !== null;
@endphp

<nav id="mobile-bottom-nav" class="md:hidden shrink-0 w-full bg-white/95 backdrop-blur-lg border-t border-apple-border z-30 flex items-center justify-around px-1.5 py-1.5 transition-transform duration-200 {{ $isMobileChatActive ? 'hidden' : 'flex' }}" style="padding-bottom: max(6px, env(safe-area-inset-bottom));">
    <!-- 1. Executive Dashboard -->
    <a href="{{ route('admin.dashboard') }}" id="bottomNavItemDashboard" data-loading-msg="Memuat Dashboard..."
       class="flex flex-col items-center justify-center flex-1 py-1 rounded-lg transition relative {{ $dashboardActive ? 'text-apple-blue font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary font-normal' }}">
        <svg class="w-5 h-5 {{ $dashboardActive ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        <span class="text-[9.5px] mt-0.5 tracking-tight">Overview</span>
    </a>

    <!-- 2. Live Inbox -->
    <a href="{{ route('admin.inbox') }}" id="bottomNavItemInbox" data-loading-msg="Memuat Inbox..."
       class="flex flex-col items-center justify-center flex-1 py-1 rounded-lg transition relative {{ $inboxActive ? 'text-apple-blue font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary font-normal' }}">
        <div class="relative">
            <svg class="w-5 h-5 {{ $inboxActive ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect width="20" height="16" x="2" y="4" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
            <span id="mobileBottomUnreadBadge" 
                  class="absolute -top-1 -right-2 text-[9px] font-bold px-1 rounded-full bg-apple-red text-white leading-tight {{ ($totalUnreadConversations ?? 0) > 0 ? 'inline-block' : 'hidden' }}">
                {{ ($totalUnreadConversations ?? 0) > 9 ? '9+' : ($totalUnreadConversations ?? 0) }}
            </span>
        </div>
        <span class="text-[9.5px] mt-0.5 tracking-tight">Inbox</span>
    </a>

    @if($isSuper)
        <!-- 3. Websites / Integrations -->
        <a href="{{ route('admin.integrations') }}" id="bottomNavItemWebsites" data-loading-msg="Memuat Integrations..."
           class="flex flex-col items-center justify-center flex-1 py-1 rounded-lg transition {{ $integrationsActive ? 'text-apple-blue font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary font-normal' }}">
            <svg class="w-5 h-5 {{ $integrationsActive ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight">Websites</span>
        </a>

        <!-- 4. Team & RBAC -->
        <a href="{{ route('admin.team') }}" id="bottomNavItemTeam" data-loading-msg="Memuat Team..."
           class="flex flex-col items-center justify-center flex-1 py-1 rounded-lg transition {{ $teamActive ? 'text-apple-blue font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary font-normal' }}">
            <svg class="w-5 h-5 {{ $teamActive ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight">Team</span>
        </a>

        <!-- 5. Logs -->
        <a href="{{ route('admin.logs') }}" id="bottomNavItemLogs" data-loading-msg="Memuat Activity Logs..."
           class="flex flex-col items-center justify-center flex-1 py-1 rounded-lg transition {{ $logsActive ? 'text-apple-blue font-semibold' : 'text-apple-textSecondary hover:text-apple-textPrimary font-normal' }}">
            <svg class="w-5 h-5 {{ $logsActive ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <span class="text-[9.5px] mt-0.5 tracking-tight">Logs</span>
        </a>
    @endif

    <!-- 6. Profile & Logout Option -->
    <div class="flex flex-col items-center justify-center flex-1 py-1 relative">
        <form action="{{ route('logout') }}" method="POST" class="m-0 flex flex-col items-center justify-center" onsubmit="return confirm('Keluar dari sesi admin?')">
            @csrf
            <button type="submit" class="flex flex-col items-center justify-center text-apple-textSecondary hover:text-apple-red transition cursor-pointer">
                <div class="w-5 h-5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-bold text-[9px] flex items-center justify-center shadow-2xs">
                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', Auth::user()->name ?? 'U'), 0, 2)) }}
                </div>
                <span class="text-[9.5px] mt-0.5 tracking-tight">Keluar</span>
            </button>
        </form>
    </div>
</nav>
