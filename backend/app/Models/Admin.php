<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Separate from User by design: admins have no self-registration path and no
 * OTP login, so no role-logic mistake can promote a user into one.
 */
class Admin extends Authenticatable implements FilamentUser
{
    use HasRoles, HasUuids, Notifiable;

    /** @var string */
    protected $guard_name = 'admin';

    protected $fillable = ['uuid', 'name', 'email', 'password', 'status'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->status === 'active';
    }
}
