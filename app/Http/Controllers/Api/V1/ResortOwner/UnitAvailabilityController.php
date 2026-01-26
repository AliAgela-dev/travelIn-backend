<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitAvailability\StoreUnitAvailabilityRequest;
use App\Http\Resources\UnitAvailabilityResource;
use App\Models\Unit;
use App\Models\UnitAvailability;
use Illuminate\Http\Request;

class UnitAvailabilityController extends Controller
{
    /**
     * List blocked dates for a unit.
     */
    public function index(Unit $unit)
    {
        $this->authorizeUnitOwnership($unit);

        return $this->successCollection(UnitAvailabilityResource::collection($unit->blockedDates()->paginate()));
    }

    /**
     * Store a new blocked period.
     */
    public function store(StoreUnitAvailabilityRequest $request, Unit $unit)
    {
        $this->authorizeUnitOwnership($unit);

        $availability = $unit->blockedDates()->create($request->validated());

        return $this->created(new UnitAvailabilityResource($availability));
    }

    /**
     * Delete a blocked period.
     */
    public function destroy(Unit $unit, UnitAvailability $availability)
    {
        $this->authorizeUnitOwnership($unit);

        if ($availability->unit_id !== $unit->id) {
            abort(404);
        }

        $availability->delete();

        return $this->success(null, 'Blocked period removed.');
    }

    /**
     * Verify the authenticated user owns the unit's resort.
     */
    private function authorizeUnitOwnership(Unit $unit): void
    {
        $unit->load('resort');

        if ($unit->resort->owner_id !== auth()->id()) {
            abort(403);
        }
    }
}
