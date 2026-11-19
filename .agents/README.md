# .agents — Aturan & Skill Agent untuk BeanTalk (Laravel 13)

Folder ini adalah **sumber kebenaran operasional** untuk semua AI agent (Claude Code, Codex, Gemini, Cursor, dll.)
yang bekerja di repo ini. Aturan di sini **wajib** dipatuhi; jika bertentangan dengan kebiasaan bawaan agent, aturan ini menang.

## Urutan baca (wajib di awal setiap sesi)

1. `rules/00-core-principles.md` — persona, tangga keputusan, batas ukuran file, reuse-first.
2. `rules/01-stack-laravel13.md` — konvensi Laravel 13 & PHP 8.3 yang dipakai repo ini.
3. Rules lain sesuai area yang disentuh (lihat tabel).
4. `../tasks/lessons.md` — pelajaran dari koreksi sebelumnya.
5. Skill yang relevan di `skills/` sebelum mengerjakan tugas sejenis.

| Area kerja | Rule |
|---|---|
| Struktur folder, letak kode baru | `rules/02-architecture.md` |
| Tenant, auth, RBAC, input, secret, upload | `rules/03-security-multitenancy.md` |
| Query, index, N+1, cache | `rules/04-database-performance.md` |
| REST API & widget SDK | `rules/05-api-contract.md` |
| Blade, JS, CSS, aset | `rules/06-frontend-blade.md` |
| Test, verifikasi, Definition of Done | `rules/07-testing-qa.md` |
| Git, deploy shared hosting, migrasi data | `rules/08-git-deploy.md` |

## Skills (prosedur langkah-demi-langkah)

| Skill | Kapan dipakai |
|---|---|
| `skills/add-api-endpoint` | Menambah endpoint REST `/api/v1/...` |
| `skills/add-admin-page` | Menambah halaman/fitur dashboard admin |
| `skills/add-migration` | Mengubah skema database |
| `skills/fix-bug` | Laporan bug / test gagal |
| `skills/security-review` | Sebelum merge perubahan yang menyentuh input, auth, atau data tenant |
| `skills/refactor-oversized-file` | Ada file melewati batas ukuran |
| `skills/deploy-shared-hosting` | Rilis ke server cPanel/shared hosting |
| `skills/import-legacy-db` | Memindahkan data dari DB BeanTalk Laravel 7 |
| `skills/build-widget-sdk` | Mengubah `packages/chat-sdk` / widget embed |

## Hubungan dengan dokumen lain

- `AGENTS.md` (root) — pintu masuk singkat yang menunjuk ke folder ini.
- `GEMINI.md` — standar teknis & arsitektur (ringkasan; detail ada di `rules/`).
- `docs/` — spesifikasi produk (PRD, DDL, kontrak API, SDK).
- `tasks/lessons.md` — memori koreksi; **tambahkan entri setiap kali user mengoreksi pekerjaan agent**.
