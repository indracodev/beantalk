<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $table = 'users';

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'assigned_user_id');
    }

    public function participatingConversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot('joined_at', 'last_read_message_id');
    }

    // ========================================================================
    // RBAC ROLE & PERMISSION HELPERS
    // ========================================================================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isStaff(): bool
    {
        return $this->role === 'agent';
    }

    // Backward compatibility aliases
    public function isOwner(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }

        $allowed = (array) $roles;

        if (in_array($this->role, $allowed)) {
            return true;
        }

        // Superadmin mewarisi semua akses manajemen (owner, admin)
        if ($this->role === 'superadmin' && (in_array('owner', $allowed) || in_array('admin', $allowed))) {
            return true;
        }

        return false;
    }

    public function canManageIntegrations(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageTeam(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canChangeRoles(): bool
    {
        return $this->isSuperAdmin();
    }
}
