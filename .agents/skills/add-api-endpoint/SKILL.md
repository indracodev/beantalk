---
name: add-api-endpoint
description: Menambah endpoint REST /api/v1 BeanTalk (widget client, admin, auth) dengan envelope standar, tenant scope, RBAC, validasi, dan test. Pakai setiap kali membuat/mengubah route di routes/api.php.
---

# Menambah Endpoint API v1

1. **Tentukan grup** (`.agents/rules/05-api-contract.md`): `client` (project.key), `admin` (tenant.scope), `auth`.
2. **Route** di `routes/api.php` dengan sintaks kelas + throttle per-route bila publik:
   ```php
   Route::post('conversations/{id}/rate', [MessageController::class, 'rate'])->middleware('throttle:30,1');
   ```
   Mutasi admin sensitif → `->middleware('role:superadmin')`.
3. **Controller** di `app/Http/Controllers/Api/{Client|Admin}`:
   - Widget: `use ResolvesVisitorConversation;` → `$this->ownedConversation($request, $id) ?? return $this->conversationNotFound();`
   - Validasi via `$request->validate()` atau FormRequest bila dipakai ulang.
   - Logika → service yang ada (cek peta reuse `rules/02-architecture.md`), bukan di controller.
   - Respons: `return $this->ok([...], 201);` / `return $this->fail('CODE', 'Pesan', 422);`
4. **Bentuk data** pakai presenter yang ada (`toWidgetArray()`, `toWidgetSummary()`), jangan susun array ulang.
5. **Audit** aksi staf: `ActivityLogger::log('entity.action', 'Deskripsi', $model, [...]);`
6. **Test** di `tests/Feature/...`: sukses, 401/403 (tanpa auth / role salah), 404 (tenant/visitor lain), 422 (validasi).
7. Verifikasi: `php artisan test --compact` + `php artisan route:list --path=api`.
