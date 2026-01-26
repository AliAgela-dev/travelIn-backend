<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateResortStatusRequest;
use App\Http\Resources\ResortResource;
use App\Models\Resort;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ResortController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Resort::with(['city', 'area', 'owner', 'media']);

        $resorts = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::callback('status', function ($query, $value) {
                    if (is_string($value) && $enum = \App\Enums\ResortStatus::tryFrom($value)) {
                         $query->where('status', $enum->value);
                    }
                }),
                AllowedFilter::exact('city_id'),
                AllowedFilter::exact('owner_id'),
            ])
            ->defaultSort('-created_at')
            ->paginate();

        return $this->successCollection(ResortResource::collection($resorts));
    }

    /**
     * Display the specified resource.
     */
    public function show(Resort $resort)
    {
        return $this->success(new ResortResource($resort->load(['city', 'area', 'owner', 'media'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResortStatusRequest $request, Resort $resort)
    {
        $data = $request->validated();

        if ($request->status !== \App\Enums\ResortStatus::Rejected->value) {
            $data['rejection_reason'] = null;
        }

        $resort->update($data);

        return $this->success(
            new ResortResource($resort->load(['city', 'area', 'owner', 'media'])),
            'Resort status updated successfully.'
        );
    }
}
