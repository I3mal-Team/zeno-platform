<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\Phone\SaudiPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class SaudiMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! SaudiPhone::isValid($value)) {
            $fail(__('errors.phone_invalid'));
        }
    }
}
