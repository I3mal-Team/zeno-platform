<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Employer;

use App\Data\Profile\OrganizationData;
use App\Enums\OrganizationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveOrganizationRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(OrganizationType::class)],
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'responsible_person_name' => ['nullable', 'string', 'max:120'],
            'commercial_registration' => [
                Rule::requiredIf(fn () => $this->input('type') === OrganizationType::Company->value),
                'nullable',
                'string',
                'digits:10',
            ],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'about' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): OrganizationData
    {
        return new OrganizationData(
            type: OrganizationType::from($this->string('type')->toString()),
            name: $this->string('name')->toString(),
            responsiblePersonName: $this->input('responsible_person_name'),
            commercialRegistration: $this->input('commercial_registration'),
            cityId: $this->integer('city_id') ?: null,
            about: $this->input('about'),
        );
    }
}
