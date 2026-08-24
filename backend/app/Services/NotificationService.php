<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\DeviceTokenRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notifications,
        private readonly DeviceTokenRepository $tokens,
    ) {}

    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    public function list(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($user, $perPage);
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }

    public function markRead(User $user, string $id): void
    {
        $this->notifications->markAsRead($user, $id);
    }

    public function markAllRead(User $user): void
    {
        $this->notifications->markAllAsRead($user);
    }

    public function registerDevice(User $user, string $token, string $platform): void
    {
        $this->tokens->register($user->id, $token, $platform);
    }

    public function forgetDevice(string $token): void
    {
        $this->tokens->forget($token);
    }
}
