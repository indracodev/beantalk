<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TeamController extends Controller
{
    /** GET /api/v1/admin/team */
    public function index(Request $request): JsonResponse
    {
        $team = User::where('tenant_id', $request->user()->tenant_id)->orderBy('role')->orderBy('name')
            ->get(['id', 'tenant_id', 'name', 'username', 'email', 'role', 'status', 'avatar_url', 'created_at']);

        return $this->ok(['team' => $team]);
    }

    /** POST /api/v1/admin/team (superadmin) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'nullable|string|min:3|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:superadmin,agent',
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'username' => $data['username'] ?? User::uniqueUsernameFrom($data['email']),
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'] ?? Str::password(16),
            'status' => 'offline',
        ]);

        ActivityLogger::log('team.invited', "Menambahkan anggota tim baru: {$user->name} ({$user->email}) sebagai {$user->role}", $user, [
            'invited_user_id' => $user->id, 'email' => $user->email, 'role' => $user->role,
        ]);

        return $this->ok(
            ['user' => $user->only(['id', 'name', 'email', 'role', 'status', 'avatar_url', 'created_at'])],
            201,
            ['message' => "Anggota tim berhasil ditambahkan sebagai {$user->role}."],
        );
    }

    /** PUT /api/v1/admin/team/{id}/role (superadmin) */
    public function updateRole(Request $request, string $id): JsonResponse
    {
        $role = $request->validate(['role' => 'required|in:superadmin,agent'])['role'];
        $current = $request->user();
        $target = User::where('tenant_id', $request->user()->tenant_id)->find($id);

        if (! $target) {
            return $this->fail('NOT_FOUND', 'Anggota tim tidak ditemukan.', 404);
        }

        if ($current->is($target) && $role !== 'superadmin' && User::where('tenant_id', $current->tenant_id)->where('role', 'superadmin')->count() <= 1) {
            return $this->fail('FORBIDDEN', 'Tidak dapat mengubah role diri sendiri karena Anda adalah satu-satunya Superadmin.', 422);
        }

        $oldRole = $target->role;
        $target->update(['role' => $role]);

        ActivityLogger::log('role.updated', "Mengubah role {$target->name} dari {$oldRole} menjadi {$role}", $target, [
            'old_role' => $oldRole, 'new_role' => $role,
        ]);

        return $this->ok(
            ['user' => $target->only(['id', 'name', 'email', 'role'])],
            200,
            ['message' => "Role {$target->name} berhasil diubah menjadi {$role}."],
        );
    }

    /** DELETE /api/v1/admin/team/{id} (superadmin) */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ((string) $request->user()->id === $id) {
            return $this->fail('FORBIDDEN', 'Anda tidak dapat menghapus akun Anda sendiri.', 422);
        }

        $target = User::where('tenant_id', $request->user()->tenant_id)->find($id);
        if (! $target) {
            return $this->fail('NOT_FOUND', 'Anggota tim tidak ditemukan.', 404);
        }

        ActivityLogger::log('team.removed', "Menghapus anggota tim: {$target->name} ({$target->email})", null, [
            'deleted_user_id' => $target->id, 'name' => $target->name, 'email' => $target->email, 'role' => $target->role,
        ]);
        $target->delete();

        return response()->json(['success' => true, 'message' => "Anggota tim {$target->name} berhasil dihapus dari sistem."]);
    }
}
