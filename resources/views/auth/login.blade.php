<!DOCTYPE html>
<html lang="id" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke BeanTalk — Universal Customer Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ==========================================================================
           DESIGN TOKENS — DEFAULT IS EXPLICITLY LIGHT (BUKAN SISTEM)
           ========================================================================== */
        :root, html.theme-light {
            --bg-primary: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-card: #FFFFFF;
            --border: #E2E8F0;
            --border-hover: #CBD5E1;
            --border-focus: #C59B27;
            --accent: #C59B27;
            --accent-hover: #AF851A;
            --btn-primary-bg: #0F172A;
            --btn-primary-hover: #1E293B;
            --btn-primary-text: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --input-bg: #F8FAFC;
            --input-border: #D1D5DB;
            --input-text: #0F172A;
            --card-shadow: 0 20px 45px -15px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(0, 0, 0, 0.03);
            --chip-bg: #F1F5F9;
            --chip-border: #E2E8F0;
            --chip-hover-bg: rgba(197, 155, 39, 0.08);
            --chip-role-text: #0F172A;
            --error-bg: rgba(239, 68, 68, 0.07);
            --error-border: rgba(239, 68, 68, 0.25);
            --error-text: #DC2626;
            --success-bg: rgba(16, 185, 129, 0.07);
            --success-border: rgba(16, 185, 129, 0.25);
            --success-text: #059669;
            --bg-glow-1: rgba(197, 155, 39, 0.05);
            --bg-glow-2: rgba(15, 23, 42, 0.03);
        }

        /* Mode Dark opsional hanya aktif bila user sengaja memilihnya */
        html.theme-dark {
            --bg-primary: #0B0F17;
            --bg-surface: #111827;
            --bg-card: rgba(17, 24, 39, 0.88);
            --border: #1F2937;
            --border-hover: #374151;
            --border-focus: #C59B27;
            --accent: #C59B27;
            --accent-hover: #D4AF37;
            --btn-primary-bg: #C59B27;
            --btn-primary-hover: #D4AF37;
            --btn-primary-text: #0B0F17;
            --text-primary: #F9FAFB;
            --text-secondary: #9CA3AF;
            --text-muted: #6B7280;
            --input-bg: rgba(31, 41, 55, 0.6);
            --input-border: #374151;
            --input-text: #F9FAFB;
            --card-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            --chip-bg: rgba(31, 41, 55, 0.5);
            --chip-border: #1F2937;
            --chip-hover-bg: rgba(197, 155, 39, 0.12);
            --chip-role-text: #F9FAFB;
            --error-bg: rgba(239, 68, 68, 0.12);
            --error-border: rgba(239, 68, 68, 0.3);
            --error-text: #FCA5A5;
            --success-bg: rgba(16, 185, 129, 0.12);
            --success-border: rgba(16, 185, 129, 0.3);
            --success-text: #6EE7B7;
            --bg-glow-1: rgba(197, 155, 39, 0.08);
            --bg-glow-2: rgba(59, 130, 246, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.15s ease;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            background-image: 
                radial-gradient(circle at 15% 15%, var(--bg-glow-1) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, var(--bg-glow-2) 0%, transparent 45%);
        }

        /* Top Bar Controls */
        .top-nav {
            position: absolute;
            top: 24px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-toggle-btn {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            border-radius: 8px;
            padding: 7px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .theme-toggle-btn:hover {
            border-color: var(--border-hover);
            color: var(--text-primary);
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: var(--card-shadow);
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent) 0%, #8C6D1F 100%);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(197, 155, 39, 0.25);
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .heading-section {
            margin-bottom: 24px;
        }

        .heading-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .heading-desc {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success-text);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--input-text);
            font-size: 14px;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            background: var(--bg-surface);
            box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.15);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input {
            accent-color: var(--accent);
            width: 16px;
            height: 16px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px 20px;
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .btn-submit:hover {
            background: var(--btn-primary-hover);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .quick-accounts {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .quick-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 12px;
        }

        .account-chips {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .account-chip {
            background: var(--chip-bg);
            border: 1px solid var(--chip-border);
            border-radius: 8px;
            padding: 9px 11px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: left;
        }

        .account-chip:hover {
            border-color: var(--accent);
            background: var(--chip-hover-bg);
        }

        .chip-role {
            font-weight: 700;
            color: var(--chip-role-text);
            display: block;
            margin-bottom: 2px;
        }

        .chip-user {
            color: var(--text-muted);
            font-size: 11px;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Theme Control -->
    <div class="top-nav">
        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()">
            <span id="themeIcon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </span>
            <span id="themeLabel">Dark Mode</span>
        </button>
    </div>

    <div class="login-card">
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div>
                <div class="brand-title">BeanTalk</div>
                <div class="brand-subtitle">Universal Customer Chat</div>
            </div>
        </div>

        <div class="heading-section">
            <h1 class="heading-title">Selamat Datang</h1>
            <p class="heading-desc">Masuk menggunakan email atau username Anda.</p>
        </div>

        @if ($errors->has('login'))
            <div class="alert alert-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf

            <!-- Email atau Username -->
            <div class="form-group">
                <label class="form-label" for="login">Email atau Username</label>
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="form-input" 
                        placeholder="superadmin atau superadmin@indraco.com" 
                        value="{{ old('login') }}" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••" 
                        required
                    >
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Masuk ke Dashboard</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <!-- Quick Fill Helpers -->
        <div class="quick-accounts">
            <div class="quick-title">Akun Bawaan (Klik untuk Isi Cepat)</div>
            <div class="account-chips" style="grid-template-columns: 1.1fr 1fr 1fr;">
                <button type="button" class="account-chip" onclick="fillCredentials('superadmin', 'password')">
                    <span class="chip-role">Superadmin</span>
                    <span class="chip-user">superadmin / ...</span>
                </button>
                <button type="button" class="account-chip" onclick="fillCredentials('sarah', 'password')">
                    <span class="chip-role">Staff CS (Sarah)</span>
                    <span class="chip-user">sarah / sarah@...</span>
                </button>
                <button type="button" class="account-chip" onclick="fillCredentials('budi', 'password')">
                    <span class="chip-role">Staff CS (Budi)</span>
                    <span class="chip-user">budi / budi@...</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(user, pass) {
            document.getElementById('login').value = user;
            document.getElementById('password').value = pass;
        }

        // ======================================================================
        // THEME HANDLER — DEFAULT IS EXPLICITLY 'light' (BUKAN SYSTEM)
        // ======================================================================
        function getInitialTheme() {
            // Selalu default ke 'light', kecuali user secara eksplisit pernah memilih 'dark'
            return localStorage.getItem('beantalk_theme') === 'dark' ? 'dark' : 'light';
        }

        function applyTheme(theme) {
            const html = document.documentElement;
            const label = document.getElementById('themeLabel');
            const icon = document.getElementById('themeIcon');

            if (theme === 'dark') {
                html.classList.remove('theme-light');
                html.classList.add('theme-dark');
                label.textContent = 'Light Mode';
                icon.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
            } else {
                html.classList.remove('theme-dark');
                html.classList.add('theme-light');
                label.textContent = 'Dark Mode';
                icon.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
            }
        }

        function toggleTheme() {
            const current = document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('beantalk_theme', next);
            applyTheme(next);
        }

        // Terapkan default light saat halaman dimuat
        applyTheme(getInitialTheme());
    </script>
</body>
</html>
