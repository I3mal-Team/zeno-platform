<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Profile\SaveOrganizationRequest;
use App\Http\Resources\V1\Profile\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrganizationController extends ApiController
{
    public function __construct(private readonly OrganizationService $organizations) {}

    public function show(Request $request): JsonResponse
    {
        $organization = $this->organizations->find($request->user())
            ?? throw new NotFoundHttpException;

        return $this->successResponse(new OrganizationResource($organization));
    }

    public function save(SaveOrganizationRequest $request): JsonResponse
    {
        $organization = $this->organizations->save($request->user(), $request->toDto());

        return $this->successResponse(
            new OrganizationResource($organization->load('city')),
            __('messages.profile_saved'),
        );
    }

    public function logo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:'.config('media.max_upload_kb')],
        ]);

        $organization = $this->organizations->find($request->user())
            ?? throw new NotFoundHttpException;
        $file = $request->file('logo');

        if ($file instanceof UploadedFile) {
            $this->organizations->saveLogo($organization, $file);
        }

        return $this->successResponse(
            new OrganizationResource($organization->fresh('city')),
            __('messages.logo_saved'),
        );
    }
}
