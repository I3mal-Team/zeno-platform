<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Jobs;

use App\Data\Jobs\JobData;
use App\Enums\ContactChannel;
use App\Support\ApplicationForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

final class SaveJobRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...ApplicationForm::definitionRules(),
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'work_type_id' => ['required', 'integer', 'exists:work_types,id'],
            'salary_unit_id' => ['required', 'integer', 'exists:salary_units,id'],
            'gender_requirement_id' => ['required', 'integer', 'exists:gender_requirements,id'],
            'nationality_requirement_id' => ['required', 'integer', 'exists:nationality_requirements,id'],
            'salary_amount' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'salary_amount_max' => ['nullable', 'numeric', 'gte:salary_amount', 'max:9999999'],
            'hours_per_week' => ['nullable', 'integer', 'min:1', 'max:168'],
            'shift_note' => ['nullable', 'string', 'max:160'],
            'vacancies_count' => ['required', 'integer', 'min:1', 'max:999'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'district_id' => [
                'nullable', 'integer',
                Rule::exists('districts', 'id')->where('city_id', $this->integer('city_id')),
            ],
            'address_line' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'contact_channel' => ['required', Rule::enum(ContactChannel::class)],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function toDto(): JobData
    {
        return new JobData(
            title: $this->string('title')->toString(),
            description: $this->input('description'),
            categoryId: $this->integer('category_id'),
            workTypeId: $this->integer('work_type_id'),
            salaryUnitId: $this->integer('salary_unit_id'),
            genderRequirementId: $this->integer('gender_requirement_id'),
            nationalityRequirementId: $this->integer('nationality_requirement_id'),
            salaryAmount: (float) $this->input('salary_amount'),
            salaryAmountMax: $this->filled('salary_amount_max') ? (float) $this->input('salary_amount_max') : null,
            hoursPerWeek: $this->filled('hours_per_week') ? $this->integer('hours_per_week') : null,
            shiftNote: $this->input('shift_note'),
            vacanciesCount: $this->integer('vacancies_count'),
            cityId: $this->integer('city_id'),
            districtId: $this->filled('district_id') ? $this->integer('district_id') : null,
            addressLine: $this->input('address_line'),
            latitude: $this->filled('latitude') ? (float) $this->input('latitude') : null,
            longitude: $this->filled('longitude') ? (float) $this->input('longitude') : null,
            contactChannel: ContactChannel::from($this->string('contact_channel')->toString()),
            expiresAt: $this->filled('expires_at') ? Carbon::parse($this->string('expires_at')->toString()) : null,
            applicationFields: ApplicationForm::normalize($this->input('application_fields')),
        );
    }
}
