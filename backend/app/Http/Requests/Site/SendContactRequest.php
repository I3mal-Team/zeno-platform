<?php

declare(strict_types=1);

namespace App\Http\Requests\Site;

use App\Data\Contact\ContactRequestData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SendContactRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'topic' => ['required', Rule::in(['support', 'employer', 'partnership', 'other'])],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'أدخل اسمك.',
            'email.required' => 'أدخل بريدك الإلكتروني.',
            'email.email' => 'أدخل بريدًا إلكترونيًا صحيحًا.',
            'message.required' => 'اكتب رسالتك.',
            'message.min' => 'الرسالة قصيرة جدًا.',
        ];
    }

    public function toDto(): ContactRequestData
    {
        return new ContactRequestData(
            name: $this->string('name')->toString(),
            email: $this->string('email')->toString(),
            topic: $this->string('topic')->toString(),
            message: $this->string('message')->toString(),
            ipAddress: $this->ip(),
        );
    }
}
