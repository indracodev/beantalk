<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Manajemen staf CS (superadmin) + Login As. */
class TeamController extends Controller
{
    private const SORTABLE = ['name', 'username', 'email', 'status', 'created_at', 'conversations_count'];

    /** GET /admin/team */
    public function index(Request $request): View
    {
        $sort = $request->input('sort', 'role');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $search = trim((string) $request->input('search'));

        $members = User::where('tenant_id', $request->user()->tenant_id)
            ->withCount('conversations')
            ->when($search !== '', fn ($q) => $q->where(fn ($s) => $s->whereLike('name', "%{$search}%")
                ->orWhereLike('username', "%{$search}%")->orWhereLike('email', "%{$search}%")->orWhereLike('role', "%{$search}%")))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when(in_array($sort, self::SORTABLE, true),
                fn ($q) => $q->orderBy($sort, $dir),
                fn ($q) => $q->orderByRaw("CASE WHEN role = 'superadmin' THEN 1 ELSE 2 END ".($sort === 'role' ? $dir : 'asc'))->orderBy('name'))
            ->paginate(15)
            ->withQueryString();

        return view('admin.team', compact('members'));
    }

    /** POST /admin/team */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate($this->rules() + [
            'username' => 'nullable|string|min:3|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'username' => $data['username'] ?? User::uniqueUsernameFrom($data['email']),
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'telegram_username' => $this->telegramHandle($data),
            'status' => 'offline',
            'avatar_url' => User::defaultAvatar($data['name']),
        ]);

        ActivityLogger::log('team.invited', "Menambahkan anggota tim baru: {$user->name} ({$user->email}) sebagai {$user->role}", $user, [
            'invited_user_id' => $user->id, 'email' => $user->email, 'role' => $user->role, 'telegram_username' => $user->telegram_username,
        ]);

        return $this->respond($request, $user, "Staf CS '{$user->name}' berhasil didaftarkan sebagai {$user->role}!", 201);
    }

    /** PUT /admin/team/{id} */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = User::where('tenant_id', $request->user()->tenant_id)->findOrFail($id);

        $data = $request->validate($this->rules() + [
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        $user->update(array_filter([
            'name' => $data['name'],
            'username' => $data['username'] ?? null,
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'] ?? null,
        ]) + ['telegram_username' => $this->telegramHandle($data)]);

        ActivityLogger::log('team.updated', "Memperbarui profil anggota tim: {$user->name} ({$user->role})", $user, [
            'updated_user_id' => $user->id, 'email' => $user->email, 'role' => $user->role, 'telegram_username' => $user->telegram_username,
        ]);

        return $this->respond($request, $user, "Profil staf '{$user->name}' berhasil diperbarui!");
    }

    /** POST /admin/team/{id}/impersonate — superadmin masuk sebagai staf lain. */
    public function impersonate(Request $request, string $id): RedirectResponse
    {
        $current = $request->user();
        $target = User::where('tenant_id', $current->tenant_id)->findOrFail($id);

        if ($target->is($current)) {
            return back()->with('error', 'Anda sudah berada di akun ini.');
        }

        $impersonatorId = $request->session()->get('impersonator_id', $current->id);

        ActivityLogger::log('user.impersonate', "Superadmin {$current->name} melakukan Login As sebagai {$target->name} ({$target->role})", $target, [
            'original_user_id' => $impersonatorId, 'target_user_id' => $target->id, 'target_role' => $target->role,
        ]);

        Auth::login($target);
        $request->session()->regenerate(); // cegah session fixation saat ganti identitas
        $request->session()->put('impersonator_id', $impersonatorId);

        return to_route('admin.inbox')->with('success', "Mode Login As aktif! Anda sekarang bertindak sebagai {$target->name} ({$target->role}).");
    }

    /** GET|POST /admin/impersonate/leave — kembali ke akun superadmin asli. */
    public function leaveImpersonation(Request $request): RedirectResponse
    {
        $originalId = $request->session()->pull('impersonator_id');
        if (! $originalId) {
            return to_route('admin.inbox');
        }

        $impersonated = $request->user();
        $original = User::where('tenant_id', $impersonated->tenant_id)->find($originalId);

        if (! $original) {
            Auth::logout();
            $request->session()->invalidate();

            return to_route('login')->with('error', 'Sesi akun asli tidak ditemukan. Silakan login kembali.');
        }

        Auth::login($original);
        $request->session()->regenerate();

        ActivityLogger::log('user.impersonate_leave', "{$original->name} keluar dari mode Login As ({$impersonated->name}) dan kembali ke akun aslinya", $original, [
            'original_user_id' => $original->id, 'impersonated_user_id' => $impersonated->id,
        ]);

        return to_route('admin.team')->with('success', "Kembali ke akun asli ({$original->name}).");
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'role' => 'required|in:superadmin,agent',
            'telegram_username' => 'nullable|string|max:50',
        ];
    }

    private function telegramHandle(array $data): ?string
    {
        return ! empty($data['telegram_username']) ? ltrim(trim($data['telegram_username']), '@') : null;
    }

    private function respond(Request $request, User $user, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? $this->ok($user->only(['id', 'name', 'email', 'role', 'telegram_username']), $status)
            : to_route('admin.team')->with('success', $message);
    }
}
