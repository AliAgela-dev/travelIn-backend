<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Area;
use App\Models\City;
use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SearchController extends Controller
{
    /**
     * Global unit search across all resorts.
     *
     * GET /api/v1/user/search/units
     */
    public function units(Request $request)
    {
        $query = Unit::active()
            ->whereHas('resort', fn($q) => $q->active());

        // Apply availability filter if dates provided
        if ($request->check_in && $request->check_out) {
            $query->availableForDates($request->check_in, $request->check_out);
        }

        $units = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::scope('city_id', 'inCity'),
                AllowedFilter::scope('area_id', 'inArea'),
                AllowedFilter::scope('guests', 'minCapacity'),
                AllowedFilter::scope('min_price', 'priceRange'),
                AllowedFilter::scope('max_price', 'priceRange'),
                AllowedFilter::scope('min_rating', 'minRating'),
            ])
            ->allowedSorts(['price_per_night', 'capacity', 'reviews_avg_rating', 'created_at'])
            ->with(['resort.city', 'resort.area', 'media'])
            ->paginate();

        return $this->successCollection(UnitResource::collection($units));
    }

    /**
     * Get metadata for search filters.
     *
     * GET /api/v1/user/search/metadata
     */
    public function metadata()
    {
        $priceStats = Unit::withoutGlobalScope('withReviewStats')
            ->active()
            ->selectRaw('MIN(price_per_night) as min_price, MAX(price_per_night) as max_price')
            ->first();

        return $this->success([
            'price_range' => [
                'min' => (float) ($priceStats->min_price ?? 0),
                'max' => (float) ($priceStats->max_price ?? 1000),
            ],
        ]);
    }

    /**
     * Get search suggestions for autocomplete.
     *
     * GET /api/v1/user/search/suggestions?q=...
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return $this->success(['suggestions' => []]);
        }

        $cities = City::where('en_name', 'like', "%{$query}%")
            ->orWhere('ar_name', 'like', "%{$query}%")
            ->limit(3)
            ->get(['id', 'ar_name', 'en_name'])
            ->map(fn($c) => [
                'type' => 'city',
                'id' => $c->id,
                'ar_name' => $c->ar_name,
                'en_name' => $c->en_name,
            ]);

        $areas = Area::where('en_name', 'like', "%{$query}%")
            ->orWhere('ar_name', 'like', "%{$query}%")
            ->limit(3)
            ->get(['id', 'ar_name', 'en_name', 'city_id'])
            ->map(fn($a) => [
                'type' => 'area',
                'id' => $a->id,
                'ar_name' => $a->ar_name,
                'en_name' => $a->en_name,
                'city_id' => $a->city_id,
            ]);

        $resorts = Resort::active()
            ->search($query)
            ->limit(3)
            ->get(['id', 'ar_name', 'en_name'])
            ->map(fn($r) => [
                'type' => 'resort',
                'id' => $r->id,
                'ar_name' => $r->ar_name,
                'en_name' => $r->en_name,
            ]);

        return $this->success([
            'suggestions' => $cities->concat($areas)->concat($resorts)->values(),
        ]);
    }
}
