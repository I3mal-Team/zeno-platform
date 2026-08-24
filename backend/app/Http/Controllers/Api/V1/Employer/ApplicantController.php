<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employer;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Employer\ApplicantProfileResource;
use App\Http\Resources\V1\Employer\ApplicantResource;
use App\Services\ApplicationReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApplicantController extends ApiController
{
    public function __construct(private readonly ApplicationReviewService $review) {}

    public function index(Request $request, string $uuid): JsonResponse
    {
        $status = $request->string('status')->toString();
        $statuses = $status !== '' ? [$status] : [];

        $applicants = $this->review->listForJob($request->user(), $uuid, $statuses, 15);

        return $this->successResponse(ApplicantResource::collection($applicants));
    }

    /** Every applicant across the employer's listings — the "المتقدمون" tab. */
    public function organizationIndex(Request $request): JsonResponse
    {
        $status = $request->string('status')->toString();
        $statuses = $status !== '' ? [$status] : [];

        $applicants = $this->review->listForOrganization($request->user(), $statuses, 15);

        return $this->successResponse(ApplicantResource::collection($applicants));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $application = $this->review->view($request->user(), $id);

        return $this->successResponse(new ApplicantProfileResource($application));
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        ['application' => $application, 'jobFilled' => $jobFilled] = $this->review->accept($request->user(), $id);

        return $this->successResponse(
            ['reference' => $application->reference_number, 'status' => $application->status->value, 'job_filled' => $jobFilled],
            __('messages.application_accepted'),
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $application = $this->review->reject($request->user(), $id, $request->input('reason'));

        return $this->successResponse(
            ['reference' => $application->reference_number, 'status' => $application->status->value],
            __('messages.application_rejected'),
        );
    }

    public function shortlist(Request $request, int $id): JsonResponse
    {
        $application = $this->review->toggleShortlist($request->user(), $id);

        return $this->successResponse(['shortlisted' => $application->shortlisted]);
    }

    public function note(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        $application = $this->review->saveNote($request->user(), $id, $validated['note'] ?? null);

        return $this->successResponse(['note' => $application->employer_note]);
    }
}
