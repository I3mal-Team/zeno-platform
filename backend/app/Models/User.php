<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid', 'phone_e164', 'role', 'email', 'status', 'locale',
    ];

    protected $hidden = ['remember_token'];

    /** Mirrors the column defaults so a freshly created model is complete. */
    protected $attributes = [
        'status' => 'active',
        'locale' => 'ar',
    ];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * The employer panel authenticates with phone and OTP, not a password, so
     * Filament's default login form is a placeholder until that flow exists.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'employer'
            && $this->role === 'employer'
            && $this->status === 'active';
    }
}
