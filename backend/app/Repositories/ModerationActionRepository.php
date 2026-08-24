<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

final class ModerationActionRepository
{
    public function record(
        int $adminId,
        string $action,
        string $targetType,
        int $targetId,
        string $reason,
    ): void {
        DB::table('moderation_actions')->insert([
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'reason' => $reason,
            'is_reversible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
