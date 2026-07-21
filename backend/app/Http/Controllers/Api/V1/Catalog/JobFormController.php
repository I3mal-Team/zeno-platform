<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Catalog\ReferenceOptionResource;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

final class JobFormController extends ApiController
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): JsonResponse
    {
        $options = $this->catalog->jobFormOptions();

        return $this->successResponse([
            'categories' => CategoryResource::collection($options->categories)->resolve(),
            'work_types' => ReferenceOptionResource::collection($options->workTypes)->resolve(),
            'salary_units' => ReferenceOptionResource::collection($options->salaryUnits)->resolve(),
            'gender_requirements' => ReferenceOptionResource::collection($options->genderRequirements)->resolve(),
            'nationality_requirements' => ReferenceOptionResource::collection($options->nationalityRequirements)->resolve(),
        ]);
    }
}
