<!DOCTYPE html>
<html lang="id" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke BeanTalk — Universal Customer Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
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

    <!-- External Script for Login -->
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
