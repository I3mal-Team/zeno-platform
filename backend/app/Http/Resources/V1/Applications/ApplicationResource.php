<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Applications;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Application */
final class ApplicationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference_number,
            'status' => $this->status->value,
            'status_label' => $this->status->candidateLabel(),
            'contact_channel' => $this->contact_channel->value,
            'applied_at' => $this->created_at->toIso8601String(),
            // Present once accepted so "تواصل" can open the thread directly.
            'conversation_uuid' => $this->relationLoaded('conversation') ? $this->conversation?->uuid : null,
            'job' => $this->whenLoaded('job', fn () => [
                'id' => $this->job->uuid,
                'title' => $this->job->title,
                'slug' => $this->job->slug,
                'status' => $this->job->status->value,
                'organization' => $this->job->relationLoaded('organization') ? $this->job->organization->name : null,
                'category' => $this->job->relationLoaded('category') ? [
                    'code' => $this->job->category->code,
                    'name' => $this->job->category->name,
                ] : null,
            ]),
        ];
    }
}
