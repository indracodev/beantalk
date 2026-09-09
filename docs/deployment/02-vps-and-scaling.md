# Deployment: VPS Deployment & Future Scale

Ketika trafik platform melonjak (misal: lebih dari 5.000 percakapan concurrent), arsitektur dapat ditingkatkan ke VPS terdedikasi tanpa menulis ulang Universal JS SDK.

---

## 1. Arsitektur VPS / Cloud

```text
                  Cloudflare / Load Balancer (SSL Termination)
                                     │
                                     ▼
                                Nginx Proxy
                                     │
                 ┌───────────────────┴───────────────────┐
                 ▼                                       ▼
       PHP-FPM / Laravel Octane                 Laravel Reverb / Soketi
       (REST API & Admin Dashboard)             (WebSocket Daemon :8080)
                 │                                       │
                 ├───────────────────┬───────────────────┘
                 ▼                   ▼
            MySQL 8.0+             Redis
        (Primary Storage)    (Pub/Sub & Cache)
```

---

## 2. Mengaktifkan WebSocket Transport (Laravel Reverb)

### 2.1 Konfigurasi Server
Ubah konfigurasi di file `.env`:
```env
# Aktifkan driver websocket
CHAT_REALTIME_DRIVER=websocket

BROADCAST_DRIVER=reverb
REVERB_APP_ID=chat_app_id
REVERB_APP_KEY=chat_reverb_key
REVERB_APP_SECRET=chat_reverb_secret
REVERB_HOST="chat.yourdomain.com"
REVERB_PORT=443
REVERB_SCHEME=https

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
```

### 2.2 Reaksi Universal JS SDK
Pada saat visitor melakukan request `POST /api/v1/identify`, server mengembalikan payload:
```json
{
  "realtime_driver": "websocket",
  "websocket_config": {
    "host": "chat.yourdomain.com",
    "key": "chat_reverb_key",
    "port": 443
  }
}
```
SDK otomatis mengaktifkan instance `WebSocketTransport` secara transparan tanpa perlu mengubah satu baris pun kode di website merchant Shopify/WordPress!

---

## 3. Worker Management (Supervisor)

Di VPS Linux (Ubuntu 22.04/24.04 LTS), jalankan process supervisor untuk queue dan websocket:

```ini
[program:chat-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/chat-backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/chat-backend/storage/logs/worker.log

[program:chat-reverb]
command=php /var/www/chat-backend/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/chat-backend/storage/logs/reverb.log
```

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Menunda VPS Sampai Trafik Membutuhkan?**
>
> Mengoperasikan VPS membutuhkan biaya server rutin, patching kernel Linux bulanan, konfigurasi firewall fail2ban, dan pemantauan memory swap.
>
> *Strategi Pragmatis: Mulai di Shared Hosting dengan Polling. Saat MRR bisnis atau trafik sudah membenarkan biayanya, switch driver ke Reverb via 1 baris `.env`.*
