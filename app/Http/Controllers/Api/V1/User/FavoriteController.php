<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Models\Favorite;
use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * List user's favorites.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->favorites()->with(['favoritable' => function ($morphTo) {
            $morphTo->morphWith([
                Resort::class => ['city', 'area', 'media'],
                Unit::class => ['resort', 'media'],
            ]);
        }]);

        if ($type = $request->filter['type'] ?? null) {
            $typeClass = match ($type) {
                'resort' => Resort::class,
                'unit' => Unit::class,
                default => null,
            };
            if ($typeClass) {
                $query->where('favoritable_type', $typeClass);
            }
        }

        return $this->successCollection(FavoriteResource::collection($query->latest()->paginate()));
    }

    /**
     * Add a favorite.
     */
    /**
     * Add a favorite.
     */
    public function store(StoreFavoriteRequest $request)
    {
        $modelClass = $request->favoritable_type === 'resort' ? Resort::class : Unit::class;
        $model = $modelClass::findOrFail($request->favoritable_id);

        $favorite = auth()->user()->favorite($model);

        return $this->created(
            new FavoriteResource($favorite->load('favoritable')),
            'Added to favorites.'
        );
    }

    /**
     * Remove a favorite.
     */
    public function destroy(Favorite $favorite)
    {
        $this->authorize('delete', $favorite);

        $favorite->delete();

        return $this->success(null, 'Removed from favorites.');
    }
}
