<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\DeviceToken;

final class DeviceTokenRepository
{
    /** Re-registering an existing token just re-points it at the current user. */
    public function register(int $userId, string $token, string $platform): void
    {
        DeviceToken::query()->updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId, 'platform' => $platform, 'last_used_at' => now()],
        );
    }

    public function forget(string $token): void
    {
        DeviceToken::query()->where('token', $token)->delete();
    }
}
