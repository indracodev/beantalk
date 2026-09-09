<!DOCTYPE html>
<html lang="id" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') — BeanTalk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ==========================================================================
           DESIGN TOKENS — DEFAULT LIGHT THEME (BUKAN SYSTEM)
           ========================================================================== */
        :root, html.theme-light {
            --bg-canvas: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-sidebar: #FFFFFF;
            --bg-subtle: #F1F5F9;
            --bg-active: #E2E8F0;
            --border: #E2E8F0;
            --border-hover: #CBD5E1;
            --border-focus: #C59B27;
            --accent: #C59B27;
            --accent-hover: #AF851A;
            --text-main: #0F172A;
            --text-sub: #475569;
            --text-muted: #94A3B8;
            --status-online: #10B981;
            --status-busy: #F59E0B;
            --status-offline: #94A3B8;
            --badge-bg: #E2E8F0;
            --badge-text: #0F172A;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            --header-border: #E2E8F0;
        }

        html.theme-dark {
            --bg-canvas: #0B0F17;
            --bg-surface: #111827;
            --bg-sidebar: #0F172A;
            --bg-subtle: #1E293B;
            --bg-active: #334155;
            --border: #1F2937;
            --border-hover: #374151;
            --border-focus: #C59B27;
            --accent: #C59B27;
            --accent-hover: #D4AF37;
            --text-main: #F8FAFC;
            --text-sub: #94A3B8;
            --text-muted: #64748B;
            --status-online: #10B981;
            --status-busy: #F59E0B;
            --status-offline: #64748B;
            --badge-bg: #1E293B;
            --badge-text: #F8FAFC;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            --header-border: #1F2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ==========================================================================
           COLLAPSIBLE SIDEBAR
           ========================================================================== */
        .sidebar {
            width: 230px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 20;
        }

        .sidebar.collapsed {
            width: 68px;
        }

        .sidebar-header {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-main);
            overflow: hidden;
            white-space: nowrap;
        }

        .brand-logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--accent) 0%, #8C6D1F 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            flex-shrink: 0;
        }

        .brand-logo-text {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        .sidebar.collapsed .brand-logo-text {
            display: none;
        }

        .sidebar-toggle-btn {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-muted);
            width: 28px;
            height: 28px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .sidebar-toggle-btn:hover {
            border-color: var(--border-hover);
            color: var(--text-main);
            background: var(--bg-subtle);
        }

        .sidebar.collapsed .sidebar-toggle-btn {
            position: absolute;
            right: 20px;
            transform: rotate(180deg);
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            padding: 8px 10px 4px;
            margin-bottom: 2px;
        }

        .sidebar.collapsed .nav-label {
            display: none;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--text-sub);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.12s ease;
            position: relative;
            white-space: nowrap;
        }

        .nav-item:hover {
            background: var(--bg-subtle);
            color: var(--text-main);
        }

        .nav-item.active {
            background: var(--bg-subtle);
            color: var(--accent);
            font-weight: 700;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-text {
            flex: 1;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .nav-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 12px;
            background: var(--badge-bg);
            color: var(--badge-text);
        }

        .sidebar.collapsed .nav-badge {
            position: absolute;
            top: 4px;
            right: 6px;
            padding: 2px 5px;
            font-size: 10px;
        }

        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            background: var(--bg-subtle);
            overflow: hidden;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--accent);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            flex-shrink: 0;
            position: relative;
        }

        .user-status-dot {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--status-online);
            border: 2px solid var(--bg-sidebar);
        }

        .user-info {
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar.collapsed .user-info {
            display: none;
        }

        .user-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .user-role-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--accent);
            letter-spacing: 0.04em;
        }

        .footer-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .footer-btn {
            flex: 1;
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            color: var(--text-sub);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .footer-btn:hover {
            border-color: var(--border-hover);
            color: var(--text-main);
            background: var(--bg-subtle);
        }

        .sidebar.collapsed .footer-actions {
            flex-direction: column;
        }

        .sidebar.collapsed .footer-btn span {
            display: none;
        }

        /* ==========================================================================
           MAIN CONTENT AREA
           ========================================================================== */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-canvas);
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Collapsible Sidebar -->
    <aside class="sidebar" id="mainSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.inbox') }}" class="sidebar-brand">
                <div class="brand-logo-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <span class="brand-logo-text">BeanTalk</span>
            </a>
            <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" onclick="toggleSidebar()" title="Minimize / Expand Sidebar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Percakapan</div>
            <a href="{{ route('admin.inbox') }}" class="nav-item {{ request()->routeIs('admin.inbox*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </span>
                <span class="nav-text">Live Inbox</span>
            </a>

            <div class="nav-label">Manajemen</div>
            <a href="{{ route('admin.integrations') }}" class="nav-item {{ request()->routeIs('admin.integrations*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </span>
                <span class="nav-text">Integrasi Web</span>
            </a>

            @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.team') }}" class="nav-item {{ request()->routeIs('admin.team*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </span>
                <span class="nav-text">Tim CS</span>
            </a>
            @endif

            <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </span>
                <span class="nav-text">Activity Logs</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->username ?? Auth::user()->name, 0, 2)) }}
                    <span class="user-status-dot"></span>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role-badge">{{ Auth::user()->role }}</div>
                </div>
            </div>

            <div class="footer-actions">
                <button type="button" class="footer-btn" onclick="toggleTheme()" id="themeBtn" title="Ganti Tema">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="themeSvg">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <span id="themeLabelText">Dark</span>
                </button>

                <form action="{{ route('logout') }}" method="POST" style="flex: 1; display: flex;">
                    @csrf
                    <button type="submit" class="footer-btn" style="width: 100%; color: #EF4444;" title="Keluar">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-wrapper">
        @yield('content')
    </main>

    <script>
        // ======================================================================
        // SIDEBAR COLLAPSE TOGGLE
        // ======================================================================
        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('beantalk_sidebar_collapsed', sidebar.classList.contains('collapsed'));
        }

        if (localStorage.getItem('beantalk_sidebar_collapsed') === 'true') {
            document.getElementById('mainSidebar').classList.add('collapsed');
        }

        // ======================================================================
        // THEME TOGGLE — DEFAULT LIGHT (BUKAN SISTEM)
        // ======================================================================
        function applyTheme(theme) {
            const html = document.documentElement;
            const label = document.getElementById('themeLabelText');
            if (theme === 'dark') {
                html.classList.remove('theme-light');
                html.classList.add('theme-dark');
                if (label) label.textContent = 'Light';
            } else {
                html.classList.remove('theme-dark');
                html.classList.add('theme-light');
                if (label) label.textContent = 'Dark';
            }
        }

        function toggleTheme() {
            const current = document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('beantalk_theme', next);
            applyTheme(next);
        }

        applyTheme(localStorage.getItem('beantalk_theme') === 'dark' ? 'dark' : 'light');
    </script>
    @stack('scripts')
</body>
</html>
