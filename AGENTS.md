# AGENTS.md — Pintu Masuk Agent

Repositori **BeanTalk (Universal Customer Chat)** — Laravel 13, PHP 8.3+, MySQL, widget TypeScript Shadow DOM.

## Wajib dibaca sebelum bekerja
1. **[.agents/README.md](.agents/README.md)** — indeks rules & skills (sumber kebenaran operasional).
2. **[.agents/rules/](.agents/rules/)** — aturan keras: prinsip, konvensi L13, arsitektur & peta reuse, keamanan,
   performa DB, kontrak API, frontend, testing/DoD, deploy.
3. **[.agents/skills/](.agents/skills/)** — prosedur: endpoint API, halaman admin, migrasi, bug fix, security review,
   pecah file gemuk, deploy shared hosting, impor DB lama, build widget.
4. **[tasks/lessons.md](tasks/lessons.md)** — memori koreksi. Tambahkan entri setiap kali user mengoreksi pekerjaan.

## Ringkasan aturan paling sering dilanggar
- Reuse dulu (lihat peta reuse `.agents/rules/02-architecture.md`); jangan menduplikasi query/markup/helper.
- Tidak ada file kode > 300 baris (PHP, Blade, JS, CSS, test) — pecah per tanggung jawab.
- Tidak ada query di dalam loop; eager load relasi yang dirender; statistik pakai agregasi SQL.
- Identitas (tenant, user, role) hanya dari sesi/middleware server; mutasi superadmin dijaga di server.
- Selesai = test lulus + smoke MySQL + tidak ada ERROR di log.

## Dokumen terkait
- [GEMINI.md](GEMINI.md) — standar teknis & arsitektur ringkas.
- [docs/](docs/) — PRD, DDL, kontrak API, SDK.
