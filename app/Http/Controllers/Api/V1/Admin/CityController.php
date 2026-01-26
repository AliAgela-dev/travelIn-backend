<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
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
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::exact('status'),
                'name_ar', 
                'name_en'
            ])
            ->latest()
            ->paginate();

        return $this->successCollection(CityResource::collection($cities));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());

        return $this->created(new CityResource($city), 'City created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        return $this->success(new CityResource($city));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());

        return $this->success(new CityResource($city), 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        // Check if city has areas
        if ($city->areas()->exists()) {
            return $this->error('Cannot delete city with associated areas.', 422);
        }

        // Check if city has users
        if ($city->users()->exists()) {
            return $this->error('Cannot delete city with associated users.', 422);
        }

        $city->delete();

        return $this->success(null, 'City deleted successfully.');
    }
}
