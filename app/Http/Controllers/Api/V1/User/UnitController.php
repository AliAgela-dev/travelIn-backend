<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitController extends Controller
{
    /**
     * List units for a specific resort with filters.
     */
    public function index(Request $request, Resort $resort)
    {
        $query = Unit::where('resort_id', $resort->id)->active();

        // Apply availability filter if dates provided
        if ($request->check_in && $request->check_out) {
            $query->availableForDates($request->check_in, $request->check_out);
        }

        $units = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::scope('guests', 'minCapacity'),
                AllowedFilter::scope('min_price', 'priceRange'),
                AllowedFilter::scope('max_price', 'priceRange'),
            ])
            ->allowedSorts(['price_per_night', 'capacity'])
            ->with('media')
            ->withAvg('reviews', 'rating')
            ->paginate();

        return $this->successCollection(UnitResource::collection($units));
    }

    /**
     * Show a specific unit.
     */
    public function show(Resort $resort, Unit $unit)
    {
        if ($unit->resort_id !== $resort->id || $unit->status !== \App\Enums\GeneralStatus::Active) {
            abort(404);
        }

        return $this->success(new UnitResource($unit->load('media')));
    }
}
