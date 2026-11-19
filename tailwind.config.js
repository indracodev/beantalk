/**
 * Tailwind CSS v3 — dikompilasi sekali ke public/css/tailwind.css (di-commit; server tidak butuh Node).
 * Build: npm run build:css   |   saat mengembangkan UI: npm run watch:css
 */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './public/js/**/*.js',
        '!./public/js/chat-widget.js',
        './app/**/*.php', // class yang disusun di PHP (mis. warna avatar di DashboardMetrics)
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'SF Pro Text', 'system-ui', 'sans-serif'],
                mono: ['SF Mono', 'JetBrains Mono', 'ui-monospace', 'Menlo', 'monospace'],
            },
            colors: {
                // Skala `blue-*` bawaan diganti skala biji kopi agar semua aksen info/utama di view ikut tema BeanTalk.
                // (`sky-*` tetap = warna resmi Telegram; `indigo-*` = warna sekunder badge/peran.)
                blue: {
                    50: '#F8F4F1',
                    100: '#EFE6DF',
                    200: '#E0CFC2',
                    300: '#CBB09B',
                    400: '#B08C72',
                    500: '#93705A',
                    600: '#7A5A44',
                    700: '#664A38',
                    800: '#533C2E',
                    900: '#443226',
                    950: '#261B14',
                },
                // Palet "Roasted Bean" (BeanTalk = biji kopi): aksen cokelat sangrai, netral abu batu hangat.
                // Semua hex solid (bukan rgba): modifier /80 Tailwind v3 mengganti alpha.
                // Harus sama dengan :root di public/css/admin/01-base.css.
                apple: {
                    canvas: '#F5F4F2',
                    surface: '#FFFFFF',
                    sidebar: '#EBE7E2', // oat
                    border: '#E5E1DC',
                    subtleBorder: '#EFECE8',
                    divider: '#E5E1DC',
                    textPrimary: '#322E2A', // espresso
                    inkHover: '#45403A',
                    textSecondary: '#645D56',
                    textTertiary: '#847C74',
                    blue: '#7A5A44', // aksen utama: biji kopi sangrai (nama token dipertahankan)
                    blueHover: '#694B37',
                    green: '#3FA672',
                    orange: '#D9922E',
                    red: '#D9534F',
                },
            },
            boxShadow: {
                'apple-sm': '0 1px 2px rgba(50, 46, 42, 0.04)',
                'apple-card': '0 1px 3px rgba(50, 46, 42, 0.04)',
                'apple-popover': '0 10px 28px -6px rgba(50, 46, 42, 0.12), 0 2px 6px rgba(50, 46, 42, 0.05)',
                'apple-modal': '0 20px 44px -12px rgba(50, 46, 42, 0.18), 0 0 0 1px rgba(50, 46, 42, 0.05)',
            },
        },
    },
};
