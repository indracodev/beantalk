<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use BelongsToTenant, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'avatar_url',
        'telegram_username',
        'telegram_user_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assigned_user_id');
    }

    public function participatingConversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('joined_at', 'last_read_message_id');
    }

    /** Username unik dari bagian lokal email: budi, budi_1, budi_2, ... */
    public static function uniqueUsernameFrom(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_') ?: 'user';
        $taken = static::withoutGlobalScopes()->where('username', 'like', $base.'%')->pluck('username')->flip();

        $username = $base;
        for ($i = 1; $taken->has($username); $i++) {
            $username = "{$base}_{$i}";
        }

        return $username;
    }

    /** Cari user dari email ATAU username (dipakai login web & API). */
    public static function findByLogin(string $login): ?self
    {
        $login = trim($login);
        $query = static::withoutGlobalScopes();

        return filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $query->where('email', $login)->first()
            : $query->where('username', $login)->orWhere('email', $login)->first();
    }

    /** Ringkasan profil untuk respons API. */
    public function toProfileArray(): array
    {
        return $this->only(['id', 'tenant_id', 'name', 'username', 'email', 'role', 'status', 'avatar_url']);
    }

    // ── RBAC (aplikasi internal: superadmin & agent) ─────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Role 'owner'/'admin' (warisan skema lama) dipetakan ke superadmin.
     *
     * @param  string|array<int, string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        $allowed = is_string($roles) ? array_map('trim', explode(',', $roles)) : $roles;

        if (in_array($this->role, $allowed, true)) {
            return true;
        }

        return $this->isSuperAdmin() && array_intersect(['owner', 'admin'], $allowed) !== [];
    }
}
