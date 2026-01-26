<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = QueryBuilder::for(City::class)
            ->where('status', GeneralStatus::Active)
            ->allowedFilters([
                AllowedFilter::scope('search'),
            ])
            ->paginate();

        return $this->successCollection(CityResource::collection($cities));
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        if ($city->status !== GeneralStatus::Active) {
            abort(404);
        }

        return $this->success(new CityResource($city->load('areas')));
    }
}
