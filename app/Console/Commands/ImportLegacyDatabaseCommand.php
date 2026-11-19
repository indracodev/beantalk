<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Salin data dari database BeanTalk versi Laravel 7 (skema sama, DB berbeda di server MySQL yang sama)
 * ke database aktif yang sudah dimigrasi skema L13. DB sumber hanya dibaca.
 */
class ImportLegacyDatabaseCommand extends Command
{
    protected $signature = 'beantalk:import-legacy {source : Nama database L7 sumber} {--force : Kosongkan tabel tujuan dulu}';

    protected $description = 'Impor data BeanTalk L7 ke skema Laravel 13 (token Telegram dienkripsi)';

    /** Urutan mengikuti foreign key. */
    private const TABLES = [
        'tenants', 'users', 'projects', 'project_users', 'project_domains', 'api_keys', 'widget_settings',
        'contacts', 'visitors', 'conversations', 'conversation_participants', 'messages', 'message_attachments',
        'realtime_events', 'activity_logs',
    ];

    public function handle(): int
    {
        $source = $this->argument('source');
        if (! preg_match('/^[A-Za-z0-9_]+$/', $source) || $source === DB::getDatabaseName()) {
            $this->error('Nama database sumber tidak valid / sama dengan tujuan.');

            return self::FAILURE;
        }

        if (DB::table('tenants')->exists() && ! $this->option('force')) {
            $this->error('Database tujuan sudah berisi data. Gunakan --force untuk mengosongkan lalu impor ulang.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();
        try {
            foreach (self::TABLES as $table) {
                $this->copy($source, $table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Selesai. Jalankan "php artisan optimize:clear" lalu login seperti biasa.');

        return self::SUCCESS;
    }

    private function copy(string $source, string $table): void
    {
        $sourceColumns = collect(DB::select('SELECT COLUMN_NAME AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$source, $table]))->pluck('c');
        if ($sourceColumns->isEmpty()) {
            $this->warn("- {$table}: tidak ada di sumber, dilewati");

            return;
        }

        $columns = $sourceColumns->intersect(Schema::getColumnListing($table))->values()->all();
        DB::table($table)->truncate();

        $count = 0;
        DB::table(DB::raw("`{$source}`.`{$table}`"))->select($columns)->orderBy($columns[0])
            ->chunk(500, function ($rows) use ($table, &$count) {
                $data = $rows->map(fn ($r) => $this->transform($table, (array) $r))->all();
                DB::table($table)->insert($data);
                $count += count($data);
            });

        $this->line("- {$table}: {$count} baris");
    }

    private function transform(string $table, array $row): array
    {
        if ($table === 'widget_settings' && ! empty($row['telegram_bot_token'])) {
            $row['telegram_bot_token'] = Crypt::encryptString($row['telegram_bot_token']);
        }

        return $row;
    }
}
