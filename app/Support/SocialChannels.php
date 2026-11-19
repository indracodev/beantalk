<?php

namespace App\Support;

/**
 * Kanal "Find Us Somewhere Else" di widget: daftar platform, normalisasi URL,
 * dan konversi input form (format baru JSON list atau format lama dictionary).
 */
class SocialChannels
{
    public const PLATFORMS = [
        'whatsapp' => ['name' => 'WhatsApp', 'placeholder' => '08123456789 atau https://wa.me/...'],
        'instagram' => ['name' => 'Instagram', 'placeholder' => '@username atau https://instagram.com/...'],
        'facebook' => ['name' => 'Facebook', 'placeholder' => 'username atau https://facebook.com/...'],
        'tiktok' => ['name' => 'TikTok', 'placeholder' => '@username atau https://tiktok.com/@...'],
        'youtube' => ['name' => 'YouTube', 'placeholder' => '@channel atau https://youtube.com/@...'],
        'telegram' => ['name' => 'Telegram', 'placeholder' => '@username atau https://t.me/...'],
        'shopee' => ['name' => 'Shopee Store', 'placeholder' => 'https://shopee.co.id/...'],
        'tokopedia' => ['name' => 'Tokopedia Store', 'placeholder' => 'https://tokopedia.com/...'],
        'custom' => ['name' => 'Custom Link', 'placeholder' => 'https://...'],
    ];

    private const HANDLE_URLS = [
        'instagram' => 'https://instagram.com/',
        'facebook' => 'https://facebook.com/',
        'tiktok' => 'https://tiktok.com/@',
        'youtube' => 'https://youtube.com/@',
        'telegram' => 'https://t.me/',
    ];

    /** Daftar untuk view: key => meta (+ icon). */
    public static function available(): array
    {
        $list = [];
        foreach (self::PLATFORMS as $key => $meta) {
            $list[$key] = $meta + ['icon' => $key === 'custom' ? 'link' : $key];
        }

        return $list;
    }

    /** Nomor/username → URL lengkap. Hanya http(s) yang diterima (tolak javascript:, data:, dsb.). */
    public static function normalizeUrl(string $platform, string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (! preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            if ($platform === 'whatsapp') {
                $number = preg_replace('/\D/', '', $url);
                $url = 'https://wa.me/'.(str_starts_with($number, '0') ? '62'.substr($number, 1) : $number);
            } elseif (isset(self::HANDLE_URLS[$platform])) {
                $url = self::HANDLE_URLS[$platform].ltrim($url, '@/');
            } else {
                $url = 'https://'.ltrim($url, '/');
            }
        }

        return self::isHttpUrl($url) ? $url : '';
    }

    public static function isHttpUrl(string $url): bool
    {
        return (bool) preg_match('#^https?://[^\s<>"\']+$#i', $url) && filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Input form → array tersimpan. $list = format baru (array/JSON string),
     * $legacy = format lama ['whatsapp' => ['enabled' => 1, 'url' => ...]].
     */
    public static function fromInput(mixed $list, array $legacy = []): array
    {
        if (is_string($list)) {
            $list = json_decode($list, true);
        }

        if (! empty($list) && is_array($list)) {
            return array_values(array_filter(array_map(
                fn ($item, $idx) => is_array($item) ? self::normalizeItem($item, $item['id'] ?? null, $idx) : null,
                $list,
                array_keys($list),
            )));
        }

        $items = [];
        foreach (array_keys(self::PLATFORMS) as $key) {
            if ($key === 'custom') {
                continue;
            }
            // 'messenger' lama dipetakan ke 'facebook'.
            $input = $legacy[$key] ?? ($key === 'facebook' ? ($legacy['messenger'] ?? []) : []);
            $items[] = self::normalizeItem(['platform' => $key] + (array) $input, $key, null);
        }

        return $items;
    }

    /** Data tersimpan → baris editor (lewati entri lama yang kosong total). */
    public static function forEditor(?array $stored): array
    {
        $rows = [];
        foreach ($stored ?? [] as $key => $item) {
            if (! is_array($item) || (trim($item['url'] ?? '') === '' && empty($item['enabled']))) {
                continue;
            }
            $item['platform'] ??= $item['icon'] ?? (is_string($key) ? $key : 'whatsapp');
            $row = self::normalizeItem($item, $item['id'] ?? (is_string($key) ? $key : null), null, keepUrl: true);
            $row['enabled'] = ! empty($item['enabled']);
            $rows[] = $row;
        }

        return $rows;
    }

    private static function normalizeItem(array $item, ?string $id, ?int $idx, bool $keepUrl = false): array
    {
        $platform = strtolower(trim($item['platform'] ?? $item['icon'] ?? 'whatsapp'));
        $platform = $platform === 'messenger' ? 'facebook' : $platform;
        $url = $keepUrl ? trim($item['url'] ?? '') : self::normalizeUrl($platform, (string) ($item['url'] ?? ''));
        $customIcon = trim($item['custom_icon'] ?? '');
        $customIcon = $customIcon !== '' && ! self::isHttpUrl($customIcon) && ! str_starts_with($customIcon, '/') ? '' : $customIcon;

        return [
            'id' => trim($id ?? ($platform.'_'.(($idx ?? 0) + 1))),
            'platform' => $platform,
            'name' => trim($item['name'] ?? '') ?: (self::PLATFORMS[$platform]['name'] ?? ucfirst($platform)),
            'url' => $url,
            'enabled' => ! empty($item['enabled']) && $url !== '',
            'icon' => $platform,
            'icon_type' => trim($item['icon_type'] ?? '') ?: ($customIcon !== '' ? 'custom' : 'default'),
            'custom_icon' => $customIcon,
        ];
    }
}
