# 02 — Arsitektur & Peta Reuse

## Peta folder
```
app/
  Console/Commands/        build (SDK), opt (cache), beantalk:import-legacy
  Exceptions/              ErrorActivityLogger (429/5xx/DB → activity_logs)
  Http/
    Controllers/Admin/     Dashboard, Inbox, Integration, Team, Visitor, ActivityLog, Profile (web, sesi)
    Controllers/Api/Admin/ REST admin (tenant.scope + role:)
    Controllers/Api/Client/ Widget (project.key) + Concerns/ResolvesVisitorConversation
    Controllers/Auth/      Login web
    Middleware/            CheckRole, EnforceTenantScope, ResolveProjectKey, SecurityHeaders
    Requests/              StoreIntegrationRequest, UpdateWidgetSettingsRequest
  Mail/                    ChatTranscriptMail (+ ::sendTo())
  Models/ (+ Concerns/)    Eloquent + presenter kecil (toWidgetArray, toInboxCard, ...)
  Services/                ConversationService, BotService, TelegramService, IntegrationService,
                           WidgetSettingsUpdater, VisitorAnalytics, BusinessHoursService, ActivityLogger,
                           Dashboard/{DashboardMetrics, DashboardPeriod, VolumeSeries, ProjectMetrics}
  Support/                 Asset, DateRange, SocialChannels, SortLink, Sql
  Traits/                  BelongsToTenant, FlushesProjectKeyCache
```

## Peta reuse — pakai ini, jangan tulis ulang
| Kebutuhan | Gunakan |
|---|---|
| Respons JSON envelope | `$this->ok($data, $status)` / `$this->fail($code, $msg, $status)` (base `Controller`) |
| Simpan pesan (idempoten, counter atomik) | `ConversationService::appendMessage()` |
| Tutup/buka tiket + sinkron Telegram | `ConversationService::setStatus()` |
| Kepemilikan percakapan oleh pengunjung | `ResolvesVisitorConversation::ownedConversation()` |
| Bentuk pesan/visitor/tiket untuk widget | `Message::toWidgetArray()`, `Visitor::toWidgetArray()`, `ConversationService::toWidgetSummary()` |
| Konfigurasi widget publik | `WidgetSetting::widgetConfig($ws)` |
| Daftar & counter inbox | `Conversation::inbox($user, $filters)`, `Conversation::inboxCounts()`, `->toInboxCard()` |
| Filter pengunjung | `Visitor::filter($request->only([...]))` |
| Buat integrasi / regenerate key | `IntegrationService::create()` / `regenerateKey()` |
| Simpan pengaturan widget | `WidgetSettingsUpdater::update()` |
| Normalisasi URL kanal sosial | `SocialChannels::normalizeUrl()/fromInput()/forEditor()` |
| Lookup login email/username | `User::findByLogin()` |
| Username unik | `User::uniqueUsernameFrom()` (avatar = inisial lokal, tanpa layanan eksternal) |
| Kirim transkrip | `ChatTranscriptMail::sendTo($conversation)` |
| Audit log | `ActivityLogger::log($action, $desc, $subject, $props)` |
| Resolusi project key (ter-cache) | `ApiKey::resolveActive()` / `ApiKey::forgetProjectCache()` |
| Preset tanggal today/7d/30d | `DateRange::since()` |
| Header tabel sortable | `new SortLink($defaultCol, $defaultDir)` → `->url()`, `->icon()` |
| Ekspresi SQL lintas driver | `Sql::date()`, `Sql::hour()` |
| URL aset ber-versi | Blade `@asset('js/x.js')` |
| Pagination / toolbar daftar | `<x-admin.pagination>`, `<x-admin.list-toolbar>` |

## Aturan penempatan
- Logika bisnis lintas controller → `Services/`. Helper murni tanpa state → `Support/`.
- Query yang dipakai ≥2 tempat → scope `#[Scope]` di model (atau trait `Models/Concerns/` bila panjang).
- Bentuk array respons yang dipakai ≥2 tempat → method presenter di model.
- Controller tidak boleh berisi query agregasi panjang → service.
