<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Auth;

use App\Data\Auth\VerifyOtpData;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class VerifyCodeRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:'.config('integrations.otp.length')],
        ];
    }

    public function toDto(string $phoneE164, UserRole $role): VerifyOtpData
    {
        return new VerifyOtpData(
            phoneE164: $phoneE164,
            code: $this->string('code')->toString(),
            role: $role,
        );
    }
}
