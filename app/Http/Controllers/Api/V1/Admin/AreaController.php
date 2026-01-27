<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Area\StoreAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
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
            ->with('city')
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::exact('status'),
                'name_ar',
                'name_en',
                AllowedFilter::exact('city_id'),
            ])
            ->latest()
            ->paginate();

        return $this->successCollection(AreaResource::collection($areas));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAreaRequest $request)
    {
        $area = Area::create($request->validated());

        return $this->created(new AreaResource($area->load('city')), 'Area created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return $this->success(new AreaResource($area->load('city')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->update($request->validated());

        return $this->success(new AreaResource($area->load('city')), 'Area updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        // Check if area has resorts
        if ($area->resorts()->exists()) {
            return $this->error('Cannot delete area with associated resorts.', 422);
        }

        $area->delete();

        return $this->success(null, 'Area deleted successfully.');
    }
}
