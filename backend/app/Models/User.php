<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * Phone-OTP accounts have no password and no remember-me column, so the
     * "remember me" cookie machinery is disabled by giving it no column.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /** @return HasOne<CandidateProfile, $this> */
    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    /**
     * Listings this candidate bookmarked, newest first.
     *
     * @return BelongsToMany<Job, $this>
     */
    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'saved_jobs', 'candidate_id', 'job_id')
            ->withPivot('created_at')
            ->orderByPivot('created_at', 'desc');
    }

    /**
     * A job seeker who has never saved their profile still needs the intake
     * form ("أكمل بياناتك") before they land on the site or the app home.
     */
    public function needsCandidateProfile(): bool
    {
        return $this->role === UserRole::Candidate->value
            && $this->candidateProfile()->doesntExist();
    }

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
