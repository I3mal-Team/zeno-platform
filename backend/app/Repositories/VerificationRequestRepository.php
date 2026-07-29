<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\VerificationRequestStatus;
use App\Models\VerificationRequest;

final class VerificationRequestRepository
{
    public function pendingFor(int $organizationId): ?VerificationRequest
    {
        return VerificationRequest::query()
            ->where('organization_id', $organizationId)
            ->where('status', VerificationRequestStatus::Pending->value)
            ->latest('id')
            ->first();
    }

    public function latestFor(int $organizationId): ?VerificationRequest
    {
        return VerificationRequest::query()
            ->where('organization_id', $organizationId)
            ->latest('id')
            ->first();
    }

    public function create(int $organizationId, int $submittedByUserId): VerificationRequest
    {
        return VerificationRequest::query()->create([
            'organization_id' => $organizationId,
            'submitted_by_user_id' => $submittedByUserId,
            'status' => VerificationRequestStatus::Pending->value,
        ]);
    }

    public function decide(
        VerificationRequest $request,
        VerificationRequestStatus $status,
        ?int $adminId,
        ?string $note,
    ): VerificationRequest {
        $request->forceFill([
            'status' => $status->value,
            'reviewed_by_admin_id' => $adminId,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        return $request;
    }
}
