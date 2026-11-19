<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** Link & ikon header tabel yang bisa diurutkan (?sort=&dir=). Dipakai tabel Logs & Visitors. */
class SortLink
{
    public readonly string $current;

    public readonly string $dir;

    public function __construct(string $default, private readonly string $defaultDir = 'desc')
    {
        $this->current = (string) request('sort', $default);
        $this->dir = strtolower((string) request('dir', $defaultDir)) === 'asc' ? 'asc' : 'desc';
    }

    public function url(string $column): string
    {
        $flip = $this->defaultDir === 'desc' ? 'asc' : 'desc';
        $next = $this->current === $column && $this->dir === $this->defaultDir ? $flip : $this->defaultDir;

        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $next]);
    }

    public function icon(string $column): HtmlString
    {
        return new HtmlString(match (true) {
            $this->current !== $column => '<span class="text-apple-textTertiary/40 ml-1 text-[9.5px]">↕</span>',
            $this->dir === 'desc' => '<span class="text-apple-blue font-bold ml-1 text-[9.5px]">▼</span>',
            default => '<span class="text-apple-blue font-bold ml-1 text-[9.5px]">▲</span>',
        });
    }
}
