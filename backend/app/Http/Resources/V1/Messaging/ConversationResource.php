<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Messaging;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
final class ConversationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        // The title is always the *other* participant, from the viewer's side.
        // A candidate may have applied before completing a profile, so the name
        // is read null-tolerantly (the model plugin wrongly infers it non-null).
        $viewerIsCandidate = $request->user()?->getKey() === $this->candidate_id;
        $candidateName = data_get($this->candidate, 'candidateProfile.full_name');

        $title = $viewerIsCandidate
            ? $this->organization->name
            : (is_string($candidateName) ? $candidateName : 'مرشح');

        $last = $this->relationLoaded('latestMessage') ? $this->latestMessage : null;

        return [
            'uuid' => $this->uuid,
            'title' => $title,
            'job_title' => $this->application->job->title ?? null,
            'status' => $this->status,
            'last_message' => $last?->body,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
        ];
    }
}
