<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Profile\OrganizationData;
use App\Enums\OrganizationRole;
use App\Enums\VerificationStatus;
use App\Models\Organization;
use Illuminate\Support\Str;

final class OrganizationRepository
{
    public function findForUser(int $userId): ?Organization
    {
        return Organization::query()
            ->with('city')
            ->whereHas('members', fn ($q) => $q->where('users.id', $userId))
            ->first();
    }

    /** Only an active organization has a public page; a suspended one 404s. */
    public function findPublicBySlug(string $slug): ?Organization
    {
        return Organization::query()
            ->with('city')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    public function create(OrganizationData $data, int $ownerId): Organization
    {
        $organization = Organization::query()->create([
            'uuid' => (string) Str::uuid(),
            'type' => $data->type->value,
            'name' => $data->name,
            'slug' => $this->uniqueSlug($data->name),
            'commercial_registration' => $data->commercialRegistration,
            'responsible_person_name' => $data->responsiblePersonName,
            'city_id' => $data->cityId,
            'about' => $data->about,
            'verification_status' => VerificationStatus::Unverified->value,
            'status' => 'active',
        ]);

        $organization->members()->attach($ownerId, [
            'role' => OrganizationRole::Owner->value,
            'joined_at' => now(),
        ]);

        return $organization;
    }

    /** Grants the sticky trust that lets later listings skip review (D-15). */
    public function enableAutoPublish(Organization $organization): void
    {
        $organization->forceFill(['auto_publish_jobs' => true])->save();
    }

    public function update(Organization $organization, OrganizationData $data): Organization
    {
        $attributes = [
            'type' => $data->type->value,
            'name' => $data->name,
            'responsible_person_name' => $data->responsiblePersonName,
            'city_id' => $data->cityId,
            'about' => $data->about,
        ];

        // The CR is encrypted and never returned to clients to prefill an edit
        // form, so a null here means "leave it as it is", not "clear it".
        if ($data->commercialRegistration !== null) {
            $attributes['commercial_registration'] = $data->commercialRegistration;
        }

        $organization->update($attributes);

        return $organization->fresh(['city']);
    }

    /**
     * Arabic names transliterate to empty slugs, so the uuid tail is what keeps
     * the value unique and non-empty.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        return ($base !== '' ? $base.'-' : '').Str::lower(Str::random(6));
    }
}
