<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Profile\CandidateProfileResource;
use App\Services\CandidateProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ResumeController extends ApiController
{
    public function __construct(private readonly CandidateProfileService $profiles) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:'.config('media.max_upload_kb')],
        ]);

        $profile = $this->profiles->find($request->user())
            ?? throw new NotFoundHttpException;
        $file = $request->file('resume');

        if ($file instanceof UploadedFile) {
            $this->profiles->saveResume($profile, $file);
        }

        return $this->successResponse(
            new CandidateProfileResource($profile->fresh(['city'])),
            __('messages.resume_saved'),
        );
    }
}
