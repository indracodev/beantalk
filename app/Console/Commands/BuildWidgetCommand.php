<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BuildWidgetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build';

    /**
     * Command aliases.
     *
     * @var array
     */
    protected $aliases = ['widget:build', 'sdk:build'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kompilasi TypeScript Universal Chat SDK & Shadow DOM Widget ke public/chat-widget.js';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Membangun BeanTalk Universal Chat SDK...');

        $scriptPath = base_path('packages/chat-sdk/build.js');
        if (!file_exists($scriptPath)) {
            $this->error("File build script tidak ditemukan di: {$scriptPath}");
            return 1;
        }

        $process = new Process(['node', 'build.js'], base_path('packages/chat-sdk'));
        $process->setTimeout(60);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->newLine();
            $this->error('❌ Proses build gagal.');
            return 1;
        }

        $this->newLine();
        $this->info('✨ Bundle widget berhasil dikompilasi dan disalin ke folder public/.');
        return 0;
    }
}
