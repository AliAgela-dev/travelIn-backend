<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitPricing\StoreUnitPricingRequest;
use App\Http\Resources\UnitPricingResource;
use App\Models\Unit;
use App\Models\UnitPricing;
use Illuminate\Http\Request;

class UnitPricingController extends Controller
{
    /**
     * List pricing rules for a unit.
     */
    public function index(Unit $unit)
    {
        $this->authorizeUnitOwnership($unit);

        return $this->successCollection(UnitPricingResource::collection($unit->pricings()->paginate()));
    }

    /**
     * Store a new pricing rule.
     */
    public function store(StoreUnitPricingRequest $request, Unit $unit)
    {
        $this->authorizeUnitOwnership($unit);

        $pricing = $unit->pricings()->create($request->validated());

        return $this->created(new UnitPricingResource($pricing));
    }

    /**
     * Update an existing pricing rule.
     */
    public function update(StoreUnitPricingRequest $request, Unit $unit, UnitPricing $pricing)
    {
        $this->authorizeUnitOwnership($unit);

        if ($pricing->unit_id !== $unit->id) {
            abort(404);
        }

        $pricing->update($request->validated());

        return $this->success(new UnitPricingResource($pricing));
    }

    /**
     * Delete a pricing rule.
     */
    public function destroy(Unit $unit, UnitPricing $pricing)
    {
        $this->authorizeUnitOwnership($unit);

        if ($pricing->unit_id !== $unit->id) {
            abort(404);
        }

        $pricing->delete();

        return $this->success(null, 'Pricing rule deleted.');
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
