# Architecture: System Overview & Principles

## 1. Vision & Core Principle

> **"One Core API, One Universal SDK, Many Platforms."**

Platform ini dirancang sebagai platform universal customer chat multi-tenant. Tidak boleh ada implementasi chat engine yang terpisah untuk Shopify, WordPress, Laravel, atau HTML biasa. Semua platform berkomunikasi dengan backend yang sama melalui SDK JavaScript universal yang sama.

```text
                    Laravel Core (Multi-tenant)
                                 │
            ┌────────────────────┼────────────────────┐
            │                    │                    │
       REST API v1        Realtime Layer       Admin Dashboard
            │                    │                    │
            └────────────────────┼────────────────────┘
                                 │
                        Universal JS SDK
                                 │
       ┌────────────┬────────────┼────────────┬────────────┐
       │            │            │            │            │
    Shopify      Laravel      WordPress     React/Vue     HTML
```

---

## 2. High-Level Component Flow

```text
Website / Shopify Store
         │
         ▼
Universal Chat Library (JS SDK)
         │
         ▼
Chat Widget (Shadow DOM Isolated UI)
         │
         ▼ HTTPS (REST API v1)
Laravel Core API
         │
         ▼
MySQL 8.0+ (Single Source of Truth)
         │
         ▼
Laravel Admin Dashboard (3-Column Inbox)
```

1. **Customer Side**: Widget ringan disematkan di website host, menghasilkan floating chat UI yang terisolasi dari CSS website induk menggunakan Shadow DOM.
2. **Transport Layer**: SDK berkomunikasi dengan Laravel API via HTTPS. Default realtime menggunakan Adaptive HTTP Polling yang aman untuk shared hosting.
3. **Core Server**: Laravel menangani otentikasi multi-tenant, persistensi percakapan, validasi attachment, rate limiting, dan webhook.
4. **Admin Side**: Dashboard berbasis web untuk agen/admin guna membalas pesan secara realtime tanpa reload halaman.

---

## 3. Layer Separation Rules

Sistem mematuhi pemisahan tanggung jawab yang ketat:
```text
┌───────────────────────────────┐
│          Widget UI            │ -> Representasi visual di browser (Shadow DOM)
├───────────────────────────────┤
│           SDK Layer           │ -> State machine, public API (Chat.init, events)
├───────────────────────────────┤
│        Transport Layer        │ -> Abstraksi transport (Polling, SSE, WebSocket)
├───────────────────────────────┤
│           API Layer           │ -> Controller, Request Validation, API Resource
├───────────────────────────────┤
│        Business Logic         │ -> Domain Services (ConversationService, etc.)
├───────────────────────────────┤
│          Persistence          │ -> Eloquent Models, Migrations, MySQL Database
└───────────────────────────────┘
```

**Aturan Arsitektur:**
- Widget UI dilarang melakukan HTTP request langsung tanpa melewati SDK Layer.
- SDK Layer tidak boleh berasumsi tentang transport yang dipakai (polling vs websocket).
- Database Laravel adalah **satu-satunya sumber kebenaran**. Realtime server (jika nanti ada) tidak boleh bertindak sebagai database.

---

## 4. Ponytail Pragmatic Notes

> **# ponytail: Mengapa Monolith Modular Laravel Mengalahkan Microservices?**
>
> Memecah chat engine menjadi auth service, chat service, socket service, dan dashboard service di tahap MVP menciptakan overhead network latency, deploy complexity, dan mustahil dijalankan di shared hosting.
>
> *Keputusan: Single Laravel application yang melayani API, Admin Dashboard, dan Polling Controller.*  
> *Upgrade when: Traffic per hari melampaui jutaan pesan dan memerlukan server cluster terdedikasi.*
