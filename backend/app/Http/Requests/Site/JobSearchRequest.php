<?php

declare(strict_types=1);

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

final class JobSearchRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'exists:categories,code'],
            'work_type' => ['nullable', 'string', 'exists:work_types,code'],
            'city' => ['nullable', 'integer', 'exists:cities,id'],
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
        ];
    }
}
