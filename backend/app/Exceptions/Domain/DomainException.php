<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * أساس كل استثناءات قواعد العمل.
 *
 * الـ Services ترمي هذه الاستثناءات؛ المعالج المركزي يحوّلها إلى غلاف الخطأ.
 * الـ controllers لا تمسك استثناءات.
 *
 * كل صنف يرث هذا يجب أن يقابله ثابت في
 * `mobile/lib/core/errors/error_codes.dart` — الكود هو العقد، لا الرسالة.
 */
abstract class DomainException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('errors.'.$this->messageKey()));
    }

    /** الكود الثابت الذي يتفرّع عليه العميل. */
    abstract public function errorCode(): string;

    /** مفتاح الرسالة في `lang/{locale}/errors.php`. */
    abstract protected function messageKey(): string;

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * حقول إضافية تساعد العميل على العرض دون تحليل الرسالة.
     *
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return [];
    }
}
