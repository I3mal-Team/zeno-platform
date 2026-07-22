<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Data\Auth\VerifyOtpData;
use App\Enums\OtpPurpose;
use App\Enums\UserRole;
use App\Support\Phone\InternationalPhone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class VerifyOtpRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', Rule::exists('countries', 'iso2')->where('is_active', true)],
            'code' => ['required', 'string', 'digits:'.config('integrations.otp.length')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'device_name' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isEmpty() && $this->phoneE164() === null) {
                $validator->errors()->add('phone', __('errors.phone_invalid'));
            }
        });
    }

    public function country(): string
    {
        return $this->string('country')->toString() ?: 'SA';
    }

    public function phoneE164(): ?string
    {
        return InternationalPhone::normalise($this->string('phone')->toString(), $this->country());
    }

    public function toDto(): VerifyOtpData
    {
        return new VerifyOtpData(
            phoneE164: (string) $this->phoneE164(),
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
