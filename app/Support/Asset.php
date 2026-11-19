<?php

namespace App\Support;

/** URL aset publik dengan versi berbasis waktu modifikasi berkas (cache-busting tanpa build tool). */
class Asset
{
    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}
