<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Jobs;

use Illuminate\Foundation\Http\FormRequest;

final class BrowseJobsRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'exists:categories,code'],
            'work_type' => ['nullable', 'string', 'exists:work_types,code'],
            'city' => ['nullable', 'integer', 'exists:cities,id'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'query' => $this->input('q'),
            'category' => $this->input('category'),
            'work_type' => $this->input('work_type'),
            'city' => $this->input('city'),
            'salary_min' => $this->input('salary_min'),
        ];
    }

    public function perPage(): int
    {
        return $this->filled('per_page') ? $this->integer('per_page') : 15;
    }
}
