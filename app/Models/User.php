<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'resident_id', 'role_id', 'name', 'email', 'password',
        'phone', 'avatar', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function hasRole(string ...$slugs): bool
    {
        return $this->role && in_array($this->role->slug, $slugs, true);
    }

    /**
     * Cek apakah user memiliki salah satu permission yang diberikan.
     * Role super_admin selalu dianggap memiliki semua permission.
     */
    public function hasPermission(string ...$slugs): bool
    {
        if (! $this->role) {
            return false;
        }
        if ($this->role->slug === 'super_admin') {
            return true;
        }

        return $this->role->hasAnyPermission($slugs);
    }

    /**
     * Cek apakah user memiliki seluruh permission yang diberikan.
     */
    public function hasAllPermissions(string ...$slugs): bool
    {
        if (! $this->role) {
            return false;
        }
        if ($this->role->slug === 'super_admin') {
            return true;
        }

        $granted = $this->role->permissionSlugs();

        foreach ($slugs as $slug) {
            if (! in_array($slug, $granted, true)) {
                return false;
            }
        }

        return true;
    }

    public function isResident(): bool
    {
        return $this->hasRole('resident');
    }
}
