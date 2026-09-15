<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F5F5F7] text-[#1D1D1F] antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Console') — BeanTalk</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;450;500;550;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'SF Pro Text', 'system-ui', 'sans-serif'],
                        mono: ['SF Mono', 'JetBrains Mono', 'ui-monospace', 'Menlo', 'monospace']
                    },
                    colors: {
                        apple: {
                            canvas: '#F5F5F7',
                            surface: '#FFFFFF',
                            sidebar: '#F2F2F7',
                            border: 'rgba(0, 0, 0, 0.08)',
                            subtleBorder: 'rgba(0, 0, 0, 0.04)',
                            divider: '#E5E5EA',
                            textPrimary: '#1D1D1F',
                            textSecondary: '#6E6E73',
                            textTertiary: '#86868B',
                            blue: '#0071E3',
                            blueHover: '#0077ED',
                            green: '#34C759',
                            orange: '#FF9500',
                            red: '#FF3B30',
                        }
                    },
                    boxShadow: {
                        'apple-sm': '0 1px 2px rgba(0, 0, 0, 0.04)',
                        'apple-card': '0 2px 8px -2px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03)',
                        'apple-popover': '0 12px 32px -4px rgba(0, 0, 0, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.06)',
                        'apple-modal': '0 24px 48px -12px rgba(0, 0, 0, 0.18), 0 0 1px rgba(0, 0, 0, 0.15)'
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists(public_path('css/admin.css')) ? filemtime(public_path('css/admin.css')) : time() }}">
    @stack('styles')

    <!-- CRITICAL: Early State & Anti-Flicker Script (Zero layout shift on page load) -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('beantalk_sidebar_collapsed') === 'true' && window.innerWidth > 1024) {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
                if (localStorage.getItem('beantalk_inspector_collapsed') === 'true' && window.innerWidth > 1024) {
                    document.documentElement.classList.add('inspector-is-collapsed');
                }
                if (sessionStorage.getItem('beantalk_loader_active') === 'true') {
                    document.documentElement.classList.add('beantalk-loader-active');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="h-screen h-[100dvh] max-h-[100dvh] w-full flex flex-col md:flex-row overflow-hidden font-sans select-none text-[13px] leading-normal antialiased bg-apple-canvas">

    <!-- BeanTalk Apple Global Loader Elements -->
    <div id="beantalk-progress-bar"></div>
    <div id="beantalk-loading-overlay">
        <div class="beantalk-loader-card">
            <div class="beantalk-spinner"></div>
            <span class="beantalk-loader-text" id="beantalk-loader-msg">
                <script>
                    (function() {
                        try {
                            var savedMsg = sessionStorage.getItem('beantalk_loader_msg');
                            document.write(savedMsg ? savedMsg : 'Memuat data...');
                        } catch (e) {
                            document.write('Memuat data...');
                        }
                    })();
                </script>
            </span>
        </div>
    </div>

    <!-- Mobile sidebar backdrop overlay -->
    <div class="sidebar-backdrop fixed inset-0 bg-black/30 backdrop-blur-xs z-30 md:hidden hidden" id="sidebarBackdrop" onclick="closeSidebarMobile()"></div>

    <!-- Modular Sidebar Partial (Full Height macOS style) -->
    @include('layouts.partials.sidebar')

    <!-- Main Viewport: Header, Content Panes, and Mobile Bottom Navigation -->
    <div class="flex-1 flex flex-col h-full min-h-0 min-w-0 overflow-hidden bg-apple-surface">

        <!-- Topbar -->
        <header class="h-11 md:h-12 border-b border-apple-border glass-acrylic z-20 flex items-center justify-between px-3 md:px-4 shrink-0">
            <!-- Left: Brand / Breadcrumb -->
            <div class="flex items-center gap-1.5" id="topbar-breadcrumb">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <span id="topbar-current-view" class="font-medium text-apple-textPrimary text-[12px]">@yield('header_title', 'Dashboard')</span>
                @endif
            </div>

            <!-- Center: Role Badge or Status -->
            <div class="flex items-center gap-2">
                @if(session()->has('impersonator_id'))
                    <div class="inline-flex items-center gap-1.5 bg-amber-500/10 border border-amber-300 text-amber-900 px-2 py-0.5 rounded-lg text-[10.5px] font-medium">
                        <span>Mode: <strong>{{ Auth::user()->name }}</strong></span>
                        <form action="{{ route('admin.impersonate.leave') }}" method="POST" class="inline m-0">
                            @csrf
                            <button type="submit" class="text-amber-800 underline hover:text-amber-950 ml-1 text-[10px]">Keluar</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center bg-black/5 p-0.5 rounded-lg text-[11px] font-medium text-apple-textSecondary shadow-inner">
                        <span class="px-2 py-0.5 rounded-md text-apple-textPrimary bg-white shadow-2xs font-semibold text-[10.5px]">
                            {{ ucfirst(Auth::user()->role ?? 'Staff') }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Right: Quick Status & Actions -->
            <div class="flex items-center gap-2 text-[12px]">
                <div class="hidden sm:flex items-center gap-1.5 text-apple-textSecondary bg-apple-canvas border border-apple-border px-2 py-0.5 rounded-md text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-apple-green animate-pulse"></span>
                    <span>Operational</span>
                </div>
                
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.integrations') }}" class="inline-flex items-center gap-1.5 bg-apple-textPrimary text-white hover:bg-black active:scale-[0.98] px-2.5 py-1 rounded-lg text-[11px] font-medium transition shadow-apple-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span class="hidden sm:inline">Add Website</span>
                    </a>
                @endif
            </div>
        </header>

        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mx-3 mt-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11.5px] flex items-center justify-between shadow-2xs" id="flashAlert">
                <div class="flex items-center gap-2">
                    <span class="text-apple-green font-bold">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="text-emerald-700 hover:text-emerald-900" onclick="document.getElementById('flashAlert').remove()">&times;</button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mx-3 mt-2 px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-[11.5px] flex items-center justify-between shadow-2xs" id="flashAlertError">
                <div class="flex items-center gap-2">
                    <span class="text-apple-red font-bold">✕</span>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button type="button" class="text-red-700 hover:text-red-900" onclick="document.getElementById('flashAlertError').remove()">&times;</button>
            </div>
        @endif

        <!-- Main Content Slot -->
        <main class="flex-1 flex min-h-0 min-w-0 overflow-hidden relative">
            @yield('content')
        </main>

        <!-- Mobile Bottom Navigation Bar (iOS / macOS Inspired) -->
        @include('layouts.partials.bottom-nav')
    </div>

    <!-- Notification Toast -->
    <div id="apple-toast" class="fixed bottom-5 right-5 z-50 bg-white/95 backdrop-blur-md border border-apple-border shadow-apple-popover rounded-xl p-3 flex items-start gap-2.5 max-w-sm transform translate-y-12 opacity-0 pointer-events-none transition-all duration-200 ease-out">
        <div class="w-4 h-4 rounded-full bg-apple-blue/10 text-apple-blue flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
        <div class="flex-1 min-w-0">
            <div id="toast-title" class="font-semibold text-[12px] text-apple-textPrimary">Notice</div>
            <div id="toast-message" class="text-[11px] text-apple-textSecondary leading-snug">Action completed.</div>
        </div>
    </div>

    <!-- Global Admin Scripts -->
    <script src="{{ asset('js/admin.js') }}?v={{ file_exists(public_path('js/admin.js')) ? filemtime(public_path('js/admin.js')) : time() }}"></script>
    @stack('scripts')
</body>
</html>
