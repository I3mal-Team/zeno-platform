<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Profile;

use App\Models\Organization;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Organization */
final class OrganizationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'type' => $this->type->value,
            'name' => $this->name,
            'slug' => $this->slug,
            'responsible_person_name' => $this->responsible_person_name,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
            'about' => $this->about,
            'verification_status' => $this->verification_status->value,
            'is_verified' => $this->isVerified(),
            'logo_url' => PublicMedia::url($this->getFirstMediaUrl(Organization::LOGO_COLLECTION) ?: null),
        ];
    }
}
