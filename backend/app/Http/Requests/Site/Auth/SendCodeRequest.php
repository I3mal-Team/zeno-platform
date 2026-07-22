<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Auth;

use App\Data\Auth\RequestOtpData;
use App\Enums\UserRole;
use App\Support\Phone\InternationalPhone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SendCodeRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', Rule::exists('countries', 'iso2')->where('is_active', true)],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['phone' => 'رقم الجوال', 'country' => 'الدولة', 'role' => 'صفة الدخول'];
    }

    /** The number is only meaningful against its country, so it is checked here. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->phoneE164() === null) {
                $validator->errors()->add('phone', __('errors.phone_invalid'));
            }
        });
    }

    public function phoneE164(): ?string
    {
        return InternationalPhone::normalise(
            $this->string('phone')->toString(),
            $this->string('country')->toString(),
        );
    }

    public function role(): UserRole
    {
        return UserRole::from($this->string('role')->toString());
    }

    public function toDto(): RequestOtpData
    {
        return new RequestOtpData(phoneE164: (string) $this->phoneE164());
    }
}
