<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Profile;

use App\Data\Profile\CandidateProfileData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveCandidateProfileRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'national_id' => ['nullable', 'string', 'digits:10'],
            'national_id_type' => ['nullable', Rule::in(['national', 'iqama'])],
            'birth_date' => ['nullable', 'date', 'before:'.now()->subYears(15)->toDateString()],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'nationality_code' => ['nullable', 'string', 'size:2'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'years_of_experience' => ['nullable', 'integer', 'between:0,60'],
            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:40'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDto(): CandidateProfileData
    {
        return new CandidateProfileData(
            fullName: $this->string('full_name')->toString(),
            nationalId: $this->input('national_id'),
            nationalIdType: $this->input('national_id_type'),
            birthDate: $this->input('birth_date'),
            gender: $this->input('gender'),
            nationalityCode: $this->input('nationality_code'),
            cityId: $this->integer('city_id') ?: null,
            jobTitle: $this->input('job_title'),
            yearsOfExperience: $this->has('years_of_experience')
                ? $this->integer('years_of_experience')
                : null,
            skills: $this->input('skills', []),
            bio: $this->input('bio'),
        );
    }
}
