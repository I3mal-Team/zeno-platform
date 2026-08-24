<?php

declare(strict_types=1);

namespace App\Http\Requests\Site\Profile;

use App\Data\Profile\CandidateProfileData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The web mirror of the API's profile intake. The form collects age (not a
 * birth date) and a single ID number, so this request derives the stored
 * birth_date and national_id_type the model expects.
 */
final class SaveCandidateProfileRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'national_id' => ['required', 'string', 'digits:10'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'nationality_code' => ['required', 'string', 'size:2', 'exists:countries,iso2'],
            'age' => ['nullable', 'integer', 'between:16,80'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'years_of_experience' => ['nullable', 'integer', 'between:0,60'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'full_name' => 'الاسم الكامل',
            'national_id' => 'رقم الهوية أو الإقامة',
            'city_id' => 'المدينة',
            'age' => 'العمر',
            'nationality_code' => 'الجنسية',
            'job_title' => 'المهنة',
            'years_of_experience' => 'سنوات الخبرة',
            'bio' => 'النبذة',
        ];
    }

    public function toDto(): CandidateProfileData
    {
        $nationalId = $this->filled('national_id') ? $this->string('national_id')->toString() : null;

        return new CandidateProfileData(
            fullName: $this->string('full_name')->trim()->toString(),
            nationalId: $nationalId,
            nationalIdType: $this->nationalIdType($nationalId),
            birthDate: $this->birthDate(),
            gender: $this->filled('gender') ? $this->string('gender')->toString() : null,
            nationalityCode: $this->filled('nationality_code')
                ? strtoupper($this->string('nationality_code')->toString())
                : null,
            cityId: $this->integer('city_id') ?: null,
            jobTitle: $this->filled('job_title') ? $this->string('job_title')->trim()->toString() : null,
            yearsOfExperience: $this->filled('years_of_experience') ? $this->integer('years_of_experience') : null,
            skills: [],
            bio: $this->filled('bio') ? $this->string('bio')->trim()->toString() : null,
        );
    }

    /** Saudi national IDs start with 1, iqamas with 2. */
    private function nationalIdType(?string $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        return str_starts_with($id, '2') ? 'iqama' : 'national';
    }

    private function birthDate(): ?string
    {
        if (! $this->filled('age')) {
            return null;
        }

        return now()->subYears($this->integer('age'))->toDateString();
    }
}
