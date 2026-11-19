/* layout — dipindah dari view Blade agar bisa di-cache. */
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

