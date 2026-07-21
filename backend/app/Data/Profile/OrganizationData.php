<?php

declare(strict_types=1);

namespace App\Data\Profile;

use App\Enums\OrganizationType;

final readonly class OrganizationData
{
    public function __construct(
        public OrganizationType $type,
        public string $name,
        public ?string $responsiblePersonName = null,
        public ?string $commercialRegistration = null,
        public ?int $cityId = null,
        public ?string $about = null,
    ) {}
}
