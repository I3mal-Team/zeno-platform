<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VerificationRequestStatus;
use App\Enums\VerificationStatus;
use App\Exceptions\Domain\OrganizationMissingException;
use App\Exceptions\Domain\VerificationAlreadyPendingException;
use App\Models\Organization;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Repositories\OrganizationRepository;
use App\Repositories\VerificationRequestRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Verification is the trust signal the whole marketplace leans on: a verified
 * organization publishes straight to `active` (D-15) and wears the badge
 * candidates look for. Until now the standing could only be flipped by hand in
 * the admin panel; this is the route an employer walks themselves.
 */
final class VerificationService
{
    public function __construct(
        private readonly VerificationRequestRepository $requests,
        private readonly OrganizationRepository $organizations,
    ) {}

    /** @param  array<int, UploadedFile>  $documents */
    public function submit(User $employer, array $documents): VerificationRequest
    {
        $organization = $this->organizationFor($employer);

        if ($organization->verification_status === VerificationStatus::Verified) {
            throw new VerificationAlreadyPendingException;
        }

        if ($this->requests->pendingFor($organization->id) !== null) {
            throw new VerificationAlreadyPendingException;
        }

        return DB::transaction(function () use ($organization, $employer, $documents) {
            $request = $this->requests->create($organization->id, $employer->id);

            foreach ($documents as $document) {
                $request->addMedia($document)->toMediaCollection(VerificationRequest::DOCUMENTS_COLLECTION);
            }

            // The organization reads as "under review" the moment it is filed,
            // so the badge never claims more than the admin has confirmed.
            $organization->forceFill(['verification_status' => VerificationStatus::Pending->value])->save();

            return $request;
        });
    }

    /**
     * The admin's decision. Approving is what grants the badge and, with it,
     * immediate publishing; rejecting returns the organization to unverified
     * so they may correct their documents and file again.
     */
    public function decide(
        VerificationRequest $request,
        VerificationRequestStatus $status,
        ?int $adminId,
        ?string $note = null,
    ): VerificationRequest {
        return DB::transaction(function () use ($request, $status, $adminId, $note) {
            $this->requests->decide($request, $status, $adminId, $note);

            $request->organization->forceFill([
                'verification_status' => $status === VerificationRequestStatus::Approved
                    ? VerificationStatus::Verified->value
                    : VerificationStatus::Unverified->value,
                'verified_at' => $status === VerificationRequestStatus::Approved ? now() : null,
            ])->save();

            return $request;
        });
    }

    public function currentFor(User $employer): ?VerificationRequest
    {
        $organization = $this->organizations->findForUser($employer->id);

        return $organization === null ? null : $this->requests->latestFor($organization->id);
    }

    private function organizationFor(User $employer): Organization
    {
        return $this->organizations->findForUser($employer->id) ?? throw new OrganizationMissingException;
    }
}
