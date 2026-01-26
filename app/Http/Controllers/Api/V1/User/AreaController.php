<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $areas = QueryBuilder::for(Area::class)
            ->where('status', GeneralStatus::Active)
            ->allowedFilters([
                AllowedFilter::exact('city_id'),
                AllowedFilter::scope('search'),
            ])
            ->paginate();

        return $this->successCollection(AreaResource::collection($areas));
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        if ($area->status !== GeneralStatus::Active) {
            abort(404);
        }

        return $this->success(new AreaResource($area->load('city')));
    }
}
