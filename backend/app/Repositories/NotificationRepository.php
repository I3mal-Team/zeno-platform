<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationRepository
{
    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, DatabaseNotification> $page */
        $page = $user->notifications()->paginate($perPage);

        return $page;
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $id): void
    {
        $user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
