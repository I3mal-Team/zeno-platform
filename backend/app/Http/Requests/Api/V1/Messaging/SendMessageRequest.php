<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Messaging;

use Illuminate\Foundation\Http\FormRequest;

final class SendMessageRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            // The client's idempotency key — a retried send never duplicates.
            'client_uuid' => ['required', 'uuid'],
        ];
    }

    public function body(): string
    {
        return trim($this->string('body')->toString());
    }

    public function clientUuid(): string
    {
        return $this->string('client_uuid')->toString();
    }
}
