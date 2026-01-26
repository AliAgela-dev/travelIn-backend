<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResortResource;
use App\Models\Resort;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ResortController extends Controller
{
    /**
     * List resorts with search, filter, and sort.
     */
    public function index()
    {
        $resorts = QueryBuilder::for(Resort::class)
            ->active()
            ->allowedFilters([
                AllowedFilter::scope('city_id', 'inCity'),
                AllowedFilter::scope('area_id', 'inArea'),
                AllowedFilter::scope('min_rating', 'minRating'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['reviews_avg_rating', 'en_name', 'created_at'])
            ->with(['city', 'area'])
            ->paginate();

        return $this->successCollection(ResortResource::collection($resorts));
    }

    /**
     * Show resort details.
     */
    public function show(Resort $resort)
    {
        $resort->load(['city', 'area', 'units']);

        return $this->success(new ResortResource($resort));
    }
}
