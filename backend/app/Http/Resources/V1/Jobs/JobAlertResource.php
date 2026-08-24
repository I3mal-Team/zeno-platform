<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Jobs;

use App\Models\JobAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobAlert */
final class JobAlertResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'keyword' => $this->keyword,
            'category' => $this->whenLoaded('category', fn () => [
                'code' => $this->category->code,
                'name' => $this->category->name,
            ]),
            'city' => $this->whenLoaded('city', fn () => $this->city->name),
            'work_type' => $this->whenLoaded('workType', fn () => $this->workType->name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
