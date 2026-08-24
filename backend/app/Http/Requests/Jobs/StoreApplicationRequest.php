<?php

declare(strict_types=1);

namespace App\Http\Requests\Jobs;

use App\Enums\ApplicationFieldType;
use App\Models\Job;
use App\Support\ApplicationForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Validates a candidate's answers against the job's employer-authored form. The
 * rules are built dynamically from the job's stored field definitions, so a job
 * with no custom fields accepts a bare (one-click) apply.
 */
final class StoreApplicationRequest extends FormRequest
{
    private ?Job $resolvedJob = null;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ApplicationForm::answerRules($this->jobFields());
    }

    /**
     * The scalar (text/number/select) answers keyed by field key.
     *
     * @return array<string, mixed>
     */
    public function scalarAnswers(): array
    {
        $answers = [];

        foreach ($this->jobFields() as $field) {
            $type = ApplicationFieldType::tryFrom($field['type']);

            if ($type === null || $type->isUpload()) {
                continue;
            }

            $value = $this->input('answers.'.$field['key']);

            if ($value === null || $value === '') {
                continue;
            }

            $answers[$field['key']] = $type === ApplicationFieldType::Number ? $value + 0 : (string) $value;
        }

        return $answers;
    }

    /**
     * The uploaded file/image answers keyed by field key.
     *
     * @return array<string, UploadedFile>
     */
    public function uploadedFiles(): array
    {
        $files = [];

        foreach ($this->jobFields() as $field) {
            $type = ApplicationFieldType::tryFrom($field['type']);

            if ($type === null || ! $type->isUpload()) {
                continue;
            }

            $file = $this->file('answers.'.$field['key']);

            if ($file instanceof UploadedFile) {
                $files[$field['key']] = $file;
            }
        }

        return $files;
    }

    /** @return array<int, array<string, mixed>> */
    private function jobFields(): array
    {
        $this->resolvedJob ??= Job::query()
            ->where('slug', (string) $this->route('slug'))
            ->first();

        return $this->resolvedJob->application_fields ?? [];
    }
}
