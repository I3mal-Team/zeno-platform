<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Auth;

use App\Models\User;
use App\Support\Phone\SaudiPhone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'phone' => SaudiPhone::toLocal($this->phone_e164),
            'role' => $this->role,
            'status' => $this->status,
            'phone_verified' => $this->phone_verified_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
