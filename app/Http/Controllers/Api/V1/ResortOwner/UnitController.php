<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Resort;
use App\Models\Unit;
use App\Services\MediaService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(protected MediaService $mediaService)
    {
    }

    /**
     * List units for a specific resort (owner only).
     */
    public function index(Resort $resort)
    {
        if ($resort->owner_id !== auth()->id()) {
            abort(403);
        }

        $units = $resort->units()->with('media')->paginate();

        return $this->successCollection(UnitResource::collection($units));
    }

    /**
     * Store a new unit for the resort.
     */
    public function store(StoreUnitRequest $request, Resort $resort)
    {
        if ($resort->owner_id !== auth()->id()) {
            abort(403);
        }

        $unit = $resort->units()->create($request->validated());

        // Attach media if provided
        if ($request->has('media_ids')) {
            foreach ($request->media_ids as $tempId) {
                $this->mediaService->moveMediaFromTemp($tempId, $unit, 'images');
            }
        }

        return $this->created(new UnitResource($unit->load('media')), 'Unit created successfully.');
    }

    /**
     * Show a specific unit.
     */
    public function show(Resort $resort, Unit $unit)
    {
        if ($resort->owner_id !== auth()->id() || $unit->resort_id !== $resort->id) {
            abort(403);
        }

        return $this->success(new UnitResource($unit->load('media')));
    }

    /**
     * Update a unit.
     */
    public function update(UpdateUnitRequest $request, Resort $resort, Unit $unit)
    {
        if ($resort->owner_id !== auth()->id() || $unit->resort_id !== $resort->id) {
            abort(403);
        }

        $unit->update($request->validated());

        // Attach new media if provided
        if ($request->has('media_ids')) {
            foreach ($request->media_ids as $tempId) {
                $this->mediaService->moveMediaFromTemp($tempId, $unit, 'images');
            }
        }

        return $this->success(new UnitResource($unit->load('media')), 'Unit updated successfully.');
    }

    /**
     * Delete a unit (check for bookings first).
     */
    public function destroy(Resort $resort, Unit $unit)
    {
        if ($resort->owner_id !== auth()->id() || $unit->resort_id !== $resort->id) {
            abort(403);
        }

        // Check for active/pending bookings (placeholder for Phase 17)
        // if ($unit->bookings()->whereIn('status', ['pending', 'confirmed'])->exists()) {
        //     abort(422, 'Cannot delete unit with active bookings.');
        // }

        $unit->delete();

        return $this->success(null, 'Unit deleted successfully.');
    }
}
