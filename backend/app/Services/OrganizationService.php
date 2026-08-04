<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Profile\OrganizationData;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\UploadedFile;

final class OrganizationService
{
    public function __construct(private readonly OrganizationRepository $organizations) {}

    public function find(User $user): ?Organization
    {
        return $this->organizations->findForUser($user->id);
    }

    /** Replaces the organisation's logo — the collection keeps a single file. */
    public function saveLogo(Organization $organization, UploadedFile $file): void
    {
        $organization->addMedia($file)->toMediaCollection(Organization::LOGO_COLLECTION);
    }

    /** Creating and updating share one entry point because the app has one form. */
    public function save(User $user, OrganizationData $data): Organization
    {
        $existing = $this->organizations->findForUser($user->id);

        return $existing === null
            ? $this->organizations->create($data, $user->id)
            : $this->organizations->update($existing, $data);
    }
}
