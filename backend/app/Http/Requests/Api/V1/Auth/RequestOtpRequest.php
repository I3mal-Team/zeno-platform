<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Data\Auth\RequestOtpData;
use App\Enums\OtpPurpose;
use App\Rules\SaudiMobile;
use App\Support\Phone\SaudiPhone;
use Illuminate\Foundation\Http\FormRequest;

final class RequestOtpRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', new SaudiMobile],
        ];
    }

    public function toDto(): RequestOtpData
    {
        return new RequestOtpData(
            phoneE164: SaudiPhone::normalise($this->string('phone')->toString()) ?? '',
            purpose: OtpPurpose::Login,
        );
    }
}
