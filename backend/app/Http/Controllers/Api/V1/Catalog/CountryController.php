<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Catalog\CountryResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

final class CountryController extends ApiController
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            CountryResource::collection($this->catalog->activeCountries())
        );
    }
}
