<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\ModerationActionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

final class AdminUserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly ModerationActionRepository $actions,
    ) {}

    public function suspend(User $user, string $reason, int $adminId): void
    {
        DB::transaction(function () use ($user, $reason, $adminId) {
            $this->users->suspend($user, $reason);
            $this->users->deleteAllTokens($user);

            $this->actions->record($adminId, 'suspend_user', 'user', $user->id, $reason);
        });
    }

    public function reinstate(User $user, string $reason, int $adminId): void
    {
        DB::transaction(function () use ($user, $reason, $adminId) {
            $this->users->setStatus($user, UserStatus::Active);

            $this->actions->record($adminId, 'reinstate_user', 'user', $user->id, $reason);
        });
    }
}
