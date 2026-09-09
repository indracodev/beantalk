# Database: Indexing, Performance & Maintenance

## 1. High-Performance Indexing Strategy

Polling di shared hosting menuntut query database seefisien mungkin. Query harus menggunakan *Index-Only Scan* atau *B-Tree Range Scan* dengan biaya waktu eksekusi < 2ms.

### 1.1 Polling Query Optimization
Query visitor saat polling:
```sql
SELECT id, conversation_id, sender_type, body, type, status, created_at
FROM messages
WHERE conversation_id = ? AND id > ?
ORDER BY id ASC
LIMIT 50;
```
**Indeks yang Digunakan:**
```sql
INDEX idx_msg_poll (conversation_id, id ASC);
```
- **Analisis EXPLAIN**: MySQL melompat langsung ke node B-Tree `conversation_id`, lalu membaca record dengan `id > ?` secara berurutan. Tidak ada filesort dan tidak ada full-table scan.

---

### 1.2 Admin Inbox Query Optimization
Query admin untuk memuat daftar percakapan:
```sql
SELECT * FROM conversations
WHERE tenant_id = ? AND status = 'open'
ORDER BY last_message_at DESC
LIMIT 25;
```
**Indeks yang Digunakan:**
```sql
INDEX idx_conv_inbox (tenant_id, status, last_message_at DESC);
```
- **Analisis EXPLAIN**: Mencakup `tenant_id` dan `status` secara langsung, lalu memanfaatkan urutan fisik index untuk `ORDER BY last_message_at DESC` tanpa operasi filesort tambahan.

---

### 1.3 Idempotency Check Optimization
Query saat menerima pesan baru dari klien:
```sql
SELECT id FROM messages
WHERE conversation_id = ? AND client_message_id = ?
LIMIT 1;
```
**Indeks yang Digunakan:**
```sql
INDEX idx_msg_idempotent (conversation_id, client_message_id);
```
- Menjamin pencarian instan O(log N) untuk memverifikasi apakah pengiriman ulang (retry) jaringan telah tercatat.

---

### 1.4 Eliminasi N+1 Query pada Live Workspace 3-Kolom

Untuk merender workspace Live Inbox dengan puluhan percakapan dan thread pesan tanpa degradasi performa:

1. **Eager Loading Relasi Komposit**:
   ```php
   Conversation::where('tenant_id', $tenantId)
       ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'latestMessage'])
       ->orderBy('last_message_at', 'desc');
   ```
   - Menghindari N query ke tabel `widget_settings` untuk dot color badge channel.
   - Menghindari N query untuk snippet pesan terakhir via `latestOfMany()`.
   - Mengambil data pengunjung dalam 1 query `WHERE id IN (...)`.

2. **Konsolidasi Counter Status Menjadi 1 Query Agregasi**:
   Alih-alih melakukan 4 kali roundtrip `count()`, seluruh counter tab (`Semua`, `Open`, `Closed`, `Tugas Saya`) dieksekusi dalam 1 query SQL tunggal:
   ```sql
   SELECT 
       COUNT(*) as total_all,
       SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as total_open,
       SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as total_closed,
       SUM(CASE WHEN assigned_user_id = ? THEN 1 ELSE 0 END) as total_mine
   FROM conversations WHERE tenant_id = ? LIMIT 1;
   ```

3. **Reuse In-Memory Active Conversation**:
   Jika percakapan aktif yang dipilih sudah terdapat di koleksi `$conversations`, model yang ada di memori langsung digunakan kembali dan hanya melakukan `$activeConversation->load(['messages.user'])`, memangkas 5 query redundan.

---

## 2. Table Pruning & Data Retention

Di lingkungan shared hosting dengan kuota disk terbatas, data transien harus dipangkas secara otomatis tanpa membebani server saat jam kerja aktif.

### 2.1 Scheduled Pruner Job
Dijalankan setiap malam via Laravel Task Scheduler:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneChatDataCommand extends Command
{
    protected $signature = 'chat:prune';
    protected $description = 'Prune old transient events and inactive anonymous visitors';

    public function handle(): int
    {
        // 1. Hapus buffer realtime events yang lebih lama dari 3 hari
        DB::table('realtime_events')
            ->where('created_at', '<', now()->subDays(3))
            ->delete();

        // 2. Hapus log audit yang lebih lama dari 90 hari
        DB::table('audit_logs')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        // 3. Hapus anonymous visitors tanpa riwayat percakapan yang tidak aktif > 30 hari
        DB::table('visitors')
            ->whereNull('contact_id')
            ->where('last_seen_at', '<', now()->subDays(30))
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('conversations')
                    ->whereColumn('conversations.visitor_id', 'visitors.id');
            })
            ->delete();

        return Command::SUCCESS;
    }
}
```

Daftarkan di `app/Console/Kernel.php` atau `routes/console.php`:
```php
Schedule::command('chat:prune')->dailyAt('03:00');
```

---

## 3. Ponytail Pragmatic Notes

> **# ponytail: Mengapa AUTO_INCREMENT `id` Mengalahkan UUID untuk Message Ordering?**
>
> Menggunakan UUID string (`uuid_v4`) sebagai primary key untuk pesan chat menciptakan problem besar pada polling:
> 1. UUID acak mengakibatkan *index fragmentation* pada storage engine InnoDB MySQL.
> 2. Polling terpaksa menggunakan `WHERE created_at > ?` dengan timestamp mikrodetik yang rawan *race conditions* dan *clock drift*.
>
> *Pilihan Pragmatis: `BIGINT UNSIGNED AUTO_INCREMENT` untuk `messages.id`. Klien cukup meminta `?after_id=1420`. Pasti terurut secara matematis.*
