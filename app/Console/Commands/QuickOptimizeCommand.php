<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/** Alias cepat: bersihkan semua cache lalu bangun ulang (config, route, view, event) via `optimize` bawaan L13. */
class QuickOptimizeCommand extends Command
{
    protected $signature = 'opt {--clear-only : Hanya bersihkan cache tanpa membangun ulang}';

    protected $aliases = ['cc', 'recache'];

    protected $description = 'optimize:clear + optimize dalam satu perintah';

    public function handle(): int
    {
        $this->call('optimize:clear');

        if (! $this->option('clear-only')) {
            $this->call('optimize');
        }

        return self::SUCCESS;
    }
}
