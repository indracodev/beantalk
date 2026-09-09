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
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>

    <!-- Modular Sidebar Partial -->
    @include('layouts.partials.sidebar')

    <!-- Main Content Area -->
    <main class="main-wrapper">
        @if(session('success'))
            <div class="alert-banner alert-success" id="flashAlert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('flashAlert').remove()">&times;</button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert-banner alert-danger" id="flashAlertError">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('flashAlertError').remove()">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Admin Scripts -->
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
