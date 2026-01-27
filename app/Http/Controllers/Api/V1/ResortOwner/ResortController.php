<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Enums\ResortStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resort\StoreResortRequest;
use App\Http\Requests\Resort\UpdateResortRequest;
use App\Http\Resources\ResortResource;
use App\Models\Resort;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;

class ResortController extends Controller
{
    public function __construct(protected MediaService $mediaService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resorts = auth()->user()->resorts()
            ->with(['city', 'area', 'media'])
            ->latest()
            ->paginate();

        return $this->successCollection(ResortResource::collection($resorts));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResortRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['status'] = ResortStatus::Pending;
            $data['owner_id'] = auth()->id();

            /** @var Resort $resort */
            $resort = Resort::create($data);

            if (!empty($data['media_ids'])) {
                foreach ($data['media_ids'] as $tempId) {
                    $this->mediaService->moveMediaFromTemp($tempId, $resort, 'images');
                }
            }

            return $this->created(
                new ResortResource($resort->load(['city', 'area', 'media'])),
                'Resort created and pending approval.'
            );
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Resort $resort)
    {
        if ($resort->owner_id !== auth()->id()) {
            return $this->forbidden('You do not own this resort.');
        }

        return $this->success(new ResortResource($resort->load(['city', 'area', 'media'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResortRequest $request, Resort $resort)
    {
        if ($resort->owner_id !== auth()->id()) {
            return $this->forbidden('You do not own this resort.');
        }

        return DB::transaction(function () use ($request, $resort) {
            $data = $request->validated();
            unset($data['status']);

            $resort->update($data);

            if (!empty($data['media_ids'])) {
                foreach ($data['media_ids'] as $tempId) {
                    $this->mediaService->moveMediaFromTemp($tempId, $resort, 'images');
                }
            }

            return $this->success(
                new ResortResource($resort->load(['city', 'area', 'media'])),
                'Resort updated.'
            );
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resort $resort)
    {
        if ($resort->owner_id !== auth()->id()) {
            return $this->forbidden('You do not own this resort.');
        }

        $resort->delete();

        return $this->success(null, 'Resort deleted.');
    }
}
