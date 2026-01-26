<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Unit\UpdateUnitStatusRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $units = QueryBuilder::for(Unit::class)
            ->with(['resort', 'media', 'resort.city'])
            ->allowedFilters([
                AllowedFilter::scope('active'), // Optional: to quickly see active only
                AllowedFilter::exact('resort_id'),
                AllowedFilter::callback('status', function ($query, $value) {
                    if (is_string($value) && $enum = GeneralStatus::tryFrom($value)) {
                         $query->where('status', $enum->value);
                    }
                }),
            ])
            ->defaultSort('-created_at')
            ->paginate();

        return $this->successCollection(UnitResource::collection($units));
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return $this->success(new UnitResource($unit->load(['resort', 'media'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitStatusRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return $this->success(new UnitResource($unit), 'Unit status updated successfully.');
    }
}
