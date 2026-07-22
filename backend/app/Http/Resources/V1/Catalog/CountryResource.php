<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Catalog;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Country */
final class CountryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'iso2' => $this->iso2,
            'dial_code' => $this->dial_code,
            'flag' => $this->flag,
            'name' => $this->name,
            'placeholder' => $this->placeholder,
        ];
    }
}
