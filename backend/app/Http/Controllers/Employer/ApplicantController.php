<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\ApplicationReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ApplicantController extends Controller
{
    public function __construct(private readonly ApplicationReviewService $review) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $statuses = $status !== '' ? [$status] : [];
        $search = $request->string('q')->trim()->toString();

        return view('employer.applicants.index', [
            'applicants' => $this->review->listForOrganization($request->user(), $statuses, 15, $search),
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        return view('employer.applicants.show', [
            'application' => $this->review->view($request->user(), $id),
        ]);
    }

    public function accept(Request $request, int $id): RedirectResponse
    {
        $result = $this->review->accept($request->user(), $id);

        $message = $result['jobFilled']
            ? 'تم قبول المتقدم — اكتمل عدد الشواغر، يمكنك إغلاق الإعلان.'
            : 'تم قبول المتقدم.';

        return back()->with('status', $message);
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $this->review->reject($request->user(), $id, $request->input('reason'));

        return back()->with('status', 'تم رفض المتقدم.');
    }
}
