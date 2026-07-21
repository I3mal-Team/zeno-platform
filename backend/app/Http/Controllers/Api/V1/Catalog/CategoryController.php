<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

final class CategoryController extends ApiController
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            CategoryResource::collection($this->catalog->activeCategories())
        );
    }
}
