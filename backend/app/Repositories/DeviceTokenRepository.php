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

    /** Stops every push to a closed account, on every device it registered. */
    public function forgetAllForUser(int $userId): void
    {
        DeviceToken::query()->where('user_id', $userId)->delete();
    }

    /** @return list<string> */
    public function tokensForUser(int $userId): array
    {
        return DeviceToken::query()
            ->where('user_id', $userId)
            ->pluck('token')
            ->all();
    }
}
