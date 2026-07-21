<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Catalog\CityResource;
use App\Http\Resources\V1\Catalog\DistrictResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

final class CityController extends ApiController
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            CityResource::collection($this->catalog->activeCities())
        );
    }

    public function districts(int $city): JsonResponse
    {
        return $this->successResponse(
            DistrictResource::collection($this->catalog->districtsFor($city))
        );
    }
}
