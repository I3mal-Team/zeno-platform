<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Str;

final class UserRepository
{
    public function findByPhone(string $phoneE164): ?User
    {
        return User::query()->where('phone_e164', $phoneE164)->first();
    }

    public function create(string $phoneE164, UserRole $role): User
    {
        return User::query()->create([
            'uuid' => (string) Str::uuid(),
            'phone_e164' => $phoneE164,
            'role' => $role->value,
            'status' => UserStatus::Active->value,
        ]);
    }

    public function markPhoneVerified(User $user): void
    {
        $user->forceFill(['phone_verified_at' => now()])->save();
    }

    public function touchLastActive(User $user): void
    {
        $user->forceFill(['last_active_at' => now()])->saveQuietly();
    }

    public function suspend(User $user, string $reason): void
    {
        $user->forceFill([
            'status' => UserStatus::Suspended->value,
            'suspended_reason' => $reason,
            'suspended_at' => now(),
        ])->save();
    }

    public function setStatus(User $user, UserStatus $status): void
    {
        $user->forceFill([
            'status' => $status->value,
            'suspended_reason' => null,
            'suspended_at' => null,
        ])->save();
    }

    /**
     * Closes the account without removing the row. Two constraints force this
     * shape: jobs.created_by_user_id and the verification requests restrict on
     * delete, so an employer who ever posted cannot be erased; and the unique
     * index on phone_e164 is partial on deleted_at IS NULL, so soft deleting
     * already frees the number to register again.
     */
    public function softDeleteAccount(User $user): void
    {
        $user->forceFill([
            'status' => UserStatus::Deleted->value,
            'email' => null,
            'last_active_at' => null,
        ])->save();

        $user->delete();
    }

    public function deleteAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function deleteToken(User $user, int $tokenId): void
    {
        $user->tokens()->whereKey($tokenId)->delete();
    }
}
