<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ApplicationFieldType;
use Illuminate\Validation\Rule;

/**
 * The employer-authored application form: shared validation + normalisation for
 * the field definitions (used by the API and web job requests), and the rules a
 * candidate's answers are validated against, derived from a job's definitions.
 */
final class ApplicationForm
{
    public const MAX_FIELDS = 20;

    /** Validation rules for the field-definition array the employer submits. */
    public static function definitionRules(): array
    {
        return [
            'application_fields' => ['nullable', 'array', 'max:'.self::MAX_FIELDS],
            'application_fields.*.label' => ['required', 'string', 'max:120'],
            'application_fields.*.type' => ['required', Rule::enum(ApplicationFieldType::class)],
            'application_fields.*.required' => ['nullable', 'boolean'],
            'application_fields.*.options' => ['nullable', 'array', 'max:20'],
            'application_fields.*.options.*' => ['string', 'max:80'],
        ];
    }

    /**
     * Shape raw request input into the stored definition: a stable key per
     * field, options only for selects, blanks dropped.
     *
     * @param  array<int, array<string, mixed>>|null  $input
     * @return list<array<string, mixed>>
     */
    public static function normalize(?array $input): array
    {
        $fields = [];

        foreach (array_values($input ?? []) as $index => $raw) {
            $label = trim((string) ($raw['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $type = ApplicationFieldType::tryFrom((string) ($raw['type'] ?? '')) ?? ApplicationFieldType::Text;

            $options = [];
            if ($type === ApplicationFieldType::Select) {
                foreach ($raw['options'] ?? [] as $option) {
                    $option = trim((string) $option);
                    if ($option !== '') {
                        $options[] = $option;
                    }
                }
            }

            $fields[] = [
                'key' => 'field_'.$index,
                'label' => $label,
                'type' => $type->value,
                'required' => (bool) ($raw['required'] ?? false),
                'options' => $options,
            ];
        }

        return $fields;
    }

    /**
     * Validation rules for a candidate's answers, derived from a job's stored
     * field definitions. Keys are `answers.{field_key}`; uploads validate the
     * file input at `answers.{field_key}` too.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    public static function answerRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = 'answers.'.$field['key'];
            $type = ApplicationFieldType::tryFrom($field['type']) ?? ApplicationFieldType::Text;
            $required = (bool) ($field['required'] ?? false);
            $head = $required ? ['required'] : ['nullable'];

            $rules[$key] = match ($type) {
                ApplicationFieldType::Number => [...$head, 'numeric'],
                ApplicationFieldType::Select => [...$head, 'string', Rule::in($field['options'] ?? [])],
                ApplicationFieldType::File => [...$head, 'file', 'mimes:pdf,doc,docx,jpeg,png,webp', 'max:'.config('media.max_upload_kb')],
                ApplicationFieldType::Image => [...$head, 'image', 'mimes:jpeg,png,webp', 'max:'.config('media.max_upload_kb')],
                default => [...$head, 'string', 'max:2000'],
            };
        }

        return $rules;
    }
}
