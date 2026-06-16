<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Membre de l'équipe interne de l'agence (single-tenant).
 * Rôles : owner | manager | agent | accountant.
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** Seuls les membres actifs accèdent au back-office. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
