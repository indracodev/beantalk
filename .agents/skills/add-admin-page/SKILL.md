---
name: add-admin-page
description: Menambah halaman atau fitur baru di dashboard admin BeanTalk (route web, controller per domain, service, view tipis + partial, JS di public/js dengan window.BT, RBAC). Pakai untuk perubahan di resources/views/admin atau Controllers/Admin.
---

# Menambah Halaman / Fitur Admin

1. **Route** di grup `auth` + `admin.` pada `routes/web.php`; aksi superadmin di dalam `middleware('can:superadmin')`.
2. **Controller** satu domain di `app/Http/Controllers/Admin/` (≤300 baris). Query panjang → service / scope model.
   Selalu `Model::where('tenant_id', $request->user()->tenant_id)->findOrFail($id)`.
3. **Data view**: eager load semua relasi yang dirender (`with`, `withCount`); statistik → agregasi SQL di service.
4. **View**:
   - `resources/views/admin/<halaman>.blade.php` extends `layouts.admin`, berisi kerangka + `@include`.
   - Seksi panjang → `admin/partials/<halaman>/<seksi>.blade.php`.
   - Pakai komponen yang ada: `<x-admin.pagination :paginator="$items" noun="item" />`, `<x-admin.list-toolbar ... />`.
   - Header sortable: `@php $sort = new \App\Support\SortLink('created_at'); @endphp` → `{{ $sort->url('col') }}{{ $sort->icon('col') }}`.
5. **JS**: file `public/js/pages/<halaman>.js`; data server via `window.BT_<HALAMAN> = {{ \Illuminate\Support\Js::from([...]) }};`
   lalu `<script src="@asset('js/pages/<halaman>.js')"></script>`. Fetch kirim header `X-CSRF-TOKEN`.
6. **Menu**: tambahkan ke `layouts/partials/sidebar.blade.php` dan `bottom-nav.blade.php` (sembunyikan untuk agent bila superadmin-only — tetap dijaga di server).
7. **Test** di `tests/Feature/Admin/<Area>PageTest.php` (extends `AdminTestCase`): render 200, agent 403 bila superadmin-only, filter/aksi utama.
8. Smoke MySQL + `node --check` untuk JS baru (lihat `rules/07-testing-qa.md`).
