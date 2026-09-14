<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QuickOptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opt {--clear-only : Hanya bersihkan cache tanpa re-cache}';

    /**
     * Command aliases for quick typing.
     *
     * @var array
     */
    protected $aliases = ['cc', 'recache'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan seluruh cache aplikasi dan optimasi ulang (optimize:clear, config, route, view, event) dalam 1 perintah cepat';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = microtime(true);

        if ($this->option('clear-only')) {
            $this->info('🧹 Membersihkan seluruh cache aplikasi...');
            $this->call('optimize:clear');
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->newLine();
            $this->info("✨ Seluruh cache berhasil dibersihkan dalam {$duration}ms.");
            return 0;
        }

        // STEP 1: CLEAR OLD CACHES
        $this->info('🧹 [1/2] Membersihkan seluruh cache lama...');
        $this->call('optimize:clear');

        // STEP 2: BUILD NEW OPTIMIZED CACHES
        $this->newLine();
        $this->info('⚡ [2/2] Membangun cache baru & optimasi aplikasi...');

        // 1. Config Cache
        $this->line('  ⚙️  Config Cache...');
        $this->call('config:cache');

        // 2. Route Cache
        $this->line('  🛣️  Route Cache...');
        $this->call('route:cache');

        // 3. View Cache
        $this->line('  🖥️  Blade Views Cache...');
        $this->call('view:cache');

        // 4. Event Cache
        $this->line('  📡 Events Cache...');
        $this->call('event:cache');

        $duration = round((microtime(true) - $start) * 1000, 2);

        $this->newLine();
        $this->info("🚀 SELESAI! Seluruh cache lama dibersihkan dan cache baru berhasil dibuat ({$duration}ms).");
        $this->line("   (Tip: Cukup ketik <comment>php artisan opt</comment> untuk menjalankan keduanya sekaligus)");

        return 0;
    }
}
