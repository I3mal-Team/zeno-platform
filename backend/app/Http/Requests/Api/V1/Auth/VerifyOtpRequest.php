<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Data\Auth\VerifyOtpData;
use App\Enums\OtpPurpose;
use App\Enums\UserRole;
use App\Rules\SaudiMobile;
use App\Support\Phone\SaudiPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class VerifyOtpRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', new SaudiMobile],
            'code' => ['required', 'string', 'digits:'.config('integrations.otp.length')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'device_name' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function toDto(): VerifyOtpData
    {
        return new VerifyOtpData(
            phoneE164: SaudiPhone::normalise($this->string('phone')->toString()) ?? '',
            code: $this->string('code')->toString(),
            role: UserRole::from($this->string('role')->toString()),
            purpose: OtpPurpose::Login,
        );
    }

    public function deviceName(): string
    {
        return $this->string('device_name')->toString() ?: 'unknown-device';
    }
}
