<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * List all team members in the tenant
     * GET /api/v1/admin/team
     */
    public function index(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;

        $query = User::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $members = $query->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'tenant_id', 'name', 'username', 'email', 'role', 'status', 'avatar_url', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'team' => $members
            ]
        ]);
    }

    /**
     * Invite / Create a new team member
     * POST /api/v1/admin/team
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();

        // Admin tidak dapat membuat Superadmin atau Admin lain; hanya Superadmin/Owner yang dapat membuat Admin/Superadmin
        $allowedRoles = $currentUser && ($currentUser->isSuperAdmin() || $currentUser->isOwner()) ? 'superadmin,admin,agent' : 'agent';

        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'nullable|string|min:3|max:50|alpha_dash|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'role'     => 'required|in:' . $allowedRoles,
            'password' => 'nullable|string|min:6',
        ]);

        $tenantId = (app()->bound('current_tenant_id') ? app('current_tenant_id') : null) ?? ($currentUser ? $currentUser->tenant_id : Tenant::first()->id);

        $username = $request->input('username');
        if (!$username) {
            $base = Str::slug(explode('@', $request->input('email'))[0], '_');
            $username = $base;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $counter++;
            }
        }

        $user = User::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->input('name'),
            'username'   => $username,
            'email'      => $request->input('email'),
            'role'       => $request->input('role'),
            'password'   => Hash::make($request->input('password', Str::random(12))),
            'status'     => 'offline',
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($request->input('name')) . '&background=random',
        ]);

        \App\Services\ActivityLogger::log(
            'team.invited',
            "Menambahkan anggota tim baru: {$user->name} ({$user->email}) sebagai {$user->role}",
            $user,
            ['invited_user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'status'     => $user->status,
                    'avatar_url' => $user->avatar_url,
                    'created_at' => $user->created_at,
                ]
            ],
            'message' => "Anggota tim berhasil ditambahkan sebagai {$user->role}."
        ], 201);
    }

    /**
     * Update user role (Owner only)
     * PUT /api/v1/admin/team/{id}/role
     */
    public function updateRole(Request $request, $id)
    {
        $currentUser = $request->user();

        $request->validate([
            'role' => 'required|in:superadmin,owner,admin,agent',
        ]);

        $tenantId = (app()->bound('current_tenant_id') ? app('current_tenant_id') : null) ?? ($currentUser ? $currentUser->tenant_id : Tenant::first()->id);
        $targetUser = User::where('tenant_id', $tenantId)->find($id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Anggota tim tidak ditemukan.',
                ]
            ], 404);
        }

        // Prevent demoting self if sole superadmin/owner
        if ($currentUser && $currentUser->id === $targetUser->id && !in_array($request->input('role'), ['superadmin', 'owner'])) {
            $superadminCount = User::where('tenant_id', $tenantId)->whereIn('role', ['superadmin', 'owner'])->count();
            if ($superadminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Tidak dapat mengubah role diri sendiri karena Anda adalah satu-satunya Superadmin.',
                    ]
                ], 422);
            }
        }

        $oldRole = $targetUser->role;
        $targetUser->role = $request->input('role');
        $targetUser->save();

        \App\Services\ActivityLogger::log(
            'role.updated',
            "Mengubah role {$targetUser->name} dari {$oldRole} menjadi {$targetUser->role}",
            $targetUser,
            ['old_role' => $oldRole, 'new_role' => $targetUser->role]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'    => $targetUser->id,
                    'name'  => $targetUser->name,
                    'email' => $targetUser->email,
                    'role'  => $targetUser->role,
                ]
            ],
            'message' => "Role {$targetUser->name} berhasil diubah menjadi {$targetUser->role}."
        ]);
    }

    /**
     * Remove team member (Owner only)
     * DELETE /api/v1/admin/team/{id}
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();
        $tenantId = (app()->bound('current_tenant_id') ? app('current_tenant_id') : null) ?? ($currentUser ? $currentUser->tenant_id : null);

        if ($currentUser && $currentUser->id == $id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
                ]
            ], 422);
        }

        $query = User::where('id', $id);
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $targetUser = $query->first();

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Anggota tim tidak ditemukan.',
                ]
            ], 404);
        }

        \App\Services\ActivityLogger::log(
            'team.removed',
            "Menghapus anggota tim: {$targetUser->name} ({$targetUser->email})",
            null,
            ['deleted_user_id' => $targetUser->id, 'name' => $targetUser->name, 'email' => $targetUser->email, 'role' => $targetUser->role]
        );

        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => "Anggota tim {$targetUser->name} berhasil dihapus dari sistem."
        ]);
    }
}
