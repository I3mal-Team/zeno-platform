<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Jobs;

use Illuminate\Foundation\Http\FormRequest;

final class SaveJobAlertRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'work_type_id' => ['nullable', 'integer', 'exists:work_types,id'],
        ];
    }

    /**
     * The alert's stored facets — a null facet matches anything.
     *
     * @return array<string, mixed>
     */
    public function attributesForAlert(): array
    {
        return [
            'keyword' => $this->filled('keyword') ? trim($this->string('keyword')->toString()) : null,
            'category_id' => $this->filled('category_id') ? $this->integer('category_id') : null,
            'city_id' => $this->filled('city_id') ? $this->integer('city_id') : null,
            'work_type_id' => $this->filled('work_type_id') ? $this->integer('work_type_id') : null,
        ];
    }
}
