<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\ReportTarget;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function __invoke(Request $request, string $target, int $id): RedirectResponse
    {
        $kind = ReportTarget::tryFrom($target) ?? abort(404);

        $request->validate([
            'report_reason_id' => ['required', 'integer'],
            'details' => ['nullable', 'string', 'max:1000'],
        ], [], ['report_reason_id' => 'السبب', 'details' => 'التفاصيل']);

        $this->reports->file(
            $request->user(),
            $kind,
            $id,
            (int) $request->integer('report_reason_id'),
            $request->string('details')->trim()->value() ?: null,
        );

        return back()->with('status', 'تم استلام بلاغك وسيُراجَع. شكراً لك.');
    }
}
